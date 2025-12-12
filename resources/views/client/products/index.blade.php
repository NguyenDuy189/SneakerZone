@extends('client.layouts.app')

@section('title', __('messages.shop') . ' - SneakerZone Premium')

@section('content')

{{-- HEADER SHOP --}}
<div class="bg-light py-5 mb-5">
    <div class="container text-center">
        <h1 class="display-4 fw-black text-uppercase tracking-widest animate__animated animate__fadeInUp">
            {{ __('messages.shop') }}
        </h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb justify-content-center text-uppercase fs-7 ls-1">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-muted text-decoration-none">{{ __('messages.home') }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ __('messages.shop') }}</li>
            </ol>
        </nav>
    </div>
</div>

<div class="container pb-5">
    <div class="row">
        
        {{-- SIDEBAR FILTER (Sticky) --}}
        <div class="col-lg-3 mb-5">
            <div class="sticky-top" style="top: 100px; z-index: 900;">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-bold text-uppercase mb-0">{{ __('messages.filter') }}</h5>
                    <a href="{{ route('client.products.index') }}" class="text-muted fs-7 text-decoration-none hover-underline">Reset</a>
                </div>

                <form action="{{ route('client.products.index') }}" method="GET" id="filterForm">
                    {{-- Giữ lại từ khóa tìm kiếm nếu có --}}
                    @if(request('keyword'))
                        <input type="hidden" name="keyword" value="{{ request('keyword') }}">
                    @endif

                    {{-- 1. DANH MỤC --}}
                    <div class="mb-4 border-bottom pb-4">
                        <h6 class="fw-bold text-uppercase fs-7 text-muted mb-3">{{ __('messages.category') }}</h6>
                        <ul class="list-unstyled">
                            @foreach($categories as $cat)
                            <li class="mb-2">
                                <label class="custom-checkbox d-flex align-items-center cursor-pointer">
                                    <input type="radio" name="category" value="{{ $cat->slug }}" 
                                           class="form-check-input me-2 bg-dark border-dark" 
                                           onchange="this.form.submit()"
                                           {{ request('category') == $cat->slug ? 'checked' : '' }}>
                                    <span class="{{ request('category') == $cat->slug ? 'fw-bold text-dark' : 'text-secondary' }} transition-all">
                                        {{ $cat->name }}
                                    </span>
                                </label>
                            </li>
                            @endforeach
                        </ul>
                    </div>

                    {{-- 2. KHOẢNG GIÁ --}}
                    <div class="mb-4 border-bottom pb-4">
                        <h6 class="fw-bold text-uppercase fs-7 text-muted mb-3">{{ __('messages.price') }}</h6>
                        <div class="d-flex gap-2 align-items-center">
                            <input type="number" name="min_price" class="form-control form-control-sm rounded-0 border-secondary" 
                                   placeholder="Min" value="{{ request('min_price') }}">
                            <span>-</span>
                            <input type="number" name="max_price" class="form-control form-control-sm rounded-0 border-secondary" 
                                   placeholder="Max" value="{{ request('max_price') }}">
                        </div>
                        <button type="submit" class="btn btn-dark btn-sm w-100 mt-3 rounded-0 text-uppercase fw-bold">
                            Áp dụng
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- PRODUCT GRID --}}
        <div class="col-lg-9">
            
            {{-- TOOLBAR --}}
            <div class="d-flex justify-content-between align-items-center mb-4">
                <span class="text-muted fs-7">Hiển thị {{ $products->firstItem() }}-{{ $products->lastItem() }} của {{ $products->total() }} kết quả</span>
                
                <div class="dropdown">
                    <button class="btn btn-outline-dark btn-sm dropdown-toggle rounded-0 px-3 text-uppercase" type="button" data-bs-toggle="dropdown">
                        {{ __('messages.sort') }}: 
                        @switch(request('sort'))
                            @case('price_asc') Giá tăng dần @break
                            @case('price_desc') Giá giảm dần @break
                            @case('name_asc') Tên A-Z @break
                            @default Mới nhất
                        @endswitch
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end rounded-0 shadow-sm border-0">
                        <li><a class="dropdown-item fs-7" href="{{ request()->fullUrlWithQuery(['sort' => 'latest']) }}">Mới nhất</a></li>
                        <li><a class="dropdown-item fs-7" href="{{ request()->fullUrlWithQuery(['sort' => 'price_asc']) }}">Giá tăng dần</a></li>
                        <li><a class="dropdown-item fs-7" href="{{ request()->fullUrlWithQuery(['sort' => 'price_desc']) }}">Giá giảm dần</a></li>
                    </ul>
                </div>
            </div>

            {{-- LISTING --}}
            @if($products->count() > 0)
                <div class="row g-4">
                    @foreach($products as $product)
                        <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-duration="800">
                            {{-- Sử dụng lại Component Card cao cấp đã viết trước đó --}}
                            <div class="product-card-premium h-100 group">
                                <div class="img-wrapper mb-3 position-relative bg-light rounded-3 overflow-hidden" style="padding-top: 100%;">
                                    <a href="{{ route('client.products.show', $product->slug) }}">
                                        <img src="{{ $product->image ? asset('storage/'.$product->image) : asset('img/no-image.png') }}" 
                                             class="position-absolute top-0 start-0 w-100 h-100 object-fit-contain p-4 transition-transform duration-500 hover-scale" 
                                             alt="{{ $product->name }}">
                                    </a>
                                    {{-- Quick Add Btn --}}
                                    <button class="btn btn-dark btn-icon position-absolute bottom-0 end-0 m-3 rounded-circle shadow-lg d-flex align-items-center justify-content-center opacity-0 group-hover-visible" 
                                            style="width: 40px; height: 40px; transition: all 0.3s;">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                                <div class="product-info">
                                    <div class="text-muted fs-8 text-uppercase mb-1">{{ $product->category->name ?? 'Sneaker' }}</div>
                                    <h6 class="fw-bold mb-1"><a href="{{ route('client.products.show', $product->slug) }}" class="text-dark text-decoration-none">{{ $product->name }}</a></h6>
                                    <div class="fw-bold text-accent">
                                        {{ number_format($product->price_min, 0, ',', '.') }}₫
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- PAGINATION --}}
                <div class="mt-5 d-flex justify-content-center">
                    {{ $products->links('pagination::bootstrap-5') }}
                </div>
            @else
                <div class="text-center py-5">
                    <img src="https://cdn-icons-png.flaticon.com/512/4076/4076432.png" width="80" class="mb-3 opacity-50">
                    <h5 class="text-muted">Không tìm thấy sản phẩm nào.</h5>
                    <a href="{{ route('client.products.index') }}" class="btn btn-dark rounded-pill mt-3 px-4">Xóa bộ lọc</a>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
    /* CSS Riêng cho trang Shop */
    .hover-scale:hover { transform: scale(1.08); }
    .group:hover .group-hover-visible { opacity: 1; transform: translateY(0); }
    .group-hover-visible { transform: translateY(10px); }
    .form-check-input:checked { background-color: var(--primary); border-color: var(--primary); }
    .pagination .page-link { color: var(--primary); border: none; font-weight: 600; }
    .pagination .active .page-link { background-color: var(--primary); color: #fff; border-radius: 50%; }
</style>
@endsection