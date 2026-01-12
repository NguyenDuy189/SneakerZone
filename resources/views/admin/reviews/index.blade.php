@extends('admin.layouts.app')

@section('title', 'Quản lý Đánh giá')

@section('content')
<div class="container px-6 mx-auto pb-20 max-w-7xl">

    {{-- HEADER --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8 pt-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Quản lý Đánh giá</h1>
            <p class="text-slate-500 text-sm mt-1">Kiểm duyệt và quản lý phản hồi từ khách hàng.</p>
        </div>
        {{-- (Có thể thêm nút Export ở đây nếu cần) --}}
    </div>

    {{-- FILTER BAR --}}
    <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200 mb-8">
        <form action="{{ route('admin.reviews.index') }}" method="GET">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                
                {{-- 1. Tìm kiếm --}}
                <div class="md:col-span-4 relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </div>
                    <input type="text" name="keyword" value="{{ request('keyword') }}" 
                           placeholder="Tìm theo tên khách, tên sản phẩm..." 
                           class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition-all">
                </div>

                {{-- 2. Số sao --}}
                <div class="md:col-span-3 relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-solid fa-star"></i>
                    </div>
                    <select name="rating" class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none appearance-none cursor-pointer">
                        <option value="">Tất cả số sao</option>
                        @for ($i = 5; $i >= 1; $i--)
                            <option value="{{ $i }}" {{ request('rating') == $i ? 'selected' : '' }}>{{ $i }} Sao</option>
                        @endfor
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-solid fa-chevron-down text-xs"></i>
                    </div>
                </div>

                {{-- 3. Trạng thái --}}
                {{-- Thêm class 'md:col-span-3' để ô select rộng ra --}}
                <div class="md:col-span-3 relative">
                    
                    {{-- Icon bên trái --}}
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-solid fa-toggle-on"></i>
                    </div>

                    {{-- Select Box --}}
                    <select name="status" 
                            class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none appearance-none cursor-pointer transition-all duration-200">
                        
                        <option value="all"> Tất cả trạng thái </option>
                        
                        {{-- Logic Value: 1 (Đã duyệt) --}}
                        <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>
                            Đã duyệt
                        </option>
                        
                        {{-- Logic Value: 0 (Chờ duyệt) --}}
                        <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>
                            Chờ duyệt
                        </option>
                    </select>

                    {{-- Icon mũi tên bên phải --}}
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-solid fa-chevron-down text-xs"></i>
                    </div>
                </div>

                {{-- 4. Submit --}}
                <div class="md:col-span-2 flex gap-2">
                    <button type="submit" class="flex-1 w-full h-full min-h-[42px] bg-slate-800 hover:bg-slate-700 text-white text-sm font-bold rounded-xl shadow transition-colors flex items-center justify-center gap-2">
                        <i class="fa-solid fa-filter"></i> Lọc
                    </button>
                    @if(request()->hasAny(['keyword', 'rating', 'status']))
                        <a href="{{ route('admin.reviews.index') }}" class="w-[42px] h-[42px] flex items-center justify-center bg-white border border-slate-200 rounded-xl text-slate-500 hover:text-rose-500 hover:bg-rose-50 transition-colors" title="Xóa bộ lọc">
                            <i class="fa-solid fa-rotate-left"></i>
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    {{-- DATA TABLE --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="w-full overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100 text-xs font-bold text-slate-500 uppercase tracking-wider">
                        <th class="px-6 py-4 w-16 text-center">#</th>
                        <th class="px-6 py-4 w-[25%]">Sản phẩm</th>
                        <th class="px-6 py-4 w-[20%]">Khách hàng</th>
                        <th class="px-6 py-4 text-center w-[12%]">Đánh giá</th>
                        <th class="px-6 py-4 w-[15%]">Ngày gửi</th>
                        <th class="px-6 py-4 text-center w-[12%]">Trạng thái</th>
                        <th class="px-6 py-4 text-center w-[100px]">Hành động</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($reviews as $review)
                    <tr class="hover:bg-slate-50/80 transition-colors group">
                        
                        {{-- ID --}}
                        <td class="px-6 py-4 text-center text-slate-400 text-xs">
                            {{ $review->id }}
                        </td>

                        {{-- Product --}}
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-slate-100 border border-slate-200 overflow-hidden flex-shrink-0">
                                    {{-- Giả sử Product có quan hệ thumbnail --}}
                                    <img 
                                        src="{{ $review->product && $review->product->thumbnail ? asset('storage/' . $review->product->thumbnail) : 'https://placehold.co/50x50?text=No+Img' }}" 
                                        alt="Product Image"
                                        class="w-10 h-10 object-cover rounded"
                                        
                                        {{-- 👇 QUAN TRỌNG: Thêm đoạn này để chặn reload --}}
                                        onerror="this.onerror=null; this.src='https://placehold.co/50x50?text=No+Img';"
                                    >
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-bold text-slate-800 truncate" title="{{ $review->product->name }}">
                                        {{ $review->product->name }}
                                    </p>
                                    <p class="text-xs text-slate-500">SKU: {{ $review->product->sku_code }}</p>
                                </div>
                            </div>
                        </td>

                        {{-- Customer --}}
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-xs font-bold uppercase">
                                    {{ substr($review->user->full_name ?? 'U', 0, 1) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-slate-700 truncate">{{ $review->user->full_name ?? 'Unknown' }}</p>
                                    <p class="text-xs text-slate-400 truncate">{{ $review->user->email ?? '' }}</p>
                                </div>
                            </div>
                        </td>

                        {{-- Rating --}}
                        <td class="px-6 py-4 text-center">
                            <div class="flex items-center justify-center gap-0.5 text-yellow-400 text-xs">
                                @for($i = 1; $i <= 5; $i++)
                                    @if($i <= $review->rating)
                                        <i class="fa-solid fa-star"></i>
                                    @else
                                        <i class="fa-solid fa-star text-slate-200"></i>
                                    @endif
                                @endfor
                            </div>
                            <span class="text-[10px] text-slate-400 mt-1 block">({{ $review->rating }}.0)</span>
                        </td>

                        {{-- Date --}}
                        <td class="px-6 py-4 text-sm text-slate-600">
                            {{ $review->created_at->format('d/m/Y') }} <br>
                            <span class="text-xs text-slate-400">{{ $review->created_at->format('H:i') }}</span>
                        </td>

                        {{-- Status --}}
                        <td class="px-6 py-4 text-center">
                            @if($review->is_approved)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-600 border border-blue-200">
                                    <i class="fa-solid fa-eye mr-1 text-[10px]"></i> Đã duyệt
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-red-50 text-red-600 border border-red-200">
                                    <i class="fa-solid fa-eye-slash mr-1 text-[10px]"></i> Chưa duyệt
                                </span>
                            @endif
                        </td>

                        {{-- Actions --}}
                        <td class="px-6 py-4 text-center">
                            <div class="flex items-center justify-center gap-2">
                                
                                {{-- 1. NÚT DUYỆT NHANH (TOGGLE TRẠNG THÁI) --}}
                                {{-- Logic: Click form này sẽ gọi route approve để đổi trạng thái --}}
                                <form action="{{ route('admin.reviews.approve', $review->id) }}" method="POST">
                                    @csrf 
                                    @method('PUT')

                                    <button type="submit" 
                                            class="w-9 h-9 flex items-center justify-center rounded-lg border transition-all shadow-sm
                                            {{ $review->is_approved 
                                                ? 'bg-blue-50 text-blue-600 border border-blue-200 hover:bg-blue-600 hover:text-white' 
                                                : 'bg-red-50 text-red-600 border border-red-200 hover:bg-red-600 hover:text-white' }}"
                                            title="{{ $review->is_approved ? 'Đã duyệt (Click để ẩn)' : 'Chưa duyệt (Click để duyệt)' }}">
                                        
                                        @if($review->is_approved)
                                            <i class="fa-solid fa-eye"></i>       {{-- Icon mắt mở --}}
                                        @else
                                            <i class="fa-solid fa-eye-slash"></i> {{-- Icon mắt nhắm --}}
                                        @endif
                                    </button>
                                </form>


                                {{-- 2. NÚT XEM CHI TIẾT (MỚI) --}}
                                <a href="{{ route('admin.reviews.show', $review->id) }}" 
                                class="w-9 h-9 flex items-center justify-center rounded-lg bg-white border border-indigo-200 text-indigo-600 hover:bg-indigo-600 hover:text-white transition-all shadow-sm"
                                title="Xem chi tiết & Phản hồi">
                                    <i class="fa-solid fa-circle-info"></i>
                                </a>

                                {{-- 3. NÚT XÓA --}}
                                <form action="{{ route('admin.reviews.destroy', $review->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa đánh giá này?');">
                                    @csrf 
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="w-9 h-9 flex items-center justify-center rounded-lg bg-white border border-rose-200 text-rose-500 hover:bg-rose-600 hover:text-white transition-all shadow-sm" 
                                            title="Xóa đánh giá">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>

                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center">
                            <div class="flex flex-col items-center">
                                <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mb-3 text-slate-300">
                                    <i class="fa-regular fa-comments text-3xl"></i>
                                </div>
                                <p class="text-slate-500 font-medium">Chưa có đánh giá nào.</p>
                                <p class="text-slate-400 text-xs mt-1">Đánh giá từ khách hàng sẽ xuất hiện tại đây.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- PAGINATION --}}
        @if($reviews->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
                {{ $reviews->withQueryString()->links() }}
            </div>
        @endif
    </div>
</div>
@endsection