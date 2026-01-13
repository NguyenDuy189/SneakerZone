@extends('admin.layouts.app')

@section('title', 'Quản lý đơn hàng')
@section('header', 'Danh sách đơn hàng')

@section('content')

{{-- 
    1. CẤU HÌNH TRẠNG THÁI (Dùng chung cho Filter và Table)
--}}
@php
    $statusMap = [
        'pending'    => ['label' => 'Chờ xử lý',      'class' => 'bg-yellow-100 text-yellow-800 border border-yellow-200', 'icon' => '🟡'],
        'processing' => ['label' => 'Đang đóng gói',  'class' => 'bg-blue-100 text-blue-800 border border-blue-200',       'icon' => '🔵'],
        'shipping'   => ['label' => 'Đang vận chuyển','class' => 'bg-purple-100 text-purple-800 border border-purple-200', 'icon' => '🟣'],
        'completed'  => ['label' => 'Hoàn thành',     'class' => 'bg-emerald-100 text-emerald-800 border border-emerald-200', 'icon' => '🟢'],
        'cancelled'  => ['label' => 'Đã hủy',         'class' => 'bg-rose-100 text-rose-800 border border-rose-200',       'icon' => '🔴'],
        'returned'   => ['label' => 'Trả hàng',       'class' => 'bg-slate-100 text-slate-800 border border-slate-200',     'icon' => '↩️'],
    ];
@endphp

<div class="container px-6 mx-auto mb-10 fade-in">
    
    {{-- STATS CARDS --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        {{-- Card 1: Chờ xử lý --}}
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition-shadow">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Chờ xử lý</p>
                    <h3 class="text-3xl font-extrabold text-slate-800 mt-2">
                        {{ \App\Models\Order::where('status', 'pending')->count() }}
                    </h3>
                </div>
                <div class="p-3 bg-yellow-50 rounded-xl text-yellow-500">
                    <i class="fa-regular fa-clock text-xl"></i>
                </div>
            </div>
            <p class="text-xs text-slate-400 mt-2">Cần xử lý ngay</p>
        </div>

        {{-- Card 2: Đang giao hàng --}}
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition-shadow">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Đang giao hàng</p>
                    <h3 class="text-3xl font-extrabold text-slate-800 mt-2">
                        {{ \App\Models\Order::where('status', 'shipping')->count() }}
                    </h3>
                </div>
                <div class="p-3 bg-purple-50 rounded-xl text-purple-500">
                    <i class="fa-solid fa-truck-fast text-xl"></i>
                </div>
            </div>
            <p class="text-xs text-slate-400 mt-2">Đơn vị vận chuyển</p>
        </div>

        {{-- Card 3: Doanh thu --}}
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition-shadow">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Doanh thu hôm nay</p>
                    <h3 class="text-2xl font-extrabold text-emerald-600 mt-2">
                        {{ number_format(\App\Models\Order::whereDate('created_at', today())->where('payment_status', 'paid')->sum('total_amount'), 0, ',', '.') }} <span class="text-sm text-emerald-500">₫</span>
                    </h3>
                </div>
                <div class="p-3 bg-emerald-50 rounded-xl text-emerald-500">
                    <i class="fa-solid fa-chart-line text-xl"></i>
                </div>
            </div>
            <p class="text-xs text-slate-400 mt-2">Đã thanh toán</p>
        </div>

        {{-- Card 4: Tổng đơn tháng --}}
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition-shadow">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Đơn tháng này</p>
                    <h3 class="text-3xl font-extrabold text-slate-800 mt-2">
                        {{ \App\Models\Order::whereMonth('created_at', now()->month)->count() }}
                    </h3>
                </div>
                <div class="p-3 bg-blue-50 rounded-xl text-blue-500">
                    <i class="fa-regular fa-calendar-check text-xl"></i>
                </div>
            </div>
            <p class="text-xs text-slate-400 mt-2">Tháng {{ now()->format('m/Y') }}</p>
        </div>
    </div>

    {{-- FILTERS --}}
    <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200 mb-6">
        <form action="{{ route('admin.orders.index') }}" method="GET">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                {{-- Tìm kiếm --}}
                <div class="md:col-span-4 relative group">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fa-solid fa-magnifying-glass text-slate-400 group-focus-within:text-indigo-500 transition-colors"></i>
                    </div>
                    <input type="text" name="keyword" value="{{ request('keyword') }}" 
                        class="pl-10 pr-4 py-2.5 w-full border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all shadow-sm"
                        placeholder="Tìm kiếm mã đơn, tên khách, SĐT...">
                </div>

                {{-- Select Trạng thái (Sử dụng $statusMap) --}}
                <div class="md:col-span-3">
                    <select name="status" class="w-full border border-slate-200 rounded-xl py-2.5 px-3 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 cursor-pointer shadow-sm text-slate-600 font-medium">
                        <option value="">Tất cả trạng thái</option>
                        @foreach($statusMap as $key => $config)
                            <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>
                                {{ $config['icon'] }} {{ $config['label'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Select Thanh toán --}}
                <div class="md:col-span-3">
                    <select name="payment_status" class="w-full border border-slate-200 rounded-xl py-2.5 px-3 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 cursor-pointer shadow-sm text-slate-600 font-medium">
                        <option value="">Tình trạng thanh toán</option>
                        <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>✅ Đã thanh toán</option>
                        <option value="unpaid" {{ request('payment_status') == 'unpaid' ? 'selected' : '' }}>⏳ Chưa thanh toán</option>
                        <option value="refunded" {{ request('payment_status') == 'refunded' ? 'selected' : '' }}>↩️ Hoàn tiền</option>
                    </select>
                </div>

                {{-- Nút Lọc --}}
                <div class="md:col-span-2 flex gap-2 justify-end">
                    <button type="submit" class="px-5 py-2.5 text-sm font-bold text-white bg-slate-900 rounded-xl hover:bg-slate-800 transition-all shadow-lg shadow-slate-900/20 flex items-center">
                        <i class="fa-solid fa-filter mr-2"></i> Lọc
                    </button>
                    @if(request()->hasAny(['keyword', 'status', 'payment_status']))
                        <a href="{{ route('admin.orders.index') }}" class="px-4 py-2.5 text-slate-500 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 hover:text-rose-500 transition-colors" title="Xóa bộ lọc">
                            <i class="fa-solid fa-rotate-left"></i>
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    {{-- TABLE --}}
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
                <table class="min-w-full whitespace-nowrap text-left">
                    <thead>
                    <tr class="bg-slate-50 border-b border-slate-100 text-xs font-bold text-slate-500 uppercase tracking-wider">
                        <th class="px-6 py-4">Mã đơn</th>
                        <th class="px-6 py-4">Khách hàng</th>
                        <th class="px-6 py-4 text-center">Trạng thái</th>
                        <th class="px-6 py-4 text-center">Thanh toán</th>
                        <th class="px-6 py-4 text-right">Tổng tiền</th>
                        <th class="px-6 py-4">Ngày tạo</th>
                        <th class="px-6 py-4 text-center">Tác vụ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($orders as $order)
                    <tr class="hover:bg-slate-50/80 transition-colors group">
                        
                        {{-- Mã đơn --}}
                        <td class="px-6 py-4">
                            <a href="{{ route('admin.orders.show', $order->id) }}" class="inline-flex items-center gap-2 font-mono font-bold text-indigo-600 group-hover:text-indigo-700">
                                <i class="fa-solid fa-hashtag text-xs opacity-50"></i>{{ $order->order_code }}
                            </a>
                        </td>

                        {{-- Khách hàng --}}
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-slate-200 to-slate-100 flex items-center justify-center text-xs font-bold text-slate-600 border border-white shadow-sm mr-3">
                                    {{ substr($order->shipping_address['contact_name'] ?? 'U', 0, 1) }}
                                </div>
                                <div>
                                    <div class="font-bold text-slate-700 text-sm">{{ $order->shipping_address['contact_name'] ?? 'Khách lẻ' }}</div>
                                    <div class="text-xs text-slate-400 font-mono">{{ $order->shipping_address['phone'] ?? '' }}</div>
                                </div>
                            </div>
                        </td>

                        {{-- Trạng thái (Sử dụng $statusMap) --}}
                        <td class="px-6 py-4 text-center">
                            @php
                                $status = $order->status;
                                // Lấy config từ mảng đã định nghĩa ở đầu file, fallback nếu không tìm thấy
                                $conf = $statusMap[$status] ?? ['class' => 'bg-slate-100 text-slate-600', 'label' => $status];
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold {{ $conf['class'] }}">
                                {{ $conf['label'] }}
                            </span>
                        </td>

                        {{-- Thanh toán --}}
                        <td class="px-6 py-4 text-center">
                            @if($order->payment_status == 'paid')
                                <i class="fa-solid fa-circle-check text-emerald-500 text-lg" title="Đã thanh toán"></i>
                            @elseif($order->payment_status == 'refunded')
                                <i class="fa-solid fa-circle-arrow-left text-rose-500 text-lg" title="Đã hoàn tiền"></i>
                            @else
                                <i class="fa-regular fa-circle text-slate-300 text-lg" title="Chưa thanh toán"></i>
                            @endif
                        </td>

                        {{-- Tổng tiền --}}
                        <td class="px-6 py-4 text-right">
                            <span class="font-bold text-slate-700">{{ number_format($order->total_amount, 0, ',', '.') }}</span>
                            <span class="text-xs text-slate-400">đ</span>
                        </td>

                        {{-- Ngày tạo --}}
                        <td class="px-6 py-4">
                            <span class="text-sm text-slate-600">{{ $order->created_at->format('d/m/Y') }}</span>
                            <span class="block text-xs text-slate-400">{{ $order->created_at->format('H:i') }}</span>
                        </td>

                        {{-- Tác vụ --}}
                        <td class="px-6 py-4 text-center">
                            <a href="{{ route('admin.orders.show', $order->id) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white border border-slate-200 text-slate-400 hover:text-indigo-600 hover:border-indigo-200 transition-all shadow-sm" title="Xem chi tiết">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center">
                            <div class="flex flex-col items-center justify-center text-slate-400">
                                <i class="fa-solid fa-box-open text-4xl mb-3 opacity-50"></i>
                                <p>Không tìm thấy dữ liệu đơn hàng.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        {{-- Pagination --}}
        @if($orders->hasPages())
        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
            {{ $orders->links() }}
        </div>
        @endif
    </div>
</div>
@endsection