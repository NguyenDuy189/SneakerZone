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
        $query = ShippingOrder::with(['order.customer', 'order.items', 'shipper'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $list = $query->paginate(20);

        return view('admin.shipping.index', compact('list'));
    }

    /**
     * Form gán shipper
     */
    public function assignForm(int $orderId)
    {
        $order = Order::with('customer')->findOrFail($orderId);
        $shippers = User::where('role', 'shipper')->get();

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
        ]);

        $order = Order::findOrFail($orderId);

        // Kiểm tra shipper có đúng role
        $shipper = User::where('id', $request->shipper_id)
                        ->where('role', 'shipper')
                        ->firstOrFail();

        try {
            DB::beginTransaction();

            // Tạo tracking code duy nhất
            do {
                $trackingCode = 'TRK-' . now()->format('ymd') . '-' . strtoupper(Str::random(6));
            } while (ShippingOrder::where('tracking_code', $trackingCode)->exists());

            $shipping = ShippingOrder::create([
                'order_id' => $order->id,
                'shipper_id' => $shipper->id,
                'status' => ShippingOrder::STATUS_ASSIGNED,
                'expected_delivery_date' => $request->expected_delivery_date ?? null,
                'tracking_code' => $trackingCode,
            ]);

            // Tạo log
            ShippingLog::create([
                'shipping_order_id' => $shipping->id,
                'status' => $shipping->status,
                'description' => 'Gán shipper #' . $shipper->id,
                'user_id' => optional(Auth::user())->id,
            ]);

            // Event realtime
            event(new ShippingStatusUpdated($shipping));

            DB::commit();

            return redirect()->route('admin.shipping.show', $shipping->id)
                ->with('success', 'Đã gán shipper thành công.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Assign shipping failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->withErrors('Có lỗi xảy ra khi gán shipper. Vui lòng thử lại.');
        }
    }

    /**
     * Cập nhật trạng thái
     */
    public function updateStatus(Request $request, int $id)
    {
        $request->validate([
            'status' => ['required', 'in:pending,assigned,picking,delivering,delivered,failed,returned'],
            'location' => ['nullable', 'string', 'max:255'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $shipping = ShippingOrder::findOrFail($id);

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
                'user_id' => optional(Auth::user())->id,
            ]);

            event(new ShippingStatusUpdated($shipping));

            DB::commit();

            return back()->with('success', 'Cập nhật trạng thái thành công.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Update shipping status failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->withErrors('Có lỗi xảy ra khi cập nhật trạng thái. Vui lòng thử lại.');
        }
    }

    /**
     * Hiển thị chi tiết đơn giao hàng + realtime view
     */
    public function show(int $id)
    {
        $shipping = ShippingOrder::with(['order.customer', 'order.items', 'shipper', 'logs.user'])
            ->findOrFail($id);

        return view('admin.shipping.show', compact('shipping'));
    }
}
