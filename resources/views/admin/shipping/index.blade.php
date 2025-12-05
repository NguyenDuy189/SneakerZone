@extends('admin.layouts.app')

@section('title', 'Quản lý giao hàng')
@section('header', 'Danh sách đơn giao hàng')

@section('content')
<div class="container px-6 mx-auto pb-10">

    <!-- Header Tools -->
    <div class="flex flex-col md:flex-row justify-between items-center my-6 gap-4">
        <h2 class="text-2xl font-bold text-slate-800">Tất cả đơn giao hàng</h2>
        <div class="flex gap-3">
            <a href="{{ route('admin.shipping.trash') }}" class="flex items-center px-4 py-2.5 text-sm font-medium text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-all shadow-sm">
                <i class="fa-solid fa-trash-can mr-2"></i> Thùng rác
            </a>
        </div>
    </div>

    <!-- Alert Success -->
    @if(session('success'))
        <div class="p-4 mb-6 text-sm text-emerald-700 bg-emerald-50 rounded-lg border border-emerald-200 shadow-sm flex items-center animate-fade-in-down">
            <i class="fa-solid fa-circle-check mr-2 text-lg"></i> {{ session('success') }}
        </div>
    @endif

    <!-- FILTER BAR -->
    <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-200 mb-6">
        <form action="{{ route('admin.shipping.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-12 gap-4">
            <!-- Tìm kiếm theo mã đơn -->
            <div class="md:col-span-4 relative">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 transform -translate-y-1/2 text-slate-400"></i>
                <input type="text" name="keyword" value="{{ request('keyword') }}" 
                    placeholder="Tìm theo mã đơn, tên khách hàng..." 
                    class="pl-10 pr-4 py-2.5 w-full border border-slate-300 rounded-lg focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 text-sm shadow-sm">
            </div>

            <!-- Lọc Trạng thái -->
            <div class="md:col-span-3">
                <select name="status" class="w-full border border-slate-300 rounded-lg py-2.5 px-3 text-sm focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 bg-white cursor-pointer shadow-sm">
                    <option value="">Trạng thái</option>
                    @foreach(\App\Models\ShippingOrder::STATUS_LIST as $key => $label)
                        <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Buttons -->
            <div class="md:col-span-2 flex gap-2">
                <button type="submit" class="flex-1 px-4 py-2.5 text-sm font-bold text-white bg-slate-800 rounded-lg hover:bg-slate-900 transition-colors shadow-sm">
                    <i class="fa-solid fa-filter mr-1"></i> Lọc
                </button>
                @if(request()->hasAny(['keyword','status']))
                    <a href="{{ route('admin.shipping.index') }}" class="px-3 py-2.5 text-slate-500 bg-slate-100 border border-slate-300 rounded-lg hover:bg-slate-200 transition-colors" title="Xóa bộ lọc">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- DATA TABLE -->
    <div class="w-full overflow-hidden rounded-xl shadow-md border border-slate-200 bg-white">
        <div class="w-full overflow-x-auto">
            <table class="w-full whitespace-no-wrap text-left">
                <thead>
                    <tr class="text-xs font-bold tracking-wider text-slate-500 uppercase border-b border-slate-200 bg-slate-50">
                        <th class="px-4 py-4 w-24">Mã đơn</th>
                        <th class="px-4 py-4">Khách hàng</th>
                        <th class="px-4 py-4">Shipper</th>
                        <th class="px-4 py-4">Ngày tạo</th>
                        <th class="px-4 py-4 text-center">Trạng thái</th>
                        <th class="px-4 py-4 text-center w-32">Hành động</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100" id="shipping-list">
                    @forelse($shippings as $shipping)
                        <tr class="hover:bg-slate-50 transition-colors group" id="shipping-{{ $shipping->id }}">
                            <td class="px-4 py-3 font-mono text-slate-700">{{ $shipping->tracking_code }}</td>
                            <td class="px-4 py-3">{{ $shipping->order->user?->full_name ?? '-' }}</td>
                            <td class="px-4 py-3">
                                @if($shipping->shipper)
                                    <span class="font-medium text-indigo-600">{{ $shipping->shipper->full_name }}</span>
                                    <div class="text-xs text-slate-400">{{ $shipping->shipper->phone }}</div>
                                @else
                                    <span class="text-slate-400">Chưa gán</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">{{ $shipping->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-3 text-center">
                                @php
                                    $statusColors = [
                                        'pending' => 'bg-slate-100 text-slate-600',
                                        'assigned' => 'bg-indigo-100 text-indigo-700',
                                        'picking' => 'bg-yellow-100 text-yellow-700',
                                        'delivering' => 'bg-blue-100 text-blue-700',
                                        'delivered' => 'bg-emerald-100 text-emerald-700',
                                        'failed' => 'bg-rose-100 text-rose-700',
                                        'returned' => 'bg-rose-200 text-rose-800'
                                    ];
                                @endphp
                                <span class="inline-flex items-center justify-center px-2.5 py-1 text-xs font-bold rounded-full border {{ $statusColors[$shipping->status] ?? 'bg-slate-100 text-slate-600' }}">
                                    {{ \App\Models\ShippingOrder::STATUS_LIST[$shipping->status] ?? $shipping->status }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('admin.shipping.show', $shipping->id) }}" class="p-2 text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition-colors border border-indigo-100 shadow-sm" title="Chi tiết">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mb-3">
                                        <i class="fa-solid fa-truck-fast text-3xl text-slate-300"></i>
                                    </div>
                                    <p class="font-medium text-slate-500">Chưa có đơn giao hàng nào.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-slate-200 bg-slate-50">
            {{ $shippings->links() }}
        </div>
    </div>
</div>

<!-- Realtime update -->
<script src="https://js.pusher.com/7.2/pusher.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/laravel-echo/1.14.0/echo.iife.js"></script>
<script>
    Pusher.logToConsole = false;

    window.Echo = new Echo({
        broadcaster: 'pusher',
        key: '{{ env("PUSHER_APP_KEY") }}',
        cluster: '{{ env("PUSHER_APP_CLUSTER") }}',
        forceTLS: true,
        encrypted: true
    });

    Echo.channel('shipping')
        .listen('.shipping.updated', (e) => {
            const row = document.getElementById('shipping-' + e.id);
            if(row){
                // Cập nhật shipper
                const shipperCell = row.querySelector('td:nth-child(3)');
                if(e.shipper){
                    shipperCell.innerHTML = `<span class="font-medium text-indigo-600">${e.shipper.full_name}</span>
                                             <div class="text-xs text-slate-400">${e.shipper.phone}</div>`;
                }else{
                    shipperCell.innerHTML = `<span class="text-slate-400">Chưa gán</span>`;
                }

                // Cập nhật trạng thái
                const statusCell = row.querySelector('td:nth-child(5) span');
                const statusColors = {
                    'pending': 'bg-slate-100 text-slate-600',
                    'assigned': 'bg-indigo-100 text-indigo-700',
                    'picking': 'bg-yellow-100 text-yellow-700',
                    'delivering': 'bg-blue-100 text-blue-700',
                    'delivered': 'bg-emerald-100 text-emerald-700',
                    'failed': 'bg-rose-100 text-rose-700',
                    'returned': 'bg-rose-200 text-rose-800'
                };
                statusCell.className = `inline-flex items-center justify-center px-2.5 py-1 text-xs font-bold rounded-full border ${statusColors[e.status] ?? 'bg-slate-100 text-slate-600'}`;
                statusCell.textContent = e.status_label;
            }
        });
</script>

<style>
    .animate-fade-in-down { animation: fadeInDown 0.5s ease-out; }
    @keyframes fadeInDown {
        from { opacity: 0; transform: translate3d(0, -20px, 0); }
        to { opacity: 1; transform: translate3d(0, 0, 0); }
    }
</style>
@endsection
