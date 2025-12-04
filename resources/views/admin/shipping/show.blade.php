@extends('layouts.admin')

@section('content')
<div class="container mx-auto p-4">

    <h1 class="text-2xl font-bold mb-4">Đơn giao hàng: {{ $shipping->tracking_code }}</h1>

    <div class="grid grid-cols-2 gap-4 mb-6">
        <div>
            <p><strong>Khách hàng:</strong> {{ $shipping->order->customer->name }}</p>
            <p><strong>Số điện thoại:</strong> {{ $shipping->order->customer->phone }}</p>
            <p><strong>Địa chỉ:</strong> {{ $shipping->order->customer->address }}</p>
        </div>
        <div>
            <p><strong>Shipper:</strong> {{ $shipping->shipper->name ?? '-' }}</p>
            <p><strong>Ngày giao dự kiến:</strong> {{ $shipping->expected_delivery_date ?? '-' }}</p>
        </div>
    </div>

    <div class="mb-6">
        <p><strong>Trạng thái hiện tại:</strong> <span id="shipping-status" class="font-semibold">{{ $shipping->status }}</span></p>
        <p><strong>Vị trí hiện tại:</strong> <span id="shipping-location">{{ $shipping->current_location ?? '-' }}</span></p>
        <p><strong>Ghi chú:</strong> <span id="shipping-note">{{ $shipping->note ?? '-' }}</span></p>
    </div>

    <h3 class="text-xl font-semibold mb-2">Lịch sử cập nhật</h3>
    <ul id="shipping-logs" class="border p-4 rounded bg-gray-50">
        @foreach($shipping->logs as $log)
            <li class="mb-1">
                <span class="text-gray-500">{{ $log->created_at->format('H:i d/m/Y') }}</span> - 
                <span class="font-semibold">{{ $log->status }}</span> - 
                <span>{{ $log->description }}</span> 
                (<span class="text-blue-600">{{ $log->user->name ?? 'System' }}</span>)
            </li>
        @endforeach
    </ul>

</div>

{{-- Realtime JS --}}
<script src="https://js.pusher.com/7.2/pusher.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/laravel-echo/1.14.0/echo.iife.js"></script>
<script>
    // Cấu hình Pusher
    Pusher.logToConsole = false;
    window.Echo = new Echo({
        broadcaster: 'pusher',
        key: '{{ env("PUSHER_APP_KEY") }}',
        cluster: '{{ env("PUSHER_APP_CLUSTER") }}',
        forceTLS: true,
        encrypted: true,
    });

    // Lắng nghe kênh đơn hàng theo ID
    Echo.channel('shipping.{{ $shipping->id }}')
        .listen('ShippingStatusUpdated', (e) => {
            console.log('Realtime event received:', e);

            // Cập nhật trạng thái, vị trí, note
            document.getElementById('shipping-status').innerText = e.shipping.status;
            document.getElementById('shipping-location').innerText = e.shipping.current_location ?? '-';
            document.getElementById('shipping-note').innerText = e.shipping.note ?? '-';

            // Thêm log mới vào danh sách
            const logs = document.getElementById('shipping-logs');
            const li = document.createElement('li');
            li.classList.add('mb-1');
            const updatedAt = new Date(e.shipping.updated_at).toLocaleString('vi-VN');
            const note = e.shipping.note ?? '-';
            li.innerHTML = `<span class="text-gray-500">${updatedAt}</span> - 
                            <span class="font-semibold">${e.shipping.status}</span> - 
                            <span>${note}</span> (<span class="text-blue-600">Hệ thống</span>)`;
            logs.appendChild(li);

            // Scroll xuống dưới cùng
            logs.scrollTop = logs.scrollHeight;
        });
</script>
@endsection
