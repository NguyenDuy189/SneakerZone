<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Order;
use App\Models\OrderHistory;
use App\Models\ProductVariant;
use App\Events\OrderStatusUpdated;
use Exception;

class OrderController extends Controller
{
    /**
     * ==========================================
     * 1. DANH SÁCH ĐƠN HÀNG (Có lọc trạng thái)
     * ==========================================
     */
    public function index(Request $request)
    {
        // 1. Lấy trạng thái từ URL (?status=pending)
        $status = $request->input('status');

        // 2. Query Builder
        $orders = Order::query()
            ->where('user_id', Auth::id())
            ->when($status, function ($q) use ($status) {
                return $q->where('status', $status);
            })
            // Load quan hệ để lấy ảnh & đếm số lượng
            ->with(['items.variant.product'])
            ->withCount('items')
            ->latest()
            ->paginate(5) // Phân trang 5 item
            ->withQueryString(); // Giữ lại tham số status trên URL khi bấm trang 2

        // Trả về view danh sách đơn hàng
        // Lưu ý: Đảm bảo view 'client.account.orders.index' hoặc 'client.orders.index' tồn tại
        return view('client.account.orders', compact('orders'));
    }

    /**
     * ==========================================
     * 2. CHI TIẾT ĐƠN HÀNG
     * ==========================================
     */
    public function show($id)
    {
        try {
            // 1. TRUY VẤN TỐI ƯU
            $order = Order::with([
                'items.variant.product',
                'transactions',
                // Lịch sử vận chuyển (nếu có model ShippingOrder)
                'shippingOrder.logs' => fn($q) => $q->latest(),
                // Lịch sử đơn hàng
                'histories.user'
            ])
                ->where('id', $id) // Tìm theo ID (hoặc dùng 'code' nếu DB bạn dùng mã code làm chính)
                ->where('user_id', Auth::id()) // BẢO MẬT: Chỉ chủ đơn hàng mới xem được
                ->firstOrFail();

            // 2. XỬ LÝ TIMELINE (Gộp Lịch sử vận chuyển + Lịch sử đơn hàng)
            $shippingLogs = $order->shippingOrder ? $order->shippingOrder->logs : collect();
            $orderHistory = $order->histories;

            // Gộp và sắp xếp mới nhất lên đầu
            $timeline = $shippingLogs->concat($orderHistory)
                ->sortByDesc('created_at')
                ->values();

            return view('client.account.order_details', compact('order', 'timeline'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('client.account.orders.index')
                ->with('error', 'Đơn hàng không tồn tại hoặc bạn không có quyền xem.');
        }
    }

    /**
     * ==========================================
     * 3. HỦY ĐƠN HÀNG (FULL LOGIC)
     * ==========================================
     */
    public function cancel(Request $request, $id)
    {
        // 1. Validate lý do
        $request->validate([
            'reason_option' => 'required|string',
            'other_reason'  => 'required_if:reason_option,Khác|nullable|string|max:255',
        ], [
            'reason_option.required'     => 'Vui lòng chọn lý do hủy đơn hàng.',
            'other_reason.required_if'   => 'Vui lòng nhập lý do cụ thể khi chọn mục "Khác".',
            'other_reason.max'           => 'Lý do hủy không được vượt quá 255 ký tự.',
        ]);

        try {
            DB::beginTransaction();

            // 2. Tìm đơn hàng & Khóa update (lockForUpdate)
            $order = Order::where('id', $id)
                ->where('user_id', Auth::id())
                ->with('items')
                ->lockForUpdate()
                ->firstOrFail();

            // 3. Kiểm tra trạng thái
            if (!in_array($order->status, ['pending', 'unpaid'])) {
                DB::rollBack();
                return back()->with('error', 'Đơn hàng đã được xác nhận hoặc đang vận chuyển, không thể hủy.');
            }

            // 4. Xác định lý do
            $finalReason = $request->reason_option;
            if ($request->reason_option === 'Khác') {
                $finalReason = $request->other_reason;
            }

            // 5. Hoàn kho (Restock)
            foreach ($order->items as $item) {
                if ($item->product_variant_id) {
                    $variant = ProductVariant::where('id', $item->product_variant_id)
                        ->lockForUpdate()
                        ->first();
                    if ($variant) {
                        $variant->increment('stock_quantity', $item->quantity);
                    }
                }
            }

            // 6. Cập nhật trạng thái
            $order->update([
                'status'        => 'cancelled',
                'cancel_reason' => $finalReason,
                'note'          => $order->note . " | [Khách hủy]: " . $finalReason
            ]);

            // 7. Ghi lịch sử
            $history = $order->histories()->create([
                'user_id'     => Auth::id(),
                'action'      => 'cancelled',
                'description' => 'Khách hàng hủy đơn. Lý do: ' . $finalReason,
            ]);

            DB::commit();

            // 8. Gửi Event Realtime
            try {
                // Check class tồn tại để tránh lỗi nếu chưa chạy lệnh make:event
                if (class_exists(OrderStatusUpdated::class)) {
                    $history->load('user');
                    event(new OrderStatusUpdated($order, 'cancelled', $history));
                }
            } catch (\Exception $e) {
                Log::error("Event Trigger Error: " . $e->getMessage());
            }

            return back()->with('success', 'Đã hủy đơn hàng thành công.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Cancel Order Error: " . $e->getMessage());
            return back()->with('error', 'Lỗi hệ thống khi hủy đơn. Vui lòng thử lại sau.');
        }
    }

    /**
     * ==========================================
     * 4. ĐỔI PHƯƠNG THỨC THANH TOÁN
     * ==========================================
     */
    public function changePaymentMethod(Request $request, int $id)
    {
        $request->validate([
            'payment_method' => 'required|string',
        ]);

        try {
            $order = Order::where('id', $id)
                ->where('user_id', Auth::id())
                ->where('payment_status', 'unpaid')
                ->where('status', '!=', 'cancelled')
                ->firstOrFail();

            DB::transaction(function () use ($order, $request) {
                // Cập nhật PTTT
                $order->update([
                    'payment_method' => $request->payment_method
                ]);

                // Ghi log
                $history = $order->histories()->create([
                    'action'      => 'payment_method_change',
                    'description' => 'Khách hàng đổi PTTT sang: ' . $request->payment_method,
                    'user_id'     => Auth::id(),
                ]);

                // Realtime Event
                if (class_exists(OrderStatusUpdated::class)) {
                    event(new OrderStatusUpdated($order, 'payment_method_changed', $history));
                }
            });

            return back()->with('success', 'Đã cập nhật phương thức thanh toán');
        } catch (\Exception $e) {
            return back()->with('error', 'Không thể đổi phương thức thanh toán lúc này.');
        }
    }
}
