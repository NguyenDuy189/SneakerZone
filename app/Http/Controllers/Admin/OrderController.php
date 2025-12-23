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
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    // Nhóm trạng thái giữ hàng
    const STATUS_RESERVED = ['pending', 'processing', 'shipping', 'completed'];

    // Nhóm trạng thái nhả hàng
    const STATUS_RELEASED = ['cancelled', 'returned'];

    // Chuyển đổi trạng thái hợp lệ (State Machine)
    protected $allowedStatusTransitions = [
        'pending'    => ['processing', 'cancelled'],
        'processing' => ['shipping', 'cancelled'],
        'shipping'   => ['completed', 'returned'],
        'completed'  => [],
        'cancelled'  => [],
        'returned'   => [],
    ];

    protected $allowedPaymentTransitions = [
        'unpaid'   => ['paid'],
        'paid'     => ['refunded'],
        'refunded' => [],
    ];

    // Mapping status sang tiếng Việt
    protected $statusLabels = [
        'pending'    => 'Chờ xử lý',
        'processing' => 'Đang đóng gói',
        'shipping'   => 'Đang giao hàng',
        'completed'  => 'Hoàn thành',
        'cancelled'  => 'Đã hủy',
        'returned'   => 'Trả hàng',
    ];

    // Mapping payment_status sang tiếng Việt
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
        $query = Order::with(['user', 'items']);

        if ($request->filled('keyword')) {
            $keyword = trim($request->keyword);
            $query->where(function($q) use ($keyword) {
                $q->where('order_code', 'like', "%{$keyword}%")
                  ->orWhere('shipping_address->contact_name', 'like', "%{$keyword}%")
                  ->orWhere('shipping_address->phone', 'like', "%{$keyword}%");
            });
        }

        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('payment_status')) $query->where('payment_status', $request->payment_status);

        $orders = $query->latest()->paginate(10)->withQueryString();

        return view('admin.orders.index', compact('orders'));
    }

    /**
     * Tạo đơn hàng (Checkout)
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
            // [DEADLOCK PREVENTION] Sắp xếp items theo ID để tránh khóa chéo
            $items = collect($request->items)->sortBy('variant_id')->values();

            // 1. Tạo Đơn hàng (Order Header)
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
                'total_amount'    => 0, // Tính sau
            ]);

            $grandTotal = 0;

            // 2. Xử lý từng sản phẩm
            foreach ($items as $itemData) {
                // [LOCKING] Khóa dòng dữ liệu để đảm bảo không bị mua trùng lúc cao điểm
                $variant = ProductVariant::with('product')->lockForUpdate()->find($itemData['variant_id']);

                if (!$variant || !$variant->product) throw new Exception("Sản phẩm không hợp lệ hoặc đã bị xóa.");
                if ($variant->product->trashed()) throw new Exception("Sản phẩm '{$variant->product->name}' đã ngừng kinh doanh.");
                if ($variant->stock_quantity < $itemData['quantity']) {
                    throw new Exception("Sản phẩm '{$variant->product->name}' không đủ hàng (Còn: {$variant->stock_quantity}).");
                }

                // [SNAPSHOT] Lấy giá tại thời điểm mua
                $price = $variant->sale_price > 0 ? $variant->sale_price : $variant->original_price;
                $lineTotal = $price * $itemData['quantity'];

                // Tạo Order Item
                OrderItem::create([
                    'order_id'           => $order->id,
                    'product_id'         => $variant->product_id,
                    'product_variant_id' => $variant->id,
                    'product_name'       => $variant->product->name, // Snapshot tên
                    'sku'                => $variant->sku,          // Snapshot SKU
                    'quantity'           => $itemData['quantity'],
                    'price'              => $price,                 // Snapshot giá
                    'total'              => $lineTotal,
                    'size'               => $variant->size ?? null,
                    'color'              => $variant->color ?? null,
                ]);

                // Trừ tồn kho
                $variant->decrement('stock_quantity', $itemData['quantity']);

                // Ghi log kho (Inventory Log) - QUAN TRỌNG
                // Cần đảm bảo model InventoryLog đã được import
                \App\Models\InventoryLog::create([
                    'product_variant_id' => $variant->id,
                    'user_id'            => Auth::id() ?? null,
                    'change_amount'      => -$itemData['quantity'], // Số âm vì xuất kho
                    'remaining_stock'    => $variant->stock_quantity, // Số lượng sau khi trừ
                    'type'               => 'order_out', // Loại: Xuất bán
                    'note'               => 'Xuất kho cho đơn hàng #' . $order->order_code,
                ]);

                $grandTotal += $lineTotal;
            }

            // Cập nhật tổng tiền đơn hàng
            $order->update(['total_amount' => $grandTotal + $order->shipping_fee]);

            // Ghi lịch sử đơn hàng (Order History)
            $history = \App\Models\OrderHistory::create([
                'order_id'    => $order->id,
                'user_id'     => Auth::id(),
                'action'      => 'created',
                'description' => 'Đơn hàng mới được khởi tạo thủ công.',
            ]);
            $history->load('user');

            DB::commit();

            // --- [REALTIME] Phát sự kiện ---
            // Gọi Event dùng chung mà chúng ta đã sửa ở bước trước
            \App\Events\OrderStatusUpdated::dispatch($order, 'created', $history);

            return redirect()->route('admin.orders.index')
                ->with('success', 'Đơn hàng #' . $order->order_code . ' đã được tạo thành công!');

        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Order Create Error: " . $e->getMessage());
            return back()->with('error', 'Lỗi tạo đơn hàng: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Cập nhật trạng thái đơn hàng
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

        // Validation state machine
        if (!in_array($newStatus, $this->allowedStatusTransitions[$currentStatus]) && $newStatus !== $currentStatus) {
            return back()->with('error', "Đơn hàng hiện đang '{$this->statusLabels[$currentStatus]}', không thể chuyển sang '{$this->statusLabels[$newStatus]}'");
        }

        if (!in_array($newPayment, $this->allowedPaymentTransitions[$currentPayment]) && $newPayment !== $currentPayment) {
            return back()->with('error', "Trạng thái thanh toán hiện tại '{$this->paymentLabels[$currentPayment]}', không thể chuyển sang '{$this->paymentLabels[$newPayment]}'");
        }

        DB::beginTransaction();
        try {
            $isCurrentReserved = in_array($currentStatus, self::STATUS_RESERVED);
            $isNewReserved     = in_array($newStatus, self::STATUS_RESERVED);

            if ($isCurrentReserved && !$isNewReserved) {
                foreach ($order->items as $item) {
                    ProductVariant::where('id', $item->product_variant_id)
                        ->increment('stock_quantity', $item->quantity);
                }
            } elseif (!$isCurrentReserved && $isNewReserved) {
                foreach ($order->items as $item) {
                    $variant = ProductVariant::lockForUpdate()->find($item->product_variant_id);
                    if (!$variant || $variant->stock_quantity < $item->quantity) {
                        throw new Exception("Không thể chuyển trạng thái: Sản phẩm '{$item->product_name}' hiện không đủ hàng.");
                    }
                }
                foreach ($order->items as $item) {
                    ProductVariant::where('id', $item->product_variant_id)
                        ->decrement('stock_quantity', $item->quantity);
                }
            }

            $order->update(['status' => $newStatus, 'payment_status' => $newPayment]);

            $description = "Thay đổi trạng thái: [{$this->statusLabels[$currentStatus]}] -> [{$this->statusLabels[$newStatus]}]";
            if ($currentPayment !== $newPayment) {
                $description .= ". Thanh toán: [{$this->paymentLabels[$currentPayment]}] -> [{$this->paymentLabels[$newPayment]}]";
            }

            $history = OrderHistory::create([
                'order_id'    => $order->id,
                'user_id'     => Auth::id(),
                'action'      => 'update_status',
                'description' => $description,
            ]);

            $history->load('user');

            DB::commit();

            OrderStatusUpdated::dispatch($order, $history);

            Log::info("Order #{$order->order_code} updated & broadcasted.");

            return back()->with('success', 'Cập nhật trạng thái thành công!');

        } catch (Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Lỗi hệ thống: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $order = Order::with(['items.variant', 'user', 'histories.user'])->findOrFail($id);
        return view('admin.orders.show', compact('order'));
    }

    public function print($id)
    {
        $order = Order::with(['items.variant', 'user'])->findOrFail($id);
        return view('admin.orders.print', compact('order'));
    }
}
