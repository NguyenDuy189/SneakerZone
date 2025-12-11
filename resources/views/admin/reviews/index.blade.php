@extends('admin.layouts.app')

@section('title', 'Quản lý đánh giá')

@section('content')

<div class="p-6 bg-white rounded-xl shadow-sm">

    {{-- TIÊU ĐỀ --}}
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold text-secondary">Quản lý đánh giá sản phẩm</h1>
    </div>

    {{-- BỘ LỌC (FILTER BAR) --}}
    <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200 mb-6">
        <form method="GET">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                
                {{-- 1. Từ khóa (Keyword) --}}
                <div class="md:col-span-4 relative group">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fa-solid fa-magnifying-glass text-slate-400 group-focus-within:text-indigo-500 transition-colors"></i>
                    </div>
                    <input type="text" name="keyword" value="{{ request('keyword') }}" 
                        placeholder="Tìm tên sản phẩm, khách hàng..." 
                        class="pl-10 pr-4 py-2.5 w-full border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all shadow-sm text-slate-700">
                </div>

                {{-- 2. Số sao (Rating) --}}
                <div class="md:col-span-2">
                    <select name="rating" class="w-full border border-slate-200 rounded-xl py-2.5 px-3 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 cursor-pointer shadow-sm text-slate-600 font-medium">
                        <option value=""> Số sao </option>
                        @for ($i = 5; $i >= 1; $i--)
                            <option value="{{ $i }}" {{ request('rating') == $i ? 'selected' : '' }}>
                                {{ $i }} Sao
                            </option>
                        @endfor
                    </select>
                </div>

                {{-- 3. Trạng thái (Status) --}}
                <div class="md:col-span-2">
                    <select name="status" class="w-full border border-slate-200 rounded-xl py-2.5 px-3 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 cursor-pointer shadow-sm text-slate-600 font-medium">
                        <option value=""> Trạng thái </option>
                        <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Đã duyệt</option>
                        <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Chưa duyệt</option>
                    </select>
                </div>

                {{-- 4. Ngày tạo (Date) --}}
                <div class="md:col-span-2">
                    <input type="date" name="date" value="{{ request('date') }}" 
                        class="w-full border border-slate-200 rounded-xl py-2.5 px-3 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 shadow-sm text-slate-600"
                        title="Ngày tạo">
                </div>

                {{-- 5. Action Buttons --}}
                <div class="md:col-span-2 flex gap-2 justify-end">
                    {{-- Nút Lọc (Màu đen/xám đậm chuẩn style cũ) --}}
                    <button type="submit" class="flex-1 px-4 py-2.5 text-sm font-bold text-white bg-slate-900 rounded-xl hover:bg-slate-800 transition-all shadow-lg shadow-slate-900/20 flex items-center justify-center">
                        <i class="fa-solid fa-filter mr-2"></i> Lọc
                    </button>
                    
                    {{-- Nút Reset (Chỉ hiện khi đang lọc) --}}
                    @if(request()->hasAny(['keyword', 'rating', 'status', 'date']))
                        <a href="{{ url()->current() }}" class="px-4 py-2.5 text-slate-500 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 hover:text-rose-500 transition-colors flex items-center justify-center" title="Xóa bộ lọc">
                            <i class="fa-solid fa-rotate-left"></i>
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

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
                            <form action="{{ route('admin.reviews.approve', $item->id) }}" method="POST" class="inline-block">
                                @csrf
                                @method('PUT')

                                <button type="submit" 
                                    class="w-8 h-8 flex items-center justify-center rounded-lg transition-all shadow-sm
                                    {{ $item->is_approved 
                                        ? 'bg-blue-50 text-blue-600 border border-blue-200 hover:bg-blue-600 hover:text-white' 
                                        : 'bg-red-50 text-red-600 border border-red-200 hover:bg-red-600 hover:text-white' }}"
                                    title="{{ $item->is_approved ? 'Đã hiển thị (Click để ẩn)' : 'Đang ẩn (Click để hiện)' }}">
                                    
                                    @if($item->is_approved)
                                        <i class="fa-solid fa-eye"></i>
                                    @else
                                        <i class="fa-solid fa-eye-slash"></i>
                                    @endif
                                </button>
                            </form>

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
