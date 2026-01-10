@extends('client.layouts.app')

@section('title', $product->name . ' - Sneaker Zone')

@section('content')

{{-- DATA ALPINEJS CHO PRODUCT DETAIL --}}
<div x-data="productDetail({
        basePrice: {{ $product->price_min ?? 0 }},
        variants: {{ json_encode($variantMap ?? []) }},
        isLiked: {{ ($product->is_liked ?? false) ? 'true' : 'false' }}
    })" 
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
                        {{-- Ảnh chính --}}
                        <div class="relative aspect-square bg-[#F4F4F4] rounded-xl overflow-hidden cursor-zoom-in group"
                             @click="zoomImage = activeImage; zoomOpen = true">
                            <img :src="activeImage" class="w-full h-full object-cover mix-blend-multiply transition-transform duration-500 group-hover:scale-110">
                            
                            @if($product->is_featured)
                                <div class="absolute top-4 left-4 flex items-center gap-1 bg-gradient-to-r from-rose-600 to-orange-500 text-white text-[10px] font-black px-3 py-1.5 rounded uppercase tracking-widest shadow-lg shadow-orange-500/30 animate-pulse">
                                    <i class="fa-solid fa-fire"></i> Hot Item
                                </div>
                            @endif
                        </div>

                        {{-- Thumbnails --}}
                        <div class="grid grid-cols-6 gap-2">
                            <button class="aspect-square rounded-lg overflow-hidden border transition-all bg-[#F4F4F4]"
                                    :class="activeImage === '{{ $product->image ? asset('storage/'.$product->image) : asset('img/no-image.png') }}' ? 'border-slate-900 ring-1 ring-slate-900' : 'border-transparent hover:border-slate-300'"
                                    @click="activeImage = '{{ $product->image ? asset('storage/'.$product->image) : asset('img/no-image.png') }}'">
                                <img src="{{ $product->image ? asset('storage/'.$product->image) : asset('img/no-image.png') }}" class="w-full h-full object-cover mix-blend-multiply">
                            </button>
                            @if($product->gallery_images)
                                @foreach($product->gallery_images as $img)
                                    <button class="aspect-square rounded-lg overflow-hidden border transition-all bg-[#F4F4F4]"
                                            :class="activeImage === '{{ asset('storage/'.$img->image_path) }}' ? 'border-slate-900 ring-1 ring-slate-900' : 'border-transparent hover:border-slate-300'"
                                            @click="activeImage = '{{ asset('storage/'.$img->image_path) }}'">
                                            <img src="{{ asset('storage/'.$img->image_path) }}" class="w-full h-full object-cover mix-blend-multiply">
                                    </button>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>

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
                            
                            <button type="button" @click="toggleWishlist()"
                                    class="w-12 h-12 border border-slate-200 rounded-lg flex items-center justify-center transition-all active:scale-90"
                                    :class="isLiked ? 'bg-rose-50 border-rose-200 text-rose-500' : 'text-slate-400 hover:text-rose-500 hover:border-rose-200 hover:bg-rose-50'">
                                <i class="text-xl transition-transform" :class="isLiked ? 'fa-solid fa-heart scale-110' : 'fa-regular fa-heart'"></i>
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
    </div>

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

            toggleWishlist() {
                this.isLiked = !this.isLiked;
                // Gọi API toggle wishlist nếu cần
            },

            submitCart() {
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

                const form = document.getElementById('addToCartForm');
                const formData = new FormData(form);
                formData.set('product_variant_id', this.selectedVariantId);

                fetch("{{ route('client.carts.add') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        const cartCountEl = document.getElementById('cart-count');
                        if (cartCountEl) {
                            cartCountEl.innerText = data.cart_count;
                            cartCountEl.style.display = 'flex';
                        }
                        
                        if (data.toast) {
                            window.dispatchEvent(new CustomEvent('show-toast', { detail: data.toast }));
                        } else {
                            window.showToast(data.message, 'success');
                        }
                    } else {
                        window.showToast(data.message || 'Có lỗi xảy ra', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    window.showToast('Lỗi kết nối, vui lòng thử lại.', 'error');
                });
            }
        }
    }
</script>

<style>
    [x-cloak] { display: none !important; }
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
</style>

<script>
    window.showToast = function(message, type = 'success') {
        window.dispatchEvent(new CustomEvent('show-toast', {
            detail: { message: message, type: type }
        }));
    }
</script>
@endsection