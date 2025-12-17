@extends('client.layouts.app')

@section('title', 'Tài khoản của tôi')

@section('content')
<div class="container mx-auto px-4 py-10 max-w-3xl">
    <h1 class="text-2xl font-bold mb-6">Hồ sơ tài khoản</h1>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-50 text-green-700 rounded">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('client.account.profile.update') }}" class="space-y-5">
        @csrf

        <div>
            <label class="font-semibold">Họ tên</label>
            <input name="full_name" value="{{ old('full_name', $user->full_name) }}"
                   class="w-full mt-1 border rounded px-4 py-2">
        </div>

        <div>
            <label class="font-semibold">Email</label>
            <input value="{{ $user->email }}" disabled
                   class="w-full mt-1 bg-gray-100 border rounded px-4 py-2">
        </div>

        <div>
            <label class="font-semibold">Số điện thoại</label>
            <input name="phone" value="{{ old('phone', $user->phone) }}"
                   class="w-full mt-1 border rounded px-4 py-2">
        </div>

        <div>
            <label class="font-semibold">Giới tính</label>
            <select name="gender" class="w-full mt-1 border rounded px-4 py-2">
                <option value="">-- Chọn --</option>
                <option value="male" @selected($user->gender === 'male')>Nam</option>
                <option value="female" @selected($user->gender === 'female')>Nữ</option>
                <option value="other" @selected($user->gender === 'other')>Khác</option>
            </select>
        </div>

        <div>
            <label class="font-semibold">Ngày sinh</label>
            <input type="date" name="birthday"
                   value="{{ old('birthday', optional($user->birthday)->format('Y-m-d')) }}"
                   class="w-full mt-1 border rounded px-4 py-2">
        </div>

        <div>
            <label class="font-semibold">Địa chỉ</label>
            <input name="address" value="{{ old('address', $user->address) }}"
                   class="w-full mt-1 border rounded px-4 py-2">
        </div>

        <button class="px-6 py-2 bg-indigo-600 text-white rounded font-bold">
            Lưu thay đổi
        </button>
    </form>
</div>
@endsection
