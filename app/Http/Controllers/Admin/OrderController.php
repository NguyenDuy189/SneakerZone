<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductVariant;
use App\Events\OrderStatusUpdated; // Event Realtime
use App\Models\OrderHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Exception;

class OrderController extends Controller
{
    // =========================================================================
    // CẤU HÌNH LUỒNG TRẠNG THÁI (STATE MACHINE)
    // =========================================================================
    // Key: Trạng thái HIỆN TẠI
    // Value: Danh sách các trạng thái ĐƯỢC PHÉP chuyển tới
    const TRANSITIONS = [
        'pending'    => ['processing', 'cancelled'],       // Chờ xử lý -> Đóng gói HOẶC Hủy
        'processing' => ['shipping', 'cancelled'],         // Đóng gói -> Giao hàng HOẶC Hủy
        'shipping'   => ['completed', 'returned'],         // Đang giao -> Hoàn thành HOẶC Trả hàng
        'completed'  => [],                                // Hoàn thành -> KHÓA (Kết thúc vòng đời)
        'cancelled'  => [],                                // Hủy -> KHÓA (Kết thúc vòng đời)
        'returned'   => [],                                // Trả hàng -> KHÓA (Kết thúc vòng đời)
    ];

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
                // [LOCKING] Khóa dòng dữ liệu (Pessimistic Locking) để tránh bán quá số lượng tồn
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

                // [SNAPSHOT] Lấy giá tại thời điểm mua (Tránh việc admin sửa giá sau này làm sai lệch đơn cũ)
                $price = $variant->sale_price > 0 ? $variant->sale_price : $variant->original_price;
                $lineTotal = $price * $itemData['quantity'];

                // Tạo OrderItem
                OrderItem::create([
                    'order_id'           => $order->id,
                    'product_id'         => $variant->product_id,
                    'product_variant_id' => $variant->id,
                    'product_name'       => $variant->product->name, // Snapshot tên
                    'sku'                => $variant->sku,           // Snapshot SKU
                    'quantity'           => $itemData['quantity'],
                    'price'              => $price,                  // Snapshot giá
                    'total'              => $lineTotal,
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
     * CẬP NHẬT TRẠNG THÁI (STRICT MODE & REALTIME)
     * Đảm bảo: Không đi lùi, tự động hoàn kho, thông báo realtime
     */
    public function updateStatus(Request $request, $id)
    {
        $order = Order::with('items')->findOrFail($id);
        
        $currentStatus  = $order->status;
        $currentPayment = $order->payment_status;

        // 1. Validate Input
        $validator = Validator::make($request->all(), [
            'status'         => ['required', Rule::in(array_keys(self::TRANSITIONS))],
            'payment_status' => ['required', Rule::in(['unpaid', 'paid', 'refunded'])],
        ]);

        if ($validator->fails()) return back()->withErrors($validator)->withInput();

        $newStatus  = $request->status;
        $newPayment = $request->payment_status;

        // 2. Validate Business Logic (Chặn đi lùi)
        if ($newStatus !== $currentStatus) {
            $allowedStates = self::TRANSITIONS[$currentStatus] ?? [];
            if (!in_array($newStatus, $allowedStates)) {
                return back()->with('error', "Không thể chuyển từ '{$this->getStatusName($currentStatus)}' sang '{$this->getStatusName($newStatus)}'.");
            }
        }
        
        // Validate Payment Logic
        if ($currentPayment === 'paid' && $newPayment === 'unpaid') {
            return back()->with('error', 'Không thể hủy trạng thái "Đã thanh toán".');
        }
        if ($newStatus === 'completed' && $newPayment !== 'paid') {
            return back()->with('error', 'Đơn hàng phải thanh toán xong mới được hoàn thành.');
        }

        DB::beginTransaction();
        try {
            // 3. Xử lý Kho (Inventory)
            $reservedStates = ['pending', 'processing', 'shipping'];
            $releasedStates = ['cancelled', 'returned'];
            
            $isFromReserved = in_array($currentStatus, $reservedStates);
            $isToReleased   = in_array($newStatus, $releasedStates);

            // Nếu Hủy/Trả hàng -> Cộng lại kho
            if ($newStatus !== $currentStatus && $isFromReserved && $isToReleased) {
                foreach ($order->items as $item) {
                    ProductVariant::where('id', $item->product_variant_id)
                        ->increment('stock_quantity', $item->quantity);
                }
            }

            // 4. Update Order
            $order->update([
                'status'         => $newStatus,
                'payment_status' => $newPayment,
                'updated_at'     => now()
            ]);

            // 5. [MỚI] Ghi Lịch Sử (OrderHistory)
            $historyDescription = [];
            if ($currentStatus !== $newStatus) {
                $historyDescription[] = "Trạng thái: " . $this->getStatusName($currentStatus) . " ➔ " . $this->getStatusName($newStatus);
            }
            if ($currentPayment !== $newPayment) {
                $historyDescription[] = "Thanh toán: $currentPayment ➔ $newPayment";
            }

            $history = null;
            if (!empty($historyDescription)) {
                $history = OrderHistory::create([
                    'order_id'    => $order->id,
                    'user_id'     => Auth::id(),
                    'action'      => 'Cập nhật đơn hàng',
                    'description' => implode('. ', $historyDescription),
                ]);
            }

            DB::commit();

            // 6. Realtime Broadcast
            if ($history) {
                // Lưu ý: Cần sửa Event để nhận thêm $history (xem Bước 3 bên dưới)
                try {
                    event(new OrderStatusUpdated($order, $history));
                } catch (Exception $e) {
                    Log::error("Pusher Error: " . $e->getMessage());
                }
            }

            return back()->with('success', 'Cập nhật thành công!');

        } catch (Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Lỗi: ' . $e->getMessage());
        }
    }

    /**
     * Helper: Chuyển mã trạng thái sang tiếng Việt dễ đọc
     */
    private function getStatusName($status)
    {
        return match($status) {
            'pending'    => 'Chờ xử lý',
            'processing' => 'Đang đóng gói',
            'shipping'   => 'Đang giao hàng',
            'completed'  => 'Hoàn thành',
            'cancelled'  => 'Đã hủy',
            'returned'   => 'Trả hàng/Hoàn về',
            default      => $status,
        };
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