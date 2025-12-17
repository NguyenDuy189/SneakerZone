@extends('client.layouts.app')

@section('title', 'Đăng ký')

@section('content')
<div class="min-h-[70vh] flex items-center justify-center bg-slate-50">
    <div class="w-full max-w-md bg-white rounded-2xl shadow-xl p-8">
        <h1 class="text-2xl font-black text-center mb-6">Tạo tài khoản</h1>

        <form method="POST" action="{{ route('client.register.submit') }}" class="space-y-5">
            @csrf

            <div>
                <label class="text-sm font-bold">Họ tên</label>
                <input type="text" name="name" value="{{ old('name') }}"
                    class="w-full mt-1 px-4 py-3 rounded-xl border focus:ring-2 focus:ring-indigo-200">
                @error('name') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="text-sm font-bold">Email</label>
                <input type="email" name="email" value="{{ old('email') }}"
                    class="w-full mt-1 px-4 py-3 rounded-xl border focus:ring-2 focus:ring-indigo-200">
                @error('email') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="text-sm font-bold">Mật khẩu</label>
                <input type="password" name="password"
                    class="w-full mt-1 px-4 py-3 rounded-xl border focus:ring-2 focus:ring-indigo-200">
            </div>

            <div>
                <label class="text-sm font-bold">Xác nhận mật khẩu</label>
                <input type="password" name="password_confirmation"
                    class="w-full mt-1 px-4 py-3 rounded-xl border focus:ring-2 focus:ring-indigo-200">
            </div>

            <button class="w-full py-3 rounded-xl bg-slate-900 text-white font-bold hover:bg-slate-800">
                Đăng ký
            </button>
        </form>

        <p class="text-center text-sm text-slate-500 mt-6">
            Đã có tài khoản?
            <a href="{{ route('client.login') }}" class="text-indigo-600 font-bold">Đăng nhập</a>
        </p>
    </div>
</div>
@endsection
