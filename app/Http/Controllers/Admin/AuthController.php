<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

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
        // 1. Validate dữ liệu đầu vào
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ], [
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không đúng định dạng.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
        ]);

        // 2. Kiểm tra thông tin đăng nhập
        // Auth::attempt tự động mã hóa password request và so sánh với database
        if (Auth::attempt($credentials)) {
            
            // QUAN TRỌNG: Tạo lại session ID để ngăn chặn tấn công Session Fixation
            $request->session()->regenerate();

            // Kiểm tra nếu user bị khóa (status = 0) thì logout ngay
            if (Auth::user()->status == 0) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                return back()->withErrors(['email' => 'Tài khoản của bạn đã bị khóa.']);
            }

            // Redirect về trang user muốn vào trước đó, hoặc về trang chủ
            return redirect()->intended(route('client.home'));
        }

        // 3. Đăng nhập thất bại
        return back()->withErrors([
            'email' => 'Email hoặc mật khẩu không đúng.',
        ])->onlyInput('email'); // Giữ lại email trên form để user không phải nhập lại
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
        // 1. Validate dữ liệu
        $request->validate([
            'name' => 'required|max:255',
            'email' => 'required|email|unique:users,email', // Check trùng email trong bảng users
            'password' => 'required|min:6|confirmed', // password_confirmation phải khớp
        ], [
            'name.required' => 'Vui lòng nhập họ tên.',
            'email.unique' => 'Email này đã được đăng ký.',
            'password.confirmed' => 'Mật khẩu nhập lại không khớp.',
            'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự.'
        ]);

        // 2. Tạo user mới
        $user = User::create([
            'full_name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'customer', // Mặc định là khách hàng
            'status' => 1,        // Mặc định kích hoạt
        ]);

        // 3. Tự động đăng nhập sau khi đăng ký
        Auth::login($user);
        
        // 4. Regenerate session sau khi login (An toàn)
        $request->session()->regenerate();

        return redirect()->route('client.home')
            ->with('success', 'Đăng ký tài khoản thành công!');
    }

    // =========================================================
    // ĐĂNG XUẤT (LOGOUT) - Quan trọng để tránh lỗi 419
    // =========================================================

    public function logout(Request $request)
    {
        // 1. Logout user khỏi hệ thống
        Auth::logout();

        // 2. Hủy session hiện tại
        $request->session()->invalidate();

        // 3. Tạo lại CSRF Token mới
        // Dòng này cực quan trọng để tránh lỗi 419 khi user đăng nhập lại ngay lập tức
        $request->session()->regenerateToken();

        // 4. Chuyển hướng về trang chủ hoặc trang login
        return redirect()->route('client.home');
    }
}