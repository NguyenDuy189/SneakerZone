@extends('client.layouts.app')

@section('title', 'Kết quả tìm kiếm')

@section('content')
<div class="container my-5">
    <h3 class="mb-4">
        Kết quả tìm kiếm cho: <strong>"{{ $keyword }}"</strong>
    </h3>

    @if($products->count())
        <div class="row">
            @foreach($products as $product)
                <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                    @include('client.product._product_card', ['product' => $product])
                </div>
            @endforeach
        </div>
    @else
        <p class="text-muted">Không tìm thấy sản phẩm phù hợp.</p>
    @endif
</div>
@endsection
