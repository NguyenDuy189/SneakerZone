@extends('client.layouts.app')

@section('title', $product->name . ' - Sneaker Zone')

@section('content')

{{-- DATA ALPINEJS --}}
<div x-data="productDetail({
        basePrice: {{ $product->price_min }},
        variants: {{ json_encode($variantMap) }},
    })" 
    class="bg-slate-50 min-h-screen font-sans text-slate-800 pb-20">

    {{-- 1. BREADCRUMB (Nền trắng tách biệt) --}}
    <div class="bg-white border-b border-slate-200 shadow-sm sticky top-[60px] z-30">
        <div class="container mx-auto px-4 py-4 max-w-7xl">
            <nav class="flex items-center text-xs font-medium text-slate-500">
                <a href="{{ route('client.home') }}" class="hover:text-indigo-600 transition-colors">Trang chủ</a>
                <i class="fa-solid fa-chevron-right text-[10px] mx-3 text-slate-300"></i>
                <a href="{{ route('client.products.index') }}" class="hover:text-indigo-600 transition-colors">Sản phẩm</a>
                <i class="fa-solid fa-chevron-right text-[10px] mx-3 text-slate-300"></i>
                <span class="text-slate-900 font-bold truncate">{{ $product->name }}</span>
            </nav>
        </div>
    </div>

    {{-- MAIN CONTAINER --}}
    <div class="container mx-auto px-4 py-8 max-w-7xl">
        
        {{-- LAYOUT CHÍNH: CARD SẢN PHẨM --}}
        <div class="bg-white rounded-3xl shadow-xl border border-slate-100 overflow-hidden mb-10">
            <div class="grid grid-cols-1 lg:grid-cols-12">
                
                {{-- === CỘT TRÁI: GALLERY ẢNH (Chiếm 7 phần) === --}}
                <div class="lg:col-span-7 bg-slate-50 p-6 lg:p-10 border-r border-slate-100">
                    <div class="grid gap-4">
                        {{-- Ảnh chính (Lớn, bo góc) --}}
                        <div class="relative aspect-square bg-white rounded-2xl overflow-hidden shadow-sm border border-slate-200 group cursor-zoom-in"
                             @click="zoomImage = activeImage; zoomOpen = true">
                            <img :src="activeImage" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                            
                            {{-- Badge --}}
                            @if($product->is_featured)
                                <span class="absolute top-4 left-4 bg-rose-500 text-white text-xs font-bold px-3 py-1 rounded-full shadow-lg shadow-rose-500/30">
                                    <i class="fa-solid fa-fire mr-1"></i> Hot
                                </span>
                            @endif
                        </div>

                        {{-- Thumbnails (Grid ngang) --}}
                        <div class="grid grid-cols-5 sm:grid-cols-6 gap-3">
                            {{-- Nút ảnh gốc --}}
                            <button class="aspect-square rounded-xl overflow-hidden border-2 transition-all"
                                    :class="activeImage === '{{ $product->image ? asset('storage/'.$product->image) : asset('img/no-image.png') }}' ? 'border-indigo-600 ring-2 ring-indigo-100' : 'border-white hover:border-slate-300 shadow-sm'"
                                    @click="activeImage = '{{ $product->image ? asset('storage/'.$product->image) : asset('img/no-image.png') }}'">
                                <img src="{{ $product->image ? asset('storage/'.$product->image) : asset('img/no-image.png') }}" class="w-full h-full object-cover">
                            </button>
                            {{-- Loop Gallery --}}
                            @foreach($product->gallery_images as $img)
                                <button class="aspect-square rounded-xl overflow-hidden border-2 transition-all"
                                        :class="activeImage === '{{ asset('storage/'.$img->image_path) }}' ? 'border-indigo-600 ring-2 ring-indigo-100' : 'border-white hover:border-slate-300 shadow-sm'"
                                        @click="activeImage = '{{ asset('storage/'.$img->image_path) }}'">
                                    <img src="{{ asset('storage/'.$img->image_path) }}" class="w-full h-full object-cover">
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- === CỘT PHẢI: THÔNG TIN (Chiếm 5 phần) === --}}
                <div class="lg:col-span-5 p-6 lg:p-10 flex flex-col h-full bg-white">
                    <div class="sticky top-6">
                        
                        {{-- Header Info --}}
                        <div class="mb-6 border-b border-dashed border-slate-200 pb-6">
                            <div class="flex justify-between items-start mb-2">
                                <span class="text-xs font-bold text-indigo-600 uppercase tracking-wider bg-indigo-50 px-2 py-1 rounded">
                                    {{ $product->category->name ?? 'Sneaker' }}
                                </span>
                                <a href="#reviews" class="flex items-center gap-1 text-xs font-bold text-amber-500 hover:underline">
                                    <i class="fa-solid fa-star"></i> 4.8 ({{ $product->reviews->count() }})
                                </a>
                            </div>

                            <h1 class="text-3xl font-black text-slate-900 leading-tight mb-3">
                                {{ $product->name }}
                            </h1>

                            <div class="flex items-baseline gap-2">
                                <span class="text-3xl font-bold text-indigo-600" x-text="formatMoney(currentPrice)"></span>
                            </div>
                        </div>

                        {{-- FORM MUA HÀNG --}}
                        <form action="{{ route('client.cart.add') }}" method="POST" id="addToCartForm" class="space-y-6">
                            @csrf
                            <input type="hidden" name="variant_id" x-model="selectedVariantId">
                            <input type="hidden" name="product_id" value="{{ $product->id }}">

                            {{-- Attributes Selection --}}
                            @if($groupedAttributes->count() > 0)
                                @foreach($groupedAttributes as $name => $values)
                                    <div>
                                        <div class="flex justify-between mb-2">
                                            <span class="text-sm font-bold text-slate-700 uppercase">{{ $name }}</span>
                                            @if(strtolower($name) === 'size')
                                                <button type="button" class="text-xs text-indigo-600 hover:underline"><i class="fa-solid fa-ruler-combined"></i> Bảng size</button>
                                            @endif
                                        </div>
                                        <div class="flex flex-wrap gap-2">
                                            @foreach($values as $val)
                                                <button type="button" 
                                                        class="h-10 px-4 border rounded-lg text-sm font-bold transition-all relative overflow-hidden"
                                                        :class="selectedAttributes['{{ $name }}'] == {{ $val->id }} 
                                                            ? 'bg-slate-900 text-white border-slate-900 shadow-lg shadow-slate-900/20 scale-105' 
                                                            : 'bg-white text-slate-600 border-slate-200 hover:border-slate-400 hover:bg-slate-50'"
                                                        @click="selectAttribute('{{ $name }}', {{ $val->id }})">
                                                    {{ $val->value }}
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                                <div x-show="errorMsg" x-transition class="p-3 bg-rose-50 text-rose-600 text-xs font-bold rounded-lg border border-rose-100 flex items-center gap-2">
                                    <i class="fa-solid fa-circle-exclamation"></i> <span x-text="errorMsg"></span>
                                </div>
                            @else
                                <input type="hidden" name="variant_id" value="{{ $product->variants->first()->id ?? '' }}">
                            @endif

                            {{-- Quantity & Add to Cart --}}
                            <div class="bg-slate-50 p-4 rounded-xl border border-slate-100">
                                <div class="flex items-center gap-4">
                                    {{-- Số lượng --}}
                                    <div class="flex items-center bg-white border border-slate-200 rounded-lg h-12 w-32 shadow-sm">
                                        <button type="button" @click="if(qty > 1) qty--" class="w-10 h-full text-slate-400 hover:text-black transition-colors"><i class="fa-solid fa-minus text-xs"></i></button>
                                        <input type="number" name="quantity" x-model="qty" class="flex-1 text-center font-bold text-slate-900 bg-transparent outline-none text-sm" readonly>
                                        <button type="button" @click="if(qty < currentStock) qty++" class="w-10 h-full text-slate-400 hover:text-black transition-colors"><i class="fa-solid fa-plus text-xs"></i></button>
                                    </div>
                                    
                                    {{-- Tổng tiền nhỏ --}}
                                    <div class="flex-1 text-right">
                                        <div class="text-xs text-slate-400 font-medium uppercase">Tạm tính</div>
                                        <div class="text-lg font-bold text-slate-900" x-text="formatMoney(currentPrice * qty)"></div>
                                    </div>
                                </div>
                                
                                {{-- Stock Status --}}
                                <div class="mt-3 flex justify-between items-center text-xs font-medium border-t border-slate-200 pt-3">
                                    <span class="text-slate-500">Trạng thái kho:</span>
                                    
                                    {{-- Sửa lỗi ở dòng dưới đây --}}
                                    <span x-show="currentStock > 0" class="text-emerald-600 flex items-center gap-1">
                                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span> 
                                        Còn <span x-text="currentStock"></span> sp
                                    </span>
                                    
                                    <span x-show="currentStock <= 0 && selectedVariantId" class="text-rose-500 flex items-center gap-1">
                                        <span class="w-2 h-2 rounded-full bg-rose-500"></span> Hết hàng
                                    </span>
                                </div>
                            </div>

                            {{-- Actions --}}
                            <div class="flex gap-3">
                                <button type="button" @click="submitCart()" 
                                        :disabled="currentStock <= 0"
                                        class="flex-1 h-12 bg-indigo-600 text-white font-bold rounded-xl shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 hover:-translate-y-0.5 transition-all flex items-center justify-center gap-2 uppercase tracking-wide text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                                    <i class="fa-solid fa-bag-shopping"></i> Thêm vào giỏ
                                </button>
                                <button type="button" class="w-12 h-12 border border-slate-200 text-slate-400 rounded-xl hover:text-rose-500 hover:border-rose-200 hover:bg-rose-50 transition-all shadow-sm">
                                    <i class="fa-regular fa-heart text-xl"></i>
                                </button>
                            </div>
                        </form>

                        {{-- Service Badges --}}
                        <div class="grid grid-cols-2 gap-4 mt-8 pt-6 border-t border-dashed border-slate-200">
                            <div class="flex items-start gap-3">
                                <i class="fa-solid fa-truck-fast text-indigo-600 text-xl mt-1"></i>
                                <div>
                                    <h6 class="font-bold text-xs text-slate-900 uppercase">Free Shipping</h6>
                                    <p class="text-[10px] text-slate-500 leading-tight">Cho đơn hàng > 2tr</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <i class="fa-solid fa-rotate-left text-indigo-600 text-xl mt-1"></i>
                                <div>
                                    <h6 class="font-bold text-xs text-slate-900 uppercase">Đổi trả 30 ngày</h6>
                                    <p class="text-[10px] text-slate-500 leading-tight">Thủ tục đơn giản</p>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        {{-- SECTION 2: MÔ TẢ & CHI TIẾT (Tách biệt rõ ràng) --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 mb-10">
            
            {{-- MÔ TẢ CHI TIẾT (Card Trắng) --}}
            <div class="lg:col-span-8">
                <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-8 h-full">
                    <h3 class="text-xl font-black text-slate-900 uppercase mb-6 flex items-center gap-2">
                        <span class="w-1 h-6 bg-indigo-600 rounded-full"></span> Chi tiết sản phẩm
                    </h3>
                    
                    <div class="prose prose-slate max-w-none text-slate-600 leading-relaxed">
                        {!! $product->description !!}
                        @if(empty(strip_tags($product->description)))
                            <div class="p-6 bg-slate-50 rounded-xl text-center text-slate-400 italic">
                                Thông tin mô tả đang được cập nhật...
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- THÔNG SỐ KỸ THUẬT (Card Trắng nhỏ hơn) --}}
            <div class="lg:col-span-4">
                <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-8 h-full sticky top-24">
                    <h3 class="text-xl font-black text-slate-900 uppercase mb-6 flex items-center gap-2">
                        <span class="w-1 h-6 bg-slate-900 rounded-full"></span> Thông số
                    </h3>
                    <ul class="space-y-4">
                        <li class="flex justify-between border-b border-slate-50 pb-3">
                            <span class="text-slate-500 text-sm">Mã SKU</span>
                            <span class="font-mono font-bold text-slate-900">{{ $product->sku_code ?? 'N/A' }}</span>
                        </li>
                        <li class="flex justify-between border-b border-slate-50 pb-3">
                            <span class="text-slate-500 text-sm">Thương hiệu</span>
                            <span class="font-bold text-indigo-600">{{ $product->brand->name ?? 'N/A' }}</span>
                        </li>
                        <li class="flex justify-between border-b border-slate-50 pb-3">
                            <span class="text-slate-500 text-sm">Danh mục</span>
                            <span class="font-bold text-slate-900">{{ $product->category->name }}</span>
                        </li>
                        <li class="flex justify-between border-b border-slate-50 pb-3">
                            <span class="text-slate-500 text-sm">Tình trạng</span>
                            <span class="font-bold text-emerald-600">Mới 100%</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- SECTION 3: ĐÁNH GIÁ (REVIEW) --}}
        <div id="reviews" class="bg-white rounded-3xl shadow-sm border border-slate-100 p-8 lg:p-12 scroll-mt-24">
            <h3 class="text-2xl font-black text-slate-900 uppercase text-center mb-10">Đánh giá từ khách hàng</h3>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12">
                
                {{-- Cột Form --}}
                <div class="lg:col-span-4">
                    @auth
                        <div class="bg-slate-50 border border-slate-100 p-6 rounded-2xl sticky top-24">
                            <h4 class="font-bold text-slate-900 text-lg mb-2">Viết đánh giá</h4>
                            <p class="text-xs text-slate-500 mb-4">Chia sẻ cảm nhận của bạn về sản phẩm này.</p>
                            
                            <form action="{{ route('client.reviews.store') }}" method="POST">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                
                                <div class="mb-4">
                                    <label class="block text-xs font-bold uppercase text-slate-400 mb-2">Chấm điểm</label>
                                    <div class="flex flex-row-reverse justify-end gap-2">
                                        @for($i=5; $i>=1; $i--)
                                            <input type="radio" id="star{{$i}}" name="rating" value="{{$i}}" class="peer sr-only" required>
                                            <label for="star{{$i}}" class="text-slate-300 peer-checked:text-amber-400 hover:text-amber-400 peer-hover:text-amber-400 text-2xl cursor-pointer transition-colors"><i class="fa-solid fa-star"></i></label>
                                        @endfor
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label class="block text-xs font-bold uppercase text-slate-400 mb-2">Nội dung</label>
                                    <textarea name="comment" rows="4" class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500 bg-white" placeholder="Sản phẩm đẹp, giao hàng nhanh..." required></textarea>
                                </div>

                                <button type="submit" class="w-full bg-slate-900 text-white font-bold py-3 rounded-xl hover:bg-indigo-600 transition-colors shadow-lg shadow-slate-900/20">Gửi đánh giá</button>
                            </form>
                        </div>
                    @else
                        {{-- THÔNG BÁO ĐĂNG NHẬP (DESIGN ĐẸP) --}}
                        <div class="bg-slate-900 text-white p-8 rounded-2xl text-center sticky top-24 shadow-xl">
                            <div class="w-14 h-14 bg-white/10 rounded-full flex items-center justify-center mx-auto mb-4 backdrop-blur-sm">
                                <i class="fa-solid fa-lock text-2xl text-white"></i>
                            </div>
                            <h4 class="font-bold text-lg mb-2">Thành viên mới được đánh giá</h4>
                            <p class="text-slate-300 text-xs mb-6 leading-relaxed">Vui lòng đăng nhập để chia sẻ trải nghiệm mua sắm của bạn.</p>
                            <a href="{{ route('admin.login') }}" class="block w-full bg-white text-slate-900 font-bold py-3 rounded-xl hover:bg-indigo-50 hover:text-indigo-700 transition-colors uppercase text-xs tracking-wide">Đăng nhập ngay</a>
                        </div>
                    @endauth
                </div>

                {{-- Cột List --}}
                <div class="lg:col-span-8 space-y-6">
                    @forelse($product->reviews as $review)
                        <div class="border border-slate-100 rounded-2xl p-6 hover:shadow-md transition-shadow bg-white">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-indigo-50 text-indigo-600 border border-indigo-100 flex items-center justify-center font-bold text-sm">
                                        {{ substr($review->user->name ?? 'A', 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-900 text-sm">{{ $review->user->name ?? 'Người dùng' }}</div>
                                        <div class="flex text-amber-400 text-xs">
                                            @for($i=1; $i<=5; $i++) <i class="{{ $i <= $review->rating ? 'fa-solid' : 'fa-regular' }} fa-star"></i> @endfor
                                        </div>
                                    </div>
                                </div>
                                <span class="text-xs text-slate-400 bg-slate-50 px-2 py-1 rounded">{{ $review->created_at->format('d/m/Y') }}</span>
                            </div>
                            <p class="text-slate-600 text-sm leading-relaxed">{{ $review->comment }}</p>
                        </div>
                    @empty
                        <div class="text-center py-12 border-2 border-dashed border-slate-200 rounded-2xl">
                            <i class="fa-regular fa-comments text-4xl text-slate-300 mb-3"></i>
                            <p class="text-slate-500 font-medium">Chưa có đánh giá nào.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- SẢN PHẨM LIÊN QUAN --}}
        @if($relatedProducts->count() > 0)
            <div class="mt-16">
                <div class="flex justify-between items-end mb-8 px-2">
                    <h3 class="text-2xl font-black text-slate-900 uppercase">Có thể bạn thích</h3>
                    <a href="{{ route('client.products.index') }}" class="text-sm font-bold text-indigo-600 hover:text-slate-900 transition-colors">Xem tất cả <i class="fa-solid fa-arrow-right ml-1"></i></a>
                </div>
                @include('client.products._product_row', ['items' => $relatedProducts])
            </div>
        @endif

    </div>

    {{-- ZOOM MODAL --}}
    <div x-show="zoomOpen" x-transition.opacity class="fixed inset-0 z-[100] bg-black/95 flex items-center justify-center p-4" x-cloak>
        <button @click="zoomOpen = false" class="absolute top-6 right-6 w-12 h-12 flex items-center justify-center hover:bg-white/10 rounded-full transition-colors"><i class="fa-solid fa-xmark text-3xl text-white"></i></button>
        <img :src="zoomImage" class="max-w-full max-h-screen object-contain shadow-2xl rounded-lg" @click="zoomOpen = false">
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
                    this.errorMsg = 'Phiên bản này không khả dụng.';
                    this.selectedVariantId = null;
                }
            },

            submitCart() {
                @if($groupedAttributes->count() > 0)
                    if (!this.selectedVariantId) {
                        this.errorMsg = 'Vui lòng chọn đầy đủ tùy chọn!';
                        return;
                    }
                @endif
                
                if (this.currentStock <= 0) {
                     this.errorMsg = 'Hết hàng.';
                     return;
                }
                document.getElementById('addToCartForm').submit();
            }
        }
    }
</script>

<style>
    .no-scrollbar::-webkit-scrollbar { display: none; }
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    [x-cloak] { display: none !important; }
</style>

@endsection