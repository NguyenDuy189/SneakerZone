@extends('admin.layouts.app')

@section('title', 'Quản lý đơn hàng')
@section('header', 'Danh sách đơn hàng')

@section('content')
<div class="container px-6 mx-auto mb-10 fade-in">
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition-shadow">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Chờ xử lý</p>
                    <h3 class="text-3xl font-extrabold text-slate-800 mt-2">
                        {{ \App\Models\Order::where('status', 'pending')->count() }}
                    </h3>
                </div>
                <div class="p-3 bg-amber-50 rounded-xl text-amber-500">
                    <i class="fa-regular fa-clock text-xl"></i>
                </div>
            </div>
            <p class="text-xs text-slate-400 mt-2">Cần xử lý ngay</p>
        </div>

        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition-shadow">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Đang giao hàng</p>
                    <h3 class="text-3xl font-extrabold text-slate-800 mt-2">
                        {{ \App\Models\Order::where('status', 'shipping')->count() }}
                    </h3>
                </div>
                <div class="p-3 bg-indigo-50 rounded-xl text-indigo-500">
                    <i class="fa-solid fa-truck-fast text-xl"></i>
                </div>
            </div>
            <p class="text-xs text-slate-400 mt-2">Đơn vị vận chuyển</p>
        </div>

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

    <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200 mb-6">
        <form action="{{ route('admin.orders.index') }}" method="GET">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                <div class="md:col-span-4 relative group">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fa-solid fa-magnifying-glass text-slate-400 group-focus-within:text-indigo-500 transition-colors"></i>
                    </div>
                    <input type="text" name="keyword" value="{{ request('keyword') }}" 
                        class="pl-10 pr-4 py-2.5 w-full border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all shadow-sm"
                        placeholder="Tìm kiếm mã đơn, tên khách, SĐT...">
                </div>

                <div class="md:col-span-2">
                    <select name="status" class="w-full border border-slate-200 rounded-xl py-2.5 px-3 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 cursor-pointer shadow-sm text-slate-600 font-medium">
                        <option value="">Tất cả trạng thái</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>🟡 Chờ xử lý</option>
                        <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>🔵 Đang đóng gói</option>
                        <option value="shipping" {{ request('status') == 'shipping' ? 'selected' : '' }}>🟣 Đang giao</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>🟢 Hoàn thành</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>🔴 Đã hủy</option>
                    </select>
                </div>

                <div class="md:col-span-2">
                    <select name="payment_status" class="w-full border border-slate-200 rounded-xl py-2.5 px-3 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 cursor-pointer shadow-sm text-slate-600 font-medium">
                        <option value="">Tình trạng TT</option>
                        <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Đã thanh toán</option>
                        <option value="unpaid" {{ request('payment_status') == 'unpaid' ? 'selected' : '' }}>Chưa thanh toán</option>
                        <option value="refunded" {{ request('payment_status') == 'refunded' ? 'selected' : '' }}>Hoàn tiền</option>
                    </select>
                </div>

                <div class="md:col-span-4 flex gap-2 justify-end">
                    <button type="submit" class="px-5 py-2.5 text-sm font-bold text-white bg-slate-900 rounded-xl hover:bg-slate-800 transition-all shadow-lg shadow-slate-900/20 flex items-center">
                        <i class="fa-solid fa-filter mr-2"></i> Lọc đơn
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

    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full whitespace-nowrap text-left">
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
                        <td class="px-6 py-4">
                            <a href="{{ route('admin.orders.show', $order->id) }}" class="inline-flex items-center gap-2 font-mono font-bold text-indigo-600 group-hover:text-indigo-700">
                                <i class="fa-solid fa-hashtag text-xs opacity-50"></i>{{ $order->order_code }}
                            </a>
                        </td>
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
                        <td class="px-6 py-4 text-center">
                            @php
                                $statusConfig = [
                                    'pending' => ['bg' => 'bg-amber-100', 'text' => 'text-amber-700', 'label' => 'Chờ xử lý'],
                                    'processing' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-700', 'label' => 'Đang xử lý'],
                                    'shipping' => ['bg' => 'bg-purple-100', 'text' => 'text-purple-700', 'label' => 'Đang giao'],
                                    'completed' => ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-700', 'label' => 'Hoàn thành'],
                                    'cancelled' => ['bg' => 'bg-rose-100', 'text' => 'text-rose-700', 'label' => 'Đã hủy'],
                                ];
                                $s = $statusConfig[$order->status] ?? ['bg' => 'bg-slate-100', 'text' => 'text-slate-600', 'label' => $order->status];
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold {{ $s['bg'] }} {{ $s['text'] }}">
                                {{ $s['label'] }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($order->payment_status == 'paid')
                                <i class="fa-solid fa-circle-check text-emerald-500 text-lg" title="Đã thanh toán"></i>
                            @elseif($order->payment_status == 'refunded')
                                <i class="fa-solid fa-circle-arrow-left text-rose-500 text-lg" title="Đã hoàn tiền"></i>
                            @else
                                <i class="fa-regular fa-circle text-slate-300 text-lg" title="Chưa thanh toán"></i>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <span class="font-bold text-slate-700">{{ number_format($order->total_amount, 0, ',', '.') }}</span>
                            <span class="text-xs text-slate-400">đ</span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-sm text-slate-600">{{ $order->created_at->format('d/m/Y') }}</span>
                            <span class="block text-xs text-slate-400">{{ $order->created_at->format('H:i') }}</span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <a href="{{ route('admin.orders.show', $order->id) }}" class="text-slate-400 hover:text-indigo-600 transition-colors px-2">
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
        @if($orders->hasPages())
        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
            {{ $orders->links() }}
        </div>
        @endif
    </div>
</div>
@endsection