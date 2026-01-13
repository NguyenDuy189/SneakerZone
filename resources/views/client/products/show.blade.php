@extends('client.layouts.app')

@section('title', $product->name . ' - Sneaker Zone')

@section('content')

 {{-- --- BẮT ĐẦU: LOGIC XỬ LÝ ẢNH THÔNG MINH --- --}}
@php
    $mainImg = asset('img/no-image.png'); // 1. Ảnh mặc định
    
    // Lấy đường dẫn từ DB (Ưu tiên cột thumbnail, dự phòng image)
    $dbPath = $product->thumbnail ?? $product->image; 

    if ($dbPath) {
        $cleanPath = ltrim($dbPath, '/'); // Xử lý lỗi thừa dấu /

        // TRƯỜNG HỢP 1: Ảnh nằm trong thư mục public/img (code cũ)
        if (str_contains($cleanPath, 'img/') && file_exists(public_path($cleanPath))) {
            $mainImg = asset($cleanPath);
        }
        // TRƯỜNG HỢP 2: Ảnh nằm trong Storage (Chuẩn mới)
        elseif (\Illuminate\Support\Facades\Storage::disk('public')->exists($cleanPath)) {
            $mainImg = asset('storage/' . $cleanPath);
        }
    }
@endphp

{{-- DATA ALPINEJS CHO PRODUCT DETAIL --}}
<div x-data="productDetail({
        basePrice: {{ $product->price_min ?? 0 }},
        variants: {{ json_encode($variantMap ?? []) }},
        isLiked: {{ ($product->is_liked ?? false) ? 'true' : 'false' }},
        activeImage: '{{ $mainImg }}'
    })" 
    x-init="activeImage = '{{ $mainImg }}'"
    class="bg-[#F8F9FA] min-h-screen font-sans text-slate-800 pb-20 relative">

    {{-- 1. BREADCRUMB --}}
    <div class="bg-white border-b border-slate-100 sticky top-[60px] z-30 shadow-sm">
        <div class="container mx-auto px-4 py-3 max-w-6xl">
            <nav class="flex items-center text-[11px] font-bold uppercase tracking-wide text-slate-400">
                <a href="{{ route('client.home') }}" class="hover:text-black transition-colors">Home</a>
                <i class="fa-solid fa-chevron-right text-[9px] mx-2 text-slate-300"></i>
                <a href="{{ route('client.products.index') }}" class="hover:text-black transition-colors">Shop</a>
                <i class="fa-solid fa-chevron-right text-[9px] mx-2 text-slate-300"></i>
                <span class="text-slate-900 truncate">{{ $product->name }}</span>
            </nav>
        </div>
    </div>

    {{-- MAIN CONTAINER --}}
    <div class="container mx-auto px-4 py-8 max-w-6xl">
        
        <div class="bg-white rounded-2xl shadow-xl shadow-slate-200/50 border border-white overflow-hidden mb-8">
            <div class="grid grid-cols-1 lg:grid-cols-12">
                
                {{-- === CỘT TRÁI: ẢNH === --}}
<div class="lg:col-span-6 bg-white p-6 lg:p-8 border-r border-slate-50">
    <div class="grid gap-4">
        
        {{-- A. ẢNH CHÍNH (BIG IMAGE) --}}
        <div class="relative aspect-square bg-[#F4F4F4] rounded-xl overflow-hidden cursor-zoom-in group"
             @click="zoomImage = activeImage; zoomOpen = true">
            
            {{-- Lưu ý: Dùng :src của Alpine để đổi ảnh, dùng src của Blade để SEO --}}
            <img :src="activeImage" 
                 src="{{ $mainImg }}" 
                 alt="{{ $product->name }}" 
                 class="w-full h-full object-cover object-center transition-transform duration-500 group-hover:scale-110 mix-blend-multiply">
            
            @if($product->is_featured)
                <div class="absolute top-4 left-4 flex items-center gap-1 bg-gradient-to-r from-rose-600 to-orange-500 text-white text-[10px] font-black px-3 py-1.5 rounded uppercase tracking-widest shadow-lg shadow-orange-500/30 animate-pulse">
                    <i class="fa-solid fa-fire"></i> Hot Item
                </div>
            @endif
        </div>

        {{-- B. THUMBNAILS (ẢNH NHỎ) --}}
        <div class="grid grid-cols-6 gap-2">
            
            {{-- 1. Thumbnail của ảnh chính (ĐÃ SỬA: Dùng $mainImg) --}}
            <button class="aspect-square rounded-lg overflow-hidden border transition-all bg-[#F4F4F4]"
                    :class="activeImage === '{{ $mainImg }}' ? 'border-slate-900 ring-1 ring-slate-900' : 'border-transparent hover:border-slate-300'"
                    @click="activeImage = '{{ $mainImg }}'">
                <img src="{{ $mainImg }}" class="w-full h-full object-cover mix-blend-multiply">
            </button>

            {{-- 2. Các ảnh Gallery (Logic cũ của bạn đã đúng) --}}
            @if($product->gallery_images)
                @foreach($product->gallery_images as $img)
                    @php
                        $galleryUrl = asset('img/no-image.png');
                        $cleanPath = ltrim($img->image_path, '/');
                        if (str_contains($cleanPath, 'img/') && file_exists(public_path($cleanPath))) {
                            $galleryUrl = asset($cleanPath);
                        } elseif (\Illuminate\Support\Facades\Storage::disk('public')->exists($cleanPath)) {
                            $galleryUrl = asset('storage/' . $cleanPath);
                        }
                    @endphp

                    <button class="aspect-square rounded-lg overflow-hidden border transition-all bg-[#F4F4F4]"
                            :class="activeImage === '{{ $galleryUrl }}' ? 'border-slate-900 ring-1 ring-slate-900' : 'border-transparent hover:border-slate-300'"
                            @click="activeImage = '{{ $galleryUrl }}'">
                        <img src="{{ $galleryUrl }}" class="w-full h-full object-cover mix-blend-multiply">
                    </button>
                @endforeach
            @endif
        </div>
    </div>
</div>
                   
{{-- --- KẾT THÚC LOGIC --- --}} 

                {{-- === CỘT PHẢI: INFO === --}}
                <div class="lg:col-span-6 p-6 lg:p-10 flex flex-col justify-center">
                    
                    {{-- Header Information --}}
                    <div class="mb-6 pb-6 border-b border-dashed border-slate-200">
                        
                        <div class="flex items-center gap-3 mb-3">
                            <span class="text-[10px] font-black text-indigo-600 uppercase tracking-widest bg-indigo-50 px-2 py-1 rounded">
                                {{ $product->category->name ?? 'Collection' }}
                            </span>
                            
                            {{-- Rating Summary --}}
                            <div class="flex items-center gap-1">
                                @php 
                                    $avgRating = $product->reviews_avg_rating ?? 0;
                                    $countRating = $product->reviews_count ?? 0;
                                @endphp

                                @if($countRating > 0)
                                    <div class="flex text-amber-400 text-xs">
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="{{ $i <= round($avgRating) ? 'fa-solid' : 'fa-regular' }} fa-star"></i>
                                        @endfor
                                    </div>
                                    <span class="text-xs font-bold text-slate-500 ml-1">
                                        {{ number_format($avgRating, 1) }} ({{ $countRating }} đánh giá)
                                    </span>
                                @else
                                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wide">
                                        Chưa có đánh giá
                                    </span>
                                @endif
                            </div>
                        </div>

                        <h1 class="text-3xl font-black text-slate-900 leading-tight mb-2 uppercase tracking-tight">{{ $product->name }}</h1>
                        
                        <div class="text-2xl font-bold text-slate-900 flex items-center gap-3">
                            <span x-text="formatMoney(currentPrice)"></span>
                        </div>
                    </div>

                    {{-- Form Add To Cart --}}
                    <form id="addToCartForm" class="space-y-6" @submit.prevent="submitCart()">
                        
                        <input type="hidden" name="product_variant_id" x-model="selectedVariantId">
                        <input type="hidden" name="product_id" value="{{ $product->id }}">

                        {{-- Chọn Thuộc tính --}}
                        @if(isset($groupedAttributes) && $groupedAttributes->count() > 0)
                            @foreach($groupedAttributes as $name => $values)
                                <div>
                                    <div class="flex justify-between mb-1.5">
                                        <span class="text-xs font-bold text-slate-900 uppercase tracking-wide">{{ $name }}</span>
                                        @if(strtolower($name) === 'size')
                                            <button type="button" class="text-[10px] font-bold text-slate-400 underline decoration-dashed hover:text-black">Size Guide</button>
                                        @endif
                                    </div>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($values as $val)
                                            <button type="button" 
                                                    class="h-9 px-4 border rounded text-xs font-bold transition-all relative"
                                                    :class="selectedAttributes['{{ $name }}'] == {{ $val->id }} 
                                                        ? 'bg-slate-900 text-white border-slate-900 shadow-md transform -translate-y-0.5' 
                                                        : 'bg-white text-slate-600 border-slate-200 hover:border-slate-400 hover:text-black'"
                                                    @click="selectAttribute('{{ $name }}', {{ $val->id }})">
                                                {{ $val->value }}
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                            <div x-show="errorMsg" x-transition class="text-rose-600 text-[11px] font-bold flex items-center gap-1 mt-1">
                                <i class="fa-solid fa-circle-exclamation"></i> <span x-text="errorMsg"></span>
                            </div>
                        @endif

                        {{-- Control Bar (Qty & Price) --}}
                        <div class="bg-slate-50 p-4 rounded-xl border border-slate-100 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <span class="text-xs font-bold text-slate-500 uppercase">SL:</span>
                                <div class="flex items-center bg-white border border-slate-200 rounded h-9 shadow-sm">
                                    <button type="button" @click="if(qty > 1) qty--" class="w-8 h-full hover:bg-slate-100 transition-colors rounded-l text-slate-500"><i class="fa-solid fa-minus text-[10px]"></i></button>
                                    <input type="number" name="quantity" x-model="qty" class="w-10 text-center font-bold text-slate-900 bg-transparent outline-none text-sm" readonly>
                                    <button type="button" @click="if(qty < currentStock) qty++" class="w-8 h-full hover:bg-slate-100 transition-colors rounded-r text-slate-500"><i class="fa-solid fa-plus text-[10px]"></i></button>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-[10px] text-slate-400 uppercase font-bold">Tạm tính</div>
                                <div class="text-base font-black text-indigo-600" x-text="formatMoney(currentPrice * qty)"></div>
                            </div>
                        </div>

                        {{-- Stock Status --}}
                        <div class="flex items-center justify-between text-xs font-bold pt-2 min-h-[20px]">
                            <div x-show="currentStock > 0" class="text-emerald-600 flex items-center gap-1" x-cloak>
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> 
                                Còn <span x-text="currentStock"></span> sản phẩm
                            </div>
                            <div x-show="currentStock <= 0 && selectedVariantId !== null" class="text-rose-500 flex items-center gap-1" x-cloak>
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Hết hàng
                            </div>
                             <div x-show="currentStock <= 0 && selectedVariantId === null" class="text-slate-400 flex items-center gap-1" x-cloak>
                                <i class="fa-solid fa-box-open"></i> Vui lòng chọn phân loại
                            </div>
                        </div>

                        {{-- Actions --}}
                        <div class="flex gap-3 pt-2">
                            <button type="button" @click="submitCart()" 
                                    :disabled="currentStock <= 0"
                                    class="flex-1 h-12 bg-slate-900 text-white font-bold rounded-lg hover:bg-indigo-600 transition-all shadow-xl shadow-slate-900/10 hover:shadow-indigo-600/30 flex items-center justify-center gap-2 uppercase text-xs tracking-widest disabled:opacity-50 disabled:cursor-not-allowed transform active:scale-95">
                                <i class="fa-solid fa-bag-shopping mb-0.5"></i> Thêm vào giỏ
                            </button>
                            
                            <button 
                                {{-- [FIX] Truyền ID sản phẩm vào đây --}}
                                @click="toggleWishlist({{ $product->id }})" 
                                type="button" 
                                class="group flex h-12 w-12 items-center justify-center rounded-xl border transition-all duration-300 hover:shadow-lg"
                                :class="isLiked ? 'border-rose-200 bg-rose-50' : 'border-slate-200 bg-white hover:border-indigo-200'"
                            >
                                <i class="fa-heart text-xl transition-colors duration-300"
                                :class="isLiked ? 'fa-solid text-rose-500' : 'fa-regular text-slate-400 group-hover:text-indigo-500'">
                                </i>
                            </button>
                        </div>
                    </form>

                    {{-- Footer Info --}}
                    <div class="grid grid-cols-2 gap-4 mt-8 pt-6 border-t border-dashed border-slate-200">
                        <div class="flex items-center gap-3 opacity-70 hover:opacity-100 transition-opacity">
                            <i class="fa-solid fa-truck-fast text-2xl text-slate-300"></i>
                            <div>
                                <p class="text-[10px] font-bold uppercase text-slate-900">Freeship</p>
                                <p class="text-[9px] text-slate-500">Đơn > 2 triệu</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 opacity-70 hover:opacity-100 transition-opacity">
                            <i class="fa-solid fa-shield-halved text-2xl text-slate-300"></i>
                            <div>
                                <p class="text-[10px] font-bold uppercase text-slate-900">Chính hãng</p>
                                <p class="text-[9px] text-slate-500">Bảo hành trọn đời</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- PHẦN 2: MÔ TẢ & REVIEW --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {{-- MÔ TẢ --}}
            <div class="lg:col-span-2">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8">
                    <h3 class="text-lg font-black text-slate-900 uppercase mb-4 flex items-center gap-2">
                        <span class="w-1 h-5 bg-indigo-600 rounded-full"></span> Chi tiết
                    </h3>
                    <div class="prose prose-sm prose-slate max-w-none">
                        {!! $product->description !!}
                    </div>
                </div>
            </div>

            {{-- REVIEWS (ĐÃ SỬA: HỖ TRỢ NHIỀU REVIEW) --}}
            <div class="lg:col-span-1" id="reviews">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 sticky top-24">
                    {{-- Thống kê tổng quan --}}
                    <div class="text-center mb-6 bg-slate-50 rounded-xl p-4">
                        <div class="text-5xl font-black text-slate-900 mb-1">{{ number_format($product->reviews_avg_rating ?? 0, 1) }}</div>
                        <div class="flex justify-center text-amber-400 text-sm mb-2 gap-1">
                            @php $avgRating = round($product->reviews_avg_rating ?? 0); @endphp
                            @for($i=1; $i<=5; $i++)
                                <i class="{{ $i <= $avgRating ? 'fa-solid' : 'fa-regular' }} fa-star"></i>
                            @endfor
                        </div>
                        <p class="text-xs text-slate-400 font-bold uppercase tracking-wide">Dựa trên {{ $product->reviews_count ?? 0 }} đánh giá</p>
                    </div>
                    
                    {{-- [MOD] Nút Viết Đánh Giá (Logic mới) --}}
                    <div class="mb-6">
                        @auth
                            @php
                                // Check xem user đã có review nào chưa
                                $userReviews = $reviews->where('user_id', auth()->id());
                                $reviewCount = $userReviews->count();
                            @endphp

                            <button @click="showReviewModal = true" 
                                    class="w-full py-3 bg-slate-900 text-white text-xs font-bold uppercase tracking-widest rounded-lg hover:bg-indigo-600 transition-colors shadow-lg shadow-slate-900/10 flex items-center justify-center gap-2">
                                @if($reviewCount > 0)
                                    <i class="fa-solid fa-plus-circle text-sm"></i> Viết thêm đánh giá
                                @else
                                    <i class="fa-solid fa-pen-nib text-sm"></i> Viết đánh giá
                                @endif
                            </button>

                            @if($reviewCount > 0)
                                <div class="text-[10px] text-slate-400 text-center mt-2 italic">
                                    Bạn đã đánh giá sản phẩm này <strong class="text-slate-600">{{ $reviewCount }}</strong> lần.
                                </div>
                            @endif
                        @else
                            <a href="{{ route('login') }}" class="block w-full py-3 bg-slate-100 text-slate-500 text-center text-xs font-bold uppercase tracking-widest rounded-lg hover:bg-slate-200 transition-colors">
                                <i class="fa-solid fa-lock mr-1"></i> Đăng nhập để đánh giá
                            </a>
                        @endauth
                    </div>

                    {{-- [MOD] Danh sách Review (Hiển thị Variant) --}}
                    <div class="space-y-6 max-h-[500px] overflow-y-auto pr-2 custom-scrollbar">
                        @if(isset($reviews) && $reviews->count() > 0)
                            @foreach($reviews as $review)
                                <div class="flex gap-3 pb-4 border-b border-dashed border-slate-100 last:border-0 last:pb-0">
                                    {{-- Avatar --}}
                                    <div class="flex-shrink-0">
                                        <img src="https://ui-avatars.com/api/?name={{ urlencode($review->user->full_name ?? $review->user->name ?? 'User') }}&background=random&color=fff&size=40" 
                                             class="w-10 h-10 rounded-full object-cover border border-slate-200" alt="Avatar">
                                    </div>
                                    
                                    {{-- Nội dung review --}}
                                    <div class="flex-1">
                                        <div class="flex justify-between items-start mb-1">
                                            <div>
                                                <h4 class="font-bold text-xs text-slate-900">{{ $review->user->full_name ?? $review->user->name ?? 'Khách hàng' }}</h4>
                                                
                                                <div class="flex flex-wrap items-center gap-2 mt-0.5">
                                                    <p class="text-[10px] text-slate-400">{{ $review->created_at->format('d/m/Y') }}</p>
                                                    
                                                    {{-- Badge: Đã mua hàng --}}
                                                    <span class="text-[9px] text-emerald-600 font-bold flex items-center gap-0.5 bg-emerald-50 px-1.5 py-0.5 rounded-full border border-emerald-100">
                                                        <i class="fa-solid fa-check-circle"></i> Đã mua
                                                    </span>
                                                </div>

                                                {{-- [MOD] Badge: Phân loại hàng (Nếu có relation) --}}
                                                {{-- Giả sử bạn có relation productVariant hoặc lưu text vào variant_name --}}
                                                @if(isset($review->productVariant) || !empty($review->variant_name))
                                                    <div class="mt-1.5 inline-flex items-center gap-1 bg-slate-100 px-2 py-0.5 rounded text-[10px] text-slate-500 border border-slate-200 font-medium">
                                                        <span class="opacity-50">Phân loại:</span>
                                                        <span class="text-slate-700">
                                                            {{ $review->productVariant->name ?? $review->variant_name ?? 'Size/Màu' }}
                                                        </span>
                                                    </div>
                                                @endif
                                            </div>

                                            {{-- Số sao --}}
                                            <div class="flex text-amber-400 text-[10px] gap-0.5">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <i class="{{ $i <= $review->rating ? 'fa-solid' : 'fa-regular' }} fa-star"></i>
                                                @endfor
                                            </div>
                                        </div>
                                        
                                        {{-- Comment --}}
                                        <div class="text-xs text-slate-600 leading-relaxed bg-slate-50 p-3 rounded-lg rounded-tl-none mt-2 relative group hover:bg-slate-100 transition-colors">
                                            {{ $review->comment }}
                                            <div class="w-2 h-2 bg-slate-50 absolute -top-1 left-0 rotate-45 group-hover:bg-slate-100 transition-colors"></div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                            
                            @if($reviews->hasPages())
                                <div class="pt-4">{{ $reviews->links('pagination::tailwind') }}</div>
                            @endif
                        @else
                            <div class="text-center py-8 text-slate-400">
                                <i class="fa-regular fa-comment-dots text-4xl mb-2 opacity-50"></i>
                                <p class="text-xs">Chưa có đánh giá nào.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- RELATED --}}
        @if(isset($relatedProducts) && $relatedProducts->count() > 0)
            <div class="mt-12 pt-8 border-t border-slate-200">
                <h3 class="text-xl font-black text-slate-900 uppercase mb-6">Sản phẩm liên quan</h3>
                @include('client.products._product_row', ['items' => $relatedProducts])
            </div>
        @endif
    </div>

    {{-- MODAL ZOOM ẢNH --}}
    <div x-show="zoomOpen" x-transition.opacity class="fixed inset-0 z-[100] bg-white/90 backdrop-blur-sm flex items-center justify-center p-4" x-cloak>
        <button @click="zoomOpen = false" class="absolute top-5 right-5 w-10 h-10 flex items-center justify-center bg-black text-white rounded-full hover:rotate-90 transition-transform"><i class="fa-solid fa-xmark"></i></button>
        <img :src="zoomImage" class="max-w-full max-h-[90vh] object-contain shadow-2xl rounded-xl" @click="zoomOpen = false">
    </div>`

    {{-- MODAL REVIEW (FORM) --}}
    <div x-show="showReviewModal" x-cloak class="fixed inset-0 z-[150] flex items-center justify-center p-4">
        <div x-show="showReviewModal" x-transition.opacity class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showReviewModal = false"></div>
        <div x-show="showReviewModal" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95 translate-y-4" x-transition:enter-end="opacity-100 scale-100 translate-y-0" class="bg-white w-full max-w-md rounded-2xl shadow-2xl relative z-10 overflow-hidden">
            
            <div class="bg-slate-50 px-6 py-4 border-b border-slate-100 flex justify-between items-center">
                <h3 class="font-black text-slate-900 uppercase text-sm tracking-wide flex items-center gap-2">
                    <i class="fa-solid fa-star text-amber-400"></i> Đánh giá sản phẩm
                </h3>
                <button @click="showReviewModal = false" class="text-slate-400 hover:text-rose-500 transition-colors"><i class="fa-solid fa-xmark text-lg"></i></button>
            </div>
            
            <form action="{{ route('client.reviews.store') }}" method="POST" class="p-6">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                
                {{-- [OPTIONAL] Nếu Backend bạn hỗ trợ order_id, hãy uncomment dòng dưới và xử lý logic truyền order_id vào --}}
                {{-- <input type="hidden" name="order_id" value="..."> --}}

                <div class="mb-6 text-center" x-data="{ rating: 5 }">
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Mức độ hài lòng</label>
                    <div class="flex justify-center gap-2 text-3xl text-slate-200 cursor-pointer mb-2">
                        <template x-for="i in 5">
                            <i class="fa-star transition-all hover:scale-110" 
                               :class="i <= rating ? 'fa-solid text-amber-400 drop-shadow-sm' : 'fa-solid text-slate-200 hover:text-amber-200'" 
                               @click="rating = i"></i>
                        </template>
                    </div>
                    <input type="hidden" name="rating" x-model="rating">
                    <div class="inline-block px-3 py-1 bg-indigo-50 text-indigo-700 rounded-full text-xs font-bold" 
                         x-text="['Rất tệ', 'Không hài lòng', 'Bình thường', 'Hài lòng', 'Tuyệt vời'][rating-1]">
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Nhận xét của bạn</label>
                    <textarea name="comment" rows="4" class="w-full bg-slate-50 border border-slate-200 rounded-lg p-3 text-sm focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all resize-none placeholder:text-slate-400" placeholder="Chất lượng sản phẩm, thái độ phục vụ..."></textarea>
                    <p class="text-[10px] text-slate-400 mt-1 text-right italic">* Đánh giá sẽ giúp ích cho những người mua sau.</p>
                </div>

                <div class="flex gap-3">
                    <button type="button" @click="showReviewModal = false" class="flex-1 py-3 bg-white border border-slate-200 text-slate-600 text-xs font-bold uppercase rounded-lg hover:bg-slate-50 transition-colors">Hủy bỏ</button>
                    <button type="submit" class="flex-1 py-3 bg-slate-900 text-white text-xs font-bold uppercase rounded-lg hover:bg-indigo-600 shadow-lg shadow-slate-900/10 transition-all">Gửi đánh giá</button>
                </div>
            </form>
        </div>
    </div>

</div>

{{-- SCRIPT ALPINEJS --}}
<script>
    function productDetail(config) {
        return {
            // Giữ nguyên logic lấy ảnh
            activeImage: '{{ $product->image ? asset('storage/'.$product->image) : asset('img/no-image.png') }}',
            currentPrice: config.basePrice,
            currentStock: 0,
            selectedVariantId: null,
            selectedAttributes: {},
            qty: 1,
            errorMsg: '',
            variantsMap: config.variants,
            isLiked: config.isLiked,
            zoomOpen: false,
            zoomImage: '',
            showReviewModal: false,
            isLiked: {{ $isLiked ? 'true' : 'false' }},

            init() {
                const keys = Object.keys(this.variantsMap);
                const hasAttributes = {{ (isset($groupedAttributes) && $groupedAttributes->count() > 0) ? 'true' : 'false' }};

                if (!hasAttributes && keys.length > 0) {
                    const firstItem = this.variantsMap[keys[0]];
                    this.selectedVariantId = firstItem.id;
                    this.currentStock = firstItem.stock;
                    this.currentPrice = firstItem.price || config.basePrice;
                }
            },

            formatMoney(amount) {
                return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount);
            },

            selectAttribute(name, id) {
                this.selectedAttributes[name] = id;
                this.errorMsg = '';
                
                const totalRequired = {{ isset($groupedAttributes) ? $groupedAttributes->count() : 0 }};
                const totalSelected = Object.keys(this.selectedAttributes).length;

                if (totalSelected === totalRequired) {
                    this.findMatchingVariant();
                }
            },

            findMatchingVariant() {
                const selectedIds = Object.values(this.selectedAttributes)
                                          .map(Number)
                                          .sort((a, b) => a - b)
                                          .toString();
                
                let found = false;

                for (const [key, data] of Object.entries(this.variantsMap)) {
                    const variantAttrIds = data.attributes
                                              .map(Number)
                                              .sort((a, b) => a - b)
                                              .toString();

                    if (selectedIds === variantAttrIds) {
                        this.selectedVariantId = data.id;
                        this.currentPrice = data.price || config.basePrice;
                        this.currentStock = data.stock;
                        this.qty = 1;
                        found = true;
                        break;
                    }
                }

                if (!found) {
                    this.currentStock = 0;
                    this.errorMsg = 'Phiên bản này tạm hết hàng.';
                    this.selectedVariantId = null;
                }
            },

            toggleWishlist(id) {
                // 1. Lưu trạng thái cũ để revert nếu lỗi
                const previousState = this.isLiked;

                // 2. Optimistic UI: Đổi trạng thái ngay lập tức (Để giao diện mượt)
                this.isLiked = !this.isLiked;

                // 3. Chuẩn bị dữ liệu
                const formData = new FormData();
                formData.append('product_id', id);

                // 4. Gọi API
                fetch("{{ route('client.wishlist.toggle') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(res => {
                    if (res.success) {
                        // --- A. THÀNH CÔNG ---
                        
                        // Tạo icon HTML
                        const iconHtml = res.action === 'added' 
                            ? `<span class="flex items-center justify-center"><i class="fa-solid fa-heart text-rose-500 text-2xl heart-animation"></i></span>` 
                            : `<span class="flex items-center justify-center"><i class="fa-solid fa-heart-crack text-slate-400 text-2xl"></i></span>`;

                        if (typeof Swal !== 'undefined') {
                            const Toast = Swal.mixin({
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 3000,
                                timerProgressBar: true,
                                didOpen: (toast) => {
                                    toast.onmouseenter = Swal.stopTimer;
                                    toast.onmouseleave = Swal.resumeTimer;
                                }
                            });

                            Toast.fire({
                                iconHtml: iconHtml, 
                                title: res.message,
                                customClass: {
                                    popup: 'colored-toast'
                                }
                            });
                        }
                    } else {
                        // --- B. THẤT BẠI (Lỗi Logic) ---
                        
                        this.isLiked = previousState; // Hoàn tác UI
                        
                        if (res.code === 401) {
                            // Trường hợp chưa đăng nhập
                            Swal.fire({
                                title: 'Yêu cầu đăng nhập',
                                text: res.message,
                                icon: 'info',
                                showCancelButton: true,
                                confirmButtonColor: '#4f46e5',
                                cancelButtonColor: '#cbd5e1',
                                confirmButtonText: 'Đăng nhập',
                                cancelButtonText: 'Để sau'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    window.location.href = "{{ route('login') }}";
                                }
                            });
                        } else {
                            // [FIX] Thay this.showToast bằng Swal trực tiếp để tránh lỗi
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'error',
                                title: res.message || 'Có lỗi xảy ra'
                            });
                        }
                    }
                })
                .catch(error => {
                    // --- C. LỖI MẠNG/SERVER ---
                    console.error('Error:', error);
                    this.isLiked = previousState; // Hoàn tác UI
                    
                    // [FIX] Thay this.showToast bằng Swal trực tiếp
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'error',
                        title: 'Lỗi kết nối server!'
                    });
                });
            }, // <--- Đừng quên dấu phẩy này nếu bên dưới còn hàm khác

            submitCart() {
                // 1. Validate Client (Giữ nguyên logic của bạn)
                @if(isset($groupedAttributes) && $groupedAttributes->count() > 0)
                    if (!this.selectedVariantId) {
                        this.errorMsg = 'Vui lòng chọn đầy đủ phân loại (Size/Màu)!';
                        return;
                    }
                @endif
                
                if (this.currentStock <= 0) {
                    this.errorMsg = 'Sản phẩm đã hết hàng.';
                    return;
                }

                // 2. Loading State (Mua ngay: Thêm hiệu ứng loading cho nút bấm nếu muốn)
                // let btn = document.getElementById('addToCartBtn'); 
                // if(btn) btn.classList.add('opacity-75', 'pointer-events-none');

                // 3. Chuẩn bị dữ liệu
                const form = document.getElementById('addToCartForm');
                const formData = new FormData(form);
                formData.set('product_variant_id', this.selectedVariantId);

                // 4. Gọi API
                fetch("{{ route('client.carts.add') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(res => {

                    console.log('Dữ liệu Server trả về:', res.data); // <--- Thêm dòng này

                    if (res.success === true) {
                        
                        // A. Update số lượng trên Header (Kèm hiệu ứng rung lắc)
                        const cartBadges = document.querySelectorAll('.cart-count-badge, #cart-count'); 
                        cartBadges.forEach(el => {
                            el.innerText = res.data.cart_count; 
                            el.style.display = 'flex'; 
                            el.classList.remove('hidden');
                            
                            // Reset animation
                            el.style.animation = 'none';
                            el.offsetHeight; /* trigger reflow */
                            el.style.animation = 'bounce 0.5s ease-in-out'; // Đảm bảo bạn có keyframes bounce
                        });
                        
                        // B. HIỂN THỊ ALERT PREMIUM (GLASSMORPHISM)
                        if (typeof Swal !== 'undefined') {
                            const fallbackImage = "{{ asset('img/no-image.png') }}"; 

                            const premiumHtml = `
                                <div class="group relative flex flex-col w-full max-w-sm overflow-hidden rounded-xl bg-white/95 shadow-[0_8px_30px_rgb(0,0,0,0.12)] backdrop-blur-md border border-white/20 ring-1 ring-black/5">
                                    
                                    <div class="flex items-center p-4 relative z-10">
                                        <div class="absolute -right-10 -top-10 h-32 w-32 rounded-full bg-indigo-500/10 blur-3xl group-hover:bg-indigo-500/20 transition-all duration-500"></div>
                                        
                                        <div class="relative h-16 w-16 flex-shrink-0 overflow-hidden rounded-lg border border-slate-100 bg-slate-50 shadow-sm">
                                            <img src="${res.data.image}" 
                                                onerror="this.onerror=null; this.src='${fallbackImage}';"
                                                class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110" 
                                                alt="${res.data.product_name}">
                                        </div>

                                        <div class="ml-4 flex flex-1 flex-col justify-center text-left min-w-0">
                                            <div class="mb-1 flex items-center gap-1.5">
                                                <div class="flex h-4 w-4 items-center justify-center rounded-full bg-emerald-100">
                                                    <svg class="h-2.5 w-2.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                                    </svg>
                                                </div>
                                                <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-600">Đã thêm vào giỏ</span>
                                            </div>

                                            <h4 class="truncate text-sm font-bold text-slate-800 pr-2" title="${res.data.product_name}">
                                                ${res.data.product_name}
                                            </h4>
                                            
                                            ${res.data.variant_name ? `<p class="text-xs font-medium text-slate-500 mt-0.5 truncate">${res.data.variant_name}</p>` : ''}

                                            <div class="mt-2 flex items-center gap-3">
                                                <a href="{{route('client.carts.index')}}" class="text-xs font-bold text-indigo-600 hover:text-indigo-700 hover:underline decoration-2 underline-offset-2 transition-colors">
                                                    Xem giỏ hàng
                                                </a>
                                                <span class="text-slate-300 text-xs">|</span>
                                                <button type="button" onclick="Swal.close()" class="text-xs font-medium text-slate-400 hover:text-slate-600 transition-colors">
                                                    Đóng
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="h-1 w-full bg-slate-100">
                                        <div class="h-full bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 animate-progress-bar"></div>
                                    </div>
                                </div>
                            `;

                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                html: premiumHtml,
                                showConfirmButton: false,
                                timer: 5000, 
                                timerProgressBar: false, // [QUAN TRỌNG] Tắt thanh chạy mặc định của thư viện
                                width: 'auto',
                                padding: 0,
                                background: 'transparent',
                                didOpen: (toast) => {
                                    toast.onmouseenter = Swal.stopTimer;
                                    toast.onmouseleave = Swal.resumeTimer;
                                }
                            });
                        } else {
                            alert(res.message);
                        }

                    } else {
                        // TRƯỜNG HỢP LỖI (Giao diện đỏ tinh tế)
                        const errorHtml = `
                            <div class="flex w-full max-w-sm overflow-hidden rounded-xl bg-white p-4 shadow-2xl border-l-4 border-rose-500">
                                <div class="mr-4 flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-rose-50 text-rose-500">
                                    <i class="fa-solid fa-triangle-exclamation text-lg"></i>
                                </div>
                                <div class="flex-1 text-left">
                                    <h4 class="text-sm font-bold text-slate-800">Không thể thêm</h4>
                                    <p class="mt-1 text-xs font-medium text-slate-500 leading-relaxed">
                                        ${res.message || 'Số lượng trong kho không đủ hoặc có lỗi hệ thống.'}
                                    </p>
                                </div>
                            </div>
                        `;

                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                html: errorHtml,
                                showConfirmButton: false,
                                timer: 4000,
                                background: 'transparent'
                            });
                        } else {
                            alert(res.message);
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    // Fallback UI cho lỗi mạng
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'error',
                            title: 'Lỗi kết nối',
                            text: 'Vui lòng kiểm tra mạng và thử lại.'
                        });
                    }
                })
                // .finally(() => { 
                //    if(btn) btn.classList.remove('opacity-75', 'pointer-events-none');
                // });
            }
        }
    }
</script>

<style>
    /* 1. Custom Scrollbar */
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

    /* =========================================
       PHẦN 1: CẤU HÌNH CHUNG SWEETALERT (RESET)
       ========================================= */
    
    /* Đẩy thông báo ra xa mép màn hình */
    div.swal2-container.swal2-top-end {
        padding: 1rem !important;
    }

    /* [QUAN TRỌNG] Reset nền trong suốt để hỗ trợ Custom HTML (cho Giỏ hàng) */
    .swal2-popup.swal2-toast {
        padding: 0 !important;
        overflow: visible !important;
        background: transparent !important;
        box-shadow: none !important;
        width: auto !important;
    }

    /* =========================================
       PHẦN 2: STYLE CHO TOAST "GIỎ HÀNG" (CUSTOM HTML)
       ========================================= */

    /* Animation thanh thời gian tự vẽ */
    @keyframes progressBar {
        0% { width: 100%; }
        100% { width: 0%; }
    }

    .animate-progress-bar {
        animation: progressBar 5s linear forwards; 
    }

    .group:hover .animate-progress-bar {
        animation-play-state: paused;
    }

    /* =========================================
       PHẦN 3: STYLE CHO TOAST "YÊU THÍCH" (COLORED TOAST)
       ========================================= */

    /* [FIX QUAN TRỌNG] Khôi phục nền trắng và bóng đổ (Ghi đè phần Reset ở trên) */
    .colored-toast {
        display: flex !important;
        align-items: center !important;
        padding: 12px 20px !important;
        background: #fff !important; /* Quan trọng: Màu trắng */
        box-shadow: 0 10px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1) !important;
        border-radius: 12px !important;
        border: 1px solid #f1f5f9 !important;
        min-width: 300px !important;
    }

    /* Sửa lỗi icon bị lệch hoặc có viền tròn bao quanh */
    .colored-toast .swal2-icon {
        border: none !important; /* Xóa viền tròn */
        margin: 0 15px 0 0 !important;
        width: auto !important;
        height: auto !important;
        background: transparent !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }
    
    /* Chữ tiêu đề */
    .colored-toast .swal2-title {
        font-family: 'Inter', sans-serif !important;
        font-size: 15px !important;
        font-weight: 600 !important;
        color: #334155 !important;
        margin: 0 !important;
        text-align: left !important;
    }

    /* Thanh thời gian (Progress bar) màu hồng cho Wishlist */
    .colored-toast .swal2-timer-progress-bar {
        background: #f43f5e !important; 
        height: 3px !important;
        bottom: 0 !important;
    }

    /* =========================================
       PHẦN 4: ANIMATION TIM ĐẬP (HEART BEAT)
       ========================================= */
    @keyframes heartBeat {
        0% { transform: scale(1); }
        14% { transform: scale(1.3); }
        28% { transform: scale(1); }
        42% { transform: scale(1.3); }
        70% { transform: scale(1); }
    }
    
    .heart-animation {
        display: inline-block; 
        animation: heartBeat 1.3s ease-in-out;
    }
</style>

<script>
    window.showToast = function(message, type = 'success') {
        window.dispatchEvent(new CustomEvent('show-toast', {
            detail: { message: message, type: type }
        }));
    }
</script>
@endsection