@extends('admin.layouts.app')

@section('title', 'Gán Shipper cho đơn #' . $order->id)
@section('header', 'Gán Shipper')

@section('content')
<div class="container px-6 mx-auto pb-10">

    <!-- Breadcrumb -->
    <div class="mb-6">
        <a href="{{ route('admin.shipping.show', $order->shipping?->id ?? 0) }}" class="inline-flex items-center text-sm font-medium text-slate-500 hover:text-indigo-600 transition-colors">
            <i class="fa-solid fa-arrow-left mr-2"></i> Quay lại chi tiết đơn
        </a>
    </div>

    <!-- Alert Errors -->
    @if ($errors->any())
        <div class="mb-6 p-4 bg-rose-50 border-l-4 border-rose-500 text-rose-800 rounded-lg shadow-sm">
            <div class="flex items-center mb-1">
                <i class="fa-solid fa-circle-exclamation mr-2"></i>
                <p class="font-bold">Vui lòng kiểm tra các lỗi:</p>
            </div>
            <ul class="list-disc list-inside text-sm pl-6">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.shipping.assign', $order->id) }}" method="POST">
        @csrf
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            <!-- Order Info -->
            <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
                <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center">
                    <i class="fa-solid fa-box mr-2 text-indigo-500"></i> Thông tin đơn hàng
                </h3>
                <p><strong>Mã đơn hàng:</strong> {{ $order->id }}</p>
                <p><strong>Khách hàng:</strong> {{ $order->user?->full_name ?? '-' }}</p>
                <p><strong>Điện thoại:</strong> {{ $order->user?->phone ?? '-' }}</p>
                <p><strong>Địa chỉ:</strong> {{ $order->user?->address ?? '-' }}</p>
            </div>

            <!-- Assign Shipper -->
            <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
                <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center">
                    <i class="fa-solid fa-truck mr-2 text-indigo-500"></i> Gán Shipper
                </h3>

                <div class="mb-4">
                    <label class="block text-sm font-bold text-slate-700 mb-2">Chọn Shipper <span class="text-rose-500">*</span></label>
                    <select name="shipper_id" class="w-full border border-slate-300 rounded-lg py-2.5 px-3 text-sm focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 bg-white cursor-pointer shadow-sm" required>
                        <option value=""> Chọn Shipper </option>
                        @foreach($shippers as $shipper)
                            <option value="{{ $shipper->id }}" {{ old('shipper_id') == $shipper->id ? 'selected' : '' }}>
                                {{ $shipper->full_name }} ({{ $shipper->phone }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-bold text-slate-700 mb-2">Ngày giao dự kiến</label>
                    <input type="date" name="expected_delivery_date" value="{{ old('expected_delivery_date') }}"
                        class="w-full border border-slate-300 rounded-lg px-4 py-2.5 text-sm focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm">
                </div>

                <div class="flex gap-3">
                    <button type="submit" class="flex-1 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-lg shadow-md hover:shadow-lg transition-all transform active:scale-[0.98] flex items-center justify-center">
                        <i class="fa-solid fa-truck-fast mr-2"></i> Gán Shipper
                    </button>
                    <a href="{{ route('admin.shipping.show', $order->shipping?->id ?? 0) }}" class="flex-1 py-3 bg-white border border-slate-300 text-slate-700 font-bold rounded-lg hover:bg-slate-50 text-center transition-colors shadow-sm">
                        Hủy
                    </a>
                </div>
            </div>

        </div>
    </form>
</div>

<!-- Realtime update script -->
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

    // Nếu có trang show đang mở, lắng nghe event realtime
    const shippingId = {{ $order->shipping?->id ?? 'null' }};
    if(shippingId){
        Echo.private('shipping.' + shippingId)
            .listen('.shipping.updated', (e) => {
                console.log('Realtime update assign:', e);

                // Cập nhật shipper trên trang show nếu đang mở
                const shipperName = document.getElementById('shipper-name');
                const shipperPhone = document.getElementById('shipper-phone');
                if(shipperName) shipperName.innerText = e.shipper.full_name;
                if(shipperPhone) shipperPhone.innerText = e.shipper.phone;
            });
    }
</script>

<style>
    .animate-fade-in-down { animation: fadeInDown 0.5s ease-out; }
    @keyframes fadeInDown {
        from { opacity: 0; transform: translate3d(0, -20px, 0); }
        to { opacity: 1; transform: translate3d(0, 0, 0); }
    }
</style>
@endsection
