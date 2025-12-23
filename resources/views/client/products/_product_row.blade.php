{{-- 
    COMPONENT: PRODUCT ROW (GRID)
    Nhận vào biến $items (Collection sản phẩm)
--}}

<div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-x-4 gap-y-8 md:gap-x-6 md:gap-y-10">
    
    @foreach ($items as $index => $product)
        {{-- PRODUCT CARD --}}
        <div class="group relative flex flex-col overflow-hidden rounded-2xl bg-white border border-gray-100 shadow-sm hover:shadow-[0_8px_30px_rgb(0,0,0,0.08)] transition-all duration-500" 
             data-aos="fade-up" 
             data-aos-delay="{{ $index * 50 }}">
            
            {{-- 1. IMAGE AREA --}}
            <div class="relative aspect-[4/5] overflow-hidden bg-slate-50">
                
                {{-- Badges --}}
                <div class="absolute top-3 left-3 z-20 flex flex-col gap-2">
                    @if($product->is_featured)
                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[10px] font-bold bg-indigo-600/90 text-white backdrop-blur-sm uppercase tracking-wider shadow-sm">
                            Hot
                        </span>
                    @endif
                    
                    {{-- Logic Sale giả lập (Hoặc dùng logic thật nếu có field sale_price) --}}
                    @if($product->price_min < 1000000) 
                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[10px] font-bold bg-rose-500/90 text-white backdrop-blur-sm uppercase tracking-wider shadow-sm">
                            Sale
                        </span>
                    @endif
                </div>

                {{-- Wishlist Button --}}
                <button class="absolute top-3 right-3 z-20 w-8 h-8 rounded-full bg-white/80 backdrop-blur-md flex items-center justify-center text-slate-400 hover:text-rose-500 hover:bg-white transition-all shadow-sm scale-0 group-hover:scale-100 duration-300">
                    <i class="fa-regular fa-heart"></i>
                </button>

                {{-- Link & Images --}}
                <a href="{{ route('client.products.show', $product->slug) }}" class="block w-full h-full">
                    {{-- Xử lý ảnh: Ưu tiên ảnh thật, nếu không có dùng ảnh placeholder --}}
                    @php
                        $mainImg = $product->image && \Illuminate\Support\Facades\Storage::disk('public')->exists($product->image) 
                            ? asset('storage/'.$product->image) 
                            : asset('img/no-image.png');
                        
                        // Lấy ảnh gallery đầu tiên làm ảnh hover (nếu có)
                        $hoverImg = null;
                        if($product->gallery_images && $product->gallery_images->isNotEmpty()) {
                            $firstGallery = $product->gallery_images->first();
                            if(\Illuminate\Support\Facades\Storage::disk('public')->exists($firstGallery->image_path)){
                                $hoverImg = asset('storage/' . $firstGallery->image_path);
                            }
                        }
                    @endphp

                    {{-- Ảnh chính --}}
                    <img src="{{ $mainImg }}" 
                         alt="{{ $product->name }}"
                         class="absolute inset-0 h-full w-full object-cover object-center transition-transform duration-700 group-hover:scale-110 {{ $hoverImg ? 'group-hover:opacity-0' : '' }}"
                         loading="lazy"
                         onerror="this.src='https://placehold.co/400x500?text=No+Image'">
                    
                    {{-- Ảnh Hover (Nếu có) --}}
                    @if($hoverImg)
                        <img src="{{ $hoverImg }}" 
                             alt="{{ $product->name }}"
                             class="absolute inset-0 h-full w-full object-cover object-center transition-transform duration-700 scale-110 opacity-0 group-hover:opacity-100 group-hover:scale-100">
                    @endif
                    
                    {{-- Dark Overlay khi hover (để chữ nổi hơn) --}}
                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/5 transition-colors duration-300"></div>
                </a>

                {{-- Quick Add Button (Slide Up) --}}
                <div class="absolute bottom-0 left-0 right-0 p-4 translate-y-full group-hover:translate-y-0 transition-transform duration-300 ease-out z-20">
                    <a href="{{ route('client.products.show', $product->slug) }}" 
                       class="flex w-full items-center justify-center gap-2 rounded-xl bg-white/95 backdrop-blur py-3 text-sm font-bold text-slate-900 shadow-lg hover:bg-slate-900 hover:text-white transition-colors">
                        <i class="fa-solid fa-cart-plus"></i> Tùy chọn
                    </a>
                </div>
            </div>

            {{-- 2. INFO AREA --}}
            <div class="flex flex-1 flex-col p-4">
                {{-- Category & Size Preview --}}
                <div class="mb-1 flex items-center justify-between">
                    <a href="{{ route('client.products.index', ['category' => $product->category->slug ?? '']) }}" class="text-[10px] font-bold uppercase tracking-wide text-slate-400 hover:text-indigo-600 transition-colors">
                        {{ $product->category->name ?? 'Sneaker' }}
                    </a>
                    
                    {{-- Đếm số lượng biến thể (size) --}}
                    @if($product->variants_count > 0 || ($product->variants && $product->variants->count() > 0))
                        <span class="text-[10px] text-slate-400 bg-slate-50 px-1.5 py-0.5 rounded">
                            {{ $product->variants_count ?? $product->variants->count() }} sizes
                        </span>
                    @endif
                </div>

                {{-- Product Name --}}
                <h3 class="mb-2 text-base font-bold text-slate-900 leading-snug line-clamp-2 min-h-[2.5rem]">
                    <a href="{{ route('client.products.show', $product->slug) }}" class="hover:text-indigo-600 transition-colors">
                        {{ $product->name }}
                    </a>
                </h3>

                {{-- Price & Rating --}}
                <div class="mt-auto flex items-end justify-between">
                    <div class="flex flex-col">
                        <span class="text-lg font-extrabold text-slate-900">
                            {{ number_format($product->price_min, 0, ',', '.') }}<span class="text-xs align-top">₫</span>
                        </span>
                    </div>
                    
                    {{-- Fake Rating (Hoặc dùng real rating nếu có relation reviews) --}}
                    <div class="flex items-center gap-1 text-amber-400 text-xs">
                        <i class="fa-solid fa-star"></i>
                        <span class="text-slate-400 font-medium ml-0.5">4.8</span>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

</div>