@extends('admin.layouts.app')

@section('content')
<div class="container px-6 mx-auto grid pb-10">
    
    <!-- HEADER SECTION -->
    <div class="flex flex-col md:flex-row justify-between items-center my-8 gap-4">
        <div>
            <h2 class="text-3xl font-bold text-gray-800">Tổng quan</h2>
            <p class="text-sm text-gray-500 mt-1">
                Thống kê từ: <span class="font-semibold text-blue-600">{{ $date_label }}</span>
            </p>
        </div>

        <!-- DATE FILTER -->
        <form method="GET" action="{{ route('admin.dashboard') }}" class="bg-white p-1 rounded-lg shadow-sm border border-gray-200">
            <select name="date_range" onchange="this.form.submit()" class="text-sm border-none focus:ring-0 bg-transparent text-gray-700 font-medium cursor-pointer py-2 pl-3 pr-8 rounded-md hover:bg-gray-50 transition-colors">
                <option value="today" {{ $filters['date_range'] == 'today' ? 'selected' : '' }}>Hôm nay</option>
                <option value="yesterday" {{ $filters['date_range'] == 'yesterday' ? 'selected' : '' }}>Hôm qua</option>
                <option value="7_days" {{ $filters['date_range'] == '7_days' ? 'selected' : '' }}>7 ngày qua</option>
                <option value="30_days" {{ $filters['date_range'] == '30_days' ? 'selected' : '' }}>30 ngày qua</option>
                <option value="this_month" {{ $filters['date_range'] == 'this_month' ? 'selected' : '' }}>Tháng này</option>
                <option value="last_month" {{ $filters['date_range'] == 'last_month' ? 'selected' : '' }}>Tháng trước</option>
            </select>
        </form>
    </div>

    <!-- 1. KPI CARDS -->
    <div class="grid gap-6 mb-8 md:grid-cols-2 xl:grid-cols-4">
        <!-- Revenue Card -->
        <div class="relative overflow-hidden rounded-2xl p-6 bg-gradient-to-br from-blue-600 to-indigo-700 text-white shadow-lg shadow-blue-200 hover:-translate-y-1 transition-transform duration-300">
            <div class="absolute right-0 top-0 h-32 w-32 translate-x-8 -translate-y-8 rounded-full bg-white opacity-10"></div>
            <p class="text-blue-100 text-xs font-bold tracking-wider uppercase">Tổng doanh thu</p>
            <h3 class="text-2xl font-bold mt-1 truncate" title="{{ number_format($revenue['value']) }}">{{ number_format($revenue['value']) }} <span class="text-sm font-normal opacity-80">đ</span></h3>
            <div class="mt-4 flex items-center text-sm font-medium">
                <span class="{{ $revenue['is_positive'] ? 'bg-white/20 text-white' : 'bg-red-500/80 text-white' }} px-2 py-0.5 rounded flex items-center shadow-sm">
                    {{ $revenue['is_positive'] ? '▲' : '▼' }} {{ abs($revenue['growth']) }}%
                </span>
                <span class="ml-2 text-blue-200 text-xs">so với kỳ trước</span>
            </div>
        </div>

        <!-- Orders Card -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-gray-500 text-xs font-bold uppercase tracking-wider">Đơn hàng</p>
                    <h3 class="text-2xl font-bold text-gray-800 mt-1">{{ number_format($orders['value']) }}</h3>
                </div>
                <div class="p-2 bg-orange-50 rounded-lg text-orange-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                </div>
            </div>
            <div class="mt-4 flex items-center text-xs font-medium text-gray-500">
                 <span class="{{ $orders['is_positive'] ? 'text-green-600' : 'text-red-500' }} font-bold mr-1">
                    {{ $orders['is_positive'] ? '+' : '-' }}{{ abs($orders['growth']) }}%
                </span>
                kỳ trước
            </div>
        </div>

        <!-- Customers Card -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-gray-500 text-xs font-bold uppercase tracking-wider">Khách mới</p>
                    <h3 class="text-2xl font-bold text-gray-800 mt-1">{{ number_format($users['value']) }}</h3>
                </div>
                <div class="p-2 bg-teal-50 rounded-lg text-teal-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
            </div>
            <div class="mt-4 flex items-center text-xs font-medium text-gray-500">
                 <span class="{{ $users['is_positive'] ? 'text-green-600' : 'text-red-500' }} font-bold mr-1">
                    {{ $users['is_positive'] ? '+' : '-' }}{{ abs($users['growth']) }}%
                </span>
                kỳ trước
            </div>
        </div>

        <!-- AOV Card -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-gray-500 text-xs font-bold uppercase tracking-wider">Giá trị TB/Đơn</p>
                    <h3 class="text-2xl font-bold text-gray-800 mt-1">{{ number_format($aov['value']) }} <span class="text-xs text-gray-400">đ</span></h3>
                </div>
                <div class="p-2 bg-purple-50 rounded-lg text-purple-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                </div>
            </div>
            <div class="mt-4 w-full bg-gray-100 rounded-full h-1.5">
                <div class="bg-purple-500 h-1.5 rounded-full" style="width: 75%"></div>
            </div>
            <p class="text-xs text-gray-400 mt-2 text-right">Hiệu suất bán hàng</p>
        </div>
    </div>

    <!-- 2. CHARTS SECTION -->
    <div class="grid gap-6 mb-8 md:grid-cols-3">
        <!-- Main Line Chart -->
        <div class="min-w-0 p-6 bg-white rounded-2xl shadow-sm border border-gray-100 md:col-span-2">
            <div class="flex justify-between items-center mb-6">
                <h4 class="text-lg font-bold text-gray-800">Biểu đồ doanh thu</h4>
            </div>
            <div class="relative h-80 w-full">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>

        <!-- Doughnut Chart (Order Status) -->
        <div class="min-w-0 p-6 bg-white rounded-2xl shadow-sm border border-gray-100">
            <h4 class="text-lg font-bold text-gray-800 mb-6">Tỷ lệ đơn hàng</h4>
            <div class="relative h-48 w-full mb-6">
                <canvas id="statusChart"></canvas>
            </div>
            <div class="space-y-2">
                <!-- Legend Custom -->
                <div class="flex justify-between text-xs text-gray-600">
                    <span class="flex items-center"><span class="w-2 h-2 rounded-full bg-green-500 mr-2"></span>Hoàn thành</span>
                    <span class="font-bold">{{ $order_status['completed'] }}</span>
                </div>
                <div class="flex justify-between text-xs text-gray-600">
                    <span class="flex items-center"><span class="w-2 h-2 rounded-full bg-yellow-400 mr-2"></span>Chờ xử lý</span>
                    <span class="font-bold">{{ $order_status['pending'] }}</span>
                </div>
                 <div class="flex justify-between text-xs text-gray-600">
                    <span class="flex items-center"><span class="w-2 h-2 rounded-full bg-red-400 mr-2"></span>Hủy</span>
                    <span class="font-bold">{{ $order_status['cancelled'] }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. TOP LISTS & NOTIFICATIONS -->
    <div class="grid gap-6 mb-8 md:grid-cols-3">
        <!-- Top Products -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h4 class="text-lg font-bold text-gray-800 mb-4">Top Sản phẩm</h4>
            <div class="space-y-4">
                @forelse($top_products as $product)
                <div class="flex items-center justify-between">
                    <div class="flex items-center min-w-0">
                        <div class="w-10 h-10 rounded-lg bg-gray-100 flex-shrink-0 flex items-center justify-center text-xs font-bold text-gray-500">
                            {{ $loop->iteration }}
                        </div>
                        <div class="ml-3 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate w-32" title="{{ $product['name'] }}">{{ $product['name'] }}</p>
                            <p class="text-xs text-gray-500">{{ number_format($product['sold']) }} đã bán</p>
                        </div>
                    </div>
                    <div class="text-sm font-bold text-gray-700">{{ number_format($product['revenue']) }}đ</div>
                </div>
                @empty
                <p class="text-center text-sm text-gray-400 py-4">Chưa có dữ liệu</p>
                @endforelse
            </div>
        </div>

        <!-- Top Customers -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h4 class="text-lg font-bold text-gray-800 mb-4">Khách hàng VIP</h4>
            <div class="space-y-4">
                @forelse($top_customers as $customer)
                <div class="flex items-center justify-between">
                    <div class="flex items-center min-w-0">
                        <div class="w-8 h-8 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs font-bold">
                            {{ substr($customer['name'], 0, 1) }}
                        </div>
                        <div class="ml-3 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate w-32">{{ $customer['name'] }}</p>
                            <p class="text-xs text-gray-500">{{ $customer['count'] }} đơn hàng</p>
                        </div>
                    </div>
                    <div class="text-sm font-bold text-gray-700">{{ number_format($customer['spent']) }}đ</div>
                </div>
                @empty
                <p class="text-center text-sm text-gray-400 py-4">Chưa có dữ liệu</p>
                @endforelse
            </div>
        </div>

        <!-- Notifications -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <div class="flex justify-between items-center mb-4">
                <h4 class="text-lg font-bold text-gray-800">Thông báo</h4>
                <span class="text-xs bg-red-100 text-red-600 px-2 py-0.5 rounded-full font-bold">Mới</span>
            </div>
            <div class="space-y-4 relative before:absolute before:left-2 before:top-2 before:h-full before:w-0.5 before:bg-gray-100">
                @forelse($notifications as $notify)
                <div class="relative pl-6">
                    <span class="absolute left-0 top-1.5 w-4 h-4 rounded-full border-2 border-white bg-blue-500"></span>
                    <p class="text-sm font-medium text-gray-800 line-clamp-1">{{ $notify->title }}</p>
                    <p class="text-xs text-gray-500 mt-0.5 line-clamp-2">{{ $notify->message }}</p>
                    <span class="text-[10px] text-gray-400">{{ $notify->created_at->diffForHumans() }}</span>
                </div>
                @empty
                <p class="text-center text-sm text-gray-400 py-4">Không có thông báo mới</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- 4. RECENT ORDERS TABLE -->
    <div class="w-full bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
            <h4 class="font-bold text-gray-800">Đơn hàng mới nhất</h4>
            <a href="{{ route('admin.orders.index') }}" class="text-sm text-blue-600 hover:underline">Xem tất cả &rarr;</a>
        </div>
        <div class="w-full overflow-x-auto">
            <table class="w-full whitespace-no-wrap">
                <thead>
                    <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b bg-white">
                        <th class="px-6 py-3">Mã đơn</th>
                        <th class="px-6 py-3">Khách hàng</th>
                        <th class="px-6 py-3">Tổng tiền</th>
                        <th class="px-6 py-3">Trạng thái</th>
                        <th class="px-6 py-3">Ngày đặt</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($recent_orders as $order)
                    <tr class="text-gray-700 hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 font-mono text-sm text-blue-600 font-medium">#{{ $order->id }}</td>
                        <td class="px-6 py-4 text-sm font-medium">{{ $order->user->name ?? 'Vãng lai' }}</td>
                        <td class="px-6 py-4 text-sm font-bold">{{ number_format($order->total_amount) }} đ</td>
                        <td class="px-6 py-4 text-xs">
                             @php
                                $stt = [
                                    'pending'    => ['bg'=>'bg-yellow-100', 'txt'=>'text-yellow-800', 'lbl'=>'Chờ xử lý'],
                                    'processing' => ['bg'=>'bg-blue-100',   'txt'=>'text-blue-800',   'lbl'=>'Đang xử lý'],
                                    'shipping'   => ['bg'=>'bg-indigo-100', 'txt'=>'text-indigo-800', 'lbl'=>'Đang giao'],
                                    'completed'  => ['bg'=>'bg-green-100',  'txt'=>'text-green-800',  'lbl'=>'Hoàn thành'],
                                    'cancelled'  => ['bg'=>'bg-red-100',    'txt'=>'text-red-800',    'lbl'=>'Đã hủy'],
                                ];
                                $s = $stt[$order->status] ?? ['bg'=>'bg-gray-100', 'txt'=>'text-gray-800', 'lbl'=>$order->status];
                            @endphp
                            <span class="px-2.5 py-0.5 rounded-full font-semibold {{ $s['bg'] }} {{ $s['txt'] }}">
                                {{ $s['lbl'] }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $order->created_at->format('H:i d/m') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-6 py-4 text-center text-gray-400">Chưa có đơn hàng nào</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    // 1. BIỂU ĐỒ DOANH THU (LINE)
    const ctxRev = document.getElementById('revenueChart');
    if (ctxRev) {
        let grad = ctxRev.getContext('2d').createLinearGradient(0, 0, 0, 300);
        grad.addColorStop(0, 'rgba(37, 99, 235, 0.2)');
        grad.addColorStop(1, 'rgba(37, 99, 235, 0.0)');

        new Chart(ctxRev, {
            type: 'line',
            data: {
                labels: {!! json_encode($charts['labels']) !!},
                datasets: [{
                    label: 'Doanh thu',
                    data: {!! json_encode($charts['revenue']) !!},
                    borderColor: '#2563EB',
                    backgroundColor: grad,
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true,
                    pointRadius: 3,
                    pointHoverRadius: 5
                }, {
                    label: 'Đơn hàng',
                    data: {!! json_encode($charts['orders']) !!},
                    borderColor: '#F59E0B', // Orange
                    borderWidth: 2,
                    borderDash: [5, 5],
                    tension: 0.4,
                    fill: false,
                    pointRadius: 0,
                    yAxisID: 'y1'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(17, 24, 39, 0.9)',
                        padding: 10,
                        callbacks: {
                            label: function(ctx) {
                                let label = ctx.dataset.label || '';
                                if (label === 'Doanh thu') {
                                    return label + ': ' + new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(ctx.raw);
                                }
                                return label + ': ' + ctx.raw;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { borderDash: [4, 4], color: '#f3f4f6' },
                        ticks: { callback: (v) => v >= 1000000 ? (v/1000000) + 'tr' : v }
                    },
                    y1: {
                        display: false,
                        position: 'right',
                        grid: { drawOnChartArea: false }
                    },
                    x: { grid: { display: false } }
                }
            }
        });
    }

    // 2. BIỂU ĐỒ TRẠNG THÁI (DOUGHNUT)
    const ctxStat = document.getElementById('statusChart');
    if (ctxStat) {
        const stats = {!! json_encode($order_status) !!};
        new Chart(ctxStat, {
            type: 'doughnut',
            data: {
                labels: ['Hoàn thành', 'Chờ xử lý', 'Đang giao', 'Hủy'],
                datasets: [{
                    data: [
                        stats['completed'] || 0,
                        (stats['pending'] || 0) + (stats['processing'] || 0),
                        stats['shipping'] || 0,
                        stats['cancelled'] || 0
                    ],
                    backgroundColor: ['#10B981', '#FBBF24', '#6366F1', '#EF4444'],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '75%',
                plugins: { legend: { display: false } }
            }
        });
    }
});
</script>
@endpush