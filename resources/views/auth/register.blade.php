@extends('client.layouts.app')

@section('title', 'Đăng ký')

@section('content')
<div class="container my-5" style="max-width: 450px;">
    <h3 class="text-center fw-bold mb-4">Đăng ký SneakerZone</h3>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div class="mb-3">
            <label>Họ tên</label>
            <input type="text" name="name" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Mật khẩu</label>
            <input type="password" name="password" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Xác nhận mật khẩu</label>
            <input type="password" name="password_confirmation" class="form-control" required>
        </div>

        <button class="btn btn-dark w-100 mb-3">Đăng ký</button>

        <p class="text-center">
            Đã có tài khoản?
            <a href="{{ route('login') }}">Đăng nhập</a>
        </p>
    </form>
</div>
@endsection
