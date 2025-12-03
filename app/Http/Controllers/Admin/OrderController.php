<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    /**
     * Danh sách đơn hàng (Filter, Search, Sort)
     */
    public function index(Request $request)
    {
        $query = Order::with(['user', 'items']); // Eager loading để tránh N+1 Query

        // 1. Tìm kiếm: Mã đơn hoặc Tên khách hàng
        if ($request->filled('keyword')) {
            $keyword = trim($request->keyword);
            $query->where(function ($q) use ($keyword) {
                $q->where('order_code', 'like', "%{$keyword}%")
                  ->orWhereHas('user', fn($u) => $u->where('full_name', 'like', "%{$keyword}%"))
                  ->orWhere('shipping_address->contact_name', 'like', "%{$keyword}%"); // Tìm trong JSON
            });
        }

        // 2. Lọc theo Trạng thái đơn hàng
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // 3. Lọc theo Trạng thái thanh toán
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // 4. Lọc theo Ngày đặt
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // 5. Sắp xếp (Mặc định mới nhất)
        $query->latest();

        $orders = $query->paginate(10)->withQueryString();

        return view('admin.orders.index', compact('orders'));
    }

    /**
     * Chi tiết đơn hàng (Hóa đơn)
     */
    public function show($id)
    {
        // Load items và thông tin biến thể (dù biến thể đã xóa mềm)
        $order = Order::with(['user', 'items.variant' => function($q) {
            $q->withTrashed(); 
        }])->findOrFail($id);

        return view('admin.orders.show', compact('order'));
    }

    /**
     * Cập nhật trạng thái đơn hàng (Xử lý Logic Kho)
     */
    public function updateStatus(Request $request, $id)
    {
        $order = Order::with('items')->findOrFail($id);

        // Validate trạng thái hợp lệ
        $request->validate([
            'status' => ['required', Rule::in(['pending', 'processing', 'shipping', 'completed', 'cancelled'])],
            'payment_status' => ['required', Rule::in(['unpaid', 'paid', 'refunded'])],
        ], [
            'status.in' => 'Trạng thái đơn hàng không hợp lệ.',
            'payment_status.in' => 'Trạng thái thanh toán không hợp lệ.',
        ]);

        DB::beginTransaction();
        try {
            $oldStatus = $order->status;
            $newStatus = $request->status;

            // --- LOGIC HOÀN KHO (RESTOCK) NẾU HỦY ĐƠN ---
            // Nếu đơn hàng đang KHÔNG PHẢI là hủy -> Chuyển sang HỦY
            // Thì phải cộng lại số lượng vào kho
            if ($newStatus === 'cancelled' && $oldStatus !== 'cancelled') {
                $this->restockItems($order);
            }

            // --- LOGIC TRỪ KHO (DEDUCT) NẾU KHÔI PHỤC ĐƠN HỦY ---
            // Nếu đơn hàng đang là HỦY -> Chuyển sang trạng thái khác (Khôi phục)
            // Thì phải trừ lại kho (nếu đủ hàng)
            if ($oldStatus === 'cancelled' && $newStatus !== 'cancelled') {
                $canRestore = $this->deductItems($order);
                if (!$canRestore) {
                    return back()->with('error', 'Không thể khôi phục đơn hàng vì kho không đủ số lượng!');
                }
            }

            // Cập nhật trạng thái
            $order->update([
                'status' => $newStatus,
                'payment_status' => $request->payment_status,
            ]);

            DB::commit();
            
            // Ghi Log hệ thống (Tùy chọn)
            Log::info("Admin cập nhật đơn hàng #{$order->order_code}: $oldStatus -> $newStatus");

            return back()->with('success', 'Cập nhật trạng thái đơn hàng thành công!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Lỗi cập nhật đơn hàng #{$id}: " . $e->getMessage());
            return back()->with('error', 'Lỗi hệ thống: ' . $e->getMessage());
        }
    }

    /**
     * In hóa đơn (Tùy chọn mở rộng)
     */
    public function print($id)
    {
        $order = Order::with(['user', 'items'])->findOrFail($id);
        return view('admin.orders.print', compact('order'));
    }

    // =========================================================================
    // PRIVATE HELPERS (XỬ LÝ KHO)
    // =========================================================================

    /**
     * Hoàn trả hàng vào kho (Khi hủy đơn)
     */
    private function restockItems(Order $order)
    {
        foreach ($order->items as $item) {
            if ($item->product_variant_id) {
                $variant = ProductVariant::find($item->product_variant_id);
                if ($variant) {
                    $variant->increment('stock_quantity', $item->quantity);
                }
            }
        }
    }

    /**
     * Trừ hàng trong kho (Khi khôi phục đơn hủy)
     * Trả về false nếu kho không đủ
     */
    private function deductItems(Order $order)
    {
        // 1. Kiểm tra trước xem có đủ hàng không
        foreach ($order->items as $item) {
            if ($item->product_variant_id) {
                $variant = ProductVariant::find($item->product_variant_id);
                
                // Nếu biến thể đã bị xóa hoặc không đủ hàng
                if (!$variant || $variant->stock_quantity < $item->quantity) {
                    return false; 
                }
            }
        }

        // 2. Nếu đủ hết thì mới trừ
        foreach ($order->items as $item) {
            if ($item->product_variant_id) {
                ProductVariant::where('id', $item->product_variant_id)
                    ->decrement('stock_quantity', $item->quantity);
            }
        }

        return true;
    }
}