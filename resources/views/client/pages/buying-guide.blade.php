@extends('client.layouts.app')

@section('title', 'Hướng dẫn mua hàng')

@section('content')
    <div class="container mx-auto px-4 py-12 max-w-4xl">
        <h1 class="text-3xl font-display font-bold text-slate-900 mb-8 border-b pb-4">Hướng dẫn mua hàng</h1>
        
        <div class="prose prose-slate max-w-none prose-headings:font-display prose-a:text-indigo-600">
            <h3>Bước 1: Tìm kiếm sản phẩm</h3>
            <p>Bạn có thể tìm sản phẩm theo danh mục hoặc sử dụng thanh tìm kiếm...</p>

            <h3>Bước 2: Thêm vào giỏ hàng</h3>
            <p>Chọn size và màu sắc phù hợp, sau đó nhấn nút "Thêm vào giỏ".</p>

            <h3>Bước 3: Thanh toán</h3>
            <p>Điền thông tin giao hàng và chọn phương thức thanh toán...</p>
        </div>
    </div>
@endsection