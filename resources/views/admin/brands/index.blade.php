@extends('admin.layouts.app')

@section('title', 'Quản lý thương hiệu')
@section('header', 'Thương hiệu sản phẩm')

@section('content')
<div class="container px-6 mx-auto pb-12">
    
    {{-- HEADER + ADD BUTTON --}}
    <div class="flex justify-between items-center my-6">
        <h2 class="text-2xl font-semibold text-slate-800">Danh sách thương hiệu</h2>
        <a href="{{ route('admin.brands.create') }}" 
           class="px-5 py-2.5 text-sm font-medium text-white bg-indigo-600 rounded-xl hover:bg-indigo-700 shadow-lg shadow-indigo-200 transition-all transform active:scale-95 flex items-center">
            <i class="fa-solid fa-plus mr-2"></i> Thêm mới
        </a>
    </div>

    {{-- FILTER BAR --}}
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm mb-6">
        <form action="{{ route('admin.brands.index') }}" method="GET" class="flex flex-col md:flex-row gap-4">
            
            {{-- SEARCH --}}
            <div class="relative flex-grow">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </div>
                <input type="text" name="keyword" value="{{ request('keyword') }}" 
                       class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition-all placeholder:text-slate-400"
                       placeholder="Tìm kiếm tên thương hiệu...">
            </div>

            {{-- SORT (Sắp xếp) --}}
            <div class="relative min-w-[200px]">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <i class="fa-solid fa-arrow-down-a-z"></i>
                </div>
                <select name="sort" onchange="this.form.submit()" 
                        class="w-full pl-10 pr-10 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-600 focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none appearance-none cursor-pointer">
                    <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Mới nhất</option>
                    <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Cũ nhất</option>
                    <option value="name_asc" {{ request('sort') == 'name_asc' ? 'selected' : '' }}>Tên A-Z</option>
                    <option value="name_desc" {{ request('sort') == 'name_desc' ? 'selected' : '' }}>Tên Z-A</option>
                </select>
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-slate-400">
                    <i class="fa-solid fa-chevron-down text-xs"></i>
                </div>
            </div>

            {{-- BUTTON --}}
            <button type="submit" 
                    class="px-6 py-2.5 bg-slate-800 hover:bg-slate-700 text-white text-sm font-bold rounded-xl shadow-md transition-all flex items-center justify-center gap-2">
                <i class="fa-solid fa-filter"></i> Lọc
            </button>
        </form>
    </div>

    {{-- DATA TABLE --}}
    <div class="w-full overflow-hidden rounded-2xl shadow-sm border border-slate-200 bg-white">
        <div class="w-full overflow-x-auto">
            <table class="w-full whitespace-nowrap text-left">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100 text-xs font-bold text-slate-500 uppercase tracking-wider">
                        <th class="px-6 py-4 w-16 text-center">#</th>
                        <th class="px-6 py-4 w-24">Logo</th>
                        <th class="px-6 py-4">Tên thương hiệu</th>
                        <th class="px-6 py-4">Slug (Đường dẫn)</th>
                        <th class="px-6 py-4">Mô tả ngắn</th>
                        <th class="px-6 py-4 text-center w-32">Hành động</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($brands as $brand)
                    <tr class="hover:bg-slate-50/80 transition-colors group">
                        
                        {{-- ID --}}
                        <td class="px-6 py-4 text-center text-slate-400 text-xs">
                            {{ $loop->iteration + $brands->firstItem() - 1 }}
                        </td>

                        {{-- Logo --}}
                        <td class="px-6 py-4">
                            <div class="w-12 h-12 rounded-xl border border-slate-200 bg-white p-1 flex items-center justify-center overflow-hidden">
                                @if($brand->logo_url)
                                    <img src="{{ asset('storage/' . $brand->logo_url) }}" 
                                         alt="{{ $brand->name }}" 
                                         class="w-full h-full object-contain">
                                @else
                                    <span class="text-[10px] text-slate-300 font-bold">N/A</span>
                                @endif
                            </div>
                        </td>

                        {{-- Tên --}}
                        <td class="px-6 py-4">
                            <div class="font-bold text-slate-700 text-sm group-hover:text-indigo-600 transition-colors">
                                {{ $brand->name }}
                            </div>
                            <div class="text-[10px] text-slate-400 mt-0.5">
                                Ngày tạo: {{ $brand->created_at->format('d/m/Y') }}
                            </div>
                        </td>

                        {{-- Slug --}}
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-1 rounded-md bg-slate-100 text-slate-600 text-xs font-mono border border-slate-200">
                                {{ $brand->slug }}
                            </span>
                        </td>

                        {{-- Mô tả --}}
                        <td class="px-6 py-4">
                            <span class="text-sm text-slate-500 truncate max-w-[200px] block" title="{{ $brand->description }}">
                                {{ Str::limit($brand->description, 40) ?? '—' }}
                            </span>
                        </td>

                        {{-- Hành động --}}
                        <td class="px-6 py-4 text-center">
                            <div class="flex items-center justify-center gap-2 opacity-100 group-hover:opacity-100 transition-opacity">
                                <a href="{{ route('admin.brands.edit', $brand->id) }}" 
                                   class="w-8 h-8 flex items-center justify-center rounded-lg bg-white border border-slate-200 text-indigo-600 hover:border-indigo-200 hover:bg-indigo-50 transition-all shadow-sm"
                                   title="Chỉnh sửa">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>

                                <form action="{{ route('admin.brands.destroy', $brand->id) }}" method="POST" onsubmit="return confirm('Xóa thương hiệu này sẽ ảnh hưởng đến các sản phẩm thuộc về nó. Bạn chắc chắn chứ?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" 
                                            class="w-8 h-8 flex items-center justify-center rounded-lg bg-white border border-slate-200 text-rose-600 hover:border-rose-200 hover:bg-rose-50 transition-all shadow-sm"
                                            title="Xóa">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-12 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mb-3 text-slate-300">
                                    <i class="fa-solid fa-tag text-2xl"></i>
                                </div>
                                <p class="text-slate-500 font-medium">Chưa có thương hiệu nào.</p>
                                <p class="text-slate-400 text-xs mt-1">Hãy thêm thương hiệu mới để bắt đầu.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- PAGINATION --}}
        @if($brands->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
                {{ $brands->withQueryString()->links() }}
            </div>
        @endif
    </div>
</div>
@endsection