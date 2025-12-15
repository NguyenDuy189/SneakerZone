<div class="card product-card h-100 border-0 shadow-sm">

    {{-- ẢNH --}}
    <div class="position-relative overflow-hidden" style="padding-top: 100%; background: #f8f9fa;">
        <a href="{{ route('client.products.show', $product->slug) }}">
            <img 
                src="{{ $product->image ? asset('img/products/' . $product->image) : asset('img/no-image.png') }}"
                class="card-img-top position-absolute top-0 start-0 w-100 h-100"
                style="object-fit: contain; background-color: #f8f9fa;"
                alt="{{ $product->name }}">
        </a>
    </div>

    <div class="card-body">
        {{-- TÊN --}}
        <h5 class="card-title" style="font-size: 16px;">
            <a href="{{ route('client.products.show', $product->slug) }}"
               class="text-dark text-decoration-none fw-bold">
                {{ $product->name }}
            </a>
        </h5>

        {{-- GIÁ --}}
        <p class="card-text text-danger fw-bold">
            {{ $product->price_min ? number_format($product->price_min) . ' VNĐ' : 'Liên hệ' }}
        </p>

        {{-- ADD TO CART --}}
        <form action="{{ route('cart.add', $product->id) }}" method="POST">
            @csrf
            <button class="btn btn-dark btn-sm w-100">
                Thêm vào giỏ
            </button>
        </form>
    </div>
</div>
