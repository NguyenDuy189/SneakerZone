@extends('admin.layouts.app')

@section('title', 'Dashboard Analytics')

@section('content')
<div class="container px-6 mx-auto grid pb-10">
    
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center my-6 gap-4 bg-white p-4 rounded-xl shadow-sm border border-gray-100">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Tổng quan kinh doanh</h2>
            <p class="text-sm text-gray-500">Thống kê từ: <span class="font-bold text-blue-600">{{ $range['start']->format('d/m/Y') }}</span> đến <span class="font-bold text-blue-600">{{ $range['end']->format('d/m/Y') }}</span></p>
        </div>

        <form method="GET" action="{{ route('admin.dashboard') }}" class="flex flex-col md:flex-row gap-3 items-end" id="filterForm">
            <select name="date_range" id="dateRangeSelect" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5">
                <option value="today" {{ $range['filter_key'] == 'today' ? 'selected' : '' }}>Hôm nay</option>
                <option value="yesterday" {{ $range['filter_key'] == 'yesterday' ? 'selected' : '' }}>Hôm qua</option>
                <option value="7_days" {{ $range['filter_key'] == '7_days' ? 'selected' : '' }}>7 ngày qua</option>
                <option value="this_month" {{ $range['filter_key'] == 'this_month' ? 'selected' : '' }}>Tháng này</option>
                <option value="last_month" {{ $range['filter_key'] == 'last_month' ? 'selected' : '' }}>Tháng trước</option>
                <option value="custom" {{ $range['filter_key'] == 'custom' ? 'selected' : '' }}>Tùy chọn ngày...</option>
            </select>

            <div id="customDateRange" class="{{ $range['filter_key'] == 'custom' ? 'flex' : 'hidden' }} gap-2">
                <input type="date" name="start_date" value="{{ request('start_date') }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5">
                <input type="date" name="end_date" value="{{ request('end_date') }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5">
            </div>

            <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5">
                <i class="fa-solid fa-filter mr-1"></i> Lọc
            </button>
            
            <a href="{{ route('admin.dashboard.export', request()->all()) }}" class="text-white bg-green-600 hover:bg-green-700 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2.5">
                <i class="fa-solid fa-file-export mr-1"></i> Excel
            </a>
        </form>
    </div>

    <div class="grid gap-6 mb-8 md:grid-cols-2 xl:grid-cols-4">
        <div class="flex items-center p-4 bg-white rounded-xl shadow-xs border-l-4 border-blue-500">
            <div class="p-3 mr-4 text-blue-500 bg-blue-100 rounded-full">
                <i class="fa-solid fa-money-bill-wave text-xl"></i>
            </div>
            <div>
                <p class="mb-2 text-sm font-medium text-gray-600">Tổng doanh thu</p>
                <p class="text-lg font-semibold text-gray-700">{{ number_format($revenue['value']) }} đ</p>
                <p class="text-xs {{ $revenue['is_up'] ? 'text-green-600' : 'text-red-600' }} font-bold">
                    <i class="fa-solid {{ $revenue['is_up'] ? 'fa-arrow-up' : 'fa-arrow-down' }}"></i> {{ abs($revenue['growth']) }}%
                </p>
            </div>
        </div>
        
        <div class="flex items-center p-4 bg-white rounded-xl shadow-xs border-l-4 border-orange-500">
            <div class="p-3 mr-4 text-orange-500 bg-orange-100 rounded-full">
                <i class="fa-solid fa-cart-shopping text-xl"></i>
            </div>
            <div>
                <p class="mb-2 text-sm font-medium text-gray-600">Đơn hàng</p>
                <p class="text-lg font-semibold text-gray-700">{{ number_format($orders['value']) }}</p>
                <p class="text-xs {{ $orders['is_up'] ? 'text-green-600' : 'text-red-600' }} font-bold">
                    {{ $orders['is_up'] ? '+' : '-' }}{{ abs($orders['growth']) }}%
                </p>
            </div>
        </div>

        <div class="flex items-center p-4 bg-white rounded-xl shadow-xs border-l-4 border-teal-500">
            <div class="p-3 mr-4 text-teal-500 bg-teal-100 rounded-full">
                <i class="fa-solid fa-users text-xl"></i>
            </div>
            <div>
                <p class="mb-2 text-sm font-medium text-gray-600">Khách hàng mới</p>
                <p class="text-lg font-semibold text-gray-700">{{ number_format($customers['value']) }}</p>
                <p class="text-xs {{ $customers['is_up'] ? 'text-green-600' : 'text-red-600' }} font-bold">
                    {{ $customers['is_up'] ? '+' : '-' }}{{ abs($customers['growth']) }}%
                </p>
            </div>
        </div>

        <div class="flex items-center p-4 bg-white rounded-xl shadow-xs border-l-4 border-purple-500">
            <div class="p-3 mr-4 text-purple-500 bg-purple-100 rounded-full">
                <i class="fa-solid fa-receipt text-xl"></i>
            </div>
            <div>
                <p class="mb-2 text-sm font-medium text-gray-600">Giá trị TB/Đơn</p>
                <p class="text-lg font-semibold text-gray-700">{{ number_format($avg_order['value']) }} đ</p>
                <p class="text-xs text-gray-400">Trung bình mỗi đơn</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        
        <div class="lg:col-span-2 min-w-0 p-4 bg-white rounded-xl shadow-xs border border-gray-100">
            <h4 class="mb-4 font-bold text-gray-800">Biểu đồ doanh thu & Đơn hàng</h4>
            <div class="relative h-80">
                <canvas id="mainChart"></canvas>
            </div>
        </div>

        <div class="lg:col-span-1 flex flex-col gap-6">
            
            <div class="min-w-0 p-4 bg-white rounded-xl shadow-xs border border-gray-100">
                <h4 class="mb-2 font-bold text-gray-800 text-sm">Doanh thu theo danh mục</h4>
                <div class="relative h-40">
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>

            <div class="min-w-0 p-4 bg-white rounded-xl shadow-xs border border-gray-100">
                <h4 class="mb-2 font-bold text-gray-800 text-sm">Trạng thái đơn hàng</h4>
                <div class="relative h-40 flex justify-center mb-4">
                    <canvas id="statusChart"></canvas>
                </div>
                
                <div class="space-y-2 text-sm">
                    @foreach($status_chart['list'] as $status)
                    <div class="flex items-center justify-between p-2 rounded-lg bg-gray-50 border border-gray-100 hover:bg-gray-100 transition">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full {{ $status['color'] }}"></span>
                            <span class="text-gray-600 font-medium">{{ $status['label'] }}</span>
                        </div>
                        <span class="font-bold text-gray-800">{{ $status['count'] }} đơn</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        
        <div class="lg:col-span-2 w-full overflow-hidden rounded-xl shadow-xs bg-white border border-gray-100 flex flex-col">
            <div class="p-4 border-b border-gray-100">
                <h4 class="font-bold text-gray-800">Top Sản phẩm bán chạy</h4>
            </div>
            <div class="w-full overflow-x-auto">
                <table class="w-full whitespace-no-wrap">
                    <thead class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b bg-gray-50">
                        <tr>
                            <th class="px-4 py-3">Sản phẩm</th>
                            <th class="px-4 py-3">Phân loại</th>
                            <th class="px-4 py-3 text-center">Đã bán</th>
                            <th class="px-4 py-3 text-right">Doanh thu</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @forelse($top_products as $item)
                        <tr class="text-gray-700 hover:bg-gray-50 transition">
                            <td class="px-4 py-3">
                                <p class="text-sm font-semibold truncate w-48" title="{{ $item->product_name }}">{{ $item->product_name }}</p>
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-500">{{ $item->variant_name }}</td>
                            <td class="px-4 py-3 text-sm text-center">
                                <span class="px-2 py-1 font-semibold leading-tight text-green-700 bg-green-100 rounded-full">
                                    {{ $item->sold }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-right font-bold">{{ number_format($item->revenue) }} đ</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-4 py-4 text-center text-gray-400 text-sm">Chưa có dữ liệu</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="lg:col-span-1 w-full rounded-xl shadow-xs bg-white border border-gray-100 flex flex-col h-full">
            <div class="p-4 border-b border-red-100 bg-red-50 rounded-t-xl flex justify-between items-center">
                <h4 class="font-bold text-red-700 flex items-center gap-2">
                    <i class="fa-solid fa-bell animate-pulse"></i> Cảnh báo kho hàng
                </h4>
                <a href="{{ route('admin.products.index') }}" class="text-xs font-bold text-red-600 hover:underline">Quản lý</a>
            </div>
            
            <div class="p-3 overflow-y-auto max-h-[400px] space-y-3">
                @forelse($low_stock as $variant)
                <div class="flex items-start gap-3 p-3 bg-white border border-gray-100 rounded-lg shadow-sm hover:shadow-md transition">
                    <div class="mt-1 flex-shrink-0">
                        <div class="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center text-red-500">
                            <i class="fa-solid fa-triangle-exclamation text-xs"></i>
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-bold text-gray-800 truncate" title="{{ $variant->product->name ?? 'SP' }}">
                            {{ $variant->product->name ?? 'Sản phẩm lỗi' }}
                        </p>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="bg-gray-100 text-gray-600 text-[10px] font-bold px-2 py-0.5 rounded border border-gray-200">
                                {{ $variant->name }}
                            </span>
                            <span class="text-[10px] text-gray-400">SKU: {{ $variant->sku }}</span>
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="block text-lg font-black text-red-600 leading-none">{{ $variant->stock_quantity }}</span>
                        <span class="text-[10px] text-red-400 font-bold uppercase">Còn lại</span>
                    </div>
                </div>
                @empty
                <div class="flex flex-col items-center justify-center py-10 text-gray-400">
                    <i class="fa-solid fa-clipboard-check text-4xl text-green-100 mb-3"></i>
                    <p class="text-sm">Kho hàng ổn định</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="mt-8">
        <div class="w-full bg-white rounded-xl shadow-xs border border-gray-100 overflow-hidden">
            
            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                <h4 class="font-bold text-gray-800 text-lg">Đơn hàng mới nhất</h4>
                <a href="{{ route('admin.orders.index') }}" class="text-sm font-semibold text-blue-600 hover:text-blue-800 hover:underline flex items-center transition">
                    Xem tất cả đơn hàng <i class="fa-solid fa-arrow-right ml-1"></i>
                </a>
            </div>

            <div class="w-full overflow-x-auto">
                <table class="w-full whitespace-no-wrap">
                    <thead>
                        <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b bg-gray-50">
                            <th class="px-6 py-3">Mã đơn</th>
                            <th class="px-6 py-3">Khách hàng</th>
                            <th class="px-6 py-3">Ngày đặt</th>
                            <th class="px-6 py-3 text-center">Trạng thái</th>
                            <th class="px-6 py-3 text-right">Tổng tiền</th>
                            <th class="px-6 py-3 text-center">Hành động</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @forelse($recent_orders as $order)
                        <tr class="text-gray-700 hover:bg-gray-50 transition duration-150 ease-in-out">
                            <td class="px-6 py-4">
                                <a href="{{ route('admin.orders.show', $order->id) }}" class="font-bold text-blue-600 hover:underline">
                                    #{{ $order->order_code ?? $order->id }}
                                </a>
                            </td>

                            <td class="px-6 py-4">
                                <div class="flex items-center text-sm">
                                    <div class="relative hidden w-8 h-8 mr-3 rounded-full md:block bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold text-xs">
                                        {{ substr($order->user->name ?? 'K', 0, 1) }}
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-700">{{ $order->user->name ?? 'Khách vãng lai' }}</p>
                                        <p class="text-xs text-gray-500">{{ $order->receiver_phone ?? '---' }}</p>
                                    </div>
                                </div>
                            </td>

                            <td class="px-6 py-4 text-sm">
                                <span class="block text-gray-700 font-medium">
                                    {{ $order->created_at->format('H:i - d/m/Y') }}
                                </span>
                                <span class="text-xs text-gray-400">
                                    {{ $order->created_at->diffForHumans() }}
                                </span>
                            </td>

                            <td class="px-6 py-4 text-center">
                                @php
                                    $statusConfig = [
                                        'pending'    => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-700', 'label' => 'Chờ xử lý'],
                                        'processing' => ['bg' => 'bg-blue-100',   'text' => 'text-blue-700',   'label' => 'Đang xử lý'],
                                        'shipping'   => ['bg' => 'bg-indigo-100', 'text' => 'text-indigo-700', 'label' => 'Đang giao'],
                                        'completed'  => ['bg' => 'bg-emerald-100','text' => 'text-emerald-700','label' => 'Hoàn thành'],
                                        'cancelled'  => ['bg' => 'bg-red-100',    'text' => 'text-red-700',    'label' => 'Đã hủy'],
                                        'returned'   => ['bg' => 'bg-gray-100',   'text' => 'text-gray-700',   'label' => 'Trả hàng'],
                                    ];
                                    $cfg = $statusConfig[$order->status] ?? ['bg' => 'bg-gray-100', 'text' => 'text-gray-700', 'label' => $order->status];
                                @endphp
                                <span class="px-3 py-1 font-semibold leading-tight {{ $cfg['text'] }} {{ $cfg['bg'] }} rounded-full text-xs">
                                    {{ $cfg['label'] }}
                                </span>
                            </td>

                            <td class="px-6 py-4 text-right font-bold text-gray-800">
                                {{ number_format($order->total_amount, 0, ',', '.') }} đ
                            </td>

                            <td class="px-6 py-4 text-center">
                                <a href="{{ route('admin.orders.show', $order->id) }}" class="inline-flex items-center justify-center w-8 h-8 text-gray-400 transition-colors duration-150 rounded-lg hover:text-gray-800 hover:bg-gray-100 border border-transparent hover:border-gray-200" title="Xem chi tiết">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-400 bg-gray-50">
                                <div class="flex flex-col items-center justify-center">
                                    <i class="fa-regular fa-folder-open text-4xl mb-2 text-gray-300"></i>
                                    <p>Chưa có đơn hàng nào trong khoảng thời gian này.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="px-6 py-3 border-t border-gray-100 bg-gray-50 text-xs text-gray-500">
                Hiển thị {{ $recent_orders->count() }} đơn hàng mới nhất
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // 1. Toggle Custom Date Inputs
    const dateSelect = document.getElementById('dateRangeSelect');
    const customInputs = document.getElementById('customDateRange');
    
    dateSelect.addEventListener('change', function() {
        if(this.value === 'custom') {
            customInputs.classList.remove('hidden');
            customInputs.classList.add('flex');
        } else {
            customInputs.classList.add('hidden');
            customInputs.classList.remove('flex');
            document.getElementById('filterForm').submit();
        }
    });

    // 2. Main Chart
    const ctxMain = document.getElementById('mainChart');
    if(ctxMain) {
        new Chart(ctxMain, {
            type: 'bar',
            data: {
                labels: {!! json_encode($main_chart['labels']) !!},
                datasets: [
                    {
                        label: 'Doanh thu',
                        data: {!! json_encode($main_chart['revenue']) !!},
                        backgroundColor: 'rgba(59, 130, 246, 0.6)', 
                        borderColor: 'rgba(59, 130, 246, 1)',
                        borderWidth: 1,
                        yAxisID: 'y',
                        order: 2
                    },
                    {
                        label: 'Đơn hàng',
                        data: {!! json_encode($main_chart['orders']) !!},
                        type: 'line',
                        borderColor: '#F97316',
                        backgroundColor: 'rgba(249, 115, 22, 0.2)',
                        borderWidth: 2,
                        tension: 0.4,
                        fill: false,
                        yAxisID: 'y1',
                        order: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                scales: {
                    y: { type: 'linear', display: true, position: 'left', beginAtZero: true },
                    y1: { type: 'linear', display: true, position: 'right', beginAtZero: true, grid: { drawOnChartArea: false } },
                    x: { grid: { display: false } }
                }
            }
        });
    }

    // 3. Category Pie Chart
    const ctxCat = document.getElementById('categoryChart');
    if(ctxCat) {
        new Chart(ctxCat, {
            type: 'pie',
            data: {
                labels: {!! json_encode($category_chart['labels']) !!},
                datasets: [{
                    data: {!! json_encode($category_chart['data']) !!},
                    backgroundColor: ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'right', labels: { boxWidth: 12, font: { size: 10 } } } }
            }
        });
    }

    // 4. Status Donut Chart (Tắt Legend mặc định vì đã có list HTML)
    const ctxStat = document.getElementById('statusChart');
    const stats = {!! json_encode($status_chart['counts']) !!};
    if(ctxStat) {
        new Chart(ctxStat, {
            type: 'doughnut',
            data: {
                labels: ['Hoàn thành', 'Đang xử lý', 'Giao hàng', 'Hủy'],
                datasets: [{
                    data: stats,
                    backgroundColor: ['#10B981', '#F59E0B', '#3B82F6', '#EF4444'],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: { display: false }, // Ẩn legend của ChartJS
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return ' ' + context.label + ': ' + context.raw + ' đơn';
                            }
                        }
                    }
                }
            }
        });
    }
</script>
@endpush