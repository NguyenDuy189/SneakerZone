@extends('client.layouts.app')

@section('title', 'Đổi mật khẩu')

@section('content')
<div class="container mx-auto px-4 py-10 max-w-xl">
    <h1 class="text-2xl font-bold mb-6">Đổi mật khẩu</h1>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-50 text-green-700 rounded">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('client.account.password.update') }}" class="space-y-5">
        @csrf

        <div>
            <label class="font-semibold">Mật khẩu hiện tại</label>
            <input type="password" name="current_password"
                   class="w-full mt-1 border rounded px-4 py-2">
        </div>

        <div>
            <label class="font-semibold">Mật khẩu mới</label>
            <input type="password" name="password"
                   class="w-full mt-1 border rounded px-4 py-2">
        </div>

        <div>
            <label class="font-semibold">Nhập lại mật khẩu mới</label>
            <input type="password" name="password_confirmation"
                   class="w-full mt-1 border rounded px-4 py-2">
        </div>

        <button class="px-6 py-2 bg-indigo-600 text-white rounded font-bold">
            Đổi mật khẩu
        </button>
    </form>
</div>
@endsection
