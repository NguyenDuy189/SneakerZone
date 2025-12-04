@extends('admin.layouts.app')

@section('title', 'Quản lý đánh giá')

@section('content')

<div class="p-6 bg-white rounded-xl shadow-sm">

    {{-- TIÊU ĐỀ --}}
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold text-secondary">Quản lý đánh giá sản phẩm</h1>
    </div>

    {{-- BỘ LỌC --}}
    <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">

        {{-- Từ khóa --}}
        <div>
            <label class="text-sm font-medium text-gray-700">Tìm kiếm</label>
            <input type="text" name="keyword" value="{{ request('keyword') }}"
                placeholder="Tên sản phẩm, khách hàng..."
                class="mt-1 w-full px-3 py-2 border rounded-lg">
        </div>

        {{-- Sao --}}
        <div>
            <label class="text-sm font-medium text-gray-700">Số sao</label>
            <select name="rating" class="mt-1 w-full px-3 py-2 border rounded-lg">
                <option value="">Tất cả</option>
                @for ($i = 1; $i <= 5; $i++)
                    <option value="{{ $i }}" {{ request('rating') == $i ? 'selected' : '' }}>
                        {{ $i }} sao
                    </option>
                @endfor
            </select>
        </div>

        {{-- Trạng thái --}}
        <div>
            <label class="text-sm font-medium text-gray-700">Trạng thái</label>
            <select name="status" class="mt-1 w-full px-3 py-2 border rounded-lg">
                <option value="">Tất cả</option>
                <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Đã duyệt</option>
                <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Chưa duyệt</option>
            </select>
        </div>

        {{-- Ngày --}}
        <div>
            <label class="text-sm font-medium text-gray-700">Ngày tạo</label>
            <input type="date" name="date" value="{{ request('date') }}"
                class="mt-1 w-full px-3 py-2 border rounded-lg">
        </div>

        {{-- Nút lọc --}}
        <div class="md:col-span-4 flex justify-end">
            <button class="px-5 py-2 bg-primary text-white rounded-lg hover:bg-indigo-700 transition">
                Lọc kết quả
            </button>
        </div>
    </form>

    {{-- BẢNG DỮ LIỆU --}}
    <div class="overflow-x-auto">
        <table class="w-full border border-gray-200 rounded-lg overflow-hidden">
            <thead class="bg-gray-100 text-gray-700 select-none">
                <tr>
                    <th class="px-3 py-3 text-sm font-semibold text-left">ID</th>
                    <th class="px-3 py-3 text-sm font-semibold text-left">Sản phẩm</th>
                    <th class="px-3 py-3 text-sm font-semibold text-left">Khách hàng</th>
                    <th class="px-3 py-3 text-sm font-semibold text-center">Số sao</th>
                    <th class="px-3 py-3 text-sm font-semibold text-left">Ngày tạo</th>
                    <th class="px-3 py-3 text-sm font-semibold text-center">Trạng thái</th>
                    <th class="px-3 py-3 text-sm font-semibold text-center w-32">Hành động</th>
                </tr>
            </thead>

            <tbody class="divide-y">
                @forelse ($reviews as $item)
                <tr class="hover:bg-gray-50">

                    {{-- ID --}}
                    <td class="px-3 py-3 text-sm">{{ $item->id }}</td>

                    {{-- Sản phẩm --}}
                    <td class="px-3 py-3 text-sm font-medium text-secondary">
                        {{ $item->product->name ?? '---' }}
                    </td>

                    {{-- User --}}
                    <td class="px-3 py-3 text-sm">
                        {{ $item->user->name ?? '---' }}
                    </td>

                    {{-- Rating --}}
                    <td class="px-3 py-3 text-center">
                        <div class="text-yellow-500">
                            @for ($i = 1; $i <= $item->rating; $i++)
                                ★
                            @endfor
                        </div>
                    </td>

                    {{-- Ngày --}}
                    <td class="px-3 py-3 text-sm">
                        {{ $item->created_at->format('d/m/Y H:i') }}
                    </td>

                    {{-- Trạng thái --}}
                    <td class="px-3 py-3 text-center">
                        @if ($item->is_approved)
                            <span class="text-green-600 font-medium">Đã duyệt</span>
                        @else
                            <span class="text-red-600 font-medium">Chưa duyệt</span>
                        @endif
                    </td>

                    {{-- Hành động --}}
                    <td class="px-3 py-3">
                        <div class="flex justify-center gap-2">

                            {{-- NÚT XEM CHI TIẾT (CHỈ THÊM NÚT NÀY) --}}
                            <a href="{{ route('admin.reviews.show', $item->id) }}"
                                class="w-8 h-8 flex items-center justify-center rounded-lg
                                    {{ $item->is_approved ? 'bg-blue-50 text-blue-600 border border-blue-200 hover:bg-blue-600 hover:text-white' 
                                                            : 'bg-red-50 text-red-600 border border-red-200 hover:bg-red-600 hover:text-white' }}
                                    transition"
                                title="{{ $item->is_approved ? 'Xem chi tiết' : 'Chưa duyệt – Xem chi tiết' }}">
                                
                                @if($item->is_approved)
                                    <i class="fa-solid fa-eye"></i>
                                @else
                                    <i class="fa-solid fa-eye-slash"></i>
                                @endif
                            </a>

                            {{-- Xóa --}}
                            <form method="POST"
                                  action="{{ route('admin.reviews.destroy', $item->id) }}"
                                  onsubmit="return confirm('Bạn có chắc chắn muốn xóa đánh giá này không?')">
                                @csrf @method('DELETE')
                                <button class="w-8 h-8 flex items-center justify-center rounded-lg
                                        bg-red-50 text-red-600 border border-red-200
                                        hover:bg-red-600 hover:text-white transition">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>

                        </div>
                    </td>

                </tr>
                @empty
                <tr>
                    <td colspan="7" class="py-6 text-center text-gray-500">
                        Không có đánh giá nào.
                    </td>
                </tr>
                @endforelse

            </tbody>
        </table>
    </div>

    {{-- PHÂN TRANG --}}
    <div class="mt-6">
        {{ $reviews->links() }}
    </div>

</div>

@endsection
