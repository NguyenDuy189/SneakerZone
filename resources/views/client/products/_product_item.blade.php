<div class="group relative flex flex-col overflow-hidden rounded-2xl bg-white border border-gray-100 shadow-sm hover:shadow-[0_8px_30px_rgb(0,0,0,0.08)] transition-all duration-500" 
     data-aos="fade-up" 
     data-aos-delay="{{ $index * 50 }}">
    
    {{-- [LOGIC MỚI] Kiểm tra trạng thái Like --}}
    @php
        $isLiked = false;
        if(auth()->check()) {
            $isLiked = \App\Models\Wishlist::where('user_id', auth()->id())
                        ->where('product_id', $product->id)
                        ->exists();
        }
    @endphp

    {{-- 1. IMAGE AREA --}}
    <div class="relative aspect-[4/5] overflow-hidden bg-slate-50">
        
        {{-- Badges --}}
        <div class="absolute top-3 left-3 z-20 flex flex-col gap-2 pointer-events-none">
            @if($product->is_featured)
                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[10px] font-bold bg-indigo-600/90 text-white backdrop-blur-sm uppercase tracking-wider shadow-sm">Hot</span>
            @endif
            @if($product->price_min < 1000000) 
                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[10px] font-bold bg-rose-500/90 text-white backdrop-blur-sm uppercase tracking-wider shadow-sm">Sale</span>
            @endif
        </div>

        {{-- [ĐÃ SỬA] Wishlist Button --}}
        {{-- Thêm onclick và class động dựa trên $isLiked --}}
        <button onclick="toggleWishlist({{ $product->id }}, this)"
                class="absolute top-3 right-3 z-30 w-8 h-8 rounded-full backdrop-blur-md flex items-center justify-center transition-all shadow-sm scale-0 group-hover:scale-100 duration-300
                       {{ $isLiked ? 'bg-rose-50 text-rose-500' : 'bg-white/80 text-slate-400 hover:text-rose-500 hover:bg-white' }}">
            <i class="{{ $isLiked ? 'fa-solid' : 'fa-regular' }} fa-heart"></i>
        </button>

        {{-- MAIN LINK (Logic ảnh cũ của bạn được giữ nguyên) --}}
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

            <img src="{{ $product->main_image }}" alt="{{ $product->name }}"
                class="absolute inset-0 h-full w-full object-cover object-center transition-transform duration-700 
                        group-hover:scale-110 {{ $product->hover_image ? 'group-hover:opacity-0' : '' }}"
                loading="lazy"
                onerror="this.src='https://placehold.co/400x500?text=No+Image'">

            @if($product->hover_image)
                <img src="{{ $product->hover_image }}" alt="{{ $product->name }}"
                    class="absolute inset-0 h-full w-full object-cover object-center transition-transform duration-700 
                            scale-110 opacity-0 group-hover:opacity-100 group-hover:scale-100">
            @endif
            
            <div class="absolute inset-0 bg-black/0 group-hover:bg-black/5 transition-colors duration-300"></div>
        </a>

        {{-- QUICK ADD BUTTON --}}
        <div class="absolute bottom-0 left-0 right-0 p-4 translate-y-full group-hover:translate-y-0 transition-transform duration-300 ease-out z-20">
            <a href="{{ route('client.products.show', $product->slug) }}" 
               class="flex w-full items-center justify-center gap-2 rounded-xl bg-white/95 backdrop-blur py-3 text-sm font-bold text-slate-900 shadow-lg hover:bg-slate-900 hover:text-white transition-colors cursor-pointer">
                <i class="fa-solid fa-cart-plus"></i> Tùy chọn
            </a>
        </div>
    </div>

    {{-- 2. INFO AREA --}}
    <div class="flex flex-1 flex-col p-4 relative z-20 bg-white">
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
                <span class="text-lg font-extrabold text-slate-900">
                    {{ number_format($product->price_min, 0, ',', '.') }}<span class="text-xs align-top">₫</span>
                </span>
            </div>
            <div class="flex items-center gap-1 text-amber-400 text-xs">
                <i class="fa-solid fa-star"></i>
                <span class="text-slate-400 font-medium ml-0.5">4.8</span>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function toggleWishlist(productId, btnElement) {
        // Hiệu ứng UX: Click cái đổi icon ngay lập tức
        const icon = btnElement.querySelector('i');
        const isCurrentlyLiked = icon.classList.contains('fa-solid');
        
        // Đổi trạng thái icon tạm thời
        if (isCurrentlyLiked) {
            icon.classList.remove('fa-solid', 'text-rose-500');
            icon.classList.add('fa-regular');
            btnElement.classList.remove('bg-rose-50', 'text-rose-500');
            btnElement.classList.add('bg-white/80', 'text-slate-400');
        } else {
            icon.classList.remove('fa-regular');
            icon.classList.add('fa-solid'); 
            btnElement.classList.remove('bg-white/80', 'text-slate-400');
            btnElement.classList.add('bg-rose-50', 'text-rose-500'); 
            
            // Hiệu ứng nảy tim
            icon.style.transform = 'scale(1.3)';
            setTimeout(() => icon.style.transform = 'scale(1)', 200);
        }

        // Gửi Ajax lên server
        const formData = new FormData();
        formData.append('product_id', productId);

        fetch("{{ route('client.wishlist.toggle') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
            },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                if(typeof Swal !== 'undefined') {
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 2000,
                        timerProgressBar: true
                    });
                    Toast.fire({ icon: 'success', title: data.message });
                }
            } else {
                // Nếu lỗi (ví dụ chưa đăng nhập), hoàn tác lại icon
                if (data.code === 401) {
                     Swal.fire({
                        title: 'Yêu cầu đăng nhập',
                        text: "Bạn cần đăng nhập để lưu sản phẩm yêu thích!",
                        icon: 'info',
                        showCancelButton: true,
                        confirmButtonText: 'Đăng nhập',
                        cancelButtonText: 'Hủy'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = "{{ route('login') }}";
                        }
                    });
                }
                
                // Revert icon về trạng thái cũ
                if (isCurrentlyLiked) {
                    icon.classList.add('fa-solid');
                    icon.classList.remove('fa-regular');
                } else {
                    icon.classList.add('fa-regular');
                    icon.classList.remove('fa-solid');
                }
            }
        })
        .catch(error => console.error('Error:', error));
    }
</script>
@endpush