<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password; // Cần thêm
use Illuminate\Support\Str;              // Cần thêm
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // =========================================================
    // ĐĂNG NHẬP ADMIN
    // =========================================================

    public function showLoginForm()
    {
        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ], [
            'email.required' => 'Vui lòng nhập email.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
        ]);

        // Thêm điều kiện user phải là admin hoặc staff mới được login vào đây
        // (Tuỳ logic của bạn, ở đây Auth::attempt chỉ check email/pass)
        if (Auth::attempt($credentials, $request->remember)) {
            $request->session()->regenerate();

            // Kiểm tra quyền (Ví dụ: phải là admin hoặc staff)
            $user = Auth::user();
            if (!in_array($user->role, ['admin', 'staff'])) {
                Auth::logout();
                $request->session()->invalidate();
                return back()->withErrors(['email' => 'Bạn không có quyền truy cập quản trị.']);
            }

            if ($user->status == 0) {
                Auth::logout();
                $request->session()->invalidate();
                return back()->withErrors(['email' => 'Tài khoản quản trị đã bị khóa.']);
            }

            return redirect()->intended(route('admin.dashboard'));
        }

        return back()->withErrors([
            'email' => 'Thông tin đăng nhập không chính xác.',
        ])->onlyInput('email');
    }

    // =========================================================
    // ĐĂNG XUẤT ADMIN
    // =========================================================

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('admin.login');
    }

    // =========================================================
    // QUÊN MẬT KHẨU ADMIN
    // =========================================================

    // 1. Form nhập email quên mật khẩu
    public function showLinkRequestForm()
    {
        return view('admin.auth.forgot-password'); 
        // Tạo view: resources/views/admin/auth/forgot-password.blade.php
    }

    // 2. Gửi email
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        // Gửi link. Mặc định Laravel dùng bảng users. 
        // Nếu admin chung bảng users thì dùng chung broker 'users'
        $status = Password::sendResetLink($request->only('email'));

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with(['status' => __($status)]);
        }

        return back()->withErrors(['email' => __($status)]);
    }

    // 3. Form reset password (có token)
    public function showResetForm($token)
    {
        return view('admin.auth.reset-password', ['token' => $token]);
        // Tạo view: resources/views/admin/auth/reset-password.blade.php
    }

    // 4. Update password mới
    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:6|confirmed',
        ], [
             'password.confirmed' => 'Mật khẩu xác nhận không khớp.'
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();
                
                // Admin thì không tự login ngay, bắt login lại cho an toàn
                // Hoặc nếu muốn login luôn thì: Auth::login($user);
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('admin.login')->with('status', 'Mật khẩu đã được đặt lại thành công. Vui lòng đăng nhập.');
        }

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => __($status)]);
    }
}