<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
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
    // Nhóm trạng thái "Giữ hàng" (Đã trừ tồn kho)
    const STATUS_RESERVED = ['pending', 'processing', 'shipping', 'completed'];
    
    // Nhóm trạng thái "Nhả hàng" (Trả lại tồn kho)
    const STATUS_RELEASED = ['cancelled', 'returned'];

    /**
     * Danh sách đơn hàng
     */
    public function index(Request $request)
    {
        $query = Order::with(['user', 'items']);

        // 1. Tìm kiếm
        if ($request->filled('keyword')) {
            $keyword = trim($request->keyword);
            $query->where(function($q) use ($keyword) {
                $q->where('order_code', 'like', "%{$keyword}%")
                  ->orWhere('shipping_address->contact_name', 'like', "%{$keyword}%")
                  ->orWhere('shipping_address->phone', 'like', "%{$keyword}%");
            });
        }

        // 2. Filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        $orders = $query->latest()->paginate(10)->withQueryString();

        return view('admin.orders.index', compact('orders'));
    }

    /**
     * TẠO ĐƠN HÀNG (CHECKOUT)
     * Bảo vệ: Deadlock, Race Condition, Snapshot giá
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'contact_name'   => 'required|string|max:255',
            'phone'          => ['required', 'regex:/^(0)[0-9]{9}$/'],
            'address'        => 'required|string|max:255',
            'city'           => 'required|string',
            'district'       => 'required|string',
            'ward'           => 'required|string',
            'payment_method' => ['required', Rule::in(['cod', 'vnpay', 'momo', 'banking'])],
            'note'           => 'nullable|string|max:500',
            'items'          => 'required|array|min:1',
            'items.*.variant_id' => 'required|integer|exists:product_variants,id',
            'items.*.quantity'   => 'required|integer|min:1',
        ]);

        if ($validator->fails()) return back()->withErrors($validator)->withInput();

        DB::beginTransaction();
        try {
            // [DEADLOCK PREVENTION] Sắp xếp items theo ID tăng dần để tránh khóa chéo
            $items = collect($request->items)->sortBy('variant_id')->values();

            // 1. Tạo Header đơn hàng
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

            // 2. Xử lý từng item
            foreach ($items as $itemData) {
                // [LOCKING] Khóa dòng dữ liệu (Pessimistic Locking)
                $variant = ProductVariant::with('product')
                    ->where('id', $itemData['variant_id'])
                    ->lockForUpdate()
                    ->first();

                // Validation sâu
                if (!$variant) throw new Exception("Sản phẩm không tồn tại.");
                if (!$variant->product) throw new Exception("Dữ liệu sản phẩm gốc bị lỗi.");
                
                // Check Soft Delete: Không bán hàng đã xóa
                if ($variant->product->trashed()) {
                     throw new Exception("Sản phẩm '{$variant->product->name}' đã ngừng kinh doanh.");
                }

                // Check Tồn kho
                if ($variant->stock_quantity < $itemData['quantity']) {
                    throw new Exception("Sản phẩm '{$variant->product->name}' không đủ hàng (Còn: {$variant->stock_quantity}).");
                }

                // [SNAPSHOT] Lấy giá tại thời điểm mua
                $price = $variant->sale_price > 0 ? $variant->sale_price : $variant->original_price;
                $lineTotal = $price * $itemData['quantity'];

                // Tạo OrderItem (Lưu cứng thông tin)
                OrderItem::create([
                    'order_id'           => $order->id,
                    'product_id'         => $variant->product_id,
                    'product_variant_id' => $variant->id,
                    'product_name'       => $variant->product->name, // Snapshot tên
                    'sku'                => $variant->sku,           // Snapshot SKU
                    'quantity'           => $itemData['quantity'],
                    'price'              => $price,                  // Snapshot giá
                    'total'              => $lineTotal,
                    // Giả sử có size/color
                    'size'               => $variant->size ?? null,
                    'color'              => $variant->color ?? null,
                ]);

                // Trừ kho
                $variant->decrement('stock_quantity', $itemData['quantity']);
                $grandTotal += $lineTotal;
            }

            // Cập nhật tổng tiền
            $order->update(['total_amount' => $grandTotal + $order->shipping_fee]);

            DB::commit();

            return redirect()->route('home')->with('success', 'Đặt hàng thành công! Mã đơn: ' . $order->order_code);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Checkout Error: " . $e->getMessage());
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    /**
     * CẬP NHẬT TRẠNG THÁI (STATE MACHINE)
     * Bảo vệ: Logic thanh toán, Logic hoàn kho
     */
    public function updateStatus(Request $request, $id)
    {
        $order = Order::with('items')->findOrFail($id);

        $request->validate([
            'status'         => ['required', Rule::in(array_merge(self::STATUS_RESERVED, self::STATUS_RELEASED))],
            'payment_status' => ['required', Rule::in(['unpaid', 'paid', 'refunded'])],
        ]);

        $currentStatus  = $order->status;
        $newStatus      = $request->status;
        $currentPayment = $order->payment_status;
        $newPayment     = $request->payment_status;

        // --- 1. VALIDATION LAYER (LOGIC NGHIỆP VỤ) ---
        // Để hiểu rõ hơn về luồng chuyển đổi trạng thái bên dưới, hãy xem sơ đồ sau:
        // 

        // RULE 1: Không được phép quay lui từ "Paid" về "Unpaid"
        if ($currentPayment === 'paid' && $newPayment === 'unpaid') {
            return back()->with('error', 'Lỗi bảo mật: Đơn hàng ĐÃ THANH TOÁN không được phép chuyển về chưa thanh toán!');
        }

        // RULE 2: Muốn "Completed" thì bắt buộc phải "Paid"
        if ($newStatus === 'completed' && $newPayment !== 'paid') {
            return back()->with('error', 'Lỗi logic: Đơn hàng phải THANH TOÁN xong mới được phép Hoàn thành.');
        }

        // RULE 3: "Refunded" chỉ dành cho đơn Hủy/Trả hàng
        if ($newPayment === 'refunded' && !in_array($newStatus, self::STATUS_RELEASED)) {
            return back()->with('error', 'Lỗi logic: Chỉ được hoàn tiền khi đơn hàng bị Hủy hoặc Trả hàng.');
        }

        // RULE 4: Đơn đã kết thúc (Completed/Returned) không nên bị sửa đổi trạng thái (trừ khi Admin cố tình can thiệp Hủy)
        if (in_array($currentStatus, ['completed', 'returned']) && $newStatus !== $currentStatus) {
            // Tùy chính sách, ở đây mình chặn luôn cho an toàn
            return back()->with('error', "Đơn hàng đã kết thúc ($currentStatus). Không thể thay đổi trạng thái.");
        }

        DB::beginTransaction();
        try {
            // --- 2. INVENTORY LAYER (XỬ LÝ KHO) ---
            
            $stockAction = 'none'; // 'restock' (trả kho), 'deduct' (trừ kho), 'none'

            $isCurrentReserved = in_array($currentStatus, self::STATUS_RESERVED);
            $isNewReserved     = in_array($newStatus, self::STATUS_RESERVED);

            // Logic xác định hành động kho
            if ($isCurrentReserved && !$isNewReserved) {
                // Đang giữ hàng -> Hủy/Trả => TRẢ LẠI KHO
                $stockAction = 'restock';
            } elseif (!$isCurrentReserved && $isNewReserved) {
                // Đã hủy -> Khôi phục lại (Pending/Processing) => TRỪ KHO LẠI
                $stockAction = 'deduct';
            }

            // Thực thi hành động kho
            if ($stockAction === 'restock') {
                foreach ($order->items as $item) {
                    ProductVariant::where('id', $item->product_variant_id)
                        ->increment('stock_quantity', $item->quantity);
                }
            } elseif ($stockAction === 'deduct') {
                // Check đủ hàng trước khi trừ
                foreach ($order->items as $item) {
                    // Dùng lockForUpdate để đảm bảo số lượng chính xác lúc check
                    $variant = ProductVariant::lockForUpdate()->find($item->product_variant_id);
                    
                    if (!$variant || $variant->stock_quantity < $item->quantity) {
                        throw new Exception("Không thể khôi phục đơn: Sản phẩm '{$item->product_name}' hiện không đủ hàng.");
                    }
                }
                // Trừ hàng
                foreach ($order->items as $item) {
                    ProductVariant::where('id', $item->product_variant_id)
                        ->decrement('stock_quantity', $item->quantity);
                }
            }

            // --- 3. SAVE DATA ---
            $order->update([
                'status'         => $newStatus,
                'payment_status' => $newPayment
            ]);

            DB::commit();
            
            Log::info("Order #{$order->order_code} updated: Status [$currentStatus -> $newStatus], Payment [$currentPayment -> $newPayment]");

            return back()->with('success', 'Cập nhật trạng thái thành công!');

        } catch (Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Lỗi hệ thống: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $order = Order::with(['items.variant', 'user'])->findOrFail($id);
        return view('admin.orders.show', compact('order'));
    }

    public function print($id)
    {
        $order = Order::with(['items.variant', 'user'])->findOrFail($id);
        return view('admin.orders.print', compact('order'));
    }
}