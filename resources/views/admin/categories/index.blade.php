@extends('admin.layouts.app')

@section('title', 'Quản lý danh mục')
@section('header', 'Danh mục sản phẩm')

@section('content')
<div class="container px-6 mx-auto">
    <!-- Header + Button -->
    <div class="flex justify-between items-center my-6">
        <h2 class="text-2xl font-semibold text-gray-700">Danh sách danh mục</h2>
        <a href="{{ route('admin.categories.create') }}" class="px-5 py-2.5 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 shadow-md transition-transform active:scale-95">
            <i class="fa-solid fa-plus mr-2"></i> Thêm mới
        </a>
    </div>

    <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
        <form action="{{ route('admin.categories.index') }}" method="GET" 
            class="flex flex-col md:flex-row gap-3">

            {{-- SEARCH --}}
            <div class="relative flex-grow">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </div>
                <input type="text" name="keyword" 
                    class="w-full pl-10 pr-4 py-2.5 border border-slate-300 rounded-lg text-sm text-slate-700 
                            placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 
                            focus:border-indigo-500 transition-all shadow-sm"
                    placeholder="Tìm theo tên danh mục..."
                    value="{{ request('keyword') }}">
            </div>

            {{-- FILTER LEVEL --}}
            <div class="relative min-w-[180px]">
                <select name="level" 
                    class="w-full pl-4 pr-10 py-2.5 border border-slate-300 rounded-lg text-sm text-slate-600 
                        bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 
                        appearance-none cursor-pointer shadow-sm">

                    {{-- Mặc định: không lọc --}}
                    <option value="" 
                        {{ request()->has('level') ? (request('level') === '' ? 'selected' : '') : 'selected' }}>
                        Tất cả cấp độ
                    </option>

                    {{-- Sinh level động --}}
                    @foreach($levels as $lv)
                        <option value="{{ $lv }}" 
                            {{ request('level') !== null && request('level') == $lv ? 'selected' : '' }}>
                            Level {{ $lv }}
                        </option>
                    @endforeach
                </select>

                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-slate-500">
                    <i class="fa-solid fa-chevron-down text-xs"></i>
                </div>
            </div>

            {{-- FILTER STATUS --}}
            <div class="relative min-w-[150px]">
                <select name="status" 
                    class="w-full pl-4 pr-10 py-2.5 border border-slate-300 rounded-lg text-sm text-slate-600 
                        bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 
                        appearance-none cursor-pointer shadow-sm">

                    <option value="" {{ request('status') === null || request('status') === '' ? 'selected' : '' }}>
                        Tất cả trạng thái
                    </option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Hiển thị</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Ẩn</option>
                </select>

                <!-- Icon mũi tên custom -->
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-slate-500">
                    <i class="fa-solid fa-chevron-down text-xs"></i>
                </div>
            </div>



            {{-- BUTTON --}}
            <button type="submit" 
                    class="px-6 py-2.5 bg-slate-800 hover:bg-slate-700 text-white text-sm font-medium 
                        rounded-lg shadow-md transition-all flex items-center gap-2 whitespace-nowrap">
                <i class="fa-solid fa-filter"></i>
                Lọc
            </button>

        </form>
    </div>

    <!-- Thông báo (đã có ở layout mẹ, nhưng nếu muốn hiển thị riêng ở đây cũng được) -->

    <div class="w-full overflow-hidden rounded-lg shadow-md border border-gray-200 bg-white">
        <div class="w-full overflow-x-auto">
            <table class="w-full whitespace-no-wrap">
                <thead>
                    <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b bg-gray-50">
                        <th class="px-4 py-3">Ảnh</th>
                        <th class="px-4 py-3">Tên danh mục</th>
                        <th class="px-4 py-3">Danh mục cha</th>
                        <th class="px-4 py-3">Cấp độ</th>
                        <th class="px-4 py-3 text-center">Trạng thái</th>
                        <th class="px-4 py-3 text-center">Hành động</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y">
                    @forelse ($categories as $cat)
                    <tr class="text-gray-700 hover:bg-gray-50 transition-colors">
                        <!-- Ảnh -->
                        <td class="px-4 py-3">
                            <div class="relative w-12 h-12 rounded border bg-gray-50">
                                <img class="object-cover w-full h-full rounded" 
                                     src="{{ $cat->image_url ? asset('storage/' . $cat->image_url) : 'https://placehold.co/50x50?text=No+Img' }}" 
                                     alt="" loading="lazy" />
                            </div>
                        </td>
                        <!-- Tên (Có thụt đầu dòng theo level) -->
                        <td class="px-4 py-3">
                            <div class="flex items-center">
                                <!-- Tạo khoảng trắng thụt đầu dòng visual -->
                                @if($cat->level > 0)
                                    <span class="text-gray-300 mr-2">{{ str_repeat('—', $cat->level) }}</span>
                                @endif
                                <div class="font-semibold text-gray-800">{{ $cat->name }}</div>
                            </div>
                        </td>
                        <!-- Cha -->
                        <td class="px-4 py-3 text-sm text-gray-500">
                            {{ $cat->parent ? $cat->parent->name : 'Gốc (Root)' }}
                        </td>
                        <!-- Level -->
                        <td class="px-4 py-3 text-sm">
                            <span class="px-2 py-1 bg-gray-100 text-gray-600 rounded text-xs">Level {{ $cat->level }}</span>
                        </td>
                        <!-- Trạng thái -->
                        <td class="px-4 py-3 text-center">
                            @if($cat->is_visible)
                                <span class="px-2 py-1 font-semibold leading-tight text-green-700 bg-green-100 rounded-full text-xs">Hiển thị</span>
                            @else
                                <span class="px-2 py-1 font-semibold leading-tight text-red-700 bg-red-100 rounded-full text-xs">Đang ẩn</span>
                            @endif
                        </td>
                        <!-- Hành động -->
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center space-x-3">
                                <a href="{{ route('admin.categories.edit', $cat->id) }}" class="w-8 h-8 flex items-center justify-center rounded-lg bg-white border border-slate-200 text-indigo-600 hover:border-indigo-200 hover:bg-indigo-50 transition-all shadow-sm" title="Sửa">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                <form action="{{ route('admin.categories.destroy', $cat->id) }}" method="POST" onsubmit="return confirm('Bạn chắc chắn muốn xóa danh mục này?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"  class="w-8 h-8 flex items-center justify-center rounded-lg bg-white border border-slate-200 text-rose-600 hover:border-rose-200 hover:bg-rose-50 transition-all shadow-sm" title="Xóa">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                            Chưa có danh mục nào.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t bg-gray-50">
            {{ $categories->links() }}
        </div>
    </div>
</div>
@endsection