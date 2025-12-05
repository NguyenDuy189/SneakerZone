<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShippingOrder;
use App\Models\Order;
use App\Models\User;
use App\Models\ShippingLog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Events\ShippingStatusUpdated;
use Illuminate\Support\Facades\Auth;

class ShippingController extends Controller
{
    /**
     * Danh sách đơn giao hàng
     */
    public function index(Request $request)
    {
        $query = ShippingOrder::with(['order.customer', 'shipper'])->latest();

        // Filter theo trạng thái nếu có
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Pagination chuẩn
        $shippings = $query->paginate(20);

        return view('admin.shipping.index', compact('shippings'));
    }

    /**
     * Form gán shipper
     */
    public function assignForm(int $orderId)
    {
        $order = Order::with('customer')->findOrFail($orderId);

        $shippers = User::where('role', 'shipper')
                        ->orderBy('full_name')
                        ->get();

        return view('admin.shipping.assign', compact('order', 'shippers'));
    }

    /**
     * Gán shipper
     */
    public function assign(Request $request, int $orderId)
    {
        $request->validate([
            'shipper_id' => ['required', 'exists:users,id'],
            'expected_delivery_date' => ['nullable', 'date', 'after_or_equal:today'],
        ], [
            'shipper_id.required' => 'Vui lòng chọn shipper.',
            'shipper_id.exists' => 'Shipper không tồn tại.',
            'expected_delivery_date.date' => 'Ngày dự kiến không hợp lệ.',
            'expected_delivery_date.after_or_equal' => 'Ngày dự kiến phải từ hôm nay trở đi.',
        ]);

        $order = Order::findOrFail($orderId);

        $shipper = User::where('id', $request->shipper_id)
                        ->where('role', 'shipper')
                        ->firstOrFail();

        try {
            DB::beginTransaction();

            // Sinh tracking code duy nhất
            do {
                $trackingCode = 'TRK-' . now()->format('ymd') . '-' . strtoupper(Str::random(6));
            } while (ShippingOrder::where('tracking_code', $trackingCode)->exists());

            $shipping = ShippingOrder::create([
                'order_id' => $order->id,
                'shipper_id' => $shipper->id,
                'status' => ShippingOrder::STATUS_ASSIGNED,
                'expected_delivery_date' => $request->expected_delivery_date,
                'tracking_code' => $trackingCode,
            ]);

            // Tạo log
            ShippingLog::create([
                'shipping_order_id' => $shipping->id,
                'status' => $shipping->status,
                'description' => 'Shipper được gán: #' . $shipper->id,
                'user_id' => Auth::id(),
            ]);

            // Event realtime
            event(new ShippingStatusUpdated($shipping->load('shipper')));

            DB::commit();

            return redirect()->route('admin.shipping.show', $shipping->id)
                             ->with('success', 'Gán shipper thành công.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gán shipper thất bại: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->withErrors('Có lỗi xảy ra khi gán shipper. Vui lòng thử lại.');
        }
    }

    /**
     * Cập nhật trạng thái shipping
     */
    public function updateStatus(Request $request, int $shippingId)
    {
        $request->validate([
            'status' => ['required', 'in:pending,assigned,picking,delivering,delivered,failed,returned'],
            'location' => ['nullable', 'string', 'max:255'],
            'note' => ['nullable', 'string', 'max:500'],
        ], [
            'status.required' => 'Vui lòng chọn trạng thái.',
            'status.in' => 'Trạng thái không hợp lệ.',
            'location.max' => 'Vị trí không được vượt quá 255 ký tự.',
            'note.max' => 'Ghi chú không được vượt quá 500 ký tự.',
        ]);

        $shipping = ShippingOrder::findOrFail($shippingId);

        try {
            DB::beginTransaction();

            $shipping->update([
                'status' => $request->status,
                'current_location' => $request->location,
                'note' => $request->note,
            ]);

            ShippingLog::create([
                'shipping_order_id' => $shipping->id,
                'status' => $request->status,
                'description' => $request->note,
                'location' => $request->location,
                'user_id' => Auth::id(),
            ]);

            // Event realtime
            event(new ShippingStatusUpdated($shipping->load('shipper')));

            DB::commit();

            return back()->with('success', 'Cập nhật trạng thái thành công.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Cập nhật trạng thái thất bại: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->withErrors('Có lỗi xảy ra khi cập nhật trạng thái. Vui lòng thử lại.');
        }
    }

    /**
     * Hiển thị chi tiết đơn giao hàng + realtime
     */
    public function show(int $id)
    {
        $shipping = ShippingOrder::with(['order.customer', 'order.items', 'shipper', 'logs.user'])
                                 ->findOrFail($id);

        return view('admin.shipping.show', compact('shipping'));
    }

    /**
     * Thùng rác (soft delete)
     */
    public function trash()
    {
        $shippings = ShippingOrder::onlyTrashed()
                                  ->with(['order.customer','shipper'])
                                  ->paginate(20);

        return view('admin.shipping.trash', compact('shippings'));
    }
}
