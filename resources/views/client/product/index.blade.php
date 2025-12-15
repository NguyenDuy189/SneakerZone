@extends('client.layouts.app') 

@section('title', 'Tất cả sản phẩm')

@section('content')

<div class="container my-5">
    <div class="container my-5">

    {{-- ⭐ SẢN PHẨM NỔI BẬT / SALE --}}
    @if($featuredProducts->count())
    <h2 class="mb-3 fw-bold">🔥 Sản Phẩm Nổi Bật</h2>
    @include('client.product._product_row', ['items' => $featuredProducts])
    <hr>
    @endif

    {{-- 🆕 SẢN PHẨM MỚI --}}
    @if($newProducts->count())
    <h2 class="mb-3 fw-bold">🆕 Sản Phẩm Mới</h2>
    @include('client.product._product_row', ['items' => $newProducts])
    <hr>
    @endif

    {{-- 👟 SẢN PHẨM CHẠY BỘ --}}
    @if($runningProducts->count())
    <h2 class="mb-3 fw-bold">👟 Giày Chạy Bộ</h2>
    @include('client.product._product_row', ['items' => $runningProducts])
    <hr>
    @endif

    {{-- 🔥 SẢN PHẨM BÁN CHẠY --}}
    @if($bestSellerProducts->count())
    <h2 class="mb-3 fw-bold">🔥 Sản Phẩm Bán Chạy</h2>
    @include('client.product._product_row', ['items' => $bestSellerProducts])
    <hr>
    @endif
</div>

    <h1 class="mb-4 text-center fw-bold">Tất Cả Sản Phẩm</h1>
    <div class="row">
        @foreach ($products as $product)
        <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
            <div class="card product-card h-100 border-0 shadow-sm">
                
                {{-- ẢNH SẢN PHẨM --}}
                <div class="position-relative overflow-hidden" style="padding-top: 100%; background: #f8f9fa;">
                    <a href="{{ route('client.products.show', ['slug' => $product->slug]) }}">
                        {{-- 
                           Sửa lỗi ảnh:
                           1. Dùng $product->thumbnail (khớp với Seeder)
                           2. Thêm onerror: Nếu ảnh lỗi, nó sẽ hiện ảnh màu xám thay thế
                        --}}
                        <img 
                            src="{{ $product->image ? asset('img/products/' . $product->image) : asset('img/no-image.png') }}"
                            class="card-img-top position-absolute top-0 start-0 w-100 h-100"
                            style="object-fit: contain;
                                   background-color: #f8f9fa;
                            alt="{{ $product->name }}">
                    </a>
                </div>
                
                <div class="card-body">
                    {{-- Tên sản phẩm --}}
                    <h5 class="card-title" style="font-size: 16px;">
                        <a href="{{ route('client.products.show', ['slug' => $product->slug]) }}" class="text-dark text-decoration-none fw-bold">
                            {{ $product->name }}
                        </a>
                    </h5>
                    
                    {{-- GIÁ SẢN PHẨM (Sửa từ price thành price_min) --}}
                    <p class="card-text text-danger fw-bold">
                        {{-- Kiểm tra xem có giá không, nếu không thì hiện 'Liên hệ' --}}
                        {{ $product->price_min ? number_format($product->price_min) . ' VNĐ' : 'Liên hệ' }}
                    </p>
                    
                    {{-- Nút mua --}}
                    <form action="{{ route('cart.add', $product->id) }}" method="POST" style="margin-top:10px;">
    @csrf
    <button type="submit" style="padding:10px 15px;background:red;color:#fff;border:none;">
        TEST ADD CART
    </button>
</form>


                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>

{{-- Thêm CSS nhẹ cho thẻ card đẹp hơn --}}
<style>
    .product-card { transition: transform 0.3s; }
    .product-card:hover { transform: translateY(-5px); }
</style>

@endsection