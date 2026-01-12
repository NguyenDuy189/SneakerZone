<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password; // Cần thêm cái này
use Illuminate\Support\Str;              // Cần thêm cái này
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // =========================================================
    // ĐĂNG NHẬP
    // =========================================================

    public function showLoginForm()
    {
        return view('client.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ], [
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không đúng định dạng.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
        ]);

        if (Auth::attempt($credentials, $request->remember)) {
            $request->session()->regenerate();

            if (Auth::user()->status == 0) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return back()->withErrors(['email' => 'Tài khoản của bạn đã bị khóa.']);
            }

            return redirect()->intended(route('client.home'));
        }

        return back()->withErrors([
            'email' => 'Email hoặc mật khẩu không đúng.',
        ])->onlyInput('email');
    }

    // =========================================================
    // ĐĂNG KÝ
    // =========================================================

    public function showRegisterForm()
    {
        return view('client.auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
        ], [
            'name.required' => 'Vui lòng nhập họ tên.',
            'email.unique' => 'Email này đã được đăng ký.',
            'password.confirmed' => 'Mật khẩu nhập lại không khớp.',
            'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự.'
        ]);

        $user = User::create([
            'full_name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'customer',
            'status' => 1,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('client.home')
            ->with('success', 'Đăng ký tài khoản thành công!');
    }

    // =========================================================
    // ĐĂNG XUẤT
    // =========================================================

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('client.home');
    }

    // =========================================================
    // QUÊN MẬT KHẨU (FORGOT PASSWORD)
    // =========================================================

    // 1. Hiển thị form nhập email
    public function showForgotPasswordForm()
    {
        return view('client.auth.forgot-password'); 
        // Bạn cần tạo file view: resources/views/client/auth/forgot-password.blade.php
    }

    // 2. Xử lý gửi mail reset link
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        // Gửi link reset password (sử dụng cấu hình mặc định trong config/auth.php)
        $status = Password::sendResetLink($request->only('email'));

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with(['status' => __($status)]); 
            // 'status' sẽ chứa thông báo: "Chúng tôi đã gửi link đặt lại mật khẩu vào email của bạn!"
        }

        return back()->withErrors(['email' => __($status)]);
    }

    // 3. Hiển thị form đặt lại mật khẩu mới (người dùng click từ email)
    public function showResetPasswordForm($token)
    {
        return view('client.auth.reset-password', ['token' => $token]);
        // Bạn cần tạo file view: resources/views/client/auth/reset-password.blade.php
    }

    // 4. Xử lý đổi mật khẩu
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:6|confirmed',
        ], [
            'password.confirmed' => 'Mật khẩu nhập lại không khớp.',
            'password.min' => 'Mật khẩu mới phải có ít nhất 6 ký tự.'
        ]);

        // Sử dụng Password Broker để reset
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                // Callback này chạy khi token hợp lệ
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                // Tự động đăng nhập lại user sau khi reset xong (tuỳ chọn)
                Auth::guard('web')->login($user);
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('status', __($status));
        }

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => __($status)]);
    }
}