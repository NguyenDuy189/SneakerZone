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
                    @forelse($categories as $cat)
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
                                <a href="{{ route('admin.categories.edit', $cat->id) }}" class="text-indigo-600 hover:text-indigo-900" title="Sửa">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                <form action="{{ route('admin.categories.destroy', $cat->id) }}" method="POST" onsubmit="return confirm('Bạn chắc chắn muốn xóa danh mục này?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900" title="Xóa">
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