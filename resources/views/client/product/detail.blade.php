@extends('client.layouts.app') 

@section('title', $product->name)

@section('content')

<div class="container my-5">
    <div class="row">
        {{-- Phần hiển thị Ảnh sản phẩm --}}
        <div class="col-md-6 d-flex justify-content-center">
            <img 
                src="{{ $product->image ? asset('img/products/' . $product->image) : asset('img/no-image.png') }}" 
                alt="{{ $product->name }}"
                class="img-fluid"
                style="max-height: 380px; width: auto; object-fit: contain; border: 1px solid #eee; border-radius: 10px; padding: 10px;"
            >
        </div>

        {{-- Phần thông tin chi tiết --}}
        <div class="col-md-6">
            <h1>{{ $product->name }}</h1>
            
            <p class="h3 text-danger mb-4">{{ number_format($product->price) }} VNĐ</p>

            <div class="mb-4">
                <strong>Danh mục:</strong> 
                {{ $product->category->name ?? 'Chưa phân loại' }}
            </div>

            <div class="mb-4">
                <strong>Mô tả ngắn:</strong>
                <p>{{ $product->short_description }}</p>
            </div>
            
            {{-- Form thêm vào giỏ hàng --}}
            <form action="#" method="POST">
                @csrf
                <div class="form-group mb-3">
                    <label for="quantity">Số lượng:</label>
                    <input type="number" id="quantity" name="quantity" class="form-control w-25" value="1" min="1">
                </div>
                <button type="submit" class="btn btn-lg btn-success">
                    <i class="fas fa-shopping-cart"></i> Thêm vào giỏ hàng
                </button>
            </form>
            
            <hr>
            
            <h2>Mô tả chi tiết</h2>
            <div>
                {!! $product->description !!}
            </div>

        </div>
    </div>
    
    {{-- Sản phẩm liên quan --}}
    @if($relatedProducts->count())
    <div class="mt-5">
        <h2>Sản phẩm liên quan</h2>
        <div class="row">
            @foreach ($relatedProducts as $related)
                <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                    {{-- Code thẻ sản phẩm --}}
                </div>
            @endforeach
        </div>
    </div>
    @endif
</div>

@endsection
