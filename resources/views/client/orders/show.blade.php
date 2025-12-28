@extends('client.layouts.app')

@section('title', 'Chi tiết đơn hàng ' . $order->code)

@section('content')
<div class="container mx-auto px-4 py-8 min-h-screen bg-[#F8F9FA]">
    
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-black uppercase tracking-tight flex items-center gap-2">
            <a href="{{ route('client.orders.index') }}" class="text-slate-400 hover:text-indigo-600 transition-colors mr-2">
                <i class="fa-solid fa-arrow-left text-xl"></i>
            </a>
            Chi tiết đơn hàng <span class="text-indigo-600">#{{ $order->code }}</span>
        </h1>
        
        {{-- Nút hủy đơn (chỉ hiện khi pending) --}}
        @if($order->status == 'pending')
            <form action="{{ route('client.orders.cancel', $order->code) }}" method="POST" onsubmit="return confirm('Bạn chắc chắn muốn hủy đơn này?');">
                @csrf
                <button type="submit" class="bg-white border border-rose-200 text-rose-500 hover:bg-rose-50 px-4 py-2 rounded-lg font-bold text-sm transition-colors">
                    Hủy đơn hàng
                </button>
            </form>
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        {{-- DANH SÁCH SẢN PHẨM (2 Cột) --}}
        <div class="lg:col-span-2 space-y-4">
            <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
                <div class="p-4 bg-slate-50 border-b border-slate-100 font-bold text-slate-700">
                    Sản phẩm
                </div>
                <div class="p-4 space-y-4">
                    @foreach($order->items as $item)
                        {{-- Lấy tên sp: $item->variant->product->name --}}
                        {{-- Lấy giá: number_format($item->price) --}}                        @php 
                            $product = $item->productVariant->product; 
                            $variant = $item->productVariant;
                        @endphp
                        <div class="flex gap-4">
                            {{-- Ảnh --}}
                            <div class="w-20 h-20 rounded-lg border border-slate-200 overflow-hidden flex-shrink-0">
                                <img src="{{ asset('storage/' . $product->image) }}" class="w-full h-full object-cover">
                            </div>
                            
                            {{-- Thông tin --}}
                            <div class="flex-1">
                                <h3 class="font-bold text-slate-900">{{ $product->name }}</h3>
                                <div class="text-xs text-slate-500 mt-1">
                                    @foreach($variant->attributeValues as $val)
                                        <span class="bg-slate-100 px-1.5 py-0.5 rounded mr-1">{{ $val->value }}</span>
                                    @endforeach
                                </div>
                                <div class="mt-2 text-sm">
                                    <span class="font-bold">{{ number_format($item->price) }}đ</span> 
                                    <span class="text-slate-400">x</span> 
                                    <span class="font-bold text-slate-900">{{ $item->quantity }}</span>
                                </div>
                            </div>
                            
                            {{-- Tổng dòng --}}
                            <div class="text-right font-bold text-indigo-600">
                                {{ number_format($item->total) }}đ
                            </div>
                        </div>
                        @if(!$loop->last) <hr class="border-dashed border-slate-100"> @endif
                    @endforeach
                </div>
            </div>
        </div>

        {{-- THÔNG TIN ĐƠN HÀNG (1 Cột) --}}
        <div class="lg:col-span-1 space-y-6">
            
            {{-- Thông tin người nhận --}}
            <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
                <h3 class="font-bold text-slate-900 mb-4 uppercase text-xs tracking-wider border-b pb-2">Thông tin giao hàng</h3>
                <div class="space-y-3 text-sm">
                    <div>
                        <span class="block text-slate-400 text-xs uppercase">Người nhận</span>
                        <span class="font-bold text-slate-800">{{ $order->customer_name }}</span>
                    </div>
                    <div>
                        <span class="block text-slate-400 text-xs uppercase">Số điện thoại</span>
                        <span class="font-bold text-slate-800">{{ $order->customer_phone }}</span>
                    </div>
                    <div>
                        <span class="block text-slate-400 text-xs uppercase">Địa chỉ</span>
                        <span class="font-bold text-slate-800">{{ $order->shipping_address }}</span>
                    </div>
                    @if($order->note)
                    <div>
                        <span class="block text-slate-400 text-xs uppercase">Ghi chú</span>
                        <span class="text-slate-800 italic">"{{ $order->note }}"</span>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Tổng tiền --}}
            <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
                <h3 class="font-bold text-slate-900 mb-4 uppercase text-xs tracking-wider border-b pb-2">Thanh toán</h3>
                
                <div class="flex justify-between text-sm mb-2">
                    <span class="text-slate-500">Phương thức</span>
                    <span class="font-bold uppercase">{{ $order->payment_method }}</span>
                </div>
                
                <div class="flex justify-between text-sm mb-2">
                    <span class="text-slate-500">Trạng thái</span>
                    <span class="font-bold {{ $order->payment_status == 'paid' ? 'text-emerald-600' : 'text-yellow-600' }}">
                        {{ $order->payment_status == 'paid' ? 'Đã thanh toán' : 'Chờ thanh toán' }}
                    </span>
                </div>

                <hr class="border-dashed border-slate-200 my-4">

                <div class="flex justify-between items-center">
                    <span class="font-black text-slate-900 uppercase">Tổng cộng</span>
                    <span class="font-black text-xl text-indigo-600">{{ number_format($order->total_amount) }}đ</span>
                </div>
            </div>
        </div>

    </div>

    {{-- HIỂN THỊ LỊCH SỬ ĐƠN HÀNG --}}
    <div class="mt-8 bg-white p-6 rounded-lg shadow-sm border">
        <h3 class="font-bold text-lg mb-4">Lịch sử đơn hàng</h3>
        <ul class="border-l-2 border-indigo-200 ml-3 space-y-4">
            @foreach($order->history as $his)
                <li class="relative pl-6">
                    <span class="absolute -left-[9px] top-1 w-4 h-4 bg-indigo-500 rounded-full border-2 border-white"></span>
                    <p class="text-sm text-slate-500">{{ $his->created_at->format('H:i d/m/Y') }}</p>
                    <p class="font-bold text-slate-800">{{ $his->action }}</p>
                    <p class="text-sm text-slate-600">{{ $his->description }}</p>
                </li>
            @endforeach
        </ul>
    </div>
</div>
@endsection

<script type="module">
    // Map trạng thái phải khớp chính xác với Database
    const statusMap = {
        'pending':    { label: 'Chờ xử lý', color: 'amber' },
        'processing': { label: 'Đang đóng gói', color: 'orange' }, // Sửa label cho khớp ngữ cảnh
        'shipping':   { label: 'Đang giao hàng', color: 'indigo' },
        'completed':  { label: 'Hoàn tất', color: 'emerald' },
        'cancelled':  { label: 'Đã hủy', color: 'rose' },
        'returned':   { label: 'Trả hàng', color: 'slate' },
        'paid':       { label: 'Đã thanh toán', color: 'emerald' },
        'unpaid':     { label: 'Chưa thanh toán', color: 'slate' }
    };

    const orderId = "{{ $order->id }}";
    
    Echo.private(`orders.${orderId}`)
        .listen('OrderStatusUpdated', (e) => {
            console.log('🔥 Realtime Event Received:', e);

            // 1. CẬP NHẬT BADGE TRẠNG THÁI (Status)
            if (e.order && e.order.status) {
                const config = statusMap[e.order.status] || { label: e.order.status, color: 'gray' };
                const badge = document.getElementById('order-status-badge');
                
                if (badge) {
                    // Xóa hết các class màu cũ (để tránh bị trùng class màu)
                    badge.className = `px-6 py-2.5 rounded-xl bg-${config.color}-50 text-${config.color}-700 font-bold text-sm border border-${config.color}-100 transition-all duration-300`;
                    badge.innerText = config.label;
                    
                    // Hiệu ứng nhấp nháy nhẹ để báo hiệu có thay đổi
                    badge.classList.add('ring-2', 'ring-offset-2', `ring-${config.color}-200`);
                    setTimeout(() => {
                        badge.classList.remove('ring-2', 'ring-offset-2', `ring-${config.color}-200`);
                    }, 1000);
                }
            }

            // 2. CẬP NHẬT TIMELINE (Lịch sử)
            if (e.history) {
                const container = document.getElementById('timeline-container');
                
                // Xác định màu dot
                const isCompleted = e.order.status === 'completed';
                const dotColor = isCompleted ? 'bg-emerald-500 ring-emerald-50' : 'bg-indigo-600 ring-indigo-50';
                
                // HTML mới - Lưu ý: e.history.created_at đã được format từ Server ở Bước 1
                const newLogHtml = `
                    <div class="pl-6 relative timeline-item animate-fade-in-down">
                        <span class="absolute -left-[7px] top-1.5 w-4 h-4 ${dotColor} rounded-full ring-4 transition-all duration-500"></span>
                        
                        <p class="font-bold text-slate-800">
                            ${e.history.description}
                        </p>
                        
                        <p class="text-xs text-slate-500 mt-1 font-medium">
                            ${e.history.created_at} 
                        </p>
                    </div>
                `;
                
                // Chèn vào đầu danh sách
                container.insertAdjacentHTML('afterbegin', newLogHtml);
            }
        });
</script>

<style>
    /* Thêm CSS animation đơn giản để thấy hiệu ứng mượt mà */
    @keyframes fadeInDown {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-in-down {
        animation: fadeInDown 0.5s ease-out forwards;
    }
</style>