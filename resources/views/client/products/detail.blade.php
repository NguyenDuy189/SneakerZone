@extends('client.layouts.app')

@section('title', $product->name)

@section('content')

<div class="container py-5 mt-4">
    <div class="row g-5">
        
        {{-- 1. LEFT: PRODUCT GALLERY (Sticky on Desktop) --}}
        <div class="col-lg-7">
            <div class="row g-2 sticky-top" style="top: 100px; z-index: 1;">
                {{-- Ảnh chính lớn --}}
                <div class="col-12 mb-2">
                    <div class="bg-light rounded-4 overflow-hidden position-relative" style="padding-top: 100%; cursor: zoom-in;">
                        <img id="mainImage" 
                             src="{{ $product->image ? asset('storage/'.$product->image) : asset('img/no-image.png') }}" 
                             class="position-absolute top-0 start-0 w-100 h-100 object-fit-contain p-5" 
                             alt="{{ $product->name }}">
                    </div>
                </div>
                
                {{-- Gallery Thumbnails --}}
                @if($product->gallery_images->count() > 0)
                    <div class="col-3">
                        <div class="bg-light rounded-3 overflow-hidden cursor-pointer border border-transparent hover-border-dark transition-all" 
                             onclick="changeImage('{{ asset('storage/'.$product->image) }}')">
                            <img src="{{ asset('storage/'.$product->image) }}" class="w-100 object-fit-contain p-2 aspect-square">
                        </div>
                    </div>
                    @foreach($product->gallery_images as $img)
                    <div class="col-3">
                        <div class="bg-light rounded-3 overflow-hidden cursor-pointer border border-transparent hover-border-dark transition-all"
                             onclick="changeImage('{{ asset('storage/'.$img->image_path) }}')">
                            <img src="{{ asset('storage/'.$img->image_path) }}" class="w-100 object-fit-contain p-2 aspect-square">
                        </div>
                    </div>
                    @endforeach
                @endif
            </div>
        </div>

        {{-- 2. RIGHT: PRODUCT INFO --}}
        <div class="col-lg-5">
            <div class="ps-lg-4">
                {{-- Category & Rating --}}
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="badge bg-light text-dark border px-3 py-2 rounded-pill text-uppercase fs-8 fw-bold tracking-wide">
                        {{ $product->category->name ?? 'Sneaker' }}
                    </span>
                    <div class="text-warning fs-7">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i>
                        <span class="text-muted ms-1 text-lowercase">(4.8/5)</span>
                    </div>
                </div>

                {{-- Title & Price --}}
                <h1 class="display-6 fw-black text-uppercase mb-2 lh-sm">{{ $product->name }}</h1>
                <div class="fs-3 fw-bold text-accent mb-4">
                    {{ number_format($product->price_min, 0, ',', '.') }}₫
                </div>

                {{-- Short Description --}}
                <p class="text-secondary mb-5 lh-lg">
                    {{ Str::limit(strip_tags($product->description), 150) }}
                </p>

                {{-- ADD TO CART FORM --}}
                <form action="#" method="POST" class="mb-5">
                    @csrf
                    
                    {{-- Variants Selection --}}
                    @if($product->variants->count() > 0)
                        <div class="mb-4">
                            <label class="fw-bold text-uppercase fs-8 mb-2 d-block">Chọn Size</label>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($product->variants as $variant)
                                    <input type="radio" class="btn-check" name="variant_id" id="size-{{ $variant->id }}" autocomplete="off">
                                    <label class="btn btn-outline-dark rounded-0 px-4 py-2 fw-bold" for="size-{{ $variant->id }}">
                                        {{ $variant->size }}
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Actions --}}
                    <div class="d-flex gap-3 mt-5">
                        <div class="input-group" style="width: 140px;">
                            <button class="btn btn-outline-secondary rounded-start-pill border-end-0" type="button">-</button>
                            <input type="text" class="form-control text-center border-start-0 border-end-0 fw-bold" value="1">
                            <button class="btn btn-outline-secondary rounded-end-pill border-start-0" type="button">+</button>
                        </div>
                        <button type="submit" class="btn btn-dark rounded-pill flex-grow-1 fw-bold text-uppercase tracking-wide py-3 hover-scale shadow-lg">
                            {{ __('messages.add_to_cart') }}
                        </button>
                        <button type="button" class="btn btn-light border rounded-circle flex-shrink-0 d-flex align-items-center justify-content-center hover-bg-danger hover-text-white transition-all" style="width: 54px; height: 54px;">
                            <i class="far fa-heart fs-5"></i>
                        </button>
                    </div>
                </form>

                {{-- Trust Badges --}}
                <div class="bg-light p-4 rounded-3 border border-dashed border-secondary mb-5">
                    <div class="d-flex gap-4 align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fas fa-truck text-muted fs-4"></i>
                            <span class="fs-8 fw-bold text-uppercase">Free Ship</span>
                        </div>
                        <div class="vr"></div>
                        <div class="d-flex align-items-center gap-2">
                            <i class="fas fa-check-circle text-muted fs-4"></i>
                            <span class="fs-8 fw-bold text-uppercase">Chính hãng</span>
                        </div>
                        <div class="vr"></div>
                        <div class="d-flex align-items-center gap-2">
                            <i class="fas fa-shield-alt text-muted fs-4"></i>
                            <span class="fs-8 fw-bold text-uppercase">Bảo hành</span>
                        </div>
                    </div>
                </div>

                {{-- Accordion Info --}}
                <div class="accordion accordion-flush" id="productAccordion">
                    <div class="accordion-item bg-transparent">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed bg-transparent fw-bold text-uppercase shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#desc">
                                {{ __('messages.description') }}
                            </button>
                        </h2>
                        <div id="desc" class="accordion-collapse collapse" data-bs-parent="#productAccordion">
                            <div class="accordion-body text-secondary lh-lg">
                                {!! $product->description !!}
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item bg-transparent">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed bg-transparent fw-bold text-uppercase shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#reviews">
                                {{ __('messages.reviews') }} ({{ $product->reviews->count() }})
                            </button>
                        </h2>
                        <div id="reviews" class="accordion-collapse collapse" data-bs-parent="#productAccordion">
                            <div class="accordion-body">
                                {{-- Loop Review Component --}}
                                <p class="text-muted">Chưa có đánh giá nào.</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- RELATED PRODUCTS --}}
    @if(isset($relatedProducts) && $relatedProducts->count() > 0)
        <div class="mt-5 pt-5 border-top">
            <h3 class="fw-black text-uppercase mb-4">{{ __('messages.related_products') }}</h3>
            @include('client.product._product_row', ['items' => $relatedProducts])
        </div>
    @endif
</div>

<script>
    function changeImage(src) {
        const mainImg = document.getElementById('mainImage');
        mainImg.style.opacity = 0;
        setTimeout(() => {
            mainImg.src = src;
            mainImg.style.opacity = 1;
        }, 200);
    }
</script>

<style>
    /* CSS Riêng cho trang Detail */
    .aspect-square { aspect-ratio: 1/1; }
    .hover-border-dark:hover { border-color: var(--primary) !important; }
    .hover-scale:hover { transform: translateY(-3px); }
    .hover-bg-danger:hover { background-color: #dc3545 !important; border-color: #dc3545 !important; color: white !important; }
    .btn-check:checked + .btn-outline-dark { background-color: var(--primary); color: white; }
    #mainImage { transition: opacity 0.3s ease-in-out; }
</style>

@endsection