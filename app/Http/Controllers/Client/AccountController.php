<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Events\OrderStatusUpdated;
use App\Models\Order;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Exception;

class AccountController extends Controller
{
    /**
     * =========================
     * 1. DASHBOARD / XEM PROFILE
     * =========================
     */
    public function index()
    {
        $user = Auth::user();

        // Lấy 5 đơn hàng gần nhất
        $recentOrders = Order::query()
            ->where('user_id', $user->id)
            ->withCount('items')
            ->latest()
            ->limit(5)
            ->get();

        return view('client.account.profile', compact('user', 'recentOrders'));
    }

    /**
     * =========================
     * 2. FORM SỬA PROFILE
     * =========================
     */
    public function edit()
    {
        $user = Auth::user();
        return view('client.account.edit', compact('user'));
    }

    /**
     * =========================
     * 3. XỬ LÝ CẬP NHẬT PROFILE
     * =========================
     */
    public function updateProfile(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Validate dữ liệu
        $validated = $request->validate([
            'full_name' => 'required|string|max:255', // Sửa từ name -> full_name
            'phone'     => ['nullable', 'regex:/^([0-9\s\-\+\(\)]*)$/', 'min:10'],
            'email'     => 'required|email|unique:users,email,' . $user->id,
            'gender'    => 'nullable|in:male,female,other', // Giả sử giới tính lưu dạng này
            'birthday'  => 'nullable|date',
            'address'   => 'nullable|string|max:255',
            'avatar'    => 'nullable|image|max:2048', // Tối đa 2MB
            'current_password' => 'nullable|required_with:new_password',
            'new_password'     => 'nullable|min:6|confirmed',
        ], [
            'full_name.required' => 'Vui lòng nhập họ và tên',
            'email.unique'       => 'Email này đã được sử dụng',
            'current_password.required_with' => 'Vui lòng nhập mật khẩu cũ để đổi mật khẩu mới',
            'new_password.confirmed' => 'Mật khẩu xác nhận không khớp',
            'new_password.min'   => 'Mật khẩu mới phải có ít nhất 6 ký tự',
        ]);

        DB::beginTransaction();

        try {
            // Chuẩn bị dữ liệu update (Mapping đúng tên cột DB)
            $updateData = [
                'full_name' => $validated['full_name'],
                'email'     => $validated['email'],
                'phone'     => $validated['phone'] ?? null, // Cột db là phone
                'gender'    => $validated['gender'] ?? null,
                'birthday'  => $validated['birthday'] ?? null,
                'address'   => $validated['address'] ?? null,
            ];

            // 1. Xử lý đổi mật khẩu
            if ($request->filled('new_password')) {
                if (!Hash::check($request->current_password, $user->password)) {
                    return back()->withErrors(['current_password' => 'Mật khẩu hiện tại không đúng']);
                }
                $updateData['password'] = Hash::make($request->new_password);
            }

            // 2. Xử lý upload avatar
            if ($request->hasFile('avatar')) {
                // Xóa ảnh cũ nếu có và không phải ảnh mặc định
                if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                    Storage::disk('public')->delete($user->avatar);
                }
                $updateData['avatar'] = $request->file('avatar')->store('avatars', 'public');
            }

            // Thực hiện update
            $user->update($updateData);

            DB::commit();
            
            return redirect()->route('client.account.profile')->with('success', 'Cập nhật hồ sơ thành công');

        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Update Profile Error: " . $e->getMessage());
            return back()->with('error', 'Có lỗi xảy ra, vui lòng thử lại sau.');
        }
    }

    /**
     * =========================
     * 4. DANH SÁCH ĐƠN HÀNG
     * =========================
     */
    public function orders()
    {
        $orders = Order::query()
            ->where('user_id', Auth::id())
            ->withCount('items')
            ->with([
                'shippingOrder.logs' => fn ($q) => $q->latest()->limit(1),
            ])
            ->latest()
            ->paginate(10);

        return view('client.account.orders', compact('orders'));
    }

    /**
     * =========================
     * 5. CHI TIẾT ĐƠN HÀNG
     * =========================
     */
    public function orderDetail(int $id)
    {
        $order = Order::query()
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->with([
                'items.productVariant.product',
                'transactions',
                'shippingOrder.logs' => fn ($q) => $q->latest(),
            ])
            ->firstOrFail();

        $timeline = $order->shippingOrder
            ? $order->shippingOrder->logs
            : collect();

        return view('client.account.order_details', compact('order', 'timeline'));
    }

    /**
     * =========================
     * 6. HỦY ĐƠN HÀNG
     * (Logic: pending/processing + hoàn tồn kho)
     * =========================
     */
    public function cancelOrder(int $id)
    {
        // Tìm đơn hàng thuộc về user và trạng thái cho phép hủy
        $order = Order::where('id', $id)
            ->where('user_id', Auth::id())
            ->with('items') 
            // Chỉ cho hủy khi chờ xử lý hoặc đang đóng gói (Tùy chính sách shop)
            ->whereIn('status', ['pending', 'processing']) 
            ->firstOrFail();

        DB::beginTransaction();
        try {
            // A. Hoàn trả tồn kho
            foreach ($order->items as $item) {
                if ($item->product_variant_id) {
                    $variant = ProductVariant::lockForUpdate()->find($item->product_variant_id);
                    if ($variant) {
                        $variant->increment('stock_quantity', $item->quantity);
                    }
                }
            }

            // B. Cập nhật trạng thái
            $order->update([
                'status' => 'cancelled'
            ]);

            // C. Ghi lịch sử
            $history = $order->histories()->create([
                'action' => 'cancelled',
                'description' => 'Khách hàng chủ động hủy đơn',
                'user_id' => Auth::id(),
            ]);

            DB::commit();

            // D. Realtime Event
            try {
                $history->load('user');
                event(new OrderStatusUpdated($order, 'cancelled', $history));
            } catch (Exception $e) {
                Log::error("Realtime Event Error: " . $e->getMessage());
            }

            return back()->with('success', 'Đơn hàng đã được hủy thành công.');

        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Cancel Order Error: " . $e->getMessage());
            return back()->with('error', 'Có lỗi xảy ra khi hủy đơn hàng.');
        }
    }

    /**
     * =========================
     * 7. ĐỔI PHƯƠNG THỨC THANH TOÁN
     * =========================
     */
    public function changePaymentMethod(Request $request, int $id)
    {
        $request->validate([
            'payment_method' => 'required|string',
        ]);

        $order = Order::where('id', $id)
            ->where('user_id', Auth::id())
            ->where('payment_status', 'unpaid')
            ->firstOrFail();

        DB::transaction(function () use ($order, $request) {
            $order->update([
                'payment_method' => $request->payment_method
            ]);

            $history = $order->histories()->create([
                'action' => 'payment_method_change',
                'description' => 'Khách hàng đổi phương thức thanh toán',
                'user_id' => Auth::id(),
            ]);

            // 🔥 REALTIME
            event(new OrderStatusUpdated(
                $order,
                'payment_method_changed',
                $history
            ));
        });

        return back()->with('success', 'Đã cập nhật phương thức thanh toán');
    }
}