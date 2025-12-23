@extends('client.layouts.app')

@section('title', 'Tin tức - Sneaker Zone')

@section('content')
    <div class="container mx-auto px-4 py-16">
        <div class="flex justify-between items-end mb-12">
            <div>
                <h1 class="text-3xl md:text-4xl font-display font-black text-slate-900 mb-2">TIN TỨC & SỰ KIỆN</h1>
                <p class="text-slate-500">Cập nhật xu hướng sneaker mới nhất thế giới.</p>
            </div>
            <div class="hidden md:block">
                {{-- Categories --}}
                <div class="flex gap-2">
                    <button class="px-4 py-2 rounded-full bg-slate-900 text-white text-sm font-medium">Mới nhất</button>
                    <button class="px-4 py-2 rounded-full bg-slate-100 text-slate-600 text-sm font-medium hover:bg-slate-200">Review giày</button>
                    <button class="px-4 py-2 rounded-full bg-slate-100 text-slate-600 text-sm font-medium hover:bg-slate-200">Khuyến mãi</button>
                </div>
            </div>
        </div>

        {{-- Blog Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            {{-- Bài viết 1 --}}
            <article class="group cursor-pointer">
                <div class="overflow-hidden rounded-2xl mb-4 h-64 relative">
                    <img src="https://images.unsplash.com/photo-1600185365483-26d7a4cc7519?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" 
                         alt="Blog 1" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                    <div class="absolute top-4 left-4 bg-indigo-600 text-white text-xs font-bold px-3 py-1 rounded-full uppercase">Review</div>
                </div>
                <div class="text-slate-500 text-sm mb-2 flex items-center gap-4">
                    <span><i class="fa-regular fa-calendar mr-1"></i> 22/12/2024</span>
                    <span><i class="fa-regular fa-user mr-1"></i> Admin</span>
                </div>
                <h3 class="text-xl font-bold text-slate-900 mb-2 group-hover:text-indigo-600 transition-colors line-clamp-2">
                    Top 5 đôi giày Nike Air Jordan đáng mua nhất năm 2024
                </h3>
                <p class="text-slate-500 line-clamp-3 mb-4">
                    Jordan Brand luôn biết cách làm hài lòng người hâm mộ với những phối màu cực chất. Cùng điểm qua những cái tên hot nhất...
                </p>
                <a href="#" class="text-indigo-600 font-bold text-sm hover:underline">Đọc tiếp <i class="fa-solid fa-arrow-right ml-1"></i></a>
            </article>

            {{-- Bài viết 2 --}}
            <article class="group cursor-pointer">
                <div class="overflow-hidden rounded-2xl mb-4 h-64 relative">
                    <img src="https://images.unsplash.com/photo-1607522370275-f14bc3a30f88?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" 
                         alt="Blog 2" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                    <div class="absolute top-4 left-4 bg-rose-500 text-white text-xs font-bold px-3 py-1 rounded-full uppercase">Khuyến mãi</div>
                </div>
                <div class="text-slate-500 text-sm mb-2 flex items-center gap-4">
                    <span><i class="fa-regular fa-calendar mr-1"></i> 20/12/2024</span>
                    <span><i class="fa-regular fa-user mr-1"></i> Admin</span>
                </div>
                <h3 class="text-xl font-bold text-slate-900 mb-2 group-hover:text-indigo-600 transition-colors line-clamp-2">
                    Siêu sale cuối năm - Giảm giá lên đến 50% toàn bộ cửa hàng
                </h3>
                <p class="text-slate-500 line-clamp-3 mb-4">
                    Cơ hội săn giày chính hãng giá rẻ nhất trong năm. Đừng bỏ lỡ chương trình Black Friday tại Sneaker Zone...
                </p>
                <a href="#" class="text-indigo-600 font-bold text-sm hover:underline">Đọc tiếp <i class="fa-solid fa-arrow-right ml-1"></i></a>
            </article>

            {{-- Bài viết 3 --}}
            <article class="group cursor-pointer">
                <div class="overflow-hidden rounded-2xl mb-4 h-64 relative">
                    <img src="https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" 
                         alt="Blog 3" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                    <div class="absolute top-4 left-4 bg-sky-500 text-white text-xs font-bold px-3 py-1 rounded-full uppercase">Kiến thức</div>
                </div>
                <div class="text-slate-500 text-sm mb-2 flex items-center gap-4">
                    <span><i class="fa-regular fa-calendar mr-1"></i> 18/12/2024</span>
                    <span><i class="fa-regular fa-user mr-1"></i> Admin</span>
                </div>
                <h3 class="text-xl font-bold text-slate-900 mb-2 group-hover:text-indigo-600 transition-colors line-clamp-2">
                    Cách vệ sinh giày Sneaker da lộn đúng cách tại nhà
                </h3>
                <p class="text-slate-500 line-clamp-3 mb-4">
                    Da lộn là chất liệu đẹp nhưng khó chiều. Hướng dẫn chi tiết cách vệ sinh để đôi giày của bạn luôn như mới...
                </p>
                <a href="#" class="text-indigo-600 font-bold text-sm hover:underline">Đọc tiếp <i class="fa-solid fa-arrow-right ml-1"></i></a>
            </article>
        </div>
    </div>
@endsection