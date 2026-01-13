<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Order;
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

        // Lấy 5 đơn hàng gần nhất (chỉ để hiển thị dashboard tóm tắt)
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
            'full_name' => 'required|string|max:255',
            'phone'     => ['nullable', 'regex:/^([0-9\s\-\+\(\)]*)$/', 'min:10'],
            'email'     => 'required|email|unique:users,email,' . $user->id,
            'gender'    => 'nullable|in:male,female,other',
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
            $updateData = [
                'full_name' => $validated['full_name'],
                'email'     => $validated['email'],
                'phone'     => $validated['phone'] ?? null,
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
                if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                    Storage::disk('public')->delete($user->avatar);
                }
                $updateData['avatar'] = $request->file('avatar')->store('avatars', 'public');
            }

            $user->update($updateData);

            DB::commit();
            
            return redirect()->route('client.account.profile')->with('success', 'Cập nhật hồ sơ thành công');

        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Update Profile Error: " . $e->getMessage());
            return back()->with('error', 'Có lỗi xảy ra, vui lòng thử lại sau.');
        }
    }
}