@extends('admin.layouts.app')

@section('content')
<div class="container mx-auto p-4">

    <h1 class="text-2xl font-bold mb-4">Danh sách đơn giao hàng</h1>

    {{-- Filter đơn hàng --}}
    <form method="GET" class="flex gap-2 mb-4">
        <select name="status" class="border p-2 rounded">
            <option value="">-- Tất cả trạng thái --</option>
            <option value="pending" @selected(request('status')=='pending')>Pending</option>
            <option value="assigned" @selected(request('status')=='assigned')>Assigned</option>
            <option value="picking" @selected(request('status')=='picking')>Picking</option>
            <option value="delivering" @selected(request('status')=='delivering')>Delivering</option>
            <option value="delivered" @selected(request('status')=='delivered')>Delivered</option>
            <option value="failed" @selected(request('status')=='failed')>Failed</option>
            <option value="returned" @selected(request('status')=='returned')>Returned</option>
        </select>

        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Lọc</button>
    </form>

    <table class="w-full border-collapse border">
        <thead>
            <tr class="bg-gray-100">
                <th class="border p-2">#ID</th>
                <th class="border p-2">Tracking</th>
                <th class="border p-2">Khách hàng</th>
                <th class="border p-2">Shipper</th>
                <th class="border p-2">Trạng thái</th>
                <th class="border p-2">Ngày tạo</th>
                <th class="border p-2">Hành động</th>
            </tr>
        </thead>
        <tbody>
            @foreach($list as $item)
            <tr>
                <td class="border p-2">{{ $item->id }}</td>
                <td class="border p-2">{{ $item->tracking_code }}</td>
                <td class="border p-2">{{ $item->order->customer->name ?? '-' }}</td>
                <td class="border p-2">{{ $item->shipper->name ?? '-' }}</td>
                <td class="border p-2 font-semibold" id="status-{{ $item->id }}">{{ $item->status }}</td>
                <td class="border p-2">{{ $item->created_at->format('d/m/Y H:i') }}</td>
                <td class="border p-2">
                    <a href="{{ route('admin.shipping.show', $item->id) }}" class="text-blue-600">Chi tiết</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="mt-4">
        {{ $list->withQueryString()->links() }}
    </div>
</div>

{{-- Realtime update --}}
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

    @foreach($list as $item)
        Echo.channel('shipping.{{ $item->id }}')
            .listen('ShippingStatusUpdated', (e) => {
                const statusEl = document.getElementById('status-{{ $item->id }}');
                if(statusEl){
                    statusEl.innerText = e.shipping.status;
                }
            });
    @endforeach
</script>
@endsection
