@extends('admin.layouts.app')

@section('content')
    <div class="container px-6 mx-auto grid">
        <h2 class="my-6 text-2xl font-semibold text-gray-700">Tổng quan</h2>

        <div class="grid gap-6 mb-8 md:grid-cols-2 xl:grid-cols-4">
            <div class="flex items-center p-4 bg-white rounded-lg shadow-xs border border-gray-100">
                <div class="p-3 mr-4 text-green-500 bg-green-100 rounded-full">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <p class="mb-2 text-sm font-medium text-gray-600">Tổng doanh thu</p>
                    <p class="text-lg font-semibold text-gray-700">{{ number_format($totalRevenue) }} đ</p>
                </div>
            </div>
            
            <div class="flex items-center p-4 bg-white rounded-lg shadow-xs border border-gray-100">
                <div class="p-3 mr-4 text-orange-500 bg-orange-100 rounded-full">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <p class="mb-2 text-sm font-medium text-gray-600">Đơn chờ xử lý</p>
                    <p class="text-lg font-semibold text-gray-700">{{ $pendingOrders }}</p>
                </div>
            </div>

            <div class="flex items-center p-4 bg-white rounded-lg shadow-xs border border-gray-100">
                <div class="p-3 mr-4 text-blue-500 bg-blue-100 rounded-full">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
                <div>
                    <p class="mb-2 text-sm font-medium text-gray-600">Khách hàng</p>
                    <p class="text-lg font-semibold text-gray-700">{{ $totalUsers }} <span class="text-sm text-green-600">(+{{ $usersThisMonth }})</span></p>
                </div>
            </div>

            <div class="flex items-center p-4 bg-white rounded-lg shadow-xs border border-gray-100">
                <div class="p-3 mr-4 text-teal-500 bg-teal-100 rounded-full">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                </div>
                <div>
                    <p class="mb-2 text-sm font-medium text-gray-600">Tăng trưởng</p>
                    <p class="text-lg font-semibold text-gray-700">12%</p>
                </div>
            </div>
        </div>

        <div class="grid gap-6 mb-8 md:grid-cols-3">
            <div class="min-w-0 p-4 bg-white rounded-lg shadow-xs border border-gray-100 md:col-span-2">
                <h4 class="mb-4 font-semibold text-gray-800">Doanh thu năm nay</h4>
                <div class="relative h-64">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>

            <div class="min-w-0 p-4 bg-white rounded-lg shadow-xs border border-gray-100">
                <div class="flex justify-between items-center mb-4">
                    <h4 class="font-semibold text-gray-800">Thông báo hệ thống</h4>
                    <span class="px-2 py-1 text-xs font-bold text-blue-700 bg-blue-100 rounded-full">Mới</span>
                </div>
                
                <div class="space-y-4 max-h-64 overflow-y-auto pr-2">
                    @forelse($notifications as $notify)
                        @php
                            $colors = [
                                'info' => ['bg' => 'bg-blue-50', 'text' => 'text-blue-600', 'dot' => 'bg-blue-600'],
                                'success' => ['bg' => 'bg-green-50', 'text' => 'text-green-600', 'dot' => 'bg-green-600'],
                                'warning' => ['bg' => 'bg-orange-50', 'text' => 'text-orange-600', 'dot' => 'bg-orange-600'],
                                'danger' => ['bg' => 'bg-red-50', 'text' => 'text-red-600', 'dot' => 'bg-red-600'],
                            ];
                            $style = $colors[$notify->type] ?? $colors['info'];
                        @endphp

                        <div class="flex items-start p-3 rounded-lg {{ $style['bg'] }}">
                            <span class="flex-shrink-0 w-2 h-2 mt-1.5 rounded-full {{ $style['dot'] }}"></span>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-800">{{ $notify->title }}</p>
                                <p class="text-xs text-gray-500 mt-1">{{ $notify->message }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-400 italic text-center py-4">Chưa có thông báo nào.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="w-full overflow-hidden rounded-lg shadow-xs border border-gray-100 mb-8">
            <div class="w-full overflow-x-auto">
                <table class="w-full whitespace-no-wrap">
                    <thead>
                        <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b bg-gray-50">
                            <th class="px-4 py-3">Mã Đơn</th>
                            <th class="px-4 py-3">Khách hàng</th>
                            <th class="px-4 py-3">Tổng tiền</th>
                            <th class="px-4 py-3">Thanh toán</th>
                            <th class="px-4 py-3">Trạng thái</th>
                            <th class="px-4 py-3">Ngày đặt</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y">
                        @forelse($recentOrders as $order)
                        <tr class="text-gray-700 hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 text-sm font-medium text-blue-600">#{{ $order->order_code }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center text-sm">
                                    <div class="relative hidden w-8 h-8 mr-3 rounded-full md:block">
                                        <img class="object-cover w-full h-full rounded-full" src="https://ui-avatars.com/api/?name={{ urlencode($order->user->name ?? 'Guest') }}&background=random" alt="" />
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-700">{{ $order->user->name ?? 'Khách vãng lai' }}</p>
                                        <p class="text-xs text-gray-500">{{ $order->user->email ?? 'No email' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-sm font-bold text-gray-800">{{ number_format($order->total_amount) }} đ</td>
                            <td class="px-4 py-3 text-xs">
                                @if($order->payment_status === 'paid')
                                    <span class="px-2 py-1 font-semibold leading-tight text-green-700 bg-green-100 rounded-full">Đã thanh toán</span>
                                @else
                                    <span class="px-2 py-1 font-semibold leading-tight text-red-700 bg-red-100 rounded-full">Chưa TT</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs">
                                @php
                                    $statusMap = [
                                        'pending' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-700', 'label' => 'Chờ xử lý'],
                                        'processing' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-700', 'label' => 'Đang làm'],
                                        'shipping' => ['bg' => 'bg-purple-100', 'text' => 'text-purple-700', 'label' => 'Đang giao'],
                                        'completed' => ['bg' => 'bg-green-100', 'text' => 'text-green-700', 'label' => 'Hoàn thành'],
                                        'cancelled' => ['bg' => 'bg-gray-100', 'text' => 'text-gray-700', 'label' => 'Đã hủy'],
                                    ];
                                    $status = $statusMap[$order->status] ?? ['bg' => 'bg-gray-100', 'text' => 'text-gray-600', 'label' => $order->status];
                                @endphp
                                <span class="px-2 py-1 font-semibold leading-tight rounded-full {{ $status['bg'] }} {{ $status['text'] }}">{{ $status['label'] }}</span>
                            </td>
                            <td class="px-4 py-3 text-sm">{{ $order->created_at->format('d/m/Y') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-4 py-4 text-center text-gray-500">Chưa có đơn hàng nào.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    const ctx = document.getElementById('revenueChart');
    if (ctx) {
        const revenueChart = new Chart(ctx.getContext('2d'), {
            type: 'line',
            data: {
                labels: {!! json_encode($chartLabels) !!},
                datasets: [{
                    label: 'Doanh thu (VNĐ)',
                    data: {!! json_encode($chartData) !!},
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderColor: 'rgba(59, 130, 246, 1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3,
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: 'rgba(59, 130, 246, 1)',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) { label += ': '; }
                                label += new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(context.raw);
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { borderDash: [2, 4], color: '#f3f4f6' },
                        ticks: {
                            callback: function(value) {
                                if(value >= 1000000) return (value/1000000) + 'tr';
                                if(value >= 1000) return (value/1000) + 'k';
                                return value;
                            }
                        }
                    },
                    x: { grid: { display: false } }
                }
            }
        });
    }
</script>
@endpush