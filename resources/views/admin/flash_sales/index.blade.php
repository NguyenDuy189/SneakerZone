@extends('admin.layouts.app')
@section('title', 'Quản lý Flash Sale')

@section('content')
<div class="container px-6 mx-auto grid pb-10">
    <h2 class="my-6 text-2xl font-semibold text-gray-700">Chiến dịch Flash Sale</h2>

    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.flash_sales.index') }}" 
               class="px-3 py-1 text-sm rounded-md border {{ !request('status') ? 'bg-indigo-600 text-white' : 'bg-white text-gray-600' }}">
                Tất cả
            </a>
            <a href="{{ route('admin.flash_sales.index', ['status' => 'active']) }}" 
               class="px-3 py-1 text-sm rounded-md border {{ request('status') == 'active' ? 'bg-emerald-600 text-white' : 'bg-white text-gray-600' }}">
                Đang chạy
            </a>
            <a href="{{ route('admin.flash_sales.index', ['status' => 'upcoming']) }}" 
               class="px-3 py-1 text-sm rounded-md border {{ request('status') == 'upcoming' ? 'bg-amber-500 text-white' : 'bg-white text-gray-600' }}">
                Sắp tới
            </a>
            <a href="{{ route('admin.flash_sales.index', ['status' => 'expired']) }}" 
               class="px-3 py-1 text-sm rounded-md border {{ request('status') == 'expired' ? 'bg-gray-500 text-white' : 'bg-white text-gray-600' }}">
                Đã kết thúc
            </a>
        </div>

        <div class="flex gap-2 w-full md:w-auto">
            <form action="{{ route('admin.flash_sales.index') }}" method="GET" class="relative w-full md:w-64">
                <input type="text" name="keyword" value="{{ request('keyword') }}" placeholder="Tìm tên chiến dịch..." 
                       class="w-full pl-4 pr-10 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-400 text-sm">
                <button type="submit" class="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-indigo-600">
                    <i class="fa-solid fa-search"></i>
                </button>
            </form>
            <a href="{{ route('admin.flash_sales.create') }}" class="flex items-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 whitespace-nowrap">
                <i class="fa-solid fa-plus mr-2"></i> Tạo mới
            </a>
        </div>
    </div>

    <div class="w-full overflow-hidden rounded-lg shadow-xs border bg-white">
        <div class="w-full overflow-x-auto">
            <table class="w-full whitespace-no-wrap">
                <thead>
                    <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b bg-gray-50">
                        <th class="px-4 py-3">Tên chiến dịch</th>
                        <th class="px-4 py-3">Thời gian</th>
                        <th class="px-4 py-3 text-center">Sản phẩm</th>
                        <th class="px-4 py-3 text-center">Trạng thái</th>
                        <th class="px-4 py-3 text-center">Hành động</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y">
                    @forelse($flashSales as $flash)
                    <tr class="text-gray-700 hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3">
                            <div class="font-semibold text-gray-800">{{ $flash->name }}</div>
                            @if(!$flash->is_active)
                                <span class="text-xs text-red-500 italic">(Đang tắt thủ công)</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <div class="flex flex-col">
                                <span class="text-emerald-700 font-medium">
                                    <i class="fa-regular fa-calendar-check mr-1"></i> 
                                    {{ \Carbon\Carbon::parse($flash->start_time)->format('H:i d/m/Y') }}
                                </span>
                                <span class="text-gray-400 text-xs py-0.5 ml-5">đến</span>
                                <span class="text-rose-700 font-medium">
                                    <i class="fa-regular fa-calendar-xmark mr-1"></i> 
                                    {{ \Carbon\Carbon::parse($flash->end_time)->format('H:i d/m/Y') }}
                                </span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <a href="{{ route('admin.flash_sales.items', $flash->id) }}" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 hover:bg-indigo-200">
                                {{ $flash->items_count ?? 0 }} sản phẩm
                            </a>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @php
                                $now = now();
                                if (!$flash->is_active) {
                                    $class = 'bg-gray-100 text-gray-600 border border-gray-200'; $label = 'Đã tắt';
                                } elseif ($flash->start_time > $now) {
                                    $class = 'bg-amber-100 text-amber-700 border border-amber-200'; $label = 'Sắp diễn ra';
                                } elseif ($flash->end_time < $now) {
                                    $class = 'bg-red-50 text-red-600 border border-red-100'; $label = 'Đã kết thúc';
                                } else {
                                    $class = 'bg-emerald-100 text-emerald-700 border border-emerald-200'; $label = 'Đang chạy';
                                }
                            @endphp
                            <span class="px-3 py-1 text-xs font-semibold rounded-full {{ $class }}">
                                {{ $label }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('admin.flash_sales.statistics', $flash->id) }}" class="p-2 text-sm text-purple-600 bg-purple-50 rounded-lg hover:bg-purple-100 border border-purple-200" title="Thống kê">
                                    <i class="fa-solid fa-chart-pie"></i>
                                </a>

                                <a href="{{ route('admin.flash_sales.items', $flash->id) }}" class="p-2 text-sm text-teal-600 bg-teal-50 rounded-lg hover:bg-teal-100 border border-teal-200" title="Cấu hình sản phẩm">
                                    <i class="fa-solid fa-list-check"></i>
                                </a>
                                
                                <a href="{{ route('admin.flash_sales.edit', $flash->id) }}" class="p-2 text-sm text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 border border-blue-200" title="Chỉnh sửa">
                                    <i class="fa-solid fa-pen"></i>
                                </a>
                                
                                <form action="{{ route('admin.flash_sales.destroy', $flash->id) }}" method="POST" onsubmit="return confirm('CẢNH BÁO: Xóa chiến dịch này sẽ xóa toàn bộ cài đặt giảm giá của các sản phẩm bên trong.\n\nBạn có chắc chắn muốn xóa không?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-2 text-sm text-red-600 bg-red-50 rounded-lg hover:bg-red-100 border border-red-200" title="Xóa">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                            <div class="flex flex-col items-center justify-center">
                                <i class="fa-solid fa-bolt text-4xl text-gray-300 mb-3"></i>
                                <p>Chưa có chiến dịch Flash Sale nào.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t bg-gray-50">
            {{ $flashSales->links() }}
        </div>
    </div>
</div>
@endsection