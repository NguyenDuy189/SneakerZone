@extends('client.layouts.app')
@section('title', 'Lịch sử đơn hàng - Sneaker Zone')

@section('content')
<div class="bg-slate-50 min-h-screen pb-20">
    
    {{-- 1. HEADER --}}
    <div class="bg-white border-b border-slate-100 pt-12 pb-20">
        <div class="container mx-auto px-4 text-center">
            <h1 class="text-3xl font-display font-black text-slate-900 uppercase tracking-tight">Quản lý đơn hàng</h1>
            <p class="text-slate-500 mt-2 max-w-lg mx-auto">Theo dõi trạng thái xử lý và lịch sử mua hàng của bạn.</p>
        </div>
    </div>

    <div class="container mx-auto px-4 -mt-8 relative z-10 max-w-5xl">
        
        {{-- 2. NAVIGATION TABS --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-1.5 max-w-xl mx-auto flex justify-center mb-10">
            <a href="{{ route('client.account.profile') }}" class="flex-1 py-3 px-6 text-center rounded-lg text-sm font-bold transition-all text-slate-500 hover:text-indigo-600 hover:bg-slate-50">
                Thông tin cá nhân
            </a>
            <span class="flex-1 py-3 px-6 text-center rounded-lg text-sm font-bold bg-slate-900 text-white shadow-md cursor-default">
                Lịch sử đơn hàng
            </span>
        </div>

        {{-- 3. ALERTS --}}
        @if(session('success'))
            <div class="mb-6 p-4 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center gap-3 shadow-sm animate-fade-in-down">
                <div class="bg-emerald-100 p-2 rounded-full"><i class="fa-solid fa-check"></i></div>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
        @endif
        @if(session('error'))
            <div class="mb-6 p-4 rounded-xl bg-rose-50 text-rose-700 border border-rose-200 flex items-center gap-3 shadow-sm animate-fade-in-down">
                <div class="bg-rose-100 p-2 rounded-full"><i class="fa-solid fa-triangle-exclamation"></i></div>
                <span class="font-medium">{{ session('error') }}</span>
            </div>
        @endif

        {{-- 4. CẤU HÌNH PHP --}}
        @php
            $statusConfig = [
                'pending'    => ['label' => 'Chờ xác nhận', 'class' => 'bg-amber-100 text-amber-700 border-amber-200', 'icon' => 'fa-clock'],
                'processing' => ['label' => 'Đang xử lý',   'class' => 'bg-blue-100 text-blue-700 border-blue-200',   'icon' => 'fa-gear'],
                'shipping'   => ['label' => 'Đang giao',    'class' => 'bg-indigo-100 text-indigo-700 border-indigo-200', 'icon' => 'fa-truck-fast'],
                'shipped'    => ['label' => 'Đang giao',    'class' => 'bg-indigo-100 text-indigo-700 border-indigo-200', 'icon' => 'fa-truck-fast'],
                'completed'  => ['label' => 'Hoàn thành',   'class' => 'bg-emerald-100 text-emerald-700 border-emerald-200', 'icon' => 'fa-check-double'],
                'cancelled'  => ['label' => 'Đã hủy',       'class' => 'bg-red-100 text-red-700 border-red-200',       'icon' => 'fa-ban'],
                'canceled'   => ['label' => 'Đã hủy',       'class' => 'bg-red-100 text-red-700 border-red-200',       'icon' => 'fa-ban'],
            ];
            
            $paymentMethods = [
                'cod' => 'Thanh toán khi nhận (COD)', 
                'vnpay' => 'Ví VNPAY', 
                'momo' => 'Ví MoMo', 
                'banking' => 'Chuyển khoản'
            ];
        @endphp

        {{-- 5. DANH SÁCH ĐƠN HÀNG --}}
        @if(isset($orders) && $orders->count() > 0)
            <div class="space-y-6">
                @foreach($orders as $order)
                    @php
                        $currentStatus = $statusConfig[$order->status] ?? ['label' => $order->status, 'class' => 'bg-gray-100 text-gray-600', 'icon' => 'fa-circle'];
                        $methodText = $paymentMethods[$order->payment_method] ?? $order->payment_method;
                    @endphp

                    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden hover:shadow-md transition-shadow duration-300">
                        {{-- CARD HEADER --}}
                        <div class="p-5 border-b border-slate-50 bg-slate-50/50 flex flex-col md:flex-row justify-between md:items-center gap-4">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-xl bg-white flex items-center justify-center text-indigo-600 font-black border border-slate-200 shadow-sm">
                                    <i class="fa-solid fa-receipt text-lg"></i>
                                </div>
                                <div>
                                    <h3 class="font-bold text-slate-900 text-base flex items-center gap-2">
                                        Đơn hàng <span class="font-mono text-indigo-600">#{{ $order->code ?? $order->order_code ?? $order->id }}</span>
                                    </h3>
                                    <p class="text-slate-400 text-xs mt-0.5 flex items-center gap-1">
                                        <i class="fa-regular fa-calendar"></i> {{ $order->created_at->format('d/m/Y - H:i') }}
                                    </p>
                                </div>
                            </div>
                            
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="px-3 py-1.5 rounded-lg border text-xs font-bold flex items-center gap-2 {{ $currentStatus['class'] }}">
                                    <i class="fa-solid {{ $currentStatus['icon'] }}"></i> {{ $currentStatus['label'] }}
                                </span>
                            </div>
                        </div>

                        {{-- CARD BODY --}}
                        <div class="p-6">
                            <div class="flex flex-col md:flex-row items-center gap-6">
                                {{-- Ảnh sản phẩm --}}
                                <div class="flex -space-x-3 overflow-hidden py-1 pl-1">
                                    @foreach($order->items->take(3) as $item)
                                        @php
                                            $imgSrc = asset('images/no-image.png');
                                            // Logic lấy ảnh an toàn
                                            if($item->variant && $item->variant->image) $imgSrc = Storage::url($item->variant->image);
                                            elseif($item->product && $item->product->image) $imgSrc = Storage::url($item->product->image);
                                            elseif(isset($item->productVariant) && $item->productVariant->image) $imgSrc = Storage::url($item->productVariant->image); // Support old model
                                        @endphp
                                        <div class="w-16 h-16 rounded-lg border-2 border-white shadow-sm bg-slate-100 overflow-hidden relative" title="{{ $item->product_name }}">
                                            <img src="{{ $imgSrc }}" class="w-full h-full object-cover">
                                            <span class="absolute bottom-0 right-0 bg-slate-900/80 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-tl-md backdrop-blur-sm">
                                                x{{ $item->quantity }}
                                            </span>
                                        </div>
                                    @endforeach
                                    
                                    @php $count = $order->items_count ?? $order->items->count(); @endphp
                                    @if($count > 3)
                                        <div class="w-16 h-16 rounded-lg border-2 border-white shadow-sm bg-slate-50 flex items-center justify-center text-xs font-bold text-slate-500">
                                            +{{ $count - 3 }}
                                        </div>
                                    @endif
                                </div>

                                {{-- Thông tin tiền --}}
                                <div class="flex-1 w-full md:w-auto border-t md:border-t-0 border-slate-100 pt-4 md:pt-0 mt-4 md:mt-0 grid grid-cols-2 md:block gap-4">
                                    <div>
                                        <p class="text-[10px] text-slate-400 uppercase tracking-wider font-bold mb-1">Tổng tiền</p>
                                        <p class="text-xl font-black text-slate-800">
                                            {{ number_format($order->total_money ?? $order->total_amount ?? 0, 0, ',', '.') }}đ
                                        </p>
                                    </div>
                                    <div class="md:mt-3">
                                        <p class="text-[10px] text-slate-400 uppercase tracking-wider font-bold mb-1">Thanh toán</p>
                                        <p class="text-sm font-bold text-slate-600">{{ $methodText }}</p>
                                    </div>
                                </div>

                                {{-- BUTTONS --}}
                                <div class="flex flex-col gap-3 w-full md:w-auto min-w-[170px]">
                                    {{-- Nút Xem Chi Tiết --}}
                                    <a href="{{ route('client.account.order_details', $order->id) }}" 
                                       class="w-full text-center bg-white border border-slate-200 text-slate-700 hover:border-indigo-600 hover:text-indigo-600 font-bold py-2.5 px-4 rounded-xl transition-all shadow-sm flex items-center justify-center gap-2 group text-sm">
                                        Xem chi tiết
                                        <i class="fa-solid fa-arrow-right text-xs group-hover:translate-x-1 transition-transform"></i>
                                    </a>

                                    {{-- Nút Hủy (Modal) --}}
                                    @if(in_array($order->status, ['pending', 'processing']))
                                        <button type="button"
                                            onclick="openCancelModal('{{ route('client.account.orders.cancel', $order->id) }}')" 
                                            class="w-full text-center bg-red-50 border border-red-100 text-red-600 hover:bg-red-100 hover:text-red-700 font-bold py-2.5 px-4 rounded-xl transition-all flex items-center justify-center gap-2 text-sm">
                                            <i class="fa-solid fa-xmark"></i> Hủy đơn hàng
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
                
                {{-- Pagination --}}
                <div class="mt-8">
                    {{ $orders->links() }}
                </div>
            </div>
        @else
            {{-- EMPTY STATE --}}
            <div class="text-center py-24 bg-white rounded-3xl shadow-sm border border-slate-100">
                <div class="w-24 h-24 bg-indigo-50 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fa-solid fa-box-open text-4xl text-indigo-400"></i>
                </div>
                <h3 class="text-xl font-bold text-slate-900">Chưa có đơn hàng nào</h3>
                <p class="text-slate-500 mt-2 mb-8 max-w-sm mx-auto">Giỏ hàng của bạn đang trống trơn? Hãy khám phá sản phẩm của chúng tôi ngay.</p>
                <a href="{{ url('/') }}" class="inline-flex items-center gap-2 bg-indigo-600 text-white font-bold py-3 px-8 rounded-xl hover:bg-indigo-700 shadow-lg shadow-indigo-200 transition-all transform hover:-translate-y-1">
                    <i class="fa-solid fa-cart-shopping"></i> Đến cửa hàng
                </a>
            </div>
        @endif
    </div>
</div>

{{-- 6. MODAL HỦY ĐƠN --}}
<div id="cancelModal" class="relative z-[9999] hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" onclick="closeCancelModal()"></div>
    <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-gray-100">
                <form id="cancelForm" method="POST" action="">
                    @csrf
                    @method('PATCH')
                    
                    <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                <i class="fa-solid fa-triangle-exclamation text-red-600 text-lg"></i>
                            </div>
                            <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                                <h3 class="text-lg font-bold leading-6 text-gray-900">Xác nhận hủy đơn</h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500 mb-4">Vui lòng cho chúng tôi biết lý do bạn muốn hủy đơn hàng này:</p>
                                    <div class="mb-3">
                                        <select id="reason_option" name="reason_option" onchange="toggleOtherReason(this)" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm py-2.5 bg-slate-50">
                                            <option value="">-- Chọn lý do --</option>
                                            <option value="Đổi ý, không muốn mua nữa">Đổi ý, không muốn mua nữa</option>
                                            <option value="Tìm thấy nơi khác giá tốt hơn">Tìm thấy nơi khác giá tốt hơn</option>
                                            <option value="Đặt nhầm sản phẩm">Đặt nhầm sản phẩm</option>
                                            <option value="Khác">Lý do khác...</option>
                                        </select>
                                    </div>
                                    <div id="otherReasonContainer" class="hidden">
                                        <textarea name="other_reason" id="other_reason" rows="3" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm p-3 bg-slate-50 placeholder-slate-400" placeholder="Nhập chi tiết lý do..."></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 gap-2">
                        <button type="submit" class="inline-flex w-full justify-center rounded-lg bg-red-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-red-500 sm:ml-3 sm:w-auto transition-all">
                            Xác nhận hủy
                        </button>
                        <button type="button" onclick="closeCancelModal()" class="mt-3 inline-flex w-full justify-center rounded-lg bg-white px-4 py-2.5 text-sm font-bold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto transition-all">
                            Quay lại
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- 7. JS XỬ LÝ --}}
<script>
    function openCancelModal(actionUrl) {
        const form = document.getElementById('cancelForm');
        form.action = actionUrl;
        document.getElementById('cancelModal').classList.remove('hidden');
        document.getElementById('reason_option').value = "";
        document.getElementById('otherReasonContainer').classList.add('hidden');
    }
    function closeCancelModal() {
        document.getElementById('cancelModal').classList.add('hidden');
    }
    function toggleOtherReason(select) {
        const div = document.getElementById('otherReasonContainer');
        if (select.value === 'Khác') {
            div.classList.remove('hidden');
            setTimeout(() => document.getElementById('other_reason').focus(), 100);
        } else {
            div.classList.add('hidden');
        }
    }
</script>
@endsection