<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderHistory;
use App\Models\OrderItem;
use App\Models\ProductVariant;
use App\Models\InventoryLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Exception;
use App\Events\OrderStatusUpdated; // Đảm bảo bạn đã có Event này hoặc comment lại
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    // --- CẤU HÌNH TRẠNG THÁI ---

    // 1. Nhóm trạng thái "Giữ hàng" (Kho đã bị trừ và hàng chưa quay về kho)
    // Lưu ý: 'completed' vẫn tính là trừ kho (hàng ở chỗ khách)
    const STATUS_RESERVED = ['pending', 'processing', 'shipping', 'completed'];

    // 2. Nhóm trạng thái "Nhả hàng" (Hàng quay trở lại kho)
    const STATUS_RELEASED = ['cancelled', 'returned'];

    // 3. Quy tắc chuyển đổi trạng thái (State Machine)
    protected $allowedStatusTransitions = [
        'pending'    => ['processing', 'cancelled'],             // Chờ xử lý -> Đóng gói hoặc Hủy
        'processing' => ['shipping', 'cancelled'],               // Đóng gói -> Giao hàng hoặc Hủy
        // [ĐÃ SỬA] Cho phép từ đang giao -> hoàn về hoặc thất bại (coi là huỷ)
        'shipping'   => ['completed', 'returned', 'cancelled'],  
        'completed'  => [],                                      // Đã xong -> KẾT THÚC
        'cancelled'  => [],                                      // Đã hủy -> KẾT THÚC
        'returned'   => [],                                      // Đã trả -> KẾT THÚC
    ];

    protected $statusLabels = [
        'pending'    => 'Chờ xử lý',
        'processing' => 'Đang đóng gói',
        'shipping'   => 'Đang vận chuyển',
        'completed'  => 'Hoàn thành',
        'cancelled'  => 'Đã hủy',
        'returned'   => 'Đã trả hàng/Hoàn về',
    ];

    protected $paymentLabels = [
        'unpaid'   => 'Chưa thanh toán',
        'paid'     => 'Đã thanh toán',
        'refunded' => 'Đã hoàn tiền',
    ];

    /**
     * 1. DANH SÁCH ĐƠN HÀNG
     */
    public function index(Request $request)
    {
        $query = Order::with(['user', 'items']);

        if ($request->filled('keyword')) {
            $keyword = trim($request->keyword);
            $query->where(function($q) use ($keyword) {
                $q->where('order_code', 'like', "%{$keyword}%")
                  // Lưu ý: Cần thêm cast 'shipping_address' => 'array' trong Model Order
                  ->orWhere('shipping_address->contact_name', 'like', "%{$keyword}%")
                  ->orWhere('shipping_address->phone', 'like', "%{$keyword}%")
                  ->orWhereHas('user', function($subQ) use ($keyword) {
                      $subQ->where('full_name', 'like', "%{$keyword}%")
                           ->orWhere('email', 'like', "%{$keyword}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $orders = $query->latest()->paginate(10)->withQueryString();
        return view('admin.orders.index', compact('orders'));
    }

    /**
     * 2. HIỂN THỊ CHI TIẾT
     */
    public function show($id)
    {
        $order = Order::with([
            'items.variant.product',
            'user', 
            'histories' => function($q) { $q->latest(); },
            'histories.user'
        ])->findOrFail($id);

        return view('admin.orders.show', compact('order'));
    }

    /**
     * 3. TẠO ĐƠN HÀNG THỦ CÔNG
     */
    public function create()
    {
        return view('admin.orders.create'); 
    }

    public function store(Request $request)
    {
        // ... (Giữ nguyên logic validation như cũ)
        $validator = Validator::make($request->all(), [
            'contact_name'      => 'required|string|max:255',
            'phone'             => ['required', 'regex:/^(0)[0-9]{9}$/'],
            'address'           => 'required|string',
            'city'              => 'required|string',
            'district'          => 'required|string',
            'ward'              => 'required|string',
            'payment_method'    => ['required', Rule::in(['cod', 'banking', 'momo', 'vnpay'])],
            'items'             => 'required|array|min:1',
            'items.*.variant_id'=> 'required|exists:product_variants,id',
            'items.*.quantity'  => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try {
            $grandTotal = 0;
            $shippingFee = 30000; // Có thể thay bằng logic tính phí

            $order = Order::create([
                'order_code'      => 'ORD-' . strtoupper(Str::random(10)),
                'user_id'         => Auth::id(),
                'status'          => 'pending',
                'payment_status'  => 'unpaid',
                'payment_method'  => $request->payment_method,
                'shipping_address'=> [
                    'contact_name' => $request->contact_name,
                    'phone'        => $request->phone,
                    'address'      => $request->address,
                    'city'         => $request->city,
                    'district'     => $request->district,
                    'ward'         => $request->ward,
                ],
                'note'            => $request->note,
                'shipping_fee'    => $shippingFee,
                'total_amount'    => 0,
            ]);

            foreach ($request->items as $item) {
                // Lock để trừ kho an toàn
                $variant = ProductVariant::with('product')->lockForUpdate()->find($item['variant_id']);

                if (!$variant || !$variant->product) {
                    throw new Exception("Sản phẩm ID {$item['variant_id']} lỗi.");
                }
                if ($variant->stock_quantity < $item['quantity']) {
                    throw new Exception("Sản phẩm {$variant->product->name} không đủ hàng.");
                }

                $price = $variant->sale_price > 0 ? $variant->sale_price : $variant->original_price;
                $lineTotal = $price * $item['quantity'];
                $grandTotal += $lineTotal;

                OrderItem::create([
                    'order_id'           => $order->id,
                    'product_id'         => $variant->product_id,
                    'product_variant_id' => $variant->id,
                    'product_name'       => $variant->product->name,
                    'sku'                => $variant->sku,
                    'size'               => $variant->size,
                    'color'              => $variant->color,
                    'quantity'           => $item['quantity'],
                    'price'              => $price,
                    'total'              => $lineTotal,
                ]);

                $variant->decrement('stock_quantity', $item['quantity']);

                InventoryLog::create([
                    'product_variant_id' => $variant->id,
                    'user_id'            => Auth::id(),
                    'type'               => 'order_out',
                    'change_amount'      => -$item['quantity'],
                    'remaining_stock'    => $variant->stock_quantity,
                    'note'               => "Xuất kho đơn Admin: #{$order->order_code}"
                ]);
            }

            $order->update(['total_amount' => $grandTotal + $shippingFee]);

            OrderHistory::create([
                'order_id' => $order->id,
                'user_id'  => Auth::id(),
                'action'   => 'created',
                'description' => 'Đơn hàng được tạo thủ công bởi quản trị viên.'
            ]);

            DB::commit();
            return redirect()->route('admin.orders.index')->with('success', 'Tạo đơn hàng thành công!');

        } catch (Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Lỗi tạo đơn: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * 4. CẬP NHẬT TRẠNG THÁI (CORE LOGIC)
     * Đã sửa: Tự động set Paid khi Completed
     */
    public function updateStatus(Request $request, $id)
    {
        $order = Order::with(['items', 'user'])->findOrFail($id);

        $request->validate([
            'status'         => ['required', Rule::in(array_keys($this->statusLabels))],
            // Payment status có thể null nếu status = completed (vì sẽ tự set)
            'payment_status' => ['nullable', Rule::in(array_keys($this->paymentLabels))],
        ]);

        $currentStatus  = $order->status;
        $newStatus      = $request->status;
        $currentPayment = $order->payment_status;
        
        // --- LOGIC TỰ ĐỘNG CẬP NHẬT THANH TOÁN ---
        // Nếu admin chọn 'completed', bắt buộc payment phải là 'paid'.
        if ($newStatus === 'completed') {
            $newPayment = 'paid';
        } else {
            // Nếu không phải completed thì lấy theo request, nếu request null thì giữ nguyên cũ
            $newPayment = $request->payment_status ?? $currentPayment;
        }

        // --- 1. VALIDATE STATE MACHINE ---
        if ($newStatus !== $currentStatus) {
            $allowed = $this->allowedStatusTransitions[$currentStatus] ?? [];
            if (!in_array($newStatus, $allowed)) {
                return back()->with('error', "Quy trình không hợp lệ: Không thể chuyển từ '{$this->statusLabels[$currentStatus]}' sang '{$this->statusLabels[$newStatus]}'.");
            }
        }

        // --- 2. VALIDATE LOGIC KHÁC ---
        // Chặn hoàn tác thanh toán: Đã paid thì không được về unpaid
        if ($currentPayment === 'paid' && $newPayment === 'unpaid') {
            return back()->with('error', 'Lỗi: Đơn hàng đã thanh toán không thể hoàn tác về chưa thanh toán.');
        }

        DB::beginTransaction();
        try {
            // --- 3. XỬ LÝ KHO ---
            $isCurrentReserved = in_array($currentStatus, self::STATUS_RESERVED); // Pending, Processing, Shipping, Completed
            $isNewReleased     = in_array($newStatus, self::STATUS_RELEASED);     // Cancelled, Returned

            // CASE: Nhả hàng về kho (Hủy đơn hoặc Trả hàng)
            // Logic: Đang ở trạng thái trừ kho -> Chuyển sang trạng thái trả kho
            if ($isCurrentReserved && $isNewReleased) {
                foreach ($order->items as $item) {
                    // Lock dòng dữ liệu
                    $variant = ProductVariant::lockForUpdate()->find($item->product_variant_id);
                    
                    if ($variant) {
                        $variant->increment('stock_quantity', $item->quantity);
                        
                        InventoryLog::create([
                            'product_variant_id' => $variant->id,
                            'user_id'            => Auth::id(),
                            'type'               => ($newStatus == 'returned') ? 'order_returned' : 'order_canceled',
                            'change_amount'      => $item->quantity, // Số dương (cộng lại)
                            'remaining_stock'    => $variant->stock_quantity,
                            'note'               => "Hoàn kho: Đơn #{$order->order_code} chuyển sang {$this->statusLabels[$newStatus]}"
                        ]);
                    }
                }
            }
            // CASE: Cấm khôi phục đơn đã Hủy/Hoàn trả
            // Logic: Đang ở trạng thái trả kho -> Muốn quay lại trạng thái trừ kho (Vd: Cancelled -> Pending)
            elseif (!$isCurrentReserved && !$isNewReleased) {
                 // Nếu muốn chuyển lại thành Pending/Processing...
                 if (in_array($newStatus, self::STATUS_RESERVED)) {
                     throw new Exception("Hệ thống không cho phép khôi phục đơn đã Hủy/Hoàn trả để đảm bảo tính đúng đắn của kho. Vui lòng tạo đơn mới.");
                 }
            }

            // --- 4. UPDATE DATA ---
            $updateData = [
                'status' => $newStatus,
                'payment_status' => $newPayment
            ];

            // Nếu DB có cột paid_at, cập nhật thời gian
            /*
            if ($newPayment === 'paid' && $currentPayment !== 'paid') {
                $updateData['paid_at'] = now();
            }
            */

            $order->update($updateData);

            // --- 5. GHI LOG & EVENT ---
            $changes = [];
            if ($currentStatus !== $newStatus) $changes[] = "Trạng thái: {$this->statusLabels[$newStatus]}";
            if ($currentPayment !== $newPayment) $changes[] = "Thanh toán: {$this->paymentLabels[$newPayment]}";

            if (!empty($changes)) {
                $history = OrderHistory::create([
                    'order_id'    => $order->id,
                    'user_id'     => Auth::id(),
                    'action'      => 'update_status',
                    'description' => implode(' | ', $changes),
                ]);

                // Gửi event notify cho khách hàng (nếu có)
                if (class_exists(OrderStatusUpdated::class)) {
                    event(new OrderStatusUpdated($order, 'updated', $history));
                }
            }

            DB::commit();
            return back()->with('success', ($newStatus === 'completed') 
                ? 'Đơn hàng đã HOÀN THÀNH và cập nhật ĐÃ THANH TOÁN.' 
                : 'Cập nhật trạng thái thành công!');

        } catch (Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Lỗi hệ thống: ' . $e->getMessage());
        }
    }

    /**
     * 5. IN HÓA ĐƠN
     */
    public function print($id)
    {
        $order = Order::with(['items', 'user'])->findOrFail($id);
        return view('admin.orders.print', compact('order'));
    }
}