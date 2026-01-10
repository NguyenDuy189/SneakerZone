@extends('client.layouts.app')

@section('title', 'Đăng nhập')

@section('content')
<div class="min-h-[70vh] flex items-center justify-center bg-slate-50">
    <div class="w-full max-w-md bg-white rounded-2xl shadow-xl p-8">
        <h1 class="text-2xl font-black text-center mb-6">Đăng nhập</h1>

        <form method="POST" action="{{ route('login.submit') }}" class="space-y-5">
            @csrf

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
                @error('password') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <button class="w-full py-3 rounded-xl bg-indigo-600 text-white font-bold hover:bg-indigo-700">
                Đăng nhập
            </button>
        </form>

        <p class="text-center text-sm text-slate-500 mt-6">
            Chưa có tài khoản?
            <a href="{{ route('register') }}" class="text-indigo-600 font-bold">Đăng ký</a>
        </p>
    </div>
</div>
@endsection
