@extends('admin.layouts.app')

@section('title', 'Quản lý Banner')

@section('content')
<div class="container px-6 mx-auto pb-20 max-w-7xl">

    {{-- HEADER --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8 pt-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Quản lý Banner</h1>
            <p class="text-slate-500 text-sm mt-1">Quản lý hình ảnh quảng cáo và banner trên website.</p>
        </div>
        <a href="{{ route('admin.banners.create') }}" 
           class="group inline-flex items-center px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl shadow-lg shadow-indigo-500/30 transition-all transform hover:scale-[1.02] active:scale-[0.98]">
            <i class="fa-solid fa-plus mr-2 group-hover:rotate-90 transition-transform"></i> 
            Thêm Banner Mới
        </a>
    </div>

    {{-- ALERT --}}
    @if(session('success'))
        <div class="p-4 mb-6 rounded-xl bg-emerald-50 border border-emerald-100 flex items-center gap-3 shadow-sm animate-fade-in-down">
            <div class="flex-shrink-0 w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600">
                <i class="fa-solid fa-check"></i>
            </div>
            <span class="text-emerald-800 font-medium text-sm">{{ session('success') }}</span>
        </div>
    @endif

    {{-- FILTER BAR --}}
    <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-200 mb-8">
        <form action="{{ route('admin.banners.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-12 gap-4">
            {{-- Search --}}
            <div class="md:col-span-5 relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </div>
                <input type="text" name="keyword" value="{{ request('keyword') }}" placeholder="Tìm theo tiêu đề banner..." 
                       class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition-all">
            </div>

            {{-- Filter Position --}}
            <div class="md:col-span-3 relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <i class="fa-solid fa-map-pin"></i>
                </div>
                <select name="position" class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none appearance-none cursor-pointer">
                    <option value="all">Tất cả vị trí</option>
                    <option value="home_slider" {{ request('position') == 'home_slider' ? 'selected' : '' }}>Home Slider</option>
                    <option value="home_mid" {{ request('position') == 'home_mid' ? 'selected' : '' }}>Giữa trang chủ</option>
                    <option value="sidebar" {{ request('position') == 'sidebar' ? 'selected' : '' }}>Sidebar</option>
                    <option value="footer" {{ request('position') == 'footer' ? 'selected' : '' }}>Footer</option>
                </select>
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-slate-400">
                    <i class="fa-solid fa-chevron-down text-xs"></i>
                </div>
            </div>

            {{-- Filter Status --}}
            <div class="md:col-span-3 relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <i class="fa-solid fa-toggle-on"></i>
                </div>
                <select name="status" class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none appearance-none cursor-pointer">
                    <option value="all">Tất cả trạng thái</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Đang hiển thị</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Đang ẩn</option>
                </select>
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-slate-400">
                    <i class="fa-solid fa-chevron-down text-xs"></i>
                </div>
            </div>

            {{-- Submit --}}
            <div class="md:col-span-1">
                <button type="submit" class="w-full h-full min-h-[42px] bg-slate-800 hover:bg-slate-700 text-white rounded-xl shadow transition-colors flex items-center justify-center">
                        <i class="fa-solid fa-filter mr-2"></i> Lọc
                </button>
            </div>
        </form>
    </div>

    {{-- BANNER LIST (GRID VIEW) --}}
    @if($banners->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($banners as $banner)
                <div class="group bg-white rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition-all overflow-hidden flex flex-col h-full">
                    
                    {{-- Image Preview --}}
                    <div class="relative w-full h-48 bg-slate-100 overflow-hidden">
                        <img src="{{ asset('storage/' . $banner->image_url) }}" 
                             alt="{{ $banner->title }}" 
                             class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                        
                        {{-- Status Badge --}}
                        <div class="absolute top-3 right-3">
                            @if($banner->is_active)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-white/90 backdrop-blur text-emerald-600 shadow-sm border border-emerald-100">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span> Active
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-white/90 backdrop-blur text-slate-500 shadow-sm border border-slate-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400 mr-1.5"></span> Inactive
                                </span>
                            @endif
                        </div>

                        {{-- Position Badge --}}
                        <div class="absolute bottom-3 left-3">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-slate-900/80 backdrop-blur text-white shadow-sm">
                                <i class="fa-solid fa-map-pin mr-1.5 text-[10px] opacity-70"></i> {{ $banner->position }}
                            </span>
                        </div>
                    </div>

                    {{-- Content --}}
                    <div class="p-5 flex-1 flex flex-col">
                        <h3 class="text-base font-bold text-slate-800 line-clamp-1 mb-1" title="{{ $banner->title }}">
                            {{ $banner->title }}
                        </h3>
                        <a href="{{ $banner->link }}" target="_blank" class="text-xs text-indigo-500 hover:underline truncate mb-4 block">
                            <i class="fa-solid fa-link mr-1"></i> {{ $banner->link ?? '#' }}
                        </a>

                        <div class="mt-auto pt-4 border-t border-slate-100 flex items-center justify-between">
                            <div class="text-xs text-slate-400 font-medium">
                                Priority: <span class="text-slate-700">{{ $banner->priority }}</span>
                            </div>
                            
                            <div class="flex items-center gap-2">
                                <a href="{{ route('admin.banners.edit', $banner->id) }}" 
                                   class="w-8 h-8 flex items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white transition-colors" title="Chỉnh sửa">
                                    <i class="fa-solid fa-pen"></i>
                                </a>
                                
                                <form action="{{ route('admin.banners.destroy', $banner->id) }}" method="POST" onsubmit="return confirm('Bạn chắc chắn muốn xóa banner này?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="w-8 h-8 flex items-center justify-center rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white transition-colors" title="Xóa">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="mt-8">
            {{ $banners->links() }}
        </div>
    @else
        <div class="text-center py-20 bg-white rounded-2xl border border-dashed border-slate-300">
            <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-400">
                <i class="fa-regular fa-image text-3xl"></i>
            </div>
            <h3 class="text-lg font-bold text-slate-700">Chưa có banner nào</h3>
            <p class="text-slate-500 text-sm mt-1">Hãy thêm banner mới để trang trí website của bạn.</p>
            <a href="{{ route('admin.banners.create') }}" class="inline-block mt-4 px-5 py-2 bg-indigo-600 text-white text-sm font-bold rounded-lg hover:bg-indigo-700 transition-colors">
                Thêm ngay
            </a>
        </div>
    @endif

</div>
@endsection