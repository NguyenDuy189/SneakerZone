@extends('client.layouts.app')
@section('title', 'Lịch sử đơn hàng - Sneaker Zone')

@section('content')
<div class="bg-slate-50 min-h-screen pb-20">
    {{-- Header --}}
    <div class="bg-white border-b border-slate-100 pt-12 pb-20">
        <div class="container mx-auto px-4 text-center">
            <h1 class="text-3xl font-display font-black text-slate-900">Quản lý đơn hàng</h1>
            <p class="text-slate-500 mt-2 max-w-lg mx-auto">Theo dõi trạng thái xử lý và lịch sử mua hàng của bạn.</p>
        </div>
    </div>

    <div class="container mx-auto px-4 -mt-8 relative z-10 max-w-5xl">
        {{-- Navigation Tabs --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-2 max-w-2xl mx-auto flex justify-center mb-10">
            <a href="{{ route('client.account.profile') }}" class="flex-1 py-3 px-6 text-center rounded-lg text-sm font-bold transition-all text-slate-500 hover:text-indigo-600 hover:bg-indigo-50">
                Thông tin cá nhân
            </a>
            <a href="{{ route('client.account.orders') }}" class="flex-1 py-3 px-6 text-center rounded-lg text-sm font-bold transition-all bg-slate-900 text-white shadow-md">
                Lịch sử đơn hàng
            </a>
        </div>

        {{-- Thông báo lỗi/thành công --}}
        @if(session('success'))
            <div class="mb-6 p-4 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center gap-2">
                <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mb-6 p-4 rounded-lg bg-rose-50 text-rose-700 border border-rose-200 flex items-center gap-2">
                <i class="fa-solid fa-circle-exclamation"></i> {{ session('error') }}
            </div>
        @endif

        @if($orders->count() > 0)
            <div class="space-y-6">
                @foreach($orders as $order)
                    @php
                        // 1. CẤU HÌNH TRẠNG THÁI (Bao gồm cả 'processing' và 'canceled')
                        $statusConfig = [
                            // Nhóm chờ xử lý
                            'pending'    => ['label' => 'Chờ xử lý',      'class' => 'bg-amber-100 text-amber-700 border-amber-200', 'icon' => 'fa-clock'],
                            'processing' => ['label' => 'Đang xử lý',     'class' => 'bg-amber-100 text-amber-700 border-amber-200', 'icon' => 'fa-gear'], // Thêm dòng này
                            
                            // Nhóm xác nhận/vận chuyển
                            'confirmed'  => ['label' => 'Đã xác nhận',    'class' => 'bg-blue-100 text-blue-700 border-blue-200',    'icon' => 'fa-clipboard-check'],
                            'shipping'   => ['label' => 'Đang giao hàng', 'class' => 'bg-indigo-100 text-indigo-700 border-indigo-200', 'icon' => 'fa-truck-fast'],
                            'shipped'    => ['label' => 'Đang giao hàng', 'class' => 'bg-indigo-100 text-indigo-700 border-indigo-200', 'icon' => 'fa-truck-fast'],
                            
                            // Nhóm thành công
                            'completed'  => ['label' => 'Giao thành công','class' => 'bg-emerald-100 text-emerald-700 border-emerald-200', 'icon' => 'fa-check-double'],
                            'delivered'  => ['label' => 'Giao thành công','class' => 'bg-emerald-100 text-emerald-700 border-emerald-200', 'icon' => 'fa-check-double'],

                            // Nhóm hủy/lỗi (Thêm 'canceled' 1 chữ L)
                            'cancelled'  => ['label' => 'Đã hủy',         'class' => 'bg-red-100 text-red-700 border-red-200',       'icon' => 'fa-ban'],
                            'canceled'   => ['label' => 'Đã hủy',         'class' => 'bg-red-100 text-red-700 border-red-200',       'icon' => 'fa-ban'], // Thêm dòng này
                            'failed'     => ['label' => 'Giao thất bại',  'class' => 'bg-gray-100 text-gray-700 border-gray-200',    'icon' => 'fa-triangle-exclamation'],
                            'refunded'   => ['label' => 'Đã hoàn tiền',   'class' => 'bg-purple-100 text-purple-700 border-purple-200', 'icon' => 'fa-rotate-left'],
                        ];
                        
                        // Lấy trạng thái hiện tại, nếu không khớp thì fallback về mặc định
                        $currentStatus = $statusConfig[$order->status] ?? [
                            'label' => ucfirst($order->status), 
                            'class' => 'bg-gray-50 text-gray-600 border-gray-200', 
                            'icon' => 'fa-circle'
                        ];

                        // Mapping thanh toán
                        $paymentMethods = [
                            'cod'     => 'Thanh toán khi nhận (COD)',
                            'vnpay'   => 'Ví VNPAY',
                            'momo'    => 'Ví MoMo',
                            'zalopay' => 'Ví ZaloPay',
                            'banking' => 'Chuyển khoản'
                        ];
                        $methodText = $paymentMethods[$order->payment_method] ?? $order->payment_method;
                    @endphp

                    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden hover:shadow-md transition-shadow duration-300">
                        {{-- HEADER --}}
                        <div class="p-5 border-b border-slate-50 bg-slate-50/50 flex flex-col md:flex-row justify-between md:items-center gap-4">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center text-indigo-600 font-black border border-slate-200 shadow-sm">
                                    <i class="fa-solid fa-receipt"></i>
                                </div>
                                <div>
                                    <h3 class="font-bold text-slate-900 text-base flex items-center gap-2">
                                        Đơn hàng <span class="font-mono text-indigo-600">#{{ $order->order_code }}</span>
                                    </h3>
                                    <p class="text-slate-400 text-xs mt-0.5">
                                        {{ $order->created_at->format('d/m/Y - H:i') }}
                                    </p>
                                </div>
                            </div>
                            
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="px-3 py-1.5 rounded-lg border text-xs font-bold flex items-center gap-2 {{ $currentStatus['class'] }}">
                                    <i class="fa-solid {{ $currentStatus['icon'] }}"></i>
                                    {{ $currentStatus['label'] }}
                                </span>

                                @if($order->payment_status == 'paid')
                                    <span class="px-3 py-1.5 rounded-lg border text-xs font-bold bg-emerald-50 text-emerald-600 border-emerald-100 flex items-center gap-1">
                                        <i class="fa-solid fa-check"></i> Đã thanh toán
                                    </span>
                                @else
                                    <span class="px-3 py-1.5 rounded-lg border text-xs font-bold bg-slate-100 text-slate-500 border-slate-200 flex items-center gap-1">
                                        <i class="fa-regular fa-circle"></i> Chưa thanh toán
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- BODY --}}
                        <div class="p-6">
                            <div class="flex flex-col md:flex-row items-center gap-6">
                                {{-- DANH SÁCH ẢNH SẢN PHẨM & SỐ LƯỢNG --}}
                                <div class="flex -space-x-3 overflow-hidden py-1 pl-1">
                                    @foreach($order->items->take(3) as $item)
                                        @php
                                            $imgSrc = asset('images/no-image.png');
                                            if($item->productVariant && $item->productVariant->image) {
                                                $imgSrc = Storage::url($item->productVariant->image);
                                            } elseif($item->productVariant && $item->productVariant->product->image) {
                                                $imgSrc = Storage::url($item->productVariant->product->image);
                                            }
                                        @endphp
                                        <div class="w-16 h-16 rounded-lg border-2 border-white shadow-sm bg-slate-100 overflow-hidden relative group" title="{{ $item->product_name }}">
                                            <img src="{{ $imgSrc }}" class="w-full h-full object-cover">
                                            
                                            {{-- [FIX] HIỂN THỊ SỐ LƯỢNG TRÊN ẢNH --}}
                                            <span class="absolute bottom-0 right-0 bg-slate-900/80 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-tl-md backdrop-blur-sm">
                                                x{{ $item->quantity }}
                                            </span>
                                        </div>
                                    @endforeach
                                    
                                    @if($order->items_count > 3)
                                        <div class="w-16 h-16 rounded-lg border-2 border-white shadow-sm bg-slate-50 flex items-center justify-center text-xs font-bold text-slate-500">
                                            +{{ $order->items_count - 3 }}
                                        </div>
                                    @endif
                                </div>

                                {{-- THÔNG TIN TIỀN & THANH TOÁN --}}
                                <div class="flex-1 w-full md:w-auto border-t md:border-t-0 border-slate-100 pt-4 md:pt-0 mt-4 md:mt-0 grid grid-cols-2 md:block gap-4">
                                    <div>
                                        <p class="text-xs text-slate-500 uppercase tracking-wider font-bold mb-1">Tổng tiền</p>
                                        <p class="text-xl font-black text-indigo-600">
                                            {{ number_format($order->total_amount) }}đ
                                        </p>
                                    </div>
                                    <div class="md:mt-2">
                                        <p class="text-xs text-slate-500 uppercase tracking-wider font-bold mb-1">Thanh toán</p>
                                        <p class="text-sm font-medium text-slate-700">
                                            {{ $methodText }}
                                        </p>
                                    </div>
                                </div>

                                {{-- BUTTON ACTIONS --}}
                                <div class="flex flex-col gap-3 w-full md:w-auto min-w-[160px]">
                                    <a href="{{ route('client.account.order_details', ['code' => $order->id]) }}" 
                                       class="w-full text-center bg-white border border-slate-200 text-slate-700 hover:border-indigo-600 hover:text-indigo-600 font-bold py-2.5 px-4 rounded-lg transition-all shadow-sm flex items-center justify-center gap-2 group">
                                        Xem chi tiết
                                        <i class="fa-solid fa-arrow-right text-xs group-hover:translate-x-1 transition-transform"></i>
                                    </a>

                                    {{-- 
                                        [FIX] NÚT HỦY ĐƠN 
                                        Cho phép hủy nếu trạng thái là 'pending' HOẶC 'processing'
                                    --}}
                                    @if(in_array($order->status, ['pending', 'processing']))
                                        <form action="{{ route('client.account.orders.cancel', $order->id) }}" 
                                              method="POST" 
                                              onsubmit="return confirm('Bạn có chắc chắn muốn hủy đơn hàng này không?');">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" 
                                                class="w-full text-center bg-red-50 border border-red-100 text-red-600 hover:bg-red-100 hover:text-red-700 font-bold py-2.5 px-4 rounded-lg transition-all flex items-center justify-center gap-2">
                                                <i class="fa-solid fa-xmark"></i> Hủy đơn hàng
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
                
                <div class="mt-8">
                    {{ $orders->links() }}
                </div>
            </div>
        @else
            <div class="text-center py-20 bg-white rounded-3xl shadow-sm border border-slate-100">
                <div class="w-24 h-24 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-6 text-slate-300">
                    <i class="fa-solid fa-box-open text-4xl"></i>
                </div>
                <h3 class="text-xl font-bold text-slate-900">Chưa có đơn hàng nào</h3>
                <p class="text-slate-500 mt-2 mb-8">Bạn chưa mua sản phẩm nào.</p>
                <a href="{{ route('client.products.index') }}" class="inline-block bg-indigo-600 text-white font-bold py-3 px-8 rounded-full hover:bg-indigo-700 shadow-lg shadow-indigo-200 transition-all">
                    Đến cửa hàng
                </a>
            </div>
        @endif
    </div>
</div>
@endsection