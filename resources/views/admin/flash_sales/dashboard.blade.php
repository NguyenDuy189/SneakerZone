@extends('admin.layouts.app')
@section('title', 'Thống kê Flash Sale')

@section('content')
<div class="container px-6 mx-auto grid pb-10">
    
    <div class="flex flex-col md:flex-row justify-between items-center my-6 gap-4">
        <div>
            <a href="{{ route('admin.flash_sales.index') }}" class="group w-12 h-12 flex items-center justify-center bg-white border border-gray-200 rounded-xl shadow-sm hover:shadow-md hover:bg-gray-50 hover:border-indigo-300 transition-all duration-200">
                <i class="fa-solid fa-arrow-left text-xl text-gray-500 group-hover:text-indigo-600"></i>
            </a>
            <span class="text-gray-300">|</span>
                    <span class="text-sm text-gray-500 uppercase tracking-wide">Quản lý khuyến mãi</span>
            <h2 class="text-2xl font-bold text-gray-800">
                Báo cáo hiệu quả: <span class="text-indigo-600">{{ $flashSale->name }}</span>
            </h2>
            <div class="text-sm text-gray-500 mt-1">
                @if($flashSale->is_running)
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        <span class="w-2 h-2 mr-1 bg-green-500 rounded-full"></span> Đang diễn ra
                    </span>
                @elseif($flashSale->end_time < now())
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                        Đã kết thúc
                    </span>
                @else
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                        Sắp diễn ra
                    </span>
                @endif
                <span class="mx-2">|</span>
                {{ $flashSale->start_time->format('d/m/Y H:i') }} - {{ $flashSale->end_time->format('d/m/Y H:i') }}
            </div>
        </div>
        <div>
            <a href="{{ route('admin.flash_sales.items', $flashSale->id) }}" class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 text-sm font-medium">
                <i class="fa-solid fa-gear mr-2"></i> Cấu hình sản phẩm
            </a>
        </div>
    </div>

    <div class="grid gap-6 mb-8 md:grid-cols-2 xl:grid-cols-4">
        <div class="flex items-center p-4 bg-white rounded-lg shadow-sm border border-gray-100">
            <div class="p-3 mr-4 text-green-500 bg-green-100 rounded-full">
                <i class="fa-solid fa-money-bill-wave text-xl"></i>
            </div>
            <div>
                <p class="mb-2 text-sm font-medium text-gray-600">Tổng doanh thu</p>
                <p class="text-lg font-bold text-gray-700">{{ number_format($totalRevenue) }} ₫</p>
            </div>
        </div>
        
        <div class="flex items-center p-4 bg-white rounded-lg shadow-sm border border-gray-100">
            <div class="p-3 mr-4 text-blue-500 bg-blue-100 rounded-full">
                <i class="fa-solid fa-cart-shopping text-xl"></i>
            </div>
            <div>
                <p class="mb-2 text-sm font-medium text-gray-600">Đã bán / Tổng cam kết</p>
                <p class="text-lg font-bold text-gray-700">
                    {{ $totalSold }} <span class="text-sm text-gray-400 font-normal">/ {{ $totalStockAllocated }}</span>
                </p>
            </div>
        </div>

        <div class="flex items-center p-4 bg-white rounded-lg shadow-sm border border-gray-100">
            <div class="p-3 mr-4 text-orange-500 bg-orange-100 rounded-full">
                <i class="fa-solid fa-chart-pie text-xl"></i>
            </div>
            <div>
                <p class="mb-2 text-sm font-medium text-gray-600">Tỉ lệ bán hết</p>
                <p class="text-lg font-bold text-gray-700">{{ $sellThroughRate }}%</p>
            </div>
        </div>

        <div class="flex items-center p-4 bg-white rounded-lg shadow-sm border border-gray-100">
            <div class="p-3 mr-4 text-purple-500 bg-purple-100 rounded-full">
                <i class="fa-solid fa-tags text-xl"></i>
            </div>
            <div>
                <p class="mb-2 text-sm font-medium text-gray-600">Sản phẩm tham gia</p>
                <p class="text-lg font-bold text-gray-700">{{ $totalItems }} SKU</p>
            </div>
        </div>
    </div>

    <div class="w-full bg-white rounded-lg shadow-sm p-6 mb-8 border border-gray-100">
        <h4 class="text-sm font-semibold text-gray-600 mb-3">Tiến độ tiêu thụ kho hàng Flash Sale</h4>
        <div class="w-full bg-gray-200 rounded-full h-4">
            <div class="bg-indigo-600 h-4 rounded-full transition-all duration-1000 ease-out" style="width: {{ $sellThroughRate }}%"></div>
        </div>
        <div class="flex justify-between mt-2 text-xs text-gray-500">
            <span>0%</span>
            <span>50%</span>
            <span>100%</span>
        </div>
    </div>

    <div class="grid gap-6 md:grid-cols-2">
        
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                <h3 class="font-semibold text-gray-700">🏆 Top 5 Bán Chạy Nhất</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full whitespace-no-wrap">
                    <tbody class="divide-y divide-gray-100">
                        @forelse($topProducts as $index => $item)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-center w-10">
                                @if($index == 0) <span class="text-xl">🥇</span>
                                @elseif($index == 1) <span class="text-xl">🥈</span>
                                @elseif($index == 2) <span class="text-xl">🥉</span>
                                @else <span class="font-bold text-gray-400">#{{ $index + 1 }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-sm font-medium text-gray-800">{{ $item->productVariant->product->name ?? 'Unknown' }}</div>
                                <div class="text-xs text-gray-500">{{ $item->productVariant->sku }}</div>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="text-sm font-bold text-indigo-600">{{ $item->sold_count }} đã bán</div>
                                <div class="text-xs text-gray-500">{{ number_format($item->price * $item->sold_count) }}₫</div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="px-4 py-8 text-center text-gray-500">Chưa có dữ liệu bán hàng.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                <h3 class="font-semibold text-gray-700">⚠️ Sản phẩm chưa có lượt mua</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full whitespace-no-wrap">
                    <tbody class="divide-y divide-gray-100">
                        @forelse($unsoldProducts as $item)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <div class="text-sm font-medium text-gray-800">{{ $item->productVariant->product->name ?? 'Unknown' }}</div>
                                <div class="text-xs text-gray-500">Giá sale: {{ number_format($item->price) }}₫</div>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <span class="px-2 py-1 text-xs font-semibold leading-tight text-red-700 bg-red-100 rounded-full">
                                    Tồn: {{ $item->quantity }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="2" class="px-4 py-8 text-center text-gray-500">
                                <i class="fa-solid fa-circle-check text-green-500 text-2xl mb-2 block"></i>
                                Tuyệt vời! Tất cả sản phẩm đều đã phát sinh đơn hàng.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection