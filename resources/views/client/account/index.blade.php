@extends('client.layouts.app')

@section('title', 'Tài khoản')

@section('content')
<div class="container my-5" style="max-width: 700px">
    <h3 class="mb-4 fw-bold">👤 Tài khoản của tôi</h3>

    <div class="card shadow-sm">
        <div class="card-body">
            <p><strong>Họ tên:</strong> {{ auth()->user()->full_name ?? 'Chưa cập nhật' }}</p>
            <p><strong>Email:</strong> {{ auth()->user()->email }}</p>
            <p><strong>SĐT:</strong> {{ auth()->user()->phone ?? 'Chưa có' }}</p>

            <hr>

            <div class="d-flex gap-3">
                <a href="{{ route('profile.edit') }}" class="btn btn-outline-dark">
                    ✏️ Chỉnh sửa hồ sơ
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="btn btn-danger">
                        🚪 Đăng xuất
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
