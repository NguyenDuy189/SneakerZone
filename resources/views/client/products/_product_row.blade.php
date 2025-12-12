{{-- CSS CHO PRODUCT CARD --}}
<style>
    .product-card {
        transition: all 0.3s ease;
        border: 1px solid transparent;
        background: #fff;
    }
    .product-card:hover {
        border-color: #eee;
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.05);
    }
    
    .product-img-wrap {
        position: relative;
        padding-top: 100%; /* Square aspect ratio */
        background-color: #f6f6f6;
        overflow: hidden;
    }
    
    .product-img {
        position: absolute;
        top: 0; 
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: contain; /* Ensures whole shoe is visible */
        padding: 10px; /* Padding inside the image area */
        transition: transform 0.5s ease;
    }
    
    .product-card:hover .product-img {
        transform: scale(1.05);
    }

    .card-body {
        padding: 15px;
        text-align: left;
    }

    .product-category {
        font-size: 11px;
        color: #999;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 4px;
        display: block;
    }

    .product-title {
        font-size: 15px;
        font-weight: 600;
        color: #111;
        margin-bottom: 6px;
        line-height: 1.4;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        height: 42px; /* Fixed height for title alignment */
    }
    
    .product-title a:hover { color: #555; }

    .product-price {
        font-size: 15px;
        font-weight: 700;
        color: #111;
    }

    .btn-add-cart {
        width: 100%;
        background-color: #111;
        color: #fff;
        font-size: 13px;
        font-weight: 600;
        padding: 10px 0;
        border-radius: 4px;
        margin-top: 10px;
        opacity: 0;
        transform: translateY(10px);
        transition: all 0.3s ease;
    }

    /* Show button on hover on desktop */
    .product-card:hover .btn-add-cart {
        opacity: 1;
        transform: translateY(0);
    }

    /* Always show button on mobile */
    @media (max-width: 768px) {
        .btn-add-cart { opacity: 1; transform: translateY(0); }
    }
</style>

<div class="row g-4">
    @foreach($items as $item)
    <div class="col-lg-3 col-md-4 col-sm-6 col-6">
        <div class="card product-card h-100">
            
            {{-- IMAGE --}}
            <div class="product-img-wrap">
                <a href="{{ route('client.products.show', $item->slug) }}">
                    @php
                        $currentImage = $item->thumbnail ?? $item->image;
                        if (empty($currentImage)) {
                            $imgUrl = asset('img/no-image.png');
                        } elseif (Str::startsWith($currentImage, 'http')) {
                            $imgUrl = $currentImage;
                        } else {
                            // Ensure path logic matches your storage setup
                            $imgUrl = asset('img/products/' . $currentImage);
                        }
                    @endphp
                    <img 
                        src="{{ $imgUrl }}"
                        class="product-img"
                        alt="{{ $item->name }}"
                        onerror="this.onerror=null; this.src='{{ asset('img/no-image.png') }}';"
                    >
                </a>
                
                {{-- Badges (Optional) --}}
                @if($item->is_featured)
                    <span class="badge bg-dark position-absolute top-0 start-0 m-2 rounded-0 text-uppercase" style="font-size: 10px; letter-spacing: 1px;">Hot</span>
                @endif
            </div>
            
            {{-- INFO --}}
            <div class="card-body">
                {{-- Category Placeholder (Ideally fetch from relation) --}}
                <span class="product-category">Men's Shoes</span>

                <h5 class="product-title">
                    <a href="{{ route('client.products.show', $item->slug) }}">
                        {{ $item->name }}
                    </a>
                </h5>
                
                <div class="d-flex justify-content-between align-items-center">
                    <p class="product-price mb-0">
                        {{ $item->price_min ? number_format($item->price_min, 0, ',', '.') . '₫' : 'Liên hệ' }}
                    </p>
                </div>

                <a href="#" class="btn btn-add-cart">Thêm vào giỏ</a>
            </div>
        </div>
    </div>
    @endforeach
</div>