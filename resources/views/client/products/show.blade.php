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
                    
                    {{-- Header --}}
                    <div class="mb-6 pb-6 border-b border-dashed border-slate-200">
                        <div class="flex justify-between items-start mb-2">
                            <span class="text-[10px] font-black text-indigo-600 uppercase tracking-widest bg-indigo-50 px-2 py-1 rounded">
                                {{ $product->category->name ?? 'Collection' }}
                            </span>
                            <a href="#reviews" class="flex items-center gap-1 text-xs font-bold text-amber-500 hover:underline">
                                <i class="fa-solid fa-star"></i> {{ number_format($product->reviews_avg_rating ?? 0, 1) }}
                            </a>
                        </div>
                        <h1 class="text-3xl font-black text-slate-900 leading-tight mb-2 uppercase tracking-tight">{{ $product->name }}</h1>
                        <div class="text-2xl font-bold text-slate-900" x-text="formatMoney(currentPrice)"></div>
                    </div>

                    {{-- Form Add To Cart --}}
                    <form action="{{ route('client.carts.add') }}" method="POST" id="addToCartForm" class="space-y-6">
                        @csrf
                        
                        {{-- FIX LỖI "variant id field is required": Đổi name về "variant_id" --}}
                        <input type="hidden" name="variant_id" x-model="selectedVariantId">
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
                        @else
                            {{-- Tự động chọn variant đầu tiên nếu ko có thuộc tính --}}
                            <input type="hidden" name="variant_id" value="{{ $product->variants->first()->id ?? '' }}">
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
                        <div class="flex items-center justify-between text-xs font-bold pt-2">
                             <div x-show="currentStock > 0" class="text-emerald-600 flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> 
                                Còn <span x-text="currentStock"></span> sản phẩm
                            </div>
                            <div x-show="currentStock <= 0 && selectedVariantId" class="text-rose-500 flex items-center gap-1" style="display: none;">
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Hết hàng
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
            
            {{-- MÔ TẢ SẢN PHẨM --}}
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

            {{-- ĐÁNH GIÁ SẢN PHẨM --}}
            <div class="lg:col-span-1" id="reviews">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 sticky top-24">
                    
                    <div class="text-center mb-6 bg-slate-50 rounded-xl p-4">
                        <div class="text-5xl font-black text-slate-900 mb-1">{{ number_format($product->reviews_avg_rating ?? $product->reviews->avg('rating') ?? 0, 1) }}</div>
                        <div class="flex justify-center text-amber-400 text-sm mb-2 gap-1">
                            @php $avgRating = round($product->reviews_avg_rating ?? $product->reviews->avg('rating') ?? 0); @endphp
                            @for($i=1; $i<=5; $i++)
                                <i class="{{ $i <= $avgRating ? 'fa-solid' : 'fa-regular' }} fa-star"></i>
                            @endfor
                        </div>
                        <p class="text-xs text-slate-400 font-bold uppercase tracking-wide">Dựa trên {{ $product->reviews_count ?? $product->reviews->count() }} đánh giá</p>
                    </div>
                    
                    <div class="mb-6">
                        @auth
                            <button @click="showReviewModal = true" 
                                    class="w-full py-3 bg-slate-900 text-white text-xs font-bold uppercase tracking-widest rounded-lg hover:bg-indigo-600 transition-colors shadow-lg shadow-slate-900/10">
                                <i class="fa-solid fa-pen-nib mr-2"></i> Viết đánh giá
                            </button>
                        @else
                            <a href="{{ route('client.login') }}" class="block w-full py-3 bg-slate-100 text-slate-500 text-center text-xs font-bold uppercase tracking-widest rounded-lg hover:bg-slate-200 transition-colors">
                                Đăng nhập để đánh giá
                            </a>
                        @endauth
                    </div>

                    <div class="space-y-6 max-h-[500px] overflow-y-auto pr-2 custom-scrollbar">
                        @if(isset($reviews) && $reviews->count() > 0)
                            @foreach($reviews as $review)
                                <div class="flex gap-3 pb-4 border-b border-dashed border-slate-100 last:border-0 last:pb-0">
                                    <div class="flex-shrink-0">
                                        <img src="https://ui-avatars.com/api/?name={{ urlencode($review->user->full_name ?? $review->user->name ?? 'User') }}&background=random&color=fff&size=40" 
                                             class="w-10 h-10 rounded-full object-cover border border-slate-200" alt="Avatar">
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex justify-between items-start mb-1">
                                            <div>
                                                <h4 class="font-bold text-xs text-slate-900">{{ $review->user->full_name ?? $review->user->name ?? 'Khách hàng' }}</h4>
                                                <p class="text-[10px] text-slate-400">{{ $review->created_at->format('d/m/Y H:i') }}</p>
                                            </div>
                                            <div class="flex text-amber-400 text-[10px] gap-0.5">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <i class="{{ $i <= $review->rating ? 'fa-solid' : 'fa-regular' }} fa-star"></i>
                                                @endfor
                                            </div>
                                        </div>
                                        <p class="text-xs text-slate-600 leading-relaxed bg-slate-50 p-3 rounded-lg rounded-tl-none">
                                            {{ $review->comment }}
                                        </p>
                                    </div>
                                </div>
                            @endforeach
                            
                            @if($reviews->hasPages())
                                <div class="pt-4">
                                    {{ $reviews->links('pagination::tailwind') }}
                                </div>
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

    {{-- ======================== MODAL AREA ======================== --}}
    
    <div x-show="zoomOpen" x-transition.opacity class="fixed inset-0 z-[100] bg-white/90 backdrop-blur-sm flex items-center justify-center p-4" x-cloak>
        <button @click="zoomOpen = false" class="absolute top-5 right-5 w-10 h-10 flex items-center justify-center bg-black text-white rounded-full hover:rotate-90 transition-transform"><i class="fa-solid fa-xmark"></i></button>
        <img :src="zoomImage" class="max-w-full max-h-[90vh] object-contain shadow-2xl rounded-xl" @click="zoomOpen = false">
    </div>

    <div x-show="showReviewModal" 
         x-cloak
         class="fixed inset-0 z-[150] flex items-center justify-center p-4">
        
        <div x-show="showReviewModal" 
             x-transition.opacity
             class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"
             @click="showReviewModal = false"></div>

        <div x-show="showReviewModal"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95 translate-y-4"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-95 translate-y-4"
             class="bg-white w-full max-w-md rounded-2xl shadow-2xl relative z-10 overflow-hidden">
            
            <div class="bg-slate-50 px-6 py-4 border-b border-slate-100 flex justify-between items-center">
                <h3 class="font-black text-slate-900 uppercase text-sm tracking-wide">Viết đánh giá</h3>
                <button @click="showReviewModal = false" class="text-slate-400 hover:text-rose-500 transition-colors">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form action="{{ route('client.reviews.store') }}" method="POST" class="p-6">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                
                <div class="mb-6 text-center" x-data="{ rating: 5 }">
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Bạn cảm thấy thế nào?</label>
                    <div class="flex justify-center gap-2 text-2xl text-slate-200 cursor-pointer">
                        <template x-for="i in 5">
                            <i class="fa-star transition-all hover:scale-110"
                               :class="i <= rating ? 'fa-solid text-amber-400' : 'fa-solid text-slate-200 hover:text-amber-300'"
                               @click="rating = i"></i>
                        </template>
                    </div>
                    <input type="hidden" name="rating" x-model="rating">
                    <div class="mt-2 text-xs font-bold text-indigo-600" x-text="['Tệ', 'Không hài lòng', 'Bình thường', 'Hài lòng', 'Tuyệt vời'][rating-1]"></div>
                </div>

                <div class="mb-6">
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Nội dung đánh giá</label>
                    <textarea name="comment" rows="4" 
                              class="w-full bg-slate-50 border border-slate-200 rounded-lg p-3 text-sm focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all resize-none"
                              placeholder="Chia sẻ cảm nhận của bạn về sản phẩm này..."></textarea>
                </div>

                <div class="flex gap-3">
                    <button type="button" @click="showReviewModal = false" class="flex-1 py-3 bg-white border border-slate-200 text-slate-600 text-xs font-bold uppercase rounded-lg hover:bg-slate-50 transition-colors">
                        Hủy bỏ
                    </button>
                    <button type="submit" class="flex-1 py-3 bg-slate-900 text-white text-xs font-bold uppercase rounded-lg hover:bg-indigo-600 shadow-lg shadow-slate-900/10 transition-all">
                        Gửi đánh giá
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

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
                let totalStock = 0;
                if (this.variantsMap && typeof this.variantsMap === 'object') {
                    Object.values(this.variantsMap).forEach(variant => {
                        totalStock += variant.stock;
                    });
                }
                
                @if(isset($groupedAttributes) && $groupedAttributes->count() == 0)
                    // Logic sản phẩm đơn: Lấy ID từ data object thay vì index
                    const keys = Object.keys(this.variantsMap);
                    if(keys.length > 0) {
                        const firstItem = this.variantsMap[keys[0]];
                        // FIX: Lấy ID chuẩn từ dữ liệu thay vì lấy key
                        this.selectedVariantId = firstItem.id;
                        this.currentStock = firstItem.stock;
                        this.currentPrice = firstItem.price || config.basePrice;
                    } else {
                        this.currentStock = totalStock;
                    }
                @else
                    this.currentStock = totalStock;
                @endif
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
                const selectedIds = Object.values(this.selectedAttributes).map(Number).sort().toString();
                let found = false;

                for (const [key, data] of Object.entries(this.variantsMap)) {
                    const variantAttrIds = data.attributes.sort().toString();
                    if (selectedIds === variantAttrIds) {
                        this.selectedVariantId = data.id; // Lấy ID chuẩn
                        this.currentPrice = data.price || config.basePrice;
                        this.currentStock = data.stock;
                        this.qty = 1;
                        found = true;
                        break;
                    }
                }

                if (!found) {
                    this.currentStock = 0;
                    this.errorMsg = 'Tạm hết hàng phiên bản này.';
                    this.selectedVariantId = null;
                }
            },

            toggleWishlist() {
                this.isLiked = !this.isLiked;
                // AJAX call logic here...
            },

            submitCart() {
                @if(isset($groupedAttributes) && $groupedAttributes->count() > 0)
                    if (!this.selectedVariantId) {
                        this.errorMsg = 'Vui lòng chọn Phân loại!';
                        return;
                    }
                @endif
                
                if (this.currentStock <= 0) {
                    this.errorMsg = 'Sản phẩm đã hết hàng.';
                    return;
                }

                // FIX QUAN TRỌNG: Gán giá trị vào đúng input 'variant_id'
                const form = document.getElementById('addToCartForm');
                const variantInput = form.querySelector('input[name="variant_id"]');
                
                if(variantInput) {
                    variantInput.value = this.selectedVariantId; 
                }

                form.submit();
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

{{-- === TOAST NOTIFICATION === --}}
<div x-data="{ 
        show: false, 
        message: '', 
        type: 'success' 
     }" 
     x-init="
        @if(session('success')) 
            message = '{{ session('success') }}'; type = 'success'; show = true; 
            setTimeout(() => show = false, 4000);
        @endif
        @if(session('error')) 
            message = '{{ session('error') }}'; type = 'error'; show = true; 
            setTimeout(() => show = false, 5000);
        @endif
        @if($errors->any())
            message = '{{ $errors->first() }}'; type = 'error'; show = true; 
            setTimeout(() => show = false, 5000);
        @endif
     "
     x-show="show" 
     x-cloak
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 translate-y-2 scale-95"
     x-transition:enter-end="opacity-100 translate-y-0 scale-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100 translate-y-0 scale-100"
     x-transition:leave-end="opacity-0 translate-y-2 scale-95"
     class="fixed top-24 right-5 z-[200] max-w-sm w-full bg-white rounded-xl shadow-2xl border-l-4 p-4 flex items-start gap-4"
     :class="type === 'success' ? 'border-emerald-500 shadow-emerald-500/10' : 'border-rose-500 shadow-rose-500/10'">
    
    <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5"
         :class="type === 'success' ? 'bg-emerald-100 text-emerald-600' : 'bg-rose-100 text-rose-600'">
        <i class="fa-solid" :class="type === 'success' ? 'fa-check' : 'fa-circle-exclamation'"></i>
    </div>

    <div class="flex-1">
        <h4 class="font-black text-sm uppercase tracking-wide mb-1" 
            :class="type === 'success' ? 'text-emerald-700' : 'text-rose-700'"
            x-text="type === 'success' ? 'Thành công' : 'Có lỗi xảy ra'">
        </h4>
        <p class="text-xs text-slate-600 font-medium leading-relaxed" x-text="message"></p>

        <template x-if="type === 'success'">
            <div class="mt-2">
                <a href="{{ route('client.carts.index') }}" class="text-[10px] font-bold uppercase tracking-wider underline decoration-emerald-300 decoration-2 underline-offset-2 hover:text-emerald-800 hover:decoration-emerald-500 transition-all">
                    Xem giỏ hàng &rarr;
                </a>
            </div>
        </template>
    </div>

    <button @click="show = false" class="text-slate-400 hover:text-slate-600 transition-colors p-1">
        <i class="fa-solid fa-xmark text-lg"></i>
    </button>
</div>

@endsection