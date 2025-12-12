@extends('admin.layouts.app')

@section('title', 'Chi tiết đánh giá')

@section('content')
<div class="container px-6 mx-auto pb-20 max-w-5xl">

    {{-- HEADER: Title & Back Button --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8 pt-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Chi tiết đánh giá</h1>
            <p class="text-slate-500 text-sm mt-1">Xem nội dung phản hồi từ khách hàng.</p>
        </div>
        <a href="{{ route('admin.reviews.index') }}" 
           class="group inline-flex items-center px-5 py-2.5 bg-white border border-slate-200 rounded-xl text-slate-600 hover:border-indigo-300 hover:text-indigo-600 font-medium transition-all shadow-sm">
            <i class="fa-solid fa-arrow-left mr-2 group-hover:-translate-x-1 transition-transform"></i> 
            Quay lại danh sách
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        {{-- LEFT COLUMN: NỘI DUNG ĐÁNH GIÁ (Chiếm 2/3) --}}
        <div class="lg:col-span-2 space-y-6">
            
            {{-- Card: Nội dung chính --}}
            <div class="bg-white p-8 rounded-2xl border border-slate-200 shadow-sm relative overflow-hidden">
                
                {{-- Quote Icon trang trí --}}
                <div class="absolute top-4 right-6 text-9xl text-slate-50 opacity-50 font-serif">”</div>

                <div class="relative z-10">
                    <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-4">Nội dung đánh giá</h3>
                    
                    {{-- Rating Stars --}}
                    <div class="flex items-center gap-1 mb-4">
                        @for($i = 1; $i <= 5; $i++)
                            @if($i <= $review->rating)
                                <i class="fa-solid fa-star text-yellow-400 text-xl"></i>
                            @else
                                <i class="fa-solid fa-star text-slate-200 text-xl"></i>
                            @endif
                        @endfor
                        <span class="ml-2 text-slate-500 text-sm font-medium">({{ $review->rating }}/5 sao)</span>
                    </div>

                    {{-- Review Text --}}
                    <div class="bg-slate-50 p-6 rounded-xl border border-slate-100 text-slate-700 leading-relaxed text-base italic">
                        "{{ $review->comment ?? 'Khách hàng không để lại lời bình.' }}"
                    </div>

                    {{-- Review Images (Nếu có - Giả lập) --}}
                    {{-- Nếu bảng reviews có cột images (json) thì loop ở đây --}}
                    {{-- 
                    @if(!empty($review->images))
                        <div class="mt-6">
                            <p class="text-xs font-bold text-slate-400 uppercase mb-2">Hình ảnh đính kèm</p>
                            <div class="flex gap-2">
                                @foreach($review->images as $img)
                                    <img src="{{ asset('storage/'.$img) }}" class="w-20 h-20 object-cover rounded-lg border border-slate-200 cursor-pointer hover:opacity-80">
                                @endforeach
                            </div>
                        </div>
                    @endif 
                    --}}

                    <div class="mt-6 flex items-center text-xs text-slate-400 gap-4">
                        <span><i class="fa-regular fa-clock mr-1"></i> Gửi lúc: {{ $review->created_at->format('H:i - d/m/Y') }}</span>
                        <span><i class="fa-solid fa-globe mr-1"></i> IP: {{ $review->ip_address ?? 'N/A' }}</span>
                    </div>
                </div>
            </div>

            {{-- Card: Phản hồi của Admin (Optional - Nếu bạn phát triển tính năng reply) --}}
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm opacity-60">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Phản hồi của cửa hàng</h3>
                    <span class="text-xs bg-slate-100 px-2 py-1 rounded text-slate-500">Coming Soon</span>
                </div>
                <textarea disabled class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-sm text-slate-500 cursor-not-allowed" rows="3" placeholder="Tính năng trả lời đánh giá đang được phát triển..."></textarea>
            </div>

        </div>

        {{-- RIGHT COLUMN: THÔNG TIN LIÊN QUAN (Chiếm 1/3) --}}
        <div class="lg:col-span-1 space-y-6">

            {{-- Card 1: Trạng thái & Hành động --}}
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
                <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-4">Hành động</h3>
                
                <div class="flex items-center justify-between mb-6 p-3 bg-slate-50 rounded-xl border border-slate-100">
                    <span class="text-sm font-medium text-slate-600">Trạng thái:</span>
                    @if($review->status === 'approved')
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700">
                            <i class="fa-solid fa-check-circle mr-1.5"></i> Đã duyệt
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-700">
                            <i class="fa-solid fa-hourglass-half mr-1.5"></i> Chờ duyệt
                        </span>
                    @endif
                </div>

                <div class="space-y-3">
                    {{-- Nút Duyệt / Ẩn --}}
                    @if($review->status !== 'approved')
                        <form action="{{ route('admin.reviews.approve', $review->id) }}" method="POST">
                            @csrf @method('PUT')
                            <button type="submit" class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold rounded-xl shadow-lg shadow-emerald-500/30 transition-all flex items-center justify-center">
                                <i class="fa-solid fa-check mr-2"></i> Duyệt hiển thị
                            </button>
                        </form>
                    @endif

                    {{-- Nút Xóa --}}
                    <form action="{{ route('admin.reviews.destroy', $review->id) }}" method="POST" onsubmit="return confirm('Bạn chắc chắn muốn xóa đánh giá này? Hành động không thể hoàn tác.');">
                        @csrf @method('DELETE')
                        <button type="submit" class="w-full py-2.5 bg-white border border-rose-200 text-rose-600 hover:bg-rose-50 text-sm font-bold rounded-xl transition-all flex items-center justify-center">
                            <i class="fa-solid fa-trash mr-2"></i> Xóa đánh giá
                        </button>
                    </form>
                </div>
            </div>

            {{-- Card 2: Thông tin Người đánh giá --}}
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
                <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-4">Khách hàng</h3>
                <div class="flex items-center gap-3 mb-4">
                    <img src="{{ $review->user->avatar_url ?? 'https://ui-avatars.com/api/?name='.urlencode($review->user->full_name) }}" 
                         class="w-12 h-12 rounded-full border border-slate-200">
                    <div class="overflow-hidden">
                        <p class="text-sm font-bold text-slate-800 truncate">{{ $review->user->full_name }}</p>
                        <p class="text-xs text-slate-500 truncate">{{ $review->user->email }}</p>
                    </div>
                </div>
                <a href="{{ route('admin.customers.edit', $review->user_id) }}" class="text-xs text-indigo-600 hover:underline flex items-center">
                    Xem hồ sơ khách hàng <i class="fa-solid fa-arrow-right ml-1"></i>
                </a>
            </div>

            {{-- Card 3: Thông tin Sản phẩm --}}
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
                <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-4">Sản phẩm</h3>
                <div class="flex gap-3 mb-3">
                    <div class="w-16 h-16 bg-slate-50 rounded-lg border border-slate-200 flex-shrink-0 overflow-hidden">
                        {{-- Giả sử product có quan hệ images hoặc thumbnail --}}
                        <img src="{{ asset('storage/'.$review->product->thumbnail) }}" 
                             onerror="this.src='{{ asset('images/default-product.png') }}'"
                             class="w-full h-full object-cover">
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-bold text-slate-800 line-clamp-2 leading-tight mb-1">
                            {{ $review->product->name }}
                        </p>
                        <p class="text-xs text-slate-500">SKU: {{ $review->product->sku_code }}</p>
                    </div>
                </div>
                <a href="{{ route('admin.products.edit', $review->product_id) }}" class="text-xs text-indigo-600 hover:underline flex items-center">
                    Xem sản phẩm <i class="fa-solid fa-arrow-right ml-1"></i>
                </a>
            </div>

        </div>
    </div>
</div>
@endsection