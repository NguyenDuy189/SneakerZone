@extends('client.layouts.app')

@section('title', 'Đăng nhập')

@section('content')
<div class="container my-5" style="max-width: 450px;">
    <h3 class="text-center fw-bold mb-4">Đăng nhập SneakerZone</h3>

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control" required autofocus>
        </div>

        <div class="mb-3">
            <label>Mật khẩu</label>
            <input type="password" name="password" class="form-control" required>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <input type="checkbox" name="remember"> Ghi nhớ
            </div>
            <a href="{{ route('password.request') }}">Quên mật khẩu?</a>
        </div>

        <button class="btn btn-dark w-100 mb-3">Đăng nhập</button>

        <p class="text-center">
            Chưa có tài khoản?
            <a href="{{ route('register') }}">Đăng ký</a>
        </p>

        <div class="text-center mt-3">
    <a href="{{ route('client.products.index') }}" class="text-decoration-none">
        🛍️ Vào cửa hàng (xem sản phẩm)
    </a>
        </div>
     
    </form>
</div>
@endsection
