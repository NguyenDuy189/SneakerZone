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
        // ====================================================
        // 1. VALIDATE DỮ LIỆU ĐẦU VÀO
        // ====================================================
        $request->validate([
            'reason_option' => 'required|string',
            'other_reason'  => 'required_if:reason_option,Khác|nullable|string|max:255',
        ], [
            'reason_option.required'     => 'Vui lòng chọn lý do hủy đơn hàng.',
            'other_reason.required_if'   => 'Vui lòng nhập lý do cụ thể khi chọn mục "Khác".',
            'other_reason.max'           => 'Lý do hủy không được vượt quá 255 ký tự.',
        ]);

        try {
            // Bắt đầu Transaction để đảm bảo tính toàn vẹn dữ liệu
            DB::beginTransaction();

            // ====================================================
            // 2. TÌM ĐƠN HÀNG & KHÓA UPDATE
            // ====================================================
            // Sử dụng lockForUpdate() để chặn các thao tác khác lên đơn này 
            // trong khi đang xử lý hủy.
            $order = Order::where('id', $id)
                ->where('user_id', Auth::id())
                ->with('items') // Load sẵn items để hoàn kho
                ->lockForUpdate() 
                ->firstOrFail();

            // ====================================================
            // 3. KIỂM TRA TRẠNG THÁI
            // ====================================================
            // Chỉ cho phép hủy khi đơn hàng ở trạng thái 'pending' (Chờ xác nhận)
            // Nếu bạn muốn cho hủy cả khi đang xử lý, thêm 'processing' vào mảng.
            if (!in_array($order->status, ['pending'])) {
                // Rollback ngay lập tức nếu trạng thái không hợp lệ
                DB::rollBack(); 
                return back()->with('error', 'Đơn hàng đã được xác nhận hoặc đang vận chuyển, không thể hủy.');
            }

            // ====================================================
            // 4. XỬ LÝ LÝ DO HỦY (CỘT MỚI)
            // ====================================================
            $finalReason = $request->reason_option;
            if ($request->reason_option === 'Khác') {
                $finalReason = $request->other_reason;
            }

            // ====================================================
            // 5. HOÀN KHO (RESTOCK)
            // ====================================================
            foreach ($order->items as $item) {
                if ($item->product_variant_id) {
                    $variant = ProductVariant::where('id', $item->product_variant_id)
                                             ->lockForUpdate() // Khóa dòng biến thể để cộng kho an toàn
                                             ->first();
                    
                    if ($variant) {
                        $variant->increment('stock_quantity', $item->quantity);
                    }
                }
            }

            // ====================================================
            // 6. CẬP NHẬT TRẠNG THÁI ĐƠN HÀNG
            // ====================================================
            $order->update([
                'status'        => 'cancelled',
                'cancel_reason' => $finalReason, // ✅ Lưu vào cột mới
                // Tùy chọn: Vẫn nối vào note cũ để Admin dễ đọc trong trang chi tiết cũ
                'note'          => $order->note . " | [Khách hủy]: " . $finalReason
            ]);

            // ====================================================
            // 7. GHI LỊCH SỬ ĐƠN HÀNG (ORDER HISTORY)
            // ====================================================
            $history = OrderHistory::create([
                'order_id'    => $order->id,
                'user_id'     => Auth::id(),
                'action'      => 'cancelled',
                'description' => 'Khách hàng hủy đơn. Lý do: ' . $finalReason,
            ]);

            // Commit Transaction (Lưu tất cả thay đổi vào DB)
            DB::commit();

            // ====================================================
            // 8. GỬI SỰ KIỆN (EVENT/EMAIL/REALTIME)
            // ====================================================
            // Phần này để ngoài Transaction để không làm chậm thao tác DB
            try {
                if (class_exists(OrderStatusUpdated::class)) {
                    event(new OrderStatusUpdated($order, 'cancelled', $history));
                }
            } catch (\Exception $e) {
                // Chỉ log lỗi Event, không báo lỗi cho User vì đơn đã hủy thành công rồi
                Log::error("Event Trigger Error: " . $e->getMessage());
            }

            return back()->with('success', 'Đã hủy đơn hàng thành công.');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return back()->with('error', 'Đơn hàng không tồn tại hoặc bạn không có quyền truy cập.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Client Cancel Order Error (ID: $id): " . $e->getMessage());
            return back()->with('error', 'Lỗi hệ thống khi hủy đơn. Vui lòng thử lại sau.');
        }
    }
}