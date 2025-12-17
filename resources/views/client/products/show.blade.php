@extends('client.layouts.app')

@section('title', $product->name . ' - Sneaker Zone')

@section('content')

{{-- DATA ALPINEJS --}}
<div x-data="productDetail({
        basePrice: {{ $product->price_min }},
        variants: {{ json_encode($variantMap) }},
        isLiked: {{ $product->is_liked ? 'true' : 'false' }} // Giả sử có trường này, nếu không mặc định false
    })" 
    class="bg-[#F8F9FA] min-h-screen font-sans text-slate-800 pb-20">

    {{-- 1. BREADCRUMB --}}
    <div class="bg-white border-b border-slate-100 sticky top-[60px] z-30 shadow-sm">
        <div class="container mx-auto px-4 py-3 max-w-6xl"> {{-- Thu nhỏ container --}}
            <nav class="flex items-center text-[11px] font-bold uppercase tracking-wide text-slate-400">
                <a href="{{ route('client.home') }}" class="hover:text-black transition-colors">Home</a>
                <i class="fa-solid fa-chevron-right text-[9px] mx-2 text-slate-300"></i>
                <a href="{{ route('client.products.index') }}" class="hover:text-black transition-colors">Shop</a>
                <i class="fa-solid fa-chevron-right text-[9px] mx-2 text-slate-300"></i>
                <span class="text-slate-900 truncate">{{ $product->name }}</span>
            </nav>
        </div>
    </div>

    {{-- MAIN CONTAINER (Đã thu nhỏ max-w-6xl) --}}
    <div class="container mx-auto px-4 py-8 max-w-6xl">
        
        <div class="bg-white rounded-2xl shadow-xl shadow-slate-200/50 border border-white overflow-hidden mb-8">
            <div class="grid grid-cols-1 lg:grid-cols-12">
                
                {{-- === CỘT TRÁI: ẢNH (Giảm tỉ lệ xuống còn 6 phần) === --}}
                <div class="lg:col-span-6 bg-white p-6 lg:p-8 border-r border-slate-50">
                    <div class="grid gap-4">
                        {{-- Ảnh chính --}}
                        <div class="relative aspect-square bg-[#F4F4F4] rounded-xl overflow-hidden cursor-zoom-in group"
                             @click="zoomImage = activeImage; zoomOpen = true">
                            <img :src="activeImage" class="w-full h-full object-cover mix-blend-multiply transition-transform duration-500 group-hover:scale-110">
                            
                            {{-- BADGE HOT (NỔI BẬT) --}}
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
                            @foreach($product->gallery_images as $img)
                                <button class="aspect-square rounded-lg overflow-hidden border transition-all bg-[#F4F4F4]"
                                        :class="activeImage === '{{ asset('storage/'.$img->image_path) }}' ? 'border-slate-900 ring-1 ring-slate-900' : 'border-transparent hover:border-slate-300'"
                                        @click="activeImage = '{{ asset('storage/'.$img->image_path) }}'">
                                    <img src="{{ asset('storage/'.$img->image_path) }}" class="w-full h-full object-cover mix-blend-multiply">
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- === CỘT PHẢI: INFO (Chiếm 6 phần) === --}}
                <div class="lg:col-span-6 p-6 lg:p-10 flex flex-col justify-center">
                    
                    {{-- Header --}}
                    <div class="mb-6 pb-6 border-b border-dashed border-slate-200">
                        <div class="flex justify-between items-start mb-2">
                            <span class="text-[10px] font-black text-indigo-600 uppercase tracking-widest bg-indigo-50 px-2 py-1 rounded">
                                {{ $product->category->name ?? 'Collection' }}
                            </span>
                            <a href="#reviews" class="flex items-center gap-1 text-xs font-bold text-amber-500 hover:underline">
                                <i class="fa-solid fa-star"></i> 4.8
                            </a>
                        </div>
                        <h1 class="text-3xl font-black text-slate-900 leading-tight mb-2 uppercase tracking-tight">{{ $product->name }}</h1>
                        <div class="text-2xl font-bold text-slate-900" x-text="formatMoney(currentPrice)"></div>
                    </div>

                    {{-- Form --}}
                    <form action="{{ route('client.cart.add') }}" method="POST" id="addToCartForm" class="space-y-6">
                        @csrf
                        <input type="hidden" name="variant_id" x-model="selectedVariantId">
                        <input type="hidden" name="product_id" value="{{ $product->id }}">

                        {{-- Chọn Thuộc tính --}}
                        @if($groupedAttributes->count() > 0)
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
                            <input type="hidden" name="variant_id" value="{{ $product->variants->first()->id ?? '' }}">
                        @endif

                        {{-- Control Bar (Qty & Price) --}}
                        <div class="bg-slate-50 p-4 rounded-xl border border-slate-100 flex items-center justify-between">
                            
                            {{-- Nút Số Lượng (Đã sửa logic) --}}
                            <div class="flex items-center gap-3">
                                <span class="text-xs font-bold text-slate-500 uppercase">SL:</span>
                                <div class="flex items-center bg-white border border-slate-200 rounded h-9 shadow-sm">
                                    <button type="button" @click="if(qty > 1) qty--" class="w-8 h-full hover:bg-slate-100 transition-colors rounded-l text-slate-500"><i class="fa-solid fa-minus text-[10px]"></i></button>
                                    <input type="number" name="quantity" x-model="qty" class="w-10 text-center font-bold text-slate-900 bg-transparent outline-none text-sm" readonly>
                                    <button type="button" @click="if(qty < currentStock) qty++" class="w-8 h-full hover:bg-slate-100 transition-colors rounded-r text-slate-500"><i class="fa-solid fa-plus text-[10px]"></i></button>
                                </div>
                            </div>

                            {{-- Tạm tính --}}
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
                            <div x-show="currentStock <= 0 && selectedVariantId" class="text-rose-500 flex items-center gap-1">
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
                            
                            {{-- Nút Yêu Thích (Đã sửa logic) --}}
                            <button type="button" 
                                    @click="toggleWishlist()"
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

        {{-- SECTION 2: MÔ TẢ & REVIEW --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {{-- CỘT TRÁI: MÔ TẢ (2 phần) --}}
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

            {{-- CỘT PHẢI: REVIEW (GIAO DIỆN PREMIUM) --}}
            <div class="lg:col-span-1" x-data="{ showReviewModal: false }">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 sticky top-24">
                    
                    {{-- Header Thống kê --}}
                    <div class="text-center mb-6">
                        <div class="text-5xl font-black text-slate-900 mb-1">{{ number_format($product->reviews->avg('rating') ?? 0, 1) }}</div>
                        <div class="flex justify-center text-amber-400 text-sm mb-2 gap-1">
                            @for($i=1; $i<=5; $i++)
                                <i class="{{ $i <= round($product->reviews->avg('rating')) ? 'fa-solid' : 'fa-regular' }} fa-star"></i>
                            @endfor
                        </div>
                        <p class="text-xs text-slate-400 font-bold uppercase tracking-wide">Dựa trên {{ $product->reviews->count() }} đánh giá</p>
                    </div>

                    {{-- Nút Viết đánh giá --}}
                    <div class="mb-6">
                        @auth
                            <button @click="showReviewModal = true" 
                                    class="w-full py-3 bg-slate-900 text-white text-xs font-bold uppercase tracking-widest rounded-xl hover:bg-indigo-600 hover:shadow-lg hover:-translate-y-0.5 transition-all flex items-center justify-center gap-2">
                                <i class="fa-solid fa-pen-nib"></i> Viết đánh giá
                            </button>
                        @else
                            <a href="{{ route('admin.login') }}" class="block w-full py-3 bg-slate-100 text-slate-500 text-xs font-bold uppercase tracking-widest rounded-xl text-center hover:bg-slate-200 transition-colors">
                                Đăng nhập để đánh giá
                            </a>
                        @endauth
                    </div>

                    {{-- Danh sách Review (Scrollable) --}}
                    <div class="space-y-6">
                        @forelse($reviews as $review)
                            <div class="flex gap-4 p-4 border rounded-xl bg-slate-50">
                                {{-- 1. AVATAR (Tự động tạo theo tên) --}}
                                <div class="flex-shrink-0">
                                    @if($review->user && $review->user->avatar)
                                        {{-- Nếu user có avatar thật --}}
                                        <img src="{{ asset('storage/' . $review->user->avatar) }}" 
                                            class="w-12 h-12 rounded-full object-cover border" alt="{{ $review->user->name }}">
                                    @else
                                        {{-- Nếu không có, dùng dịch vụ UI Avatars tạo ảnh theo tên --}}
                                        <img src="https://ui-avatars.com/api/?name={{ urlencode($review->user->name ?? 'K') }}&background=random&color=fff" 
                                            class="w-12 h-12 rounded-full object-cover border" alt="Avatar">
                                    @endif
                                </div>

                                {{-- 2. NỘI DUNG --}}
                                <div class="flex-1">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            {{-- HIỂN THỊ TÊN NGƯỜI DÙNG --}}
                                            <h4 class="font-bold text-gray-900">
                                                {{ $review->user->name ?? 'Khách ẩn danh' }} 
                                                {{-- Dấu ?? dùng để fallback nếu user đã bị xóa --}}
                                            </h4>
                                            
                                            {{-- Ngày đăng --}}
                                            <p class="text-xs text-gray-500">{{ $review->created_at->format('d/m/Y') }}</p>
                                        </div>

                                        {{-- Số sao --}}
                                        <div class="flex text-yellow-400 text-sm">
                                            @for($i = 1; $i <= 5; $i++)
                                                <i class="{{ $i <= $review->rating ? 'fa-solid' : 'fa-regular' }} fa-star"></i>
                                            @endfor
                                        </div>
                                    </div>

                                    {{-- Nội dung comment --}}
                                    <p class="mt-2 text-gray-700 text-sm leading-relaxed">
                                        {{ $review->comment }}
                                    </p>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-8 text-gray-500">
                                <i class="fa-regular fa-comment-dots text-4xl mb-3 opacity-50"></i>
                                <p>Chưa có đánh giá nào. Hãy là người đầu tiên!</p>
                            </div>
                        @endforelse

                        {{-- Phân trang --}}
                        <div class="mt-4">
                            {{ $reviews->links() }}
                        </div>
                    </div>
                </div>
                    

                {{-- MODAL VIẾT ĐÁNH GIÁ (POPUP) --}}
                <div x-show="showReviewModal" 
                    class="fixed inset-0 z-[100] flex items-center justify-center px-4"
                    style="display: none;">
                    
                    {{-- Backdrop --}}
                    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="showReviewModal = false" x-transition.opacity></div>

                    {{-- Modal Content --}}
                    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md relative z-10 overflow-hidden" 
                        x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 scale-90 translate-y-4"
                        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-200"
                        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                        x-transition:leave-end="opacity-0 scale-90 translate-y-4">
                        
                        {{-- Header Modal --}}
                        <div class="bg-slate-50 px-6 py-4 border-b border-slate-100 flex justify-between items-center">
                            <h3 class="font-bold text-slate-900 text-sm uppercase tracking-wide">Đánh giá sản phẩm</h3>
                            <button @click="showReviewModal = false" class="text-slate-400 hover:text-rose-500 transition-colors">
                                <i class="fa-solid fa-xmark text-lg"></i>
                            </button>
                        </div>

                        {{-- Form Body --}}
                        <div class="p-6">
                            <div class="flex items-center gap-4 mb-6">
                                <img src="{{ $product->image ? asset('storage/'.$product->image) : asset('img/no-image.png') }}" class="w-14 h-14 object-cover rounded-lg border border-slate-200">
                                <div>
                                    <p class="text-xs text-slate-500 uppercase font-bold mb-1">Bạn đang đánh giá:</p>
                                    <h4 class="font-bold text-slate-900 text-sm line-clamp-1">{{ $product->name }}</h4>
                                </div>
                            </div>

                            <form action="{{ route('client.reviews.store') }}" method="POST">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                
                                {{-- Chọn Sao --}}
                                <div class="mb-6 text-center">
                                    <label class="block text-xs font-bold text-slate-400 uppercase mb-3">Mức độ hài lòng</label>
                                    <div class="flex flex-row-reverse justify-center gap-2 group">
                                        @for($i=5; $i>=1; $i--)
                                            <input type="radio" id="modalStar{{$i}}" name="rating" value="{{$i}}" class="peer sr-only" required>
                                            <label for="modalStar{{$i}}" class="text-slate-200 peer-checked:text-amber-400 peer-hover:text-amber-400 hover:text-amber-400 text-3xl cursor-pointer transition-all hover:scale-110"><i class="fa-solid fa-star"></i></label>
                                        @endfor
                                    </div>
                                </div>

                                {{-- Nội dung --}}
                                <div class="mb-6">
                                    <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Nhận xét của bạn</label>
                                    <textarea name="comment" rows="4" class="w-full bg-slate-50 border-slate-200 rounded-xl text-sm p-3 focus:border-indigo-500 focus:ring-indigo-500 transition-colors" placeholder="Chất lượng sản phẩm thế nào? Giao hàng có nhanh không?" required></textarea>
                                </div>

                                <button type="submit" class="w-full bg-slate-900 text-white font-bold py-3.5 rounded-xl hover:bg-indigo-600 transition-colors shadow-lg shadow-slate-900/20 uppercase text-xs tracking-widest">
                                    Gửi đánh giá ngay
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- RELATED --}}
        @if($relatedProducts->count() > 0)
            <div class="mt-12 pt-8 border-t border-slate-200">
                <h3 class="text-xl font-black text-slate-900 uppercase mb-6">Sản phẩm liên quan</h3>
                @include('client.products._product_row', ['items' => $relatedProducts])
            </div>
        @endif
    </div>

    {{-- ZOOM MODAL --}}
    <div x-show="zoomOpen" x-transition.opacity class="fixed inset-0 z-[100] bg-white/90 backdrop-blur-sm flex items-center justify-center p-4" x-cloak>
        <button @click="zoomOpen = false" class="absolute top-5 right-5 w-10 h-10 flex items-center justify-center bg-black text-white rounded-full hover:rotate-90 transition-transform"><i class="fa-solid fa-xmark"></i></button>
        <img :src="zoomImage" class="max-w-full max-h-[90vh] object-contain shadow-2xl rounded-xl" @click="zoomOpen = false">
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
            isLiked: config.isLiked, // Trạng thái yêu thích từ DB
            zoomOpen: false,
            zoomImage: '',

            init() {
                let totalStock = 0;
                for (const key in this.variantsMap) {
                    totalStock += this.variantsMap[key].stock;
                }
                
                @if($groupedAttributes->count() == 0)
                    const firstId = Object.keys(this.variantsMap)[0];
                    if(firstId) {
                        this.selectedVariantId = firstId;
                        this.currentStock = this.variantsMap[firstId].stock;
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
                
                const totalRequired = {{ $groupedAttributes->count() }};
                const totalSelected = Object.keys(this.selectedAttributes).length;

                if (totalSelected === totalRequired) {
                    this.findMatchingVariant();
                }
            },

            findMatchingVariant() {
                const selectedIds = Object.values(this.selectedAttributes).map(Number).sort().toString();
                let found = false;

                for (const [variantId, data] of Object.entries(this.variantsMap)) {
                    const variantAttrIds = data.attributes.sort().toString();
                    if (selectedIds === variantAttrIds) {
                        this.selectedVariantId = variantId;
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

            // LOGIC YÊU THÍCH (WISHLIST)
            toggleWishlist() {
                this.isLiked = !this.isLiked; // Đổi trạng thái UI ngay lập tức
                
                // Gửi Ajax request lên server (Giả lập)
                // fetch('/wishlist/toggle', { method: 'POST', ... })
                
                if(this.isLiked) {
                    // Hiển thị Toast thông báo (Tuỳ chọn)
                    console.log('Đã thêm vào yêu thích');
                }
            },

            submitCart() {
                @if($groupedAttributes->count() > 0)
                    if (!this.selectedVariantId) {
                        this.errorMsg = 'Vui lòng chọn Phân loại!';
                        return;
                    }
                @endif
                
                if (this.currentStock <= 0) {
                     this.errorMsg = 'Sản phẩm đã hết hàng.';
                     return;
                }
                document.getElementById('addToCartForm').submit();
            }
        }
    }
</script>

<style>
    [x-cloak] { display: none !important; }
    /* Tùy chỉnh thanh cuộn cho box review */
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
</style>

{{-- TOAST NOTIFICATION (ALPINEJS) --}}
<div x-data="{ 
        show: false, 
        message: '', 
        type: 'success' 
     }" 
     x-init="
        @if(session('success')) 
            message = '{{ session('success') }}'; type = 'success'; show = true; setTimeout(() => show = false, 3000);
        @endif
        @if(session('error')) 
            message = '{{ session('error') }}'; type = 'error'; show = true; setTimeout(() => show = false, 4000);
        @endif
     "
     x-show="show" 
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 translate-y-2"
     x-transition:enter-end="opacity-100 translate-y-0"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100 translate-y-0"
     x-transition:leave-end="opacity-0 translate-y-2"
     class="fixed bottom-5 right-5 z-[200] flex items-center gap-3 px-6 py-4 rounded-xl shadow-2xl border"
     :class="type === 'success' ? 'bg-white border-emerald-100 text-emerald-600' : 'bg-white border-rose-100 text-rose-600'"
     style="display: none;">
    
    {{-- Icon --}}
    <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0"
         :class="type === 'success' ? 'bg-emerald-100' : 'bg-rose-100'">
        <i class="fa-solid" :class="type === 'success' ? 'fa-check' : 'fa-xmark'"></i>
    </div>

    {{-- Nội dung --}}
    <div>
        <h4 class="font-bold text-sm" x-text="type === 'success' ? 'Thành công!' : 'Thất bại!'"></h4>
        <p class="text-xs text-slate-500" x-text="message"></p>
    </div>

    {{-- Nút tắt --}}
    <button @click="show = false" class="text-slate-400 hover:text-slate-600 ml-2">
        <i class="fa-solid fa-xmark"></i>
    </button>
</div>
@endsection

