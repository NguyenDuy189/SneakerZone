<div class="group relative flex flex-col overflow-hidden rounded-2xl bg-white border border-gray-100 shadow-sm hover:shadow-[0_8px_30px_rgb(0,0,0,0.08)] transition-all duration-500" 
     data-aos="fade-up" 
     data-aos-delay="{{ $index * 50 }}">
    
    {{-- 1. IMAGE AREA --}}
    <div class="relative aspect-[4/5] overflow-hidden bg-slate-50">
        
        {{-- Badges --}}
        <div class="absolute top-3 left-3 z-20 flex flex-col gap-2 pointer-events-none"> {{-- Thêm pointer-events-none để không chặn click --}}
            @if($product->is_featured)
                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[10px] font-bold bg-indigo-600/90 text-white backdrop-blur-sm uppercase tracking-wider shadow-sm">Hot</span>
            @endif
            @if($product->price_min < 1000000) 
                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[10px] font-bold bg-rose-500/90 text-white backdrop-blur-sm uppercase tracking-wider shadow-sm">Sale</span>
            @endif
        </div>

        {{-- Wishlist --}}
        <button class="absolute top-3 right-3 z-30 w-8 h-8 rounded-full bg-white/80 backdrop-blur-md flex items-center justify-center text-slate-400 hover:text-rose-500 hover:bg-white transition-all shadow-sm scale-0 group-hover:scale-100 duration-300">
            <i class="fa-regular fa-heart"></i>
        </button>

        {{-- MAIN LINK (Bao quanh ảnh) --}}
        <a href="{{ route('client.products.show', $product->slug) }}" class="block w-full h-full relative z-10">
            @php
                $mainImg = $product->image && \Illuminate\Support\Facades\Storage::disk('public')->exists($product->image) 
                    ? asset('storage/'.$product->image) : asset('img/no-image.png');
                
                $hoverImg = null;
                if($product->gallery_images && $product->gallery_images->isNotEmpty()) {
                    $firstGallery = $product->gallery_images->first();
                    if(\Illuminate\Support\Facades\Storage::disk('public')->exists($firstGallery->image_path)){
                        $hoverImg = asset('storage/' . $firstGallery->image_path);
                    }
                }
            @endphp

            <img src="{{ $mainImg }}" alt="{{ $product->name }}"
                 class="absolute inset-0 h-full w-full object-cover object-center transition-transform duration-700 group-hover:scale-110 {{ $hoverImg ? 'group-hover:opacity-0' : '' }}"
                 loading="lazy" onerror="this.src='https://placehold.co/400x500?text=No+Image'">
            
            @if($hoverImg)
                <img src="{{ $hoverImg }}" alt="{{ $product->name }}"
                     class="absolute inset-0 h-full w-full object-cover object-center transition-transform duration-700 scale-110 opacity-0 group-hover:opacity-100 group-hover:scale-100">
            @endif
            
            <div class="absolute inset-0 bg-black/0 group-hover:bg-black/5 transition-colors duration-300"></div>
        </a>

        {{-- QUICK ADD BUTTON (Đã tách ra khỏi thẻ a ở trên) --}}
        {{-- Dùng z-20 để nằm đè lên thẻ a ảnh --}}
        <div class="absolute bottom-0 left-0 right-0 p-4 translate-y-full group-hover:translate-y-0 transition-transform duration-300 ease-out z-20">
            <a href="{{ route('client.products.show', $product->slug) }}" 
               class="flex w-full items-center justify-center gap-2 rounded-xl bg-white/95 backdrop-blur py-3 text-sm font-bold text-slate-900 shadow-lg hover:bg-slate-900 hover:text-white transition-colors cursor-pointer">
                <i class="fa-solid fa-cart-plus"></i> Tùy chọn
            </a>
        </div>
    </div>

    {{-- 2. INFO AREA --}}
    <div class="flex flex-1 flex-col p-4 relative z-20 bg-white"> {{-- Thêm z-20 và bg-white --}}
        <div class="mb-1 flex items-center justify-between">
            <a href="{{ route('client.products.index', ['category' => $product->category->slug ?? '']) }}" class="text-[10px] font-bold uppercase tracking-wide text-slate-400 hover:text-indigo-600 transition-colors">
                {{ $product->category->name ?? 'Sneaker' }}
            </a>
            @if(isset($product->variants_count) && $product->variants_count > 0)
                <span class="text-[10px] text-slate-400 bg-slate-50 px-1.5 py-0.5 rounded">{{ $product->variants_count }} sizes</span>
            @endif
        </div>

        <h3 class="mb-2 text-base font-bold text-slate-900 leading-snug line-clamp-2 min-h-[2.5rem]">
            <a href="{{ route('client.products.show', $product->slug) }}" class="hover:text-indigo-600 transition-colors">
                {{ $product->name }}
            </a>
        </h3>

        <div class="mt-auto flex items-end justify-between">
            <div class="flex flex-col">
    @php
        // Lấy biến thể đầu tiên để check giá
        $variant = $product->variants->first();
        $originalPrice = $variant ? $variant->original_price : ($product->price_min ?? 0);
        $salePrice = $variant ? $variant->sale_price : 0;
    @endphp

    @if($salePrice > 0 && $salePrice < $originalPrice)
        {{-- TRƯỜNG HỢP CÓ SALE --}}
        {{-- 1. Giá gốc (Gạch ngang, màu xám, nhỏ) --}}
        <span class="text-xs text-slate-400 line-through mb-0.5">
            {{ number_format($originalPrice, 0, ',', '.') }}₫
        </span>
        {{-- 2. Giá Sale (Màu đỏ, to đậm) --}}
        <span class="text-lg font-extrabold text-red-600">
            {{ number_format($salePrice, 0, ',', '.') }}<span class="text-xs align-top">₫</span>
        </span>
    @else
        {{-- TRƯỜNG HỢP KHÔNG SALE (Hiện giá gốc như cũ) --}}
        <span class="text-lg font-extrabold text-slate-900">
            {{ number_format($originalPrice, 0, ',', '.') }}<span class="text-xs align-top">₫</span>
        </span>
    @endif
            </div>
            <div class="flex items-center gap-1 text-amber-400 text-xs">
                <i class="fa-solid fa-star"></i>
                <span class="text-slate-400 font-medium ml-0.5">4.8</span>
            </div>
        </div>
    </div>
</div>