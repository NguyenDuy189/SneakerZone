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

// Sự kiện (Nếu bạn chưa có class này, hãy tạo hoặc comment lại dòng gọi event)
use App\Events\OrderStatusUpdated;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    // --- CẤU HÌNH TRẠNG THÁI ---

    // 1. Nhóm trạng thái "Giữ hàng" (Kho đang bị trừ)
    const STATUS_RESERVED = ['pending', 'processing', 'shipping', 'completed'];

    // 2. Nhóm trạng thái "Nhả hàng" (Hàng trả về kho)
    const STATUS_RELEASED = ['cancelled', 'returned'];

    // 3. Quy tắc chuyển đổi trạng thái (State Machine)
    protected $allowedStatusTransitions = [
        'pending'    => ['processing', 'cancelled'],             // Chờ xử lý -> Đóng gói hoặc Hủy
        'processing' => ['shipping', 'cancelled'],               // Đóng gói -> Giao hàng hoặc Hủy
        'shipping'   => ['completed', 'cancelled'],              // Giao hàng -> Xong hoặc Hủy (NẾU giao thất bại coi như Hủy)
        'completed'  => [],                                      // Đã xong -> KẾT THÚC (Admin không được quyền tự ý chuyển sang Trả hàng)
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
        $query = Order::with(['user', 'items']); // Eager loading để giảm query

        // Tìm kiếm nâng cao
        if ($request->filled('keyword')) {
            $keyword = trim($request->keyword);
            $query->where(function($q) use ($keyword) {
                $q->where('order_code', 'like', "%{$keyword}%")
                  // Tìm trong JSON MySQL (Yêu cầu MySQL 5.7+)
                  ->orWhere('shipping_address->contact_name', 'like', "%{$keyword}%")
                  ->orWhere('shipping_address->phone', 'like', "%{$keyword}%")
                  // Tìm User liên quan
                  ->orWhereHas('user', function($subQ) use ($keyword) {
                      $subQ->where('full_name', 'like', "%{$keyword}%")
                           ->orWhere('email', 'like', "%{$keyword}%");
                  });
            });
        }

        // Bộ lọc
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
            'items.variant.product', // Load sâu để lấy ảnh/tên sản phẩm gốc
            'user', 
            'histories' => function($q) { $q->latest(); }, // Lịch sử mới nhất lên đầu
            'histories.user'
        ])->findOrFail($id);

        return view('admin.orders.show', compact('order'));
    }

    /**
     * 3. TẠO ĐƠN HÀNG THỦ CÔNG (ADMIN TẠO)
     * Lưu ý: Cần mở route 'create' và 'store' trong web.php nếu chưa mở
     */
    public function create()
    {
        // Logic lấy danh sách sản phẩm để chọn (nếu cần view tạo đơn)
        // Đây là ví dụ trả về view
        return view('admin.orders.create'); 
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'contact_name'       => 'required|string|max:255',
            'phone'              => ['required', 'regex:/^(0)[0-9]{9}$/'],
            'address'            => 'required|string',
            'city'               => 'required|string',
            'district'           => 'required|string',
            'ward'               => 'required|string',
            'payment_method'     => ['required', Rule::in(['cod', 'banking', 'momo', 'vnpay'])],
            'items'              => 'required|array|min:1',
            'items.*.variant_id' => 'required|exists:product_variants,id',
            'items.*.quantity'   => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try {
            // Tính toán tổng tiền
            $grandTotal = 0;
            $orderItemsData = [];
            
            // Lấy phí ship (Hardcode hoặc lấy từ setting)
            $shippingFee = 30000; 

            // 1. Tạo Header Order trước
            $order = Order::create([
                'order_code'      => 'ORD-' . strtoupper(Str::random(10)),
                'user_id'         => Auth::id(), // Admin tạo, hoặc gán cho user khách
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
                'total_amount'    => 0, // Cập nhật sau
            ]);

            // 2. Xử lý Items & Kho
            foreach ($request->items as $item) {
                // Lock row để tránh race condition
                $variant = ProductVariant::with('product')->lockForUpdate()->find($item['variant_id']);

                if (!$variant || !$variant->product) {
                    throw new Exception("Sản phẩm ID {$item['variant_id']} không tồn tại.");
                }

                // Check tồn kho
                if ($variant->stock_quantity < $item['quantity']) {
                    throw new Exception("Sản phẩm {$variant->product->name} (SKU: {$variant->sku}) không đủ hàng. Còn lại: {$variant->stock_quantity}");
                }

                $price = $variant->sale_price > 0 ? $variant->sale_price : $variant->original_price;
                $lineTotal = $price * $item['quantity'];
                $grandTotal += $lineTotal;

                // Tạo Order Item
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

                // Trừ kho
                $variant->decrement('stock_quantity', $item['quantity']);

                // Log kho
                InventoryLog::create([
                    'product_variant_id' => $variant->id,
                    'user_id'            => Auth::id(),
                    'type'               => 'order_out',
                    'change_amount'      => -$item['quantity'],
                    'remaining_stock'    => $variant->stock_quantity,
                    'note'               => "Xuất kho đơn hàng Admin: #{$order->order_code}"
                ]);
            }

            // Cập nhật tổng tiền
            $order->update(['total_amount' => $grandTotal + $shippingFee]);

            // Ghi lịch sử
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
     * 4. CẬP NHẬT TRẠNG THÁI (ADMIN)
     * Đã chặn quyền chuyển sang 'returned'
     */
    /**
     * 4. CẬP NHẬT TRẠNG THÁI (ADMIN)
     * Đã bổ sung logic chặt chẽ cho Online & COD
     */
    public function updateStatus(Request $request, $id)
    {
        $order = Order::with(['items', 'user'])->findOrFail($id);

        $request->validate([
            'status'         => ['required', Rule::in(array_keys($this->statusLabels))],
            'payment_status' => ['required', Rule::in(array_keys($this->paymentLabels))],
        ]);

        $currentStatus  = $order->status;
        $newStatus      = $request->status;
        $currentPayment = $order->payment_status;
        $newPayment     = $request->payment_status;

        // --- 1. VALIDATE STATE MACHINE ---
        if ($newStatus !== $currentStatus) {
            $allowed = $this->allowedStatusTransitions[$currentStatus] ?? [];
            if (!in_array($newStatus, $allowed)) {
                return back()->with('error', "Quy trình không hợp lệ: Không thể chuyển từ '{$this->statusLabels[$currentStatus]}' sang '{$this->statusLabels[$newStatus]}'.");
            }
        }

        // --- 2. VALIDATE LOGIC THANH TOÁN ---

        // RULE A: Chặn hoàn tác thanh toán
        if ($currentPayment === 'paid' && $newPayment === 'unpaid') {
            return back()->with('error', 'Lỗi: Đơn hàng đã thanh toán không thể hoàn tác về chưa thanh toán.');
        }

        // RULE B: Đơn Online phải thanh toán trước khi Giao hàng
        if ($newStatus === 'shipping' && $order->payment_method !== 'cod') {
            if ($currentPayment !== 'paid' && $newPayment !== 'paid') {
                return back()->with('error', 'Lỗi: Đơn hàng thanh toán Online (Banking/VNPAY) bắt buộc phải "Đã thanh toán" trước khi giao vận chuyển.');
            }
        }

        // RULE C: Đơn COD phải thanh toán khi Hoàn thành
        if ($newStatus === 'completed' && $order->payment_method === 'cod') {
            if ($newPayment !== 'paid') {
                return back()->with('error', 'Lỗi: Đơn hàng COD khi "Hoàn thành" bắt buộc phải chọn trạng thái thanh toán là "Đã thanh toán".');
            }
        }

        // RULE D: Đơn Online chưa thanh toán không thể Hoàn thành
        if ($newStatus === 'completed' && $order->payment_method !== 'cod') {
             if ($newPayment !== 'paid') {
                 return back()->with('error', 'Lỗi: Đơn hàng Online chưa thanh toán không thể thiết lập Hoàn thành.');
             }
        }

        DB::beginTransaction();
        try {
            // --- 3. XỬ LÝ KHO ---
            $isCurrentReserved = in_array($currentStatus, self::STATUS_RESERVED);
            $isNewReserved     = in_array($newStatus, self::STATUS_RESERVED);

            // CASE 1: Nhả hàng về kho (Hủy đơn)
            if ($isCurrentReserved && !$isNewReserved) {
                foreach ($order->items as $item) {
                    $variant = ProductVariant::find($item->product_variant_id);
                    if ($variant) {
                        $variant->increment('stock_quantity', $item->quantity);
                        
                        InventoryLog::create([
                            'product_variant_id' => $variant->id,
                            'user_id'            => Auth::id(),
                            'type'               => 'order_canceled',
                            'change_amount'      => $item->quantity,
                            'remaining_stock'    => $variant->stock_quantity,
                            'note'               => "Hoàn kho: Đơn #{$order->order_code} đã bị HỦY"
                        ]);
                    }
                }
            }
            // CASE 2: Không cho phép khôi phục đơn đã hủy
            elseif (!$isCurrentReserved && $isNewReserved) {
                 throw new Exception("Hệ thống không cho phép khôi phục đơn đã Hủy. Vui lòng tạo đơn mới.");
            }

            // --- 4. UPDATE DATA ---
            $updateData = [
                'status' => $newStatus,
                'payment_status' => $newPayment
            ];

            // [FIX LỖI] Bỏ cập nhật paid_at vì DB không có cột này
            /* if ($newPayment === 'paid' && $currentPayment !== 'paid') {
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

                if (class_exists(OrderStatusUpdated::class)) {
                    event(new OrderStatusUpdated($order, 'updated', $history));
                }
            }

            DB::commit();
            return back()->with('success', 'Cập nhật trạng thái thành công!');

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