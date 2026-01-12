<?php

namespace App\Http\Controllers\Admin;

use App\Events\OrderStatusUpdated;
use App\Http\Controllers\Controller;
use App\Models\InventoryLog;
use App\Models\Order;
use App\Models\OrderHistory;
use App\Models\OrderItem;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    // Nhóm trạng thái giữ hàng (trừ kho)
    const STATUS_RESERVED = ['pending', 'processing', 'shipping', 'completed'];

    // Nhóm trạng thái nhả hàng (trả lại kho)
    const STATUS_RELEASED = ['cancelled', 'returned'];

    // Chuyển đổi trạng thái hợp lệ (State Machine)
    protected $allowedStatusTransitions = [
        'pending'    => ['processing', 'cancelled'],
        'processing' => ['shipping', 'cancelled'],
        'shipping'   => ['completed', 'returned'],
        'completed'  => [], // Đã hoàn tất thì không đổi nữa
        'cancelled'  => [], // Đã hủy thì không đổi nữa (trừ khi có logic khôi phục riêng)
        'returned'   => [],
    ];

    protected $allowedPaymentTransitions = [
        'unpaid'   => ['paid'],
        'paid'     => ['refunded'],
        'refunded' => [],
    ];

    protected $statusLabels = [
        'pending'    => 'Chờ xử lý',
        'processing' => 'Đang đóng gói',
        'shipping'   => 'Đang giao hàng',
        'completed'  => 'Hoàn thành',
        'cancelled'  => 'Đã hủy',
        'returned'   => 'Trả hàng',
    ];

    protected $paymentLabels = [
        'unpaid'   => 'Chưa thanh toán',
        'paid'     => 'Đã thanh toán',
        'refunded' => 'Đã hoàn tiền',
    ];

    /**
     * Danh sách đơn hàng
     */
    public function index(Request $request)
    {
        // Eager load user để tránh N+1 query, nhưng thông tin hiển thị chính vẫn lấy từ shipping_address
        $query = Order::with(['user', 'items']);

        if ($request->filled('keyword')) {
            $keyword = trim($request->keyword);
            $query->where(function($q) use ($keyword) {
                $q->where('order_code', 'like', "%{$keyword}%")
                  // Tìm trong JSON: Cú pháp này chỉ chạy trên MySQL 5.7+
                  ->orWhere('shipping_address->contact_name', 'like', "%{$keyword}%")
                  ->orWhere('shipping_address->phone', 'like', "%{$keyword}%")
                  // Tìm dự phòng trong bảng User nếu thông tin trong JSON bị thiếu
                  ->orWhereHas('user', function($subQ) use ($keyword) {
                      $subQ->where('full_name', 'like', "%{$keyword}%")
                           ->orWhere('email', 'like', "%{$keyword}%");
                  });
            });
        }

        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('payment_status')) $query->where('payment_status', $request->payment_status);

        $orders = $query->latest()->paginate(10)->withQueryString();

        return view('admin.orders.index', compact('orders'));
    }

    /**
     * Tạo đơn hàng (Checkout Admin)
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'contact_name'   => 'required|string|max:255',
            'phone'          => ['required', 'regex:/^(0)[0-9]{9}$/'],
            'address'        => 'required|string|max:255',
            'city'           => 'required|string|max:100',
            'district'       => 'required|string|max:100',
            'ward'           => 'required|string|max:100',
            'payment_method' => ['required', Rule::in(['cod', 'vnpay', 'momo', 'banking'])],
            'note'           => 'nullable|string|max:500',
            'items'          => 'required|array|min:1',
            'items.*.variant_id' => 'required|integer|exists:product_variants,id',
            'items.*.quantity'   => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try {
            $items = collect($request->items)->sortBy('variant_id')->values();

            // 1. Tạo Order Header
            $order = Order::create([
                'order_code'      => 'ORD-' . strtoupper(Str::random(10)),
                'user_id'         => Auth::id() ?? null,
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
                'shipping_fee'    => 30000, 
                'total_amount'    => 0,
            ]);

            $grandTotal = 0;

            // 2. Xử lý từng sản phẩm
            foreach ($items as $itemData) {
                $variant = ProductVariant::with('product')->lockForUpdate()->find($itemData['variant_id']);

                if (!$variant || !$variant->product) throw new Exception("Sản phẩm không hợp lệ.");
                if ($variant->product->trashed()) throw new Exception("Sản phẩm '{$variant->product->name}' đã ngừng kinh doanh.");
                if ($variant->stock_quantity < $itemData['quantity']) {
                    throw new Exception("Sản phẩm '{$variant->product->name}' không đủ hàng (Còn: {$variant->stock_quantity}).");
                }

                $price = $variant->sale_price > 0 ? $variant->sale_price : $variant->original_price;
                $lineTotal = $price * $itemData['quantity'];

                OrderItem::create([
                    'order_id'           => $order->id,
                    'product_id'         => $variant->product_id,
                    'product_variant_id' => $variant->id,
                    'product_name'       => $variant->product->name,
                    'sku'                => $variant->sku,
                    'quantity'           => $itemData['quantity'],
                    'price'              => $price,
                    'total'              => $lineTotal,
                    'size'               => $variant->size,
                    'color'              => $variant->color,
                ]);

                // Trừ tồn kho
                $variant->decrement('stock_quantity', $itemData['quantity']);

                // Ghi log kho
                InventoryLog::create([
                    'product_variant_id' => $variant->id,
                    'user_id'            => Auth::id(),
                    'change_amount'      => -$itemData['quantity'],
                    'remaining_stock'    => $variant->stock_quantity,
                    'type'               => 'order_out',
                    'note'               => 'Xuất kho cho đơn hàng #' . $order->order_code,
                ]);

                $grandTotal += $lineTotal;
            }

            // Cập nhật tổng tiền
            $order->update(['total_amount' => $grandTotal + $order->shipping_fee]);

            // Ghi lịch sử đơn hàng
            $history = OrderHistory::create([
                'order_id'    => $order->id,
                'user_id'     => Auth::id(),
                'action'      => 'created',
                'description' => 'Đơn hàng được khởi tạo thủ công bởi quản trị viên.',
            ]);
            $history->load('user');

            DB::commit();

            // --- [REALTIME] Phát sự kiện ---
            event(new OrderStatusUpdated($order, 'created', $history));

            return redirect()->route('admin.orders.index')
                ->with('success', 'Đơn hàng #' . $order->order_code . ' đã được tạo thành công!');

        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Order Create Error: " . $e->getMessage());
            return back()->with('error', 'Lỗi tạo đơn hàng: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Cập nhật trạng thái đơn hàng (Logic chính)
     */
    public function updateStatus(Request $request, $id)
    {
        $order = Order::with('items')->findOrFail($id);

        $request->validate([
            'status'         => ['required', Rule::in(array_keys($this->statusLabels))],
            'payment_status' => ['required', Rule::in(array_keys($this->paymentLabels))],
        ]);

        $currentStatus  = $order->status;
        $newStatus      = $request->status;
        $currentPayment = $order->payment_status;
        $newPayment     = $request->payment_status;

        // Validation Transition
        if (!in_array($newStatus, $this->allowedStatusTransitions[$currentStatus]) && $newStatus !== $currentStatus) {
            return back()->with('error', "Không thể chuyển từ '{$this->statusLabels[$currentStatus]}' sang '{$this->statusLabels[$newStatus]}'");
        }

        if (!in_array($newPayment, $this->allowedPaymentTransitions[$currentPayment]) && $newPayment !== $currentPayment) {
            return back()->with('error', "Không thể chuyển thanh toán từ '{$this->paymentLabels[$currentPayment]}' sang '{$this->paymentLabels[$newPayment]}'");
        }

        DB::beginTransaction();
        try {
            // Logic trừ/nhả kho
            $isCurrentReserved = in_array($currentStatus, self::STATUS_RESERVED);
            $isNewReserved     = in_array($newStatus, self::STATUS_RESERVED);

            if ($isCurrentReserved && !$isNewReserved) {
                // Nhả hàng (VD: Đang xử lý -> Hủy)
                foreach ($order->items as $item) {
                    ProductVariant::where('id', $item->product_variant_id)
                        ->increment('stock_quantity', $item->quantity);
                    // (Optional) Ghi log InventoryLog 'order_return' tại đây nếu cần
                }
            } elseif (!$isCurrentReserved && $isNewReserved) {
                // Giữ hàng lại (VD: Đã hủy -> Khôi phục về Đang xử lý)
                foreach ($order->items as $item) {
                    $variant = ProductVariant::lockForUpdate()->find($item->product_variant_id);
                    if (!$variant || $variant->stock_quantity < $item->quantity) {
                        throw new Exception("Không thể khôi phục: Sản phẩm '{$item->product_name}' không đủ hàng.");
                    }
                }
                foreach ($order->items as $item) {
                    ProductVariant::where('id', $item->product_variant_id)
                        ->decrement('stock_quantity', $item->quantity);
                }
            }

            // Update DB
            $updateData = [
                'status' => $newStatus, 
                'payment_status' => $newPayment
            ];
            
            // Cập nhật thời gian thanh toán nếu mới chuyển sang paid
            if ($newPayment === 'paid' && $currentPayment !== 'paid') {
                $updateData['paid_at'] = now();
            }

            $order->update($updateData);

            // --- [FIX LOGIC DESCRIPTION] ---
            // Tạo nội dung thông báo thân thiện cho Realtime
            $description = '';

            if ($newStatus === 'completed') {
                $description = 'Đơn hàng đã hoàn tất';
            } elseif ($newStatus === 'cancelled') {
                $description = 'Đơn hàng đã bị hủy';
            } elseif ($newStatus === 'shipping') {
                $description = 'Đơn hàng đã được giao cho đơn vị vận chuyển';
            } elseif ($newStatus === 'confirmed') {
                $description = 'Đơn hàng đã được xác nhận';
            } else {
                // Nội dung mặc định
                $description = "Cập nhật trạng thái: {$this->statusLabels[$newStatus]}";
            }

            // Nếu trạng thái đơn không đổi, chỉ đổi thanh toán
            if ($newStatus === $currentStatus && $newPayment !== $currentPayment) {
                 $description = "Cập nhật thanh toán: {$this->paymentLabels[$newPayment]}";
            }

            // Lưu History
            $history = OrderHistory::create([
                'order_id'    => $order->id,
                'user_id'     => Auth::id(),
                'action'      => $newStatus, // Action lưu mã trạng thái (completed/cancelled...)
                'description' => $description, // Description hiển thị ra view
            ]);

            $history->load('user');

            DB::commit();

            // --- [FIX EVENT DISPATCH] ---
            // Gọi đúng construct: ($order, $action, $history)
            event(new OrderStatusUpdated($order, 'updated', $history));

            Log::info("Order #{$order->order_code} updated to {$newStatus}");

            return back()->with('success', 'Cập nhật trạng thái thành công!');

        } catch (Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Lỗi hệ thống: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        // Load histories sắp xếp mới nhất lên đầu để hiển thị timeline đúng
        $order = Order::with(['items.variant', 'user', 'histories' => function($q) {
            $q->latest(); 
        }, 'histories.user'])->findOrFail($id);

        return view('admin.orders.show', compact('order'));
    }

    public function print($id)
    {
        $order = Order::with(['items.variant', 'user'])->findOrFail($id);
        return view('admin.orders.print', compact('order'));
    }
}