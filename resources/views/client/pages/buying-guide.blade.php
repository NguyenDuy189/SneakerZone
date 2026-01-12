@extends('client.layouts.app')

@section('title', 'Hướng Dẫn Mua Hàng - Sneaker Zone')

@section('content')
<div class="bg-white py-12">
    <div class="container mx-auto px-4">
        <div class="text-center max-w-3xl mx-auto mb-16">
            <h1 class="text-3xl font-bold text-slate-900 mb-4">Hướng dẫn mua sắm đơn giản</h1>
            <p class="text-slate-500">Chỉ với 4 bước đơn giản, bạn sẽ sở hữu ngay đôi giày mơ ước tại Sneaker Zone.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            {{-- Bước 1 --}}
            <div class="text-center p-6 bg-slate-50 rounded-2xl border border-slate-100 hover:shadow-lg transition-shadow">
                <div class="w-16 h-16 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center text-2xl font-bold mx-auto mb-4">1</div>
                <h3 class="text-lg font-bold text-slate-900 mb-2">Tìm kiếm sản phẩm</h3>
                <p class="text-sm text-slate-600">Duyệt qua danh mục hoặc dùng thanh tìm kiếm để chọn mẫu giày ưng ý.</p>
            </div>

            {{-- Bước 2 --}}
            <div class="text-center p-6 bg-slate-50 rounded-2xl border border-slate-100 hover:shadow-lg transition-shadow">
                <div class="w-16 h-16 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center text-2xl font-bold mx-auto mb-4">2</div>
                <h3 class="text-lg font-bold text-slate-900 mb-2">Thêm vào giỏ hàng</h3>
                <p class="text-sm text-slate-600">Chọn Size, Màu sắc và số lượng rồi nhấn "Thêm vào giỏ" hoặc "Mua ngay".</p>
            </div>

            {{-- Bước 3 --}}
            <div class="text-center p-6 bg-slate-50 rounded-2xl border border-slate-100 hover:shadow-lg transition-shadow">
                <div class="w-16 h-16 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center text-2xl font-bold mx-auto mb-4">3</div>
                <h3 class="text-lg font-bold text-slate-900 mb-2">Thanh toán</h3>
                <p class="text-sm text-slate-600">Điền thông tin giao hàng và chọn phương thức thanh toán (COD hoặc Chuyển khoản).</p>
            </div>

            {{-- Bước 4 --}}
            <div class="text-center p-6 bg-slate-50 rounded-2xl border border-slate-100 hover:shadow-lg transition-shadow">
                <div class="w-16 h-16 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center text-2xl font-bold mx-auto mb-4">4</div>
                <h3 class="text-lg font-bold text-slate-900 mb-2">Nhận hàng</h3>
                <p class="text-sm text-slate-600">Kiểm tra hàng và thanh toán cho shipper. Tận hưởng đôi giày mới!</p>
            </div>
        </div>
        
        <div class="mt-12 text-center">
            <a href="{{ url('/') }}" class="inline-block bg-indigo-600 text-white font-bold py-3 px-8 rounded-full hover:bg-indigo-700 transition-colors">
                Mua sắm ngay
            </a>
        </div>
    </div>
</div>
@endsection