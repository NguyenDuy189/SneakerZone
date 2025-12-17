<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    // Hồ sơ tài khoản
    public function index()
    {
        return view('client.account.profile', [
            'user' => Auth::user(),
        ]);
    }

    // Cập nhật hồ sơ
    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'full_name' => 'required|string|max:255',
            'phone'     => 'nullable|string|max:20|unique:users,phone,' . $user->id,
            'gender'    => 'nullable|in:male,female,other',
            'birthday'  => 'nullable|date',
            'address'   => 'nullable|string|max:255',
        ]);

        $user->update($request->only([
            'full_name',
            'phone',
            'gender',
            'birthday',
            'address',
        ]));

        return back()->with('success', 'Cập nhật hồ sơ thành công');
    }

    // Form đổi mật khẩu
    public function password()
    {
        return view('client.account.password');
    }

    // Xử lý đổi mật khẩu
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:6|confirmed',
        ]);

        if (!Hash::check($request->current_password, Auth::user()->password)) {
            return back()->withErrors([
                'current_password' => 'Mật khẩu hiện tại không đúng',
            ]);
        }

        Auth::user()->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Đổi mật khẩu thành công');
    }
}
