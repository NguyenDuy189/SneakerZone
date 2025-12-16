@extends('client.layouts.app')

@section('title', 'Cửa hàng - Tất cả sản phẩm')

@section('content')

{{-- X-DATA để quản lý Filter Mobile --}}
<div x-data="{ mobileFilterOpen: false }" class="bg-white min-h-screen">

    {{-- 1. PAGE HEADER --}}
    <div class="bg-slate-50 border-b border-slate-100 py-10 md:py-16">
        <div class="container mx-auto px-4 text-center">
            <h1 class="text-3xl md:text-5xl font-black text-slate-900 uppercase tracking-tight mb-4">
                Kho Giày <span class="text-indigo-600">Chính Hãng</span>
            </h1>
            <p class="text-slate-500 max-w-2xl mx-auto text-sm md:text-base">
                Khám phá bộ sưu tập đa dạng từ các thương hiệu hàng đầu thế giới.
            </p>
        </div>
    </div>

    <div class="container mx-auto px-4 py-10">
        <div class="flex flex-col lg:flex-row gap-8 xl:gap-12">

            {{-- 2. SIDEBAR FILTER (Desktop) --}}
            <aside class="hidden lg:block w-1/4 xl:w-1/5 flex-shrink-0">
                <div class="sticky top-24 pr-4 space-y-8">
                    <form action="{{ route('client.products.index') }}" method="GET" id="filterForm">
                        
                        {{-- Tìm kiếm --}}
                        <div class="mb-8">
                            <h3 class="font-bold text-slate-900 mb-4 uppercase text-xs tracking-wider">Tìm kiếm</h3>
                            <div class="relative">
                                <input type="text" name="keyword" value="{{ request('keyword') }}" placeholder="Tên sp..." class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all">
                                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-3 text-slate-400"></i>
                            </div>
                        </div>

                        {{-- Danh mục --}}
                        <div class="mb-8 border-b border-slate-100 pb-8">
                            <h3 class="font-bold text-slate-900 uppercase text-xs tracking-wider mb-4">Danh mục</h3>
                            <div class="space-y-2 max-h-60 overflow-y-auto custom-scrollbar pr-2">
                                <label class="flex items-center gap-3 cursor-pointer group">
                                    <input type="radio" name="category" value="" class="peer accent-indigo-600" {{ !request('category') ? 'checked' : '' }} onchange="this.form.submit()">
                                    <span class="text-sm text-slate-600 group-hover:text-indigo-600">Tất cả</span>
                                </label>
                                @foreach($categories as $cat)
                                    <label class="flex items-center gap-3 cursor-pointer group">
                                        <input type="radio" name="category" value="{{ $cat->slug }}" class="peer accent-indigo-600" {{ request('category') == $cat->slug ? 'checked' : '' }} onchange="this.form.submit()">
                                        <div class="flex-1 flex justify-between items-center">
                                            <span class="text-sm text-slate-600 group-hover:text-indigo-600">{{ $cat->name }}</span>
                                            <span class="text-[10px] bg-slate-100 text-slate-500 py-0.5 px-2 rounded-full">{{ $cat->products_count }}</span>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <button type="submit" class="w-full py-3 bg-slate-900 text-white font-bold rounded-xl hover:bg-indigo-600 transition-all shadow-md active:scale-95">Áp dụng</button>
                    </form>
                </div>
            </aside>

            {{-- 3. MAIN CONTENT --}}
            <main class="flex-1">
                {{-- Toolbar --}}
                <div class="flex flex-wrap items-center justify-between gap-4 mb-8">
                    <p class="text-sm text-slate-500 hidden md:block">Tìm thấy <span class="font-bold text-slate-900">{{ $products->total() }}</span> sản phẩm</p>
                    <div class="flex items-center gap-2 ml-auto">
                        <span class="text-sm text-slate-500">Sắp xếp:</span>
                        <select onchange="location.href = this.value" class="bg-white border border-slate-200 pl-3 pr-8 py-2 rounded-lg text-sm font-bold text-slate-700 focus:outline-none cursor-pointer">
                            <option value="{{ request()->fullUrlWithQuery(['sort' => 'newest']) }}" {{ request('sort') == 'newest' ? 'selected' : '' }}>Mới nhất</option>
                            <option value="{{ request()->fullUrlWithQuery(['sort' => 'price_asc']) }}" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>Giá tăng dần</option>
                            <option value="{{ request()->fullUrlWithQuery(['sort' => 'price_desc']) }}" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>Giá giảm dần</option>
                        </select>
                    </div>
                </div>

                {{-- PRODUCT GRID --}}
                @if($products->count() > 0)
                    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-3 gap-x-4 gap-y-8 md:gap-x-6 md:gap-y-10">
                        @foreach($products as $key => $product)  {{-- Đặt tên biến key --}}
                            @include('client.products._product_item', [
                                'product' => $product, 
                                'index' => $key  {{-- Truyền index vào đây --}}
                            ])
                        @endforeach
                    </div>

                    {{-- Pagination --}}
                    <div class="mt-12">
                        {{ $products->onEachSide(1)->links('pagination::tailwind') }}
                    </div>
                @else
                    <div class="flex flex-col items-center justify-center py-20 bg-slate-50 rounded-3xl">
                        <i class="fa-solid fa-box-open text-4xl text-slate-300 mb-4"></i>
                        <h3 class="text-xl font-bold text-slate-800">Không tìm thấy sản phẩm</h3>
                        <a href="{{ route('client.products.index') }}" class="mt-4 px-6 py-2 bg-indigo-600 text-white rounded-lg">Xóa bộ lọc</a>
                    </div>
                @endif
            </main>
        </div>
    </div>
</div>
@endsection