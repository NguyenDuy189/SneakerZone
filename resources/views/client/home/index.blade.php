@extends('client.layouts.app')

@section('title', 'Sneaker Zone - Flagship Store')

@section('content')

{{-- =========================================================== --}}
{{-- 1. ALERT SYSTEM (Thông báo nổi) --}}
{{-- =========================================================== --}}
<div class="fixed top-24 right-5 z-[9999] space-y-4 w-full max-w-xs">
    @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-x-full"
             x-transition:enter-end="opacity-100 translate-x-0"
             x-transition:leave="transition ease-in duration-300"
             x-transition:leave-start="opacity-100 translate-x-0"
             x-transition:leave-end="opacity-0 translate-x-full"
             class="flex items-center p-4 bg-white rounded-xl shadow-2xl border-l-4 border-emerald-500 ring-1 ring-black/5">
            <div class="flex-shrink-0 text-emerald-500">
                <i class="fa-solid fa-circle-check text-xl"></i>
            </div>
            <div class="ml-3">
                <p class="text-sm font-bold text-slate-800">Thành công!</p>
                <p class="text-sm text-slate-500">{{ session('success') }}</p>
            </div>
            <button @click="show = false" class="ml-auto text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-x-full"
             x-transition:enter-end="opacity-100 translate-x-0"
             x-transition:leave="transition ease-in duration-300"
             x-transition:leave-start="opacity-100 translate-x-0"
             x-transition:leave-end="opacity-0 translate-x-full"
             class="flex items-center p-4 bg-white rounded-xl shadow-2xl border-l-4 border-rose-500 ring-1 ring-black/5">
            <div class="flex-shrink-0 text-rose-500">
                <i class="fa-solid fa-circle-exclamation text-xl"></i>
            </div>
            <div class="ml-3">
                <p class="text-sm font-bold text-slate-800">Đã xảy ra lỗi!</p>
                <p class="text-sm text-slate-500">{{ session('error') }}</p>
            </div>
            <button @click="show = false" class="ml-auto text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
    @endif
</div>

{{-- =========================================================== --}}
{{-- 2. HERO SLIDER (Alpine.js Powered) --}}
{{-- =========================================================== --}}
<div class="relative w-full h-[85vh] bg-slate-900 overflow-hidden group">
    <div x-data="{ 
            activeSlide: 0, 
            slides: {{ $banners->count() }}, 
            timer: null,
            start() { this.timer = setInterval(() => this.next(), 6000) },
            stop() { clearInterval(this.timer) },
            next() { this.activeSlide = (this.activeSlide + 1) % this.slides },
            prev() { this.activeSlide = (this.activeSlide - 1 + this.slides) % this.slides }
         }" 
         x-init="start()" 
         @mouseenter="stop()" 
         @mouseleave="start()"
         class="relative h-full w-full">

        {{-- Slides Loop --}}
        @forelse($banners as $index => $banner)
            <div x-show="activeSlide === {{ $index }}"
                 x-transition:enter="transition ease-in-out duration-1000"
                 x-transition:enter-start="opacity-0 scale-105"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in-out duration-1000"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-100"
                 class="absolute inset-0 w-full h-full">
                
                {{-- Image --}}
                <div class="absolute inset-0 bg-black/40 z-10"></div> {{-- Dark Overlay --}}
                <img src="{{ asset('storage/' . ltrim($banner->image_url, '/')) }}" 
                     class="w-full h-full object-cover object-center" 
                     alt="{{ $banner->title }}"
                     onerror="this.src='https://images.unsplash.com/photo-1556906781-9a412961c28c?q=80&w=2070&auto=format&fit=crop'">

                {{-- Content --}}
                <div class="absolute inset-0 z-20 flex flex-col items-center justify-center text-center px-4">
                    <span class="inline-block py-1 px-4 rounded-full bg-white/10 backdrop-blur-md border border-white/20 text-white text-xs font-bold tracking-[0.2em] uppercase mb-6 animate-fade-in-down">
                        New Arrival
                    </span>
                    <h1 class="text-5xl md:text-7xl lg:text-8xl font-black text-white uppercase tracking-tighter mb-6 drop-shadow-2xl animate-fade-in-up delay-100">
                        {{ $banner->title }}
                    </h1>
                    
                    @if($banner->link)
                        <div class="animate-fade-in-up delay-200">
                            <a href="{{ $banner->link }}" class="group relative inline-flex items-center gap-3 px-8 py-4 bg-white text-slate-900 font-bold text-sm tracking-widest uppercase rounded-full overflow-hidden transition-all hover:bg-indigo-600 hover:text-white hover:shadow-lg hover:shadow-indigo-500/50">
                                <span class="relative z-10">Mua Ngay</span>
                                <i class="fa-solid fa-arrow-right relative z-10 group-hover:translate-x-1 transition-transform"></i>
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        @empty
            {{-- Fallback nếu chưa có banner --}}
            <div class="absolute inset-0 w-full h-full">
                <div class="absolute inset-0 bg-black/40 z-10"></div>
                <img src="https://images.unsplash.com/photo-1552346154-21d32810aba3?q=80&w=2070&auto=format&fit=crop" class="w-full h-full object-cover">
                <div class="absolute inset-0 z-20 flex flex-col items-center justify-center text-center">
                    <h1 class="text-6xl font-black text-white uppercase">SNEAKER ZONE</h1>
                </div>
            </div>
        @endforelse

        {{-- Controls --}}
        @if($banners->count() > 1)
            <button @click="prev()" class="absolute left-4 top-1/2 -translate-y-1/2 z-30 w-12 h-12 rounded-full border border-white/30 text-white flex items-center justify-center hover:bg-white hover:text-black transition-all opacity-0 group-hover:opacity-100">
                <i class="fa-solid fa-chevron-left"></i>
            </button>
            <button @click="next()" class="absolute right-4 top-1/2 -translate-y-1/2 z-30 w-12 h-12 rounded-full border border-white/30 text-white flex items-center justify-center hover:bg-white hover:text-black transition-all opacity-0 group-hover:opacity-100">
                <i class="fa-solid fa-chevron-right"></i>
            </button>
            
            {{-- Dots --}}
            <div class="absolute bottom-8 left-0 right-0 z-30 flex justify-center gap-3">
                @foreach($banners as $index => $banner)
                    <button @click="activeSlide = {{ $index }}" 
                            :class="activeSlide === {{ $index }} ? 'w-10 bg-indigo-500' : 'w-2 bg-white/50 hover:bg-white'"
                            class="h-1.5 rounded-full transition-all duration-500"></button>
                @endforeach
            </div>
        @endif
    </div>
</div>

{{-- =========================================================== --}}
{{-- 3. USP BAR (Niềm tin) --}}
{{-- =========================================================== --}}
<div class="bg-white border-b border-slate-100">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-2 md:grid-cols-4 divide-x divide-slate-100">
            <div class="py-8 px-4 text-center group hover:bg-slate-50 transition-colors">
                <i class="fa-solid fa-truck-fast text-3xl text-indigo-600 mb-3 group-hover:scale-110 transition-transform"></i>
                <h6 class="font-bold text-slate-900 uppercase text-xs tracking-wide">Free Shipping</h6>
                <p class="text-xs text-slate-500 mt-1">Đơn hàng > 2.000.000đ</p>
            </div>
            <div class="py-8 px-4 text-center group hover:bg-slate-50 transition-colors">
                <i class="fa-solid fa-check-to-slot text-3xl text-indigo-600 mb-3 group-hover:scale-110 transition-transform"></i>
                <h6 class="font-bold text-slate-900 uppercase text-xs tracking-wide">Chính hãng 100%</h6>
                <p class="text-xs text-slate-500 mt-1">Cam kết hoàn tiền gấp đôi</p>
            </div>
            <div class="py-8 px-4 text-center group hover:bg-slate-50 transition-colors">
                <i class="fa-solid fa-rotate text-3xl text-indigo-600 mb-3 group-hover:scale-110 transition-transform"></i>
                <h6 class="font-bold text-slate-900 uppercase text-xs tracking-wide">Đổi trả 30 ngày</h6>
                <p class="text-xs text-slate-500 mt-1">Thủ tục đơn giản</p>
            </div>
            <div class="py-8 px-4 text-center group hover:bg-slate-50 transition-colors">
                <i class="fa-solid fa-shield-halved text-3xl text-indigo-600 mb-3 group-hover:scale-110 transition-transform"></i>
                <h6 class="font-bold text-slate-900 uppercase text-xs tracking-wide">Bảo mật thanh toán</h6>
                <p class="text-xs text-slate-500 mt-1">An toàn tuyệt đối</p>
            </div>
        </div>
    </div>
</div>

<div class="container mx-auto px-4 py-16 space-y-24">

    {{-- =========================================================== --}}
    {{-- 4. BENTO GRID CATEGORIES (Danh mục) --}}
    {{-- =========================================================== --}}
    @if($categories->count() > 0)
    <section data-aos="fade-up">
        <div class="flex justify-between items-end mb-10">
            <div>
                <span class="text-indigo-600 font-bold text-xs uppercase tracking-widest block mb-2">Categories</span>
                <h2 class="text-3xl md:text-4xl font-black text-slate-900 uppercase tracking-tighter">Danh mục <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-purple-600">Nổi bật</span></h2>
            </div>
            <a href="{{ route('client.products.index') }}" class="hidden md:flex items-center gap-2 text-sm font-bold text-slate-500 hover:text-indigo-600 transition-colors group">
                Xem tất cả <span class="group-hover:translate-x-1 transition-transform">→</span>
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 grid-rows-2 gap-4 h-[600px]">
            {{-- Item 1 (Lớn nhất) --}}
            <a href="{{ route('client.products.index', ['category' => $categories[0]->slug ?? '']) }}" class="col-span-1 md:col-span-2 row-span-2 relative group overflow-hidden rounded-3xl">
                <img src="{{ $categories[0]->image_url ? asset('storage/'.$categories[0]->image_url) : 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?q=80&w=2070&auto=format&fit=crop' }}" 
                     class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">
                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>
                <div class="absolute bottom-8 left-8 text-white">
                    <h3 class="text-3xl font-black uppercase mb-2">{{ $categories[0]->name ?? 'Nike' }}</h3>
                    <button class="px-5 py-2 bg-white/20 backdrop-blur-md border border-white/30 rounded-full text-sm font-bold hover:bg-white hover:text-black transition-all">Khám phá</button>
                </div>
            </a>

            {{-- Item 2 --}}
            @if(isset($categories[1]))
            <a href="{{ route('client.products.index', ['category' => $categories[1]->slug]) }}" class="col-span-1 md:col-span-2 relative group overflow-hidden rounded-3xl bg-slate-100">
                <div class="absolute inset-0 flex items-center justify-between px-8">
                    <div class="relative z-10">
                        <h3 class="text-2xl font-black uppercase text-slate-900 mb-2">{{ $categories[1]->name }}</h3>
                        <span class="text-xs font-bold text-slate-500 underline decoration-2 underline-offset-4 decoration-indigo-500">Shop Now</span>
                    </div>
                    <img src="{{ $categories[1]->image_url ? asset('storage/'.$categories[1]->image_url) : 'https://pngimg.com/d/adidas_PNG17.png' }}" 
                         class="w-48 drop-shadow-2xl transition-transform duration-500 group-hover:-rotate-12 group-hover:scale-110">
                </div>
            </a>
            @endif

            {{-- Item 3 & 4 --}}
            @foreach($categories->skip(2)->take(2) as $cat)
            <a href="{{ route('client.products.index', ['category' => $cat->slug]) }}" class="col-span-1 relative group overflow-hidden rounded-3xl">
                <img src="{{ $cat->image_url ? asset('storage/'.$cat->image_url) : 'https://images.unsplash.com/photo-1608231387042-66d1773070a5?q=80&w=1974&auto=format&fit=crop' }}" 
                     class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">
                <div class="absolute inset-0 bg-black/40 group-hover:bg-black/20 transition-colors"></div>
                <div class="absolute bottom-6 left-6 text-white">
                    <h3 class="text-xl font-bold uppercase">{{ $cat->name }}</h3>
                </div>
            </a>
            @endforeach
        </div>
    </section>
    @endif

    {{-- =========================================================== --}}
    {{-- 5. TRENDING NOW (Sản phẩm nổi bật) --}}
    {{-- =========================================================== --}}
    <section>
        <div class="text-center mb-12">
            <span class="text-indigo-600 font-bold text-xs uppercase tracking-widest">Selected for you</span>
            <h2 class="text-3xl md:text-5xl font-black text-slate-900 mt-2 uppercase">Trending <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-purple-600">Now</span></h2>
        </div>

        {{-- Grid Products --}}
        @if($featuredProducts->count() > 0)
            @include('client.products._product_row', ['items' => $featuredProducts])
        @else
            <div class="text-center text-slate-400 py-10">Đang cập nhật sản phẩm...</div>
        @endif
    </section>

    {{-- =========================================================== --}}
    {{-- 6. PROMO BANNER (Parallax Effect) --}}
    {{-- =========================================================== --}}
    @if($promoBanner)
    <section class="relative h-[500px] rounded-3xl overflow-hidden flex items-center shadow-2xl">
        {{-- Background Fixed --}}
        <div class="absolute inset-0 bg-fixed bg-center bg-cover" 
             style="background-image: url('{{ asset('storage/' . $promoBanner->image_url) }}');"></div>
        <div class="absolute inset-0 bg-black/50"></div>
        
        <div class="relative container mx-auto px-4 text-center text-white z-10" data-aos="zoom-in">
            <span class="inline-block py-1 px-3 border border-white/30 rounded-full text-xs font-bold uppercase tracking-widest mb-4">Limited Edition</span>
            <h2 class="text-5xl md:text-7xl font-black uppercase mb-6 tracking-tighter drop-shadow-xl">
                {{ $promoBanner->title }}
            </h2>
            <p class="text-lg md:text-xl text-slate-200 mb-8 max-w-2xl mx-auto font-light">
                {{ $promoBanner->content ?? 'Cơ hội sở hữu những siêu phẩm giới hạn với mức giá ưu đãi chưa từng có.' }}
            </p>
            @if($promoBanner->link)
            <div class="flex justify-center gap-4">
                <a href="{{ $promoBanner->link }}" class="px-8 py-3 bg-white text-black font-bold rounded-full hover:bg-indigo-50 transition-colors shadow-lg transform hover:-translate-y-1">
                    Mua ngay
                </a>
            </div>
            @endif
        </div>
    </section>
    @endif

    {{-- =========================================================== --}}
    {{-- 7. NEW ARRIVALS (Hàng mới về) --}}
    {{-- =========================================================== --}}
    <section>
        <div class="flex flex-col md:flex-row justify-between items-end mb-10 gap-4">
            <div>
                <h2 class="text-3xl md:text-4xl font-black text-slate-900 uppercase">Hàng mới về</h2>
                <p class="text-slate-500 mt-2">Cập nhật xu hướng thời trang mới nhất mỗi ngày.</p>
            </div>
            <a href="{{ route('client.products.index', ['sort' => 'latest']) }}" class="px-6 py-2 border border-slate-200 rounded-full font-bold text-sm hover:bg-slate-900 hover:text-white transition-colors">
                Xem toàn bộ
            </a>
        </div>
        
        @if($newProducts->count() > 0)
            @include('client.products._product_row', ['items' => $newProducts])
        @endif
    </section>

    {{-- =========================================================== --}}
    {{-- 8. NEWSLETTER (Đăng ký nhận tin) --}}
    {{-- =========================================================== --}}
    <section class="relative bg-slate-900 rounded-3xl p-10 md:p-20 text-center overflow-hidden">
        {{-- Decorative Blobs --}}
        <div class="absolute top-0 left-0 w-64 h-64 bg-indigo-500/20 rounded-full blur-3xl -translate-x-1/2 -translate-y-1/2"></div>
        <div class="absolute bottom-0 right-0 w-96 h-96 bg-purple-500/20 rounded-full blur-3xl translate-x-1/3 translate-y-1/3"></div>

        <div class="relative z-10 max-w-2xl mx-auto">
            <h2 class="text-3xl md:text-5xl font-black text-white uppercase mb-4 tracking-tight">Đừng bỏ lỡ ưu đãi!</h2>
            <p class="text-slate-300 mb-8 text-lg">Đăng ký nhận tin để nhận mã giảm giá <span class="text-indigo-400 font-bold">10%</span> cho đơn hàng đầu tiên và cập nhật sản phẩm mới nhất.</p>
            
            <form class="flex flex-col sm:flex-row gap-3">
                <input type="email" placeholder="Nhập email của bạn..." class="flex-1 px-6 py-4 rounded-full bg-white/10 border border-white/20 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white/20 transition-all">
                <button type="button" class="px-8 py-4 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-full transition-all shadow-lg hover:shadow-indigo-500/50">
                    Đăng ký ngay
                </button>
            </form>
            <p class="text-xs text-slate-500 mt-4">*Chúng tôi cam kết bảo mật thông tin của bạn.</p>
        </div>
    </section>

</div>

{{-- CSS Custom Animations --}}
<style>
    .animate-fade-in-up { animation: fadeInUp 0.8s ease-out forwards; opacity: 0; transform: translateY(20px); }
    .animate-fade-in-down { animation: fadeInDown 0.8s ease-out forwards; opacity: 0; transform: translateY(-20px); }
    
    @keyframes fadeInUp { to { opacity: 1; transform: translateY(0); } }
    @keyframes fadeInDown { to { opacity: 1; transform: translateY(0); } }
    
    .delay-100 { animation-delay: 0.1s; }
    .delay-200 { animation-delay: 0.2s; }
</style>

@endsection