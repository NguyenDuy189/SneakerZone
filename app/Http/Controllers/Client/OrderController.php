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
use App\Events\OrderStatusUpdated; // Sự kiện Realtime
use Exception;

class OrderController extends Controller
{
    /**
     * ==========================================
     * 1. DANH SÁCH ĐƠN HÀNG
     * ==========================================
     */
    public function index()
    {
        $userId = Auth::id();

        $orders = Order::query()
            ->where('user_id', $userId)
            ->withCount('items') // Đếm số sản phẩm để hiển thị (VD: Đơn hàng gồm 3 món)
            ->with('items.variant.product') // Load ảnh sản phẩm đầu tiên làm thumbnail (tùy chọn)
            ->latest() // Tương đương orderBy('created_at', 'desc')
            ->paginate(10);
        return view('client.orders.index', compact('orders'));
    }

    /**
     * ==========================================
     * 2. CHI TIẾT ĐƠN HÀNG
     * ==========================================
     */
    public function show($code)
    {
        /** * Eager Loading tối ưu:
         * - histories: Lấy cả log "Đang đóng gói", "Chờ xử lý" (Khắc phục lỗi không hiện trạng thái)
         * - transactions: Lấy thông tin thanh toán (nếu có)
         */
        $order = Order::with([
                'items.variant.product', 
                'items.variant.attributeValues.attribute',
                'histories.user', // Load người thực hiện lịch sử (để biết Admin nào duyệt hay khách tự hủy)
                'transactions'
            ])
            ->where('code', $code)
            ->where('user_id', Auth::id()) // BẢO MẬT: Chặn xem đơn người khác
            ->firstOrFail();

        // Sắp xếp lịch sử: Mới nhất lên đầu timeline
        // Lưu ý: Đảm bảo trong Model Order có function histories() { return $this->hasMany(...); }
        $timeline = $order->histories->sortByDesc('created_at');

        return view('client.orders.show', compact('order', 'timeline'));
    }

    /**
     * ==========================================
     * 3. HỦY ĐƠN HÀNG (FULL LOGIC)
     * ==========================================
     */
    public function cancel(Request $request, $id)
    {
        // 1. Tìm đơn hàng theo ID và User hiện tại
        $order = Order::where('id', $id)
            ->where('user_id', Auth::id())
            ->with('items') 
            ->firstOrFail();

        // [FIX] Validate trạng thái: 
        // Cho phép hủy nếu trạng thái là 'pending' (Chờ xử lý) HOẶC 'processing' (Đang đóng gói)
        // Dùng !in_array để kiểm tra nếu trạng thái KHÔNG nằm trong danh sách cho phép
        if (!in_array($order->status, ['pending', 'processing'])) {
            return back()->with('error', 'Đơn hàng đã được bàn giao cho đơn vị vận chuyển, không thể hủy lúc này.');
        }

        // 3. Transaction
        DB::beginTransaction();

        try {
            // A. Hoàn trả tồn kho
            foreach ($order->items as $item) {
                if ($item->product_variant_id) {
                    // Dùng lockForUpdate để tránh race condition
                    $variant = ProductVariant::lockForUpdate()->find($item->product_variant_id);
                    if ($variant) {
                        $variant->increment('stock_quantity', $item->quantity);
                    }
                }
            }

            // B. Cập nhật trạng thái đơn hàng
            $order->update([
                'status' => 'cancelled' // Hoặc 'canceled' tùy DB của bạn
            ]);

            // C. Ghi lịch sử
            $history = OrderHistory::create([
                'order_id'    => $order->id,
                'user_id'     => Auth::id(),
                'action'      => 'cancelled',
                'description' => 'Khách hàng đã chủ động hủy đơn hàng',
            ]);
            
            // Load quan hệ user để event realtime hiển thị tên người hủy
            $history->load('user');

            DB::commit();

            // D. Kích hoạt Realtime
            try {
                event(new OrderStatusUpdated($order, 'cancelled', $history));
            } catch (Exception $e) {
                Log::error("Realtime Event Error: " . $e->getMessage());
            }

            return back()->with('success', 'Đã hủy đơn hàng thành công.');

        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Client Cancel Order Error: " . $e->getMessage());
            return back()->with('error', 'Có lỗi xảy ra, vui lòng thử lại sau.');
        }
    }
}