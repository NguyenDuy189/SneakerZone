@extends('layouts.main')

@section('content')

<h2>Sản phẩm thuộc danh mục: {{ $category->name }}</h2>

<div class="row">
    @foreach ($products as $product)
        <div class="col-md-3 mb-3">
            <div class="card">
                <img src="/uploads/{{ $product->image }}" class="card-img-top">
                <div class="card-body">
                    <h5>{{ $product->name }}</h5>
                    <p>{{ number_format($product->price) }} đ</p>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div>
    {{ $products->links() }}
</div>

@endsection
