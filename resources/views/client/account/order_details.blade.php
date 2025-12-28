@extends('client.layouts.app')

@section('title', 'Chi tiết đơn hàng #' . $order->order_code)

@section('content')
@php
    // ==========================================
    // 1. CHUẨN BỊ DỮ LIỆU (PHP SIDE)
    // ==========================================

    // Xử lý địa chỉ an toàn (tránh lỗi null)
    $address = [
        'name'    => '—',
        'phone'   => '—',
        'address' => '—',
    ];

    if (!empty($order->shipping_address)) {
        // Model đã cast 'array', nhưng check lại cho chắc
        $raw = is_array($order->shipping_address) ? $order->shipping_address : json_decode($order->shipping_address, true);
        if (is_array($raw)) {
            $address['name']    = $raw['contact_name'] ?? $raw['name'] ?? '—';
            $address['phone']   = $raw['phone'] ?? '—';
            $address['address'] = $raw['address'] ?? '—';
        }
    } elseif ($order->user) {
        // Fallback nếu shipping_address rỗng (dữ liệu cũ)
        $address['name']  = $order->user->name;
        $address['phone'] = $order->user->phone;
    }

    // Tính toán tiền
    $subtotal = $order->total_amount - ($order->shipping_fee ?? 0) + ($order->discount_amount ?? 0); 
    // (Hoặc dùng logic cũ của bạn tùy cấu trúc DB)

    // ==========================================
    // 2. TỪ ĐIỂN TRẠNG THÁI (FULL)
    // ==========================================
    
    // Map màu sắc (Tailwind)
    $statusColorMap = [
        'pending'    => 'bg-amber-50 text-amber-700 border-amber-200',
        'processing' => 'bg-orange-50 text-orange-700 border-orange-200',
        'confirmed'  => 'bg-blue-50 text-blue-700 border-blue-200',
        'shipping'   => 'bg-indigo-50 text-indigo-700 border-indigo-200',
        'completed'  => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'cancelled'  => 'bg-rose-50 text-rose-700 border-rose-200',
        'failed'     => 'bg-red-50 text-red-700 border-red-200',
        'refunded'   => 'bg-purple-50 text-purple-700 border-purple-200',
        'returned'   => 'bg-gray-100 text-gray-700 border-gray-200', 
    ];
    
    // Map Tiếng Việt
    $statusLabelMap = [
        'pending'    => 'Chờ xử lý',
        'processing' => 'Đang đóng gói',
        'confirmed'  => 'Đã xác nhận',
        'shipping'   => 'Đang giao hàng',
        'completed'  => 'Giao thành công', // Hoặc "Hoàn tất"
        'cancelled'  => 'Đã hủy',
        'failed'     => 'Giao thất bại',
        'refunded'   => 'Đã hoàn tiền',
        'returned'   => 'Đã trả hàng', 
    ];

    // Chuẩn hóa key hiện tại
    $statusKey = strtolower(trim($order->status));
    $currentClass = $statusColorMap[$statusKey] ?? 'bg-slate-50 text-slate-700 border-slate-200';
    $currentLabel = $statusLabelMap[$statusKey] ?? $order->status;
@endphp

<div class="bg-slate-50 min-h-screen py-10">
    <div class="max-w-7xl mx-auto px-4 lg:px-8">

        {{-- ================= HEADER ================= --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
            <a href="{{ route('client.account.orders') }}"
               class="inline-flex items-center gap-2 text-slate-500 hover:text-indigo-600 font-semibold transition group">
                <span class="w-8 h-8 rounded-full bg-white border border-slate-200 flex items-center justify-center shadow-sm group-hover:border-indigo-200">
                    <i class="fa-solid fa-arrow-left text-xs"></i>
                </span>
                Quay lại danh sách
            </a>

            <div class="text-right hidden md:block">
                <p class="text-xs font-bold tracking-widest text-slate-400 uppercase">Order ID</p>
                <p class="text-slate-800 font-mono font-bold">#{{ $order->order_code }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            {{-- ================= LEFT COLUMN (Sản phẩm & Thanh toán) ================= --}}
            <div class="lg:col-span-2 space-y-8">

                {{-- CARD CHÍNH --}}
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                    
                    {{-- Header Card --}}
                    <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div>
                            <h1 class="text-xl font-bold text-slate-900 flex items-center gap-2">
                                Đơn hàng <span class="text-indigo-600">#{{ $order->order_code }}</span>
                            </h1>
                            <p class="text-sm text-slate-500 mt-1">
                                Đặt ngày: {{ $order->created_at->format('H:i d/m/Y') }}
                            </p>
                        </div>

                        {{-- BADGE TRẠNG THÁI (Có ID để JS bắt) --}}
                        <div id="order-status-container">
                            <span id="order-status-badge" 
                                class="inline-block px-4 py-2 rounded-lg border font-bold text-sm shadow-sm transition-all duration-300 {{ $currentClass }}">
                                {{ $currentLabel }}
                            </span>
                        </div>
                    </div>

                    {{-- Danh sách sản phẩm --}}
                    <div class="p-6 space-y-6">
                        @foreach($order->items as $item)
                            @php
                                $variant = $item->productVariant;
                                $product = $variant?->product;
                                // Logic ảnh: Variant -> Product -> Default
                                $imagePath = $variant?->image ?? $product?->image;
                                $imageUrl = $imagePath ? Storage::url($imagePath) : asset('images/no-image.png');
                            @endphp

                            <div class="flex gap-4">
                                <div class="w-20 h-20 flex-shrink-0 rounded-lg bg-slate-100 border border-slate-200 overflow-hidden">
                                    <img src="{{ $imageUrl }}" class="w-full h-full object-cover" alt="Product Image">
                                </div>
                                <div class="flex-1">
                                    <h4 class="font-bold text-slate-800 line-clamp-2">
                                        {{ $item->product_name ?? 'Sản phẩm đã bị xóa' }}
                                    </h4>
                                    <p class="text-sm text-slate-500 mt-1">
                                        Phân loại: <span class="font-medium text-slate-700">{{ $item->product_variant_name ?? '—' }}</span>
                                    </p>
                                    <div class="flex justify-between items-center mt-2">
                                        <span class="text-xs font-bold bg-slate-100 text-slate-600 px-2 py-0.5 rounded">x{{ $item->quantity }}</span>
                                        <span class="font-bold text-indigo-900">{{ number_format($item->price) }}đ</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Phần thanh toán --}}
                    <div class="bg-slate-50 p-6 border-t border-slate-100">
                        <div class="space-y-3 text-sm text-slate-600">
                            <div class="flex justify-between">
                                <span>Tạm tính</span>
                                <span class="font-medium">{{ number_format($subtotal) }}đ</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Phí vận chuyển</span>
                                <span class="font-medium">{{ number_format($order->shipping_fee) }}đ</span>
                            </div>
                            @if($order->discount_amount > 0)
                                <div class="flex justify-between text-green-600">
                                    <span>Giảm giá</span>
                                    <span class="font-medium">-{{ number_format($order->discount_amount) }}đ</span>
                                </div>
                            @endif
                            <div class="flex justify-between text-lg font-black text-slate-900 border-t border-slate-200 pt-3 mt-3">
                                <span>Tổng thanh toán</span>
                                <span class="text-indigo-600">{{ number_format($order->total_amount) }}đ</span>
                            </div>
                        </div>

                        {{-- Phương thức thanh toán --}}
                        <div class="mt-6 pt-4 border-t border-dashed border-slate-300 flex items-center justify-between">
                             <div class="flex items-center gap-2 text-slate-500 text-sm">
                                 <i class="fa-regular fa-credit-card"></i> 
                                 <span class="uppercase font-bold text-slate-700" id="payment-method-text">{{ $order->payment_method }}</span>
                             </div>
                             
                             {{-- BADGE THANH TOÁN (Có ID để JS bắt) --}}
                             @php
                                 $isPaid = $order->payment_status === 'paid';
                                 $paymentClass = $isPaid 
                                     ? 'bg-emerald-100 text-emerald-700' 
                                     : 'bg-slate-200 text-slate-600';
                                 $paymentLabel = match($order->payment_status) {
                                     'paid' => 'Đã thanh toán',
                                     'unpaid' => 'Chưa thanh toán',
                                     'refunded' => 'Đã hoàn tiền',
                                     default => 'Chưa thanh toán'
                                 };
                             @endphp
                             <span id="payment-status-label" class="text-xs px-2.5 py-1 rounded font-bold {{ $paymentClass }}">
                                 @if($isPaid) <i class="fa-solid fa-check mr-1"></i> @endif
                                 {{ $paymentLabel }}
                             </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ================= RIGHT COLUMN (Info & Timeline) ================= --}}
            <div class="space-y-8">

                {{-- THÔNG TIN NHẬN HÀNG --}}
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
                    <h3 class="font-bold text-slate-800 mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-location-dot text-rose-500"></i>
                        Thông tin nhận hàng
                    </h3>
                    <div class="space-y-4 border-l-2 border-slate-100 pl-4 ml-1">
                        <div>
                            <p class="text-xs text-slate-400 font-bold uppercase">Người nhận</p>
                            <p class="font-medium text-slate-700">{{ $address['name'] }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-400 font-bold uppercase">Số điện thoại</p>
                            <p class="font-medium text-slate-700">{{ $address['phone'] }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-400 font-bold uppercase">Địa chỉ</p>
                            <p class="text-slate-600 text-sm leading-relaxed">{{ $address['address'] }}</p>
                        </div>
                    </div>
                </div>

                {{-- TIMELINE (LỊCH SỬ) --}}
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
                    <h3 class="font-bold text-slate-800 mb-6 flex items-center gap-2">
                        <i class="fa-solid fa-clock-rotate-left text-indigo-500"></i>
                        Lịch sử đơn hàng
                    </h3>

                    {{-- CONTAINER CHÍNH CHO TIMELINE --}}
                    {{-- JS sẽ chèn vào đầu div này (afterbegin) --}}
                    <div id="timeline-container" class="relative border-l-2 border-slate-100 ml-3 space-y-8 pb-2">
                        
                        {{-- 1. LOGS TỪ DB (Mới nhất nằm trên) --}}
                        @foreach($order->histories as $history)
                            <div class="pl-6 relative timeline-item group">
                                {{-- Dấu chấm tròn --}}
                                <span class="absolute -left-[9px] top-1.5 w-4 h-4 bg-white border-[3px] border-indigo-600 rounded-full group-hover:scale-110 transition-transform shadow-sm"></span>
                                
                                {{-- Nội dung --}}
                                <p class="font-bold text-slate-800 text-sm">{{ $history->description }}</p>
                                <div class="flex items-center gap-2 mt-1">
                                    <p class="text-xs text-slate-500 font-medium">{{ $history->created_at->format('H:i d/m/Y') }}</p>
                                    <span class="text-[10px] bg-slate-100 text-slate-500 px-1.5 rounded border border-slate-200">
                                        {{ $history->user ? $history->user->name : 'Hệ thống' }}
                                    </span>
                                </div>
                            </div>
                        @endforeach

                        {{-- 2. TRẠNG THÁI KHỞI TẠO (Luôn nằm cuối cùng) --}}
                        <div class="pl-6 relative">
                            <span class="absolute -left-[9px] top-1.5 w-4 h-4 bg-slate-200 rounded-full border-[3px] border-white ring-1 ring-slate-100"></span>
                            <p class="font-semibold text-slate-500 text-sm">Đơn hàng được tạo</p>
                            <p class="text-xs text-slate-400 mt-1">{{ $order->created_at->format('H:i d/m/Y') }}</p>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script type="module">
    // 1. Map Class CSS (Phải giống hệt PHP để đồng bộ)
    const statusClasses = {
        'pending':    'bg-amber-50 text-amber-700 border-amber-200',
        'processing': 'bg-orange-50 text-orange-700 border-orange-200',
        'confirmed':  'bg-blue-50 text-blue-700 border-blue-200',
        'shipping':   'bg-indigo-50 text-indigo-700 border-indigo-200',
        'completed':  'bg-emerald-50 text-emerald-700 border-emerald-200',
        'cancelled':  'bg-rose-50 text-rose-700 border-rose-200',
        'failed':     'bg-red-50 text-red-700 border-red-200',
        'refunded':   'bg-purple-50 text-purple-700 border-purple-200',
        'returned':   'bg-gray-100 text-gray-700 border-gray-200',
    };

    // 2. Map Tiếng Việt
    const statusLabelMap = {
        'pending':    'Chờ xử lý',
        'processing': 'Đang đóng gói',
        'confirmed':  'Đã xác nhận',
        'shipping':   'Đang giao hàng',
        'completed':  'Giao thành công',
        'cancelled':  'Đã hủy',
        'failed':     'Giao thất bại',
        'refunded':   'Đã hoàn tiền',
        'returned':   'Đã trả hàng',
    };

    const orderId = "{{ $order->id }}";
    
    // Bắt đầu lắng nghe
    Echo.private(`orders.${orderId}`)
        .listen('.OrderStatusUpdated', (e) => {
            console.log('🔔 Order Update:', e);

            // ---------------------------------------------
            // A. CẬP NHẬT BADGE TRẠNG THÁI
            // ---------------------------------------------
            if (e.order && e.order.status) {
                const badge = document.getElementById('order-status-badge');
                if (badge) {
                    const statusKey = e.order.status.toLowerCase();
                    
                    // 1. Lấy class và text mới
                    const newClass = statusClasses[statusKey] || 'bg-slate-50 text-slate-700 border-slate-200';
                    const newLabel = statusLabelMap[statusKey] || e.order.status;

                    // 2. Hiệu ứng chuyển đổi
                    badge.style.opacity = '0.5';
                    setTimeout(() => {
                        badge.className = `inline-block px-4 py-2 rounded-lg border font-bold text-sm shadow-sm transition-all duration-300 ${newClass}`;
                        badge.innerText = newLabel;
                        badge.style.opacity = '1';
                    }, 200);
                }
            }

            // ---------------------------------------------
            // B. CẬP NHẬT TRẠNG THÁI THANH TOÁN
            // ---------------------------------------------
            if (e.order && e.order.payment_status) {
                const paymentLabel = document.getElementById('payment-status-label');
                const paymentMethod = document.getElementById('payment-method-text');
                
                // Cập nhật text method nếu có đổi
                if(paymentMethod && e.order.payment_method) {
                    paymentMethod.innerText = e.order.payment_method;
                }

                if (paymentLabel) {
                    const isPaid = e.order.payment_status === 'paid';
                    
                    // Xác định Text tiếng Việt
                    let labelText = 'Chưa thanh toán';
                    if (isPaid) labelText = 'Đã thanh toán';
                    if (e.order.payment_status === 'refunded') labelText = 'Đã hoàn tiền';

                    // Xác định Class màu
                    let labelClass = 'bg-slate-200 text-slate-600';
                    let iconHtml = '';
                    
                    if (isPaid) {
                        labelClass = 'bg-emerald-100 text-emerald-700';
                        iconHtml = '<i class="fa-solid fa-check mr-1"></i>';
                    }

                    // Apply thay đổi
                    paymentLabel.className = `text-xs px-2.5 py-1 rounded font-bold transition-colors duration-500 ${labelClass}`;
                    paymentLabel.innerHTML = `${iconHtml} ${labelText}`;
                }
            }

            // ---------------------------------------------
            // C. CẬP NHẬT TIMELINE
            // ---------------------------------------------
            if (e.history) {
                const container = document.getElementById('timeline-container');
                if (container) {
                    const timeString = e.history.created_at || 'Vừa xong';
                    const userName   = e.history.user_name || 'Hệ thống';

                    // HTML Log mới
                    const newLogHtml = `
                        <div class="pl-6 relative timeline-item group animate-[pulse_1.5s_ease-in-out_1]">
                            <span class="absolute -left-[9px] top-1.5 w-4 h-4 bg-white border-[3px] border-indigo-600 rounded-full shadow-sm"></span>
                            <p class="font-bold text-slate-800 text-sm">
                                ${e.history.description}
                            </p>
                            <div class="flex items-center gap-2 mt-1">
                                <p class="text-xs text-slate-500 font-medium">
                                    ${timeString}
                                </p>
                                <span class="text-[10px] bg-indigo-50 text-indigo-600 px-1.5 rounded border border-indigo-100">
                                    Mới
                                </span>
                            </div>
                        </div>
                    `;

                    // Chèn lên ĐẦU timeline (afterbegin) để log mới nhất nằm trên cùng
                    container.insertAdjacentHTML('afterbegin', newLogHtml);
                }
            }
        });
</script>
@endpush