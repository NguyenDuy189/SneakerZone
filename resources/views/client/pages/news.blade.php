@extends('client.layouts.app')

@section('title', 'Tin Tức & Blog - Sneaker Zone')

@section('content')
<div class="bg-white py-10">
    <div class="container mx-auto px-4">
        <h1 class="text-3xl font-bold text-slate-900 mb-8">Tin tức mới nhất</h1>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            {{-- Bài viết 1: Giày chạy bộ --}}
            <article class="flex flex-col group">
                <a href="#" class="rounded-2xl overflow-hidden mb-4 relative h-60">
                    {{-- ẢNH 1: Thay bằng ảnh giày chạy bộ Nike --}}
                    <img src="https://images.unsplash.com/photo-1542291026-7eec264c27ff?q=80&w=600&auto=format&fit=crop" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" alt="Giày chạy bộ">
                    <div class="absolute bottom-3 left-3 bg-white/90 backdrop-blur px-3 py-1 rounded-lg text-xs font-bold text-indigo-600">
                        Xu hướng
                    </div>
                </a>
                <div class="text-sm text-slate-500 mb-2">04 Tháng 01, 2026</div>
                <h3 class="text-xl font-bold text-slate-900 mb-2 group-hover:text-indigo-600 transition-colors">
                    <a href="#">Top 5 đôi giày chạy bộ đáng mua nhất năm 2026</a>
                </h3>
                <p class="text-slate-600 line-clamp-2">Tổng hợp những mẫu giày chạy bộ công nghệ mới nhất từ Nike, Adidas giúp bạn bứt phá mọi giới hạn...</p>
            </article>

            {{-- Bài viết 2: Sự kiện khai trương --}}
            <article class="flex flex-col group">
                <a href="#" class="rounded-2xl overflow-hidden mb-4 relative h-60">
                    {{-- ẢNH 2: Thay bằng ảnh bộ sưu tập giày/cửa hàng giày --}}
                    <img src="https://images.unsplash.com/photo-1556906781-9a412961c28c?q=80&w=600&auto=format&fit=crop" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" alt="Sự kiện khai trương">
                    <div class="absolute bottom-3 left-3 bg-white/90 backdrop-blur px-3 py-1 rounded-lg text-xs font-bold text-rose-500">
                        Sự kiện
                    </div>
                </a>
                <div class="text-sm text-slate-500 mb-2">01 Tháng 01, 2026</div>
                <h3 class="text-xl font-bold text-slate-900 mb-2 group-hover:text-indigo-600 transition-colors">
                    <a href="#">Khai trương chi nhánh Sneaker Zone Đà Nẵng</a>
                </h3>
                <p class="text-slate-600 line-clamp-2">Cùng nhìn lại những hình ảnh sôi động trong ngày khai trương chi nhánh thứ 3 của chúng tôi...</p>
            </article>

            {{-- Bài viết 3: Kiến thức vệ sinh giày --}}
            <article class="flex flex-col group">
                <a href="#" class="rounded-2xl overflow-hidden mb-4 relative h-60">
                    {{-- ẢNH 3: Thay bằng ảnh giày lifestyle/da lộn --}}
                    <img src="https://images.unsplash.com/photo-1608231387042-66d1773070a5?q=80&w=600&auto=format&fit=crop" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" alt="Vệ sinh giày">
                    <div class="absolute bottom-3 left-3 bg-white/90 backdrop-blur px-3 py-1 rounded-lg text-xs font-bold text-indigo-600">
                        Kiến thức
                    </div>
                </a>
                <div class="text-sm text-slate-500 mb-2">28 Tháng 12, 2025</div>
                <h3 class="text-xl font-bold text-slate-900 mb-2 group-hover:text-indigo-600 transition-colors">
                    <a href="#">Cách vệ sinh giày da lộn đúng cách tại nhà</a>
                </h3>
                <p class="text-slate-600 line-clamp-2">Giày da lộn rất đẹp nhưng khó chiều? Đừng lo, hãy áp dụng ngay 5 mẹo nhỏ sau đây...</p>
            </article>
        </div>
    </div>
</div>
@endsection