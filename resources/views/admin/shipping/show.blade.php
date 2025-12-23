@extends('admin.layouts.app')

@section('title', 'Chi tiết đơn giao hàng')
@section('header', 'Đơn giao hàng #' . $shipping->tracking_code)

@section('content')
<div class="container px-6 mx-auto pb-10">

    <!-- Alert success -->
    @if(session('success'))
    <div class="p-4 mb-6 text-sm text-emerald-700 bg-emerald-50 rounded-lg border border-emerald-200 shadow-sm flex items-center animate-fade-in-down">
        <i class="fa-solid fa-circle-check mr-2 text-lg"></i> {{ session('success') }}
    </div>
    @endif

    <!-- ORDER INFO -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Customer -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
            <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center">
                <i class="fa-solid fa-user mr-2 text-indigo-500"></i> Thông tin khách hàng
            </h3>
            @if($shipping->order && $shipping->order->user)
                <p><strong>Họ tên:</strong> {{ $shipping->order->user->full_name }}</p>
                <p><strong>Điện thoại:</strong> {{ $shipping->order->user->phone }}</p>
                <p><strong>Địa chỉ:</strong> {{ $shipping->order->user->address ?? '-' }}</p>
            @else
                <p class="text-slate-400">Thông tin khách hàng không có sẵn.</p>
            @endif
        </div>

        <!-- Shipper & Delivery -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
            <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center">
                <i class="fa-solid fa-truck mr-2 text-indigo-500"></i> Thông tin giao hàng
            </h3>
            <p><strong>Shipper:</strong> <span id="shipper-name">{{ $shipping->shipper?->full_name ?? '-' }}</span></p>
            <p><strong>Điện thoại Shipper:</strong> <span id="shipper-phone">{{ $shipping->shipper?->phone ?? '-' }}</span></p>
            <p><strong>Ngày giao dự kiến:</strong> 
                {{ $shipping->expected_delivery_date ? \Carbon\Carbon::parse($shipping->expected_delivery_date)->format('H:i d/m/Y') : '-' }}
            </p>
            <p><strong>Mã tracking:</strong> {{ $shipping->tracking_code }}</p>
        </div>
    </div>

    <!-- SHIPPING STATUS -->
    <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 mb-6">
        <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center">
            <i class="fa-solid fa-info-circle mr-2 text-indigo-500"></i> Trạng thái giao hàng
        </h3>
        <p><strong>Trạng thái hiện tại:</strong> 
            <span id="shipping-status" class="font-semibold text-indigo-600">
                {{ \App\Models\ShippingOrder::STATUS_LABELS[$shipping->status] ?? $shipping->status }}
            </span>
        </p>
        <p><strong>Vị trí hiện tại:</strong> <span id="current-location">{{ $shipping->current_location ?? '-' }}</span></p>
        <p><strong>Ghi chú:</strong> <span id="note">{{ $shipping->note ?? '-' }}</span></p>
    </div>

    <!-- LOGS -->
    <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
        <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center">
            <i class="fa-solid fa-list-ul mr-2 text-indigo-500"></i> Lịch sử cập nhật
        </h3>
        <div id="shipping-logs" class="divide-y divide-slate-100 max-h-96 overflow-y-auto">
            @forelse($shipping->logs as $log)
                <div class="py-2">
                    <span class="text-gray-500">{{ \Carbon\Carbon::parse($log->created_at)->format('H:i d/m/Y') }}</span> - 
                    <span class="font-semibold">{{ \App\Models\ShippingOrder::STATUS_LABELS[$log->status] ?? $log->status }}</span> - 
                    <span>{{ $log->description ?? '-' }}</span> 
                    (<span class="text-blue-600">{{ $log->user?->full_name ?? 'Hệ thống' }}</span>)
                </div>
            @empty
                <div class="py-2 text-slate-400">Chưa có cập nhật nào.</div>
            @endforelse
        </div>
    </div>
</div>

<!-- Realtime JS -->
<script src="https://js.pusher.com/7.2/pusher.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/laravel-echo/1.14.0/echo.iife.js"></script>
<script>
    Pusher.logToConsole = false;

    window.Echo = new Echo({
        broadcaster: 'pusher',
        key: '{{ env("PUSHER_APP_KEY") }}',
        cluster: '{{ env("PUSHER_APP_CLUSTER") }}',
        forceTLS: true,
    });

    Echo.private('shipping.{{ $shipping->id }}')
        .listen('.shipping.updated', (e) => {
            // Cập nhật trạng thái
            document.getElementById('shipping-status').innerText = e.status_label;
            document.getElementById('current-location').innerText = e.current_location ?? '-';
            document.getElementById('note').innerText = e.note ?? '-';

            // Cập nhật shipper info
            if(e.shipper){
                document.getElementById('shipper-name').innerText = e.shipper.full_name;
                document.getElementById('shipper-phone').innerText = e.shipper.phone;
            }

            // Cập nhật logs
            const logContainer = document.getElementById('shipping-logs');
            logContainer.innerHTML = '';
            if(e.logs && e.logs.length){
                e.logs.forEach(log => {
                    const div = document.createElement('div');
                    div.className = 'py-2';
                    div.innerHTML = `<span class="text-gray-500">${log.created_at}</span> - <span class="font-semibold">${log.status_label}</span> - <span>${log.description ?? '-'}</span> (<span class="text-blue-600">${log.user_name}</span>)`;
                    logContainer.appendChild(div);
                });
                logContainer.scrollTop = logContainer.scrollHeight;
            }
        });
</script>

<style>
    .animate-fade-in-down {
        animation: fadeInDown 0.5s ease-out;
    }
    @keyframes fadeInDown {
        from { opacity: 0; transform: translate3d(0, -20px, 0); }
        to { opacity: 1; transform: translate3d(0, 0, 0); }
    }
</style>
@endsection
    