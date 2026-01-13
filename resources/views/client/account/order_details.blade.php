@extends('client.layouts.app')

@section('title', 'Chi tiết đơn hàng #' . $order->order_code)

@section('content')
@php
    // ==========================================
    // 1. CHUẨN BỊ DỮ LIỆU & LOGIC HIỂN THỊ
    // ==========================================

    // A. Xử lý địa chỉ an toàn (Phòng trường hợp data cũ hoặc lỗi JSON)
    $address = [
        'name'    => '—',
        'phone'   => '—',
        'address' => '—',
    ];

    if (!empty($order->shipping_address)) {
        // Model Order đã cast 'array', nhưng check lại cho chắc chắn
        $raw = is_array($order->shipping_address) ? $order->shipping_address : json_decode($order->shipping_address, true);
        if (is_array($raw)) {
            $address['name']    = $raw['contact_name'] ?? $raw['name'] ?? '—';
            $address['phone']   = $raw['phone'] ?? '—';
            $address['address'] = $raw['address'] ?? '—';
        }
    } elseif ($order->user) {
        // Fallback: Lấy từ User nếu đơn hàng thiếu địa chỉ
        $address['name']  = $order->user->name;
        $address['phone'] = $order->user->phone;
    }

    // B. Map màu sắc trạng thái (Tailwind CSS - Màu Pastel dịu mắt)
    $statusColorMap = [
        'pending'    => 'bg-amber-50 text-amber-700 border-amber-200',    // Vàng
        'confirmed'  => 'bg-blue-50 text-blue-700 border-blue-200',       // Xanh dương
        'processing' => 'bg-indigo-50 text-indigo-700 border-indigo-200', // Tím nhạt
        'shipping'   => 'bg-cyan-50 text-cyan-700 border-cyan-200',       // Xanh ngọc
        'completed'  => 'bg-emerald-50 text-emerald-700 border-emerald-200', // Xanh lá
        'cancelled'  => 'bg-rose-50 text-rose-700 border-rose-200',       // Đỏ hồng
        'refunded'   => 'bg-purple-50 text-purple-700 border-purple-200', // Tím đậm
        'failed'     => 'bg-red-50 text-red-700 border-red-200',          // Đỏ
        'returned'   => 'bg-gray-100 text-gray-700 border-gray-200',      // Xám
    ];
    
    // C. Map Tên trạng thái sang Tiếng Việt
    $statusLabelMap = [
        'pending'    => 'Chờ xử lý',
        'confirmed'  => 'Đã xác nhận',
        'processing' => 'Đang đóng gói',
        'shipping'   => 'Đang vận chuyển',
        'completed'  => 'Giao thành công',
        'cancelled'  => 'Đã hủy',
        'refunded'   => 'Đã hoàn tiền',
        'failed'     => 'Giao thất bại',
        'returned'   => 'Đã trả hàng',
    ];

    // Xác định trạng thái hiện tại
    $statusKey    = strtolower(trim($order->status));
    $currentClass = $statusColorMap[$statusKey] ?? 'bg-slate-50 text-slate-700 border-slate-200';
    $currentLabel = $statusLabelMap[$statusKey] ?? ucfirst($order->status);
@endphp

<div class="bg-slate-50/50 min-h-screen py-10 font-sans">
    <div class="max-w-7xl mx-auto px-4 lg:px-8">

        {{-- ================= HEADER ================= --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
            <a href="{{ route('client.account.orders.index') }}"
               class="inline-flex items-center gap-2 text-slate-500 hover:text-indigo-600 font-semibold transition group">
                <span class="w-8 h-8 rounded-full bg-white border border-slate-200 flex items-center justify-center shadow-sm group-hover:border-indigo-200 transition-colors">
                    <i class="fa-solid fa-arrow-left text-xs"></i>
                </span>
                Quay lại danh sách
            </a>

            <div class="text-right hidden md:block">
                <p class="text-[10px] font-bold tracking-widest text-slate-400 uppercase">Mã đơn hàng</p>
                <p class="text-slate-800 font-mono font-bold text-lg">#{{ $order->order_code }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            {{-- ================= CỘT TRÁI (Sản phẩm & Thanh toán) - Chiếm 2 phần ================= --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- CARD 1: THÔNG TIN CHÍNH & DANH SÁCH SẢN PHẨM --}}
                <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
                    
                    {{-- Header Card --}}
                    <div class="p-6 border-b border-slate-50 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div>
                            <h1 class="text-xl font-bold text-slate-900 flex items-center gap-2">
                                Đơn hàng <span class="text-indigo-600">#{{ $order->order_code }}</span>
                            </h1>
                            <p class="text-sm text-slate-500 mt-1">
                                Đặt ngày: {{ $order->created_at->format('H:i - d/m/Y') }}
                            </p>
                        </div>

                        {{-- BADGE TRẠNG THÁI (Có ID để JS update Realtime) --}}
                        <div id="order-status-container">
                            <span id="order-status-badge" 
                                class="inline-block px-4 py-2 rounded-lg border font-bold text-sm shadow-sm transition-all duration-300 {{ $currentClass }}">
                                {{ $currentLabel }}
                            </span>
                        </div>
                    </div>

                    {{-- Body Card: Danh sách sản phẩm --}}
                    <div class="p-6 space-y-6">
                        @foreach($order->items as $item)
                            @php
                                // A. KHỞI TẠO QUAN HỆ
                                $variant = $item->variant; 
                                $product = $variant ? $variant->product : $item->product; // Fallback lấy product nếu variant bị xóa

                                // B. XỬ LÝ ẢNH (Logic: Snapshot -> Variant -> Product)
                                $imgRaw = $item->thumbnail; // 1. Snapshot lưu cứng
                                
                                if (empty($imgRaw) && $variant) {
                                    $imgRaw = $variant->image; // 2. Ảnh variant
                                }
                                if (empty($imgRaw) && $product) {
                                    $imgRaw = $product->thumbnail; // 3. Ảnh product (thường tên cột là thumbnail)
                                }

                                // Xử lý đường dẫn đầy đủ
                                if (!empty($imgRaw) && !Str::startsWith($imgRaw, ['http', 'https'])) {
                                    $imgUrl = asset('storage/' . $imgRaw);
                                } else {
                                    $imgUrl = $imgRaw ?: asset('img/no-image.png');
                                }

                                // C. LẤY THÔNG TIN TÊN & LINK
                                $name = $item->product_name ?? ($product ? $product->name : 'Sản phẩm không xác định');
                                $slug = $product ? $product->slug : '#';
                                $sku  = $item->sku ?? ($variant ? $variant->sku : '---');

                                // D. XỬ LÝ BIẾN THỂ (SIZE / COLOR)
                                $size  = null;
                                $color = null;
                                if ($variant && $variant->attributeValues) {
                                    foreach ($variant->attributeValues as $av) {
                                        if ($av->attribute) {
                                            if (strtolower($av->attribute->code) == 'size')  $size  = $av->value;
                                            if (strtolower($av->attribute->code) == 'color') $color = $av->value;
                                        }
                                    }
                                }
                            @endphp

                            <div class="flex gap-4 py-2 group">
                                {{-- Ảnh Sản Phẩm --}}
                                <a href="{{ $product ? route('client.products.show', $slug) : '#' }}" class="w-20 h-20 flex-shrink-0 rounded-lg bg-slate-100 border border-slate-200 overflow-hidden relative">
                                    <img src="{{ $imgUrl }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" alt="{{ $name }}" onerror="this.src='{{ asset('img/no-image.png') }}'">
                                </a>

                                {{-- Thông tin chi tiết --}}
                                <div class="flex-1 flex flex-col justify-between">
                                    <div>
                                        <h4 class="font-bold text-slate-800 line-clamp-2 text-sm md:text-base">
                                            <a href="{{ $product ? route('client.products.show', $slug) : '#' }}" class="hover:text-indigo-600 transition-colors">
                                                {{ $name }}
                                            </a>
                                        </h4>
                                        
                                        <div class="flex flex-wrap items-center gap-2 mt-1.5">
                                            {{-- Badge SKU --}}
                                            @if($sku !== '---')
                                                <span class="text-[10px] font-mono bg-slate-100 text-slate-500 px-1.5 py-0.5 rounded border border-slate-200">
                                                    {{ $sku }}
                                                </span>
                                            @endif

                                            {{-- Badge Biến thể (Size/Color) --}}
                                            @if($size || $color)
                                                <span class="text-[10px] font-bold bg-indigo-50 text-indigo-600 px-2 py-0.5 rounded border border-indigo-100 flex items-center gap-1">
                                                    @if($size) 
                                                        <span>Size: {{ $size }}</span>
                                                    @endif
                                                    
                                                    @if($size && $color) 
                                                        <span class="text-indigo-300">|</span> 
                                                    @endif
                                                    
                                                    @if($color) 
                                                        <span>Màu: {{ $color }}</span>
                                                    @endif
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="flex justify-between items-end mt-2">
                                        <span class="text-xs font-bold bg-slate-50 text-slate-600 px-2.5 py-1 rounded-full border border-slate-100">
                                            x{{ $item->quantity }}
                                        </span>
                                        <span class="font-bold text-indigo-900">
                                            {{ number_format($item->price) }}đ
                                        </span>
                                    </div>
                                </div>
                            </div>

                            @if(!$loop->last)
                                <div class="border-b border-slate-50 border-dashed my-4"></div>
                            @endif
                        @endforeach
                    </div>

                    {{-- Footer Card: Tổng tiền --}}
                    <div class="bg-slate-50/50 p-6 border-t border-slate-100 space-y-3">
                        <div class="flex justify-between text-sm text-slate-600">
                            <span>Tạm tính</span>
                            <span class="font-medium">{{ number_format($order->subtotal ?? ($order->total_amount - $order->shipping_fee + $order->discount_amount)) }}đ</span>
                        </div>
                        <div class="flex justify-between text-sm text-slate-600">
                            <span>Phí vận chuyển</span>
                            <span class="font-medium">{{ number_format($order->shipping_fee) }}đ</span>
                        </div>
                        @if($order->discount_amount > 0)
                            <div class="flex justify-between text-sm text-emerald-600">
                                <span>Giảm giá (Voucher)</span>
                                <span class="font-bold">-{{ number_format($order->discount_amount) }}đ</span>
                            </div>
                        @endif
                        
                        <div class="border-t border-slate-200 border-dashed my-3 pt-3 flex justify-between items-center">
                            <span class="font-bold text-slate-800">Tổng thanh toán</span>
                            <span class="text-xl font-black text-indigo-700">{{ number_format($order->total_amount) }}đ</span>
                        </div>
                    </div>
                </div>

                {{-- CARD 2: CÁC NÚT HÀNH ĐỘNG --}}
                @if(in_array($order->status, ['pending', 'unpaid']) || in_array($order->status, ['completed', 'cancelled']))
                <div class="flex justify-end gap-3">
                    {{-- Nút Hủy (Chỉ hiện khi đơn mới) --}}
                    @if(in_array($order->status, ['pending', 'processing'])) {{-- Hoặc 'unpaid' tùy logic của bạn --}}
                        <button type="button"
                            {{-- SỬA TÊN ROUTE TẠI ĐÂY CHO KHỚP VỚI WEB.PHP --}}
                            onclick="openCancelModal('{{ route('client.account.orders.cancel', $order->id) }}')"
                            class="w-full text-center bg-red-50 border border-red-100 text-red-600 hover:bg-red-100 hover:text-red-700 font-bold py-2.5 px-4 rounded-xl transition-all flex items-center justify-center gap-2 text-sm">
                            <i class="fa-solid fa-xmark"></i> Hủy đơn hàng
                        </button>
                    @endif

                    {{-- Nút Mua Lại (Hiện khi đã xong hoặc hủy) --}}
                    @if(in_array($order->status, ['completed', 'cancelled']))
                        <form action="{{ route('client.carts.add') }}" method="POST">
                            @csrf
                            {{-- Mẹo: Mua lại sản phẩm đầu tiên hoặc chuyển hướng --}}
                            <input type="hidden" name="product_id" value="{{ $order->items->first()->variant->product_id ?? '' }}">
                             <button type="button" onclick="window.location.href='{{ route('client.products.show', $order->items->first()->variant->product->slug ?? '#') }}'" 
                                class="px-5 py-2.5 rounded-lg border border-slate-300 text-slate-700 font-bold text-sm hover:bg-white hover:text-indigo-600 transition-colors shadow-sm bg-white">
                                <i class="fa-solid fa-rotate-right mr-1"></i> Mua lại
                            </button>
                        </form>
                    @endif

                    {{-- Nút Thanh toán ngay (Nếu chưa trả tiền) --}}
                    @if($order->payment_status === 'unpaid' && $order->payment_method !== 'cod' && $order->status !== 'cancelled')
                        <a href="#" class="px-5 py-2.5 rounded-lg bg-indigo-600 text-white font-bold text-sm shadow-lg shadow-indigo-200 hover:bg-indigo-700 transition-all hover:-translate-y-0.5">
                            Thanh toán ngay
                        </a>
                    @endif
                </div>
                @endif

            </div>

            {{-- ================= CỘT PHẢI (Thông tin & Timeline) - Chiếm 1 phần ================= --}}
            <div class="space-y-6">

                {{-- INFO 1: THÔNG TIN NHẬN HÀNG --}}
                <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
                    <h3 class="font-bold text-slate-800 mb-4 flex items-center gap-2 text-sm uppercase tracking-wider">
                        <i class="fa-solid fa-location-dot text-rose-500"></i>
                        Thông tin nhận hàng
                    </h3>
                    <div class="space-y-4">
                        <div class="flex gap-3">
                            <div class="w-8 h-8 rounded-full bg-slate-50 flex items-center justify-center text-slate-400 flex-shrink-0">
                                <i class="fa-regular fa-user text-xs"></i>
                            </div>
                            <div>
                                <p class="text-[10px] text-slate-400 font-bold uppercase mb-0.5">Người nhận</p>
                                <p class="font-medium text-slate-700 text-sm">{{ $address['name'] }}</p>
                            </div>
                        </div>
                        <div class="flex gap-3">
                            <div class="w-8 h-8 rounded-full bg-slate-50 flex items-center justify-center text-slate-400 flex-shrink-0">
                                <i class="fa-solid fa-phone text-xs"></i>
                            </div>
                            <div>
                                <p class="text-[10px] text-slate-400 font-bold uppercase mb-0.5">Số điện thoại</p>
                                <p class="font-medium text-slate-700 text-sm">{{ $address['phone'] }}</p>
                            </div>
                        </div>
                        <div class="flex gap-3">
                            <div class="w-8 h-8 rounded-full bg-slate-50 flex items-center justify-center text-slate-400 flex-shrink-0">
                                <i class="fa-solid fa-map-location-dot text-xs"></i>
                            </div>
                            <div>
                                <p class="text-[10px] text-slate-400 font-bold uppercase mb-0.5">Địa chỉ</p>
                                <p class="text-sm text-slate-600 leading-relaxed">{{ $address['address'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- INFO 2: THANH TOÁN --}}
                <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
                    <h3 class="font-bold text-slate-800 mb-4 flex items-center gap-2 text-sm uppercase tracking-wider">
                        <i class="fa-regular fa-credit-card text-indigo-500"></i>
                        Thanh toán
                    </h3>
                    <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg border border-slate-100">
                        <span class="font-bold text-sm text-slate-700 uppercase" id="payment-method-text">
                            {{ $order->payment_method == 'cod' ? 'Tiền mặt (COD)' : $order->payment_method }}
                        </span>
                        
                        {{-- Badge Payment Status --}}
                        @php
                             $isPaid = $order->payment_status === 'paid';
                             $payClass = $isPaid ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-600';
                             $payText  = $isPaid ? 'Đã thanh toán' : 'Chưa thanh toán';
                             if($order->payment_status === 'refunded') {
                                 $payClass = 'bg-purple-100 text-purple-700';
                                 $payText = 'Đã hoàn tiền';
                             }
                        @endphp
                        <span id="payment-status-label" class="text-[10px] font-bold px-2 py-1 rounded {{ $payClass }}">
                            @if($isPaid) <i class="fa-solid fa-check mr-1"></i> @endif
                            {{ $payText }}
                        </span>
                    </div>
                </div>

                {{-- INFO 3: TIMELINE (LỊCH SỬ) --}}
                <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
                    <h3 class="font-bold text-slate-800 mb-6 flex items-center gap-2 text-sm uppercase tracking-wider">
                        <i class="fa-solid fa-clock-rotate-left text-indigo-500"></i>
                        Lịch sử đơn hàng
                    </h3>

                    <div id="timeline-container" class="relative border-l-2 border-slate-100 ml-3 space-y-8 pb-2">
                        @foreach($timeline as $history)
                            <div class="pl-6 relative group">
                                {{-- Dấu chấm --}}
                                <span class="absolute -left-[9px] top-1.5 w-4 h-4 bg-white border-[3px] border-indigo-600 rounded-full shadow-sm group-hover:scale-110 transition-transform"></span>
                                
                                {{-- Nội dung --}}
                                <p class="font-bold text-slate-800 text-sm">{{ $history->description ?? 'Cập nhật trạng thái' }}</p>
                                <div class="flex items-center gap-2 mt-1">
                                    <p class="text-xs text-slate-500 font-medium">{{ $history->created_at->format('H:i d/m/Y') }}</p>
                                    <span class="text-[10px] bg-slate-100 text-slate-500 px-1.5 rounded border border-slate-200">
                                        {{ $history->user ? $history->user->name : 'Hệ thống' }}
                                    </span>
                                </div>
                            </div>
                        @endforeach

                        {{-- Mốc khởi tạo --}}
                        <div class="pl-6 relative">
                            <span class="absolute -left-[9px] top-1.5 w-4 h-4 bg-slate-200 rounded-full border-[3px] border-white ring-1 ring-slate-100"></span>
                            <p class="font-medium text-slate-500 text-sm">Đơn hàng được tạo</p>
                            <p class="text-xs text-slate-400 mt-1">{{ $order->created_at->format('H:i d/m/Y') }}</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

{{-- ================= MODAL HỦY ĐƠN HÀNG ================= --}}
<div id="cancel-modal" class="fixed inset-0 z-[999] hidden">
    {{-- Lớp nền mờ --}}
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="closeCancelModal()"></div>

    {{-- Nội dung Modal --}}
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-md bg-white rounded-2xl shadow-2xl p-6 transform transition-all scale-100">
        
        <div class="text-center mb-6">
            <div class="w-12 h-12 bg-red-100 text-red-600 rounded-full flex items-center justify-center mx-auto mb-3">
                <i class="fa-solid fa-triangle-exclamation text-xl"></i>
            </div>
            <h3 class="text-lg font-bold text-slate-800">Xác nhận hủy đơn hàng?</h3>
            <p class="text-sm text-slate-500 mt-1">Hành động này không thể hoàn tác. Vui lòng cho chúng tôi biết lý do bạn hủy đơn.</p>
        </div>

        {{-- Form gửi request Hủy --}}
        <form id="cancel-form" action="" method="POST">
            @csrf
            @method('PATCH')

            <div class="space-y-3 mb-6 text-left">
                <label class="flex items-center gap-3 p-3 border border-slate-200 rounded-xl cursor-pointer hover:bg-slate-50 transition-colors">
                    <input type="radio" name="reason_option" value="Thay đổi ý định" class="w-4 h-4 text-red-600 focus:ring-red-500" checked>
                    <span class="text-sm font-medium text-slate-700">Tôi muốn thay đổi sản phẩm/địa chỉ</span>
                </label>
                
                <label class="flex items-center gap-3 p-3 border border-slate-200 rounded-xl cursor-pointer hover:bg-slate-50 transition-colors">
                    <input type="radio" name="reason_option" value="Tìm thấy giá rẻ hơn" class="w-4 h-4 text-red-600 focus:ring-red-500">
                    <span class="text-sm font-medium text-slate-700">Tôi tìm thấy nơi khác rẻ hơn</span>
                </label>

                <label class="flex items-center gap-3 p-3 border border-slate-200 rounded-xl cursor-pointer hover:bg-slate-50 transition-colors">
                    <input type="radio" name="reason_option" value="Khác" class="w-4 h-4 text-red-600 focus:ring-red-500" id="reason-other-radio">
                    <span class="text-sm font-medium text-slate-700">Lý do khác</span>
                </label>

                {{-- Input lý do khác (Hiện khi chọn radio Khác) --}}
                <textarea name="other_reason" id="other-reason-input" rows="2" 
                    class="w-full border-slate-200 rounded-lg text-sm focus:border-red-500 focus:ring-red-500 hidden mt-2" 
                    placeholder="Nhập lý do của bạn..."></textarea>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <button type="button" onclick="closeCancelModal()" class="w-full py-2.5 rounded-xl border border-slate-200 text-slate-600 font-bold hover:bg-slate-50 transition-colors">
                    Đóng
                </button>
                <button type="submit" class="w-full py-2.5 rounded-xl bg-red-600 text-white font-bold hover:bg-red-700 shadow-lg shadow-red-200 transition-all">
                    Xác nhận Hủy
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
{{-- Script xử lý Realtime (Chỉ chạy khi bạn đã cài Pusher & Laravel Echo) --}}

{{-- 1. SCRIPT XỬ LÝ MODAL (Dùng script thường để onclick gọi được) --}}
<script>
    // Hàm mở Modal
    function openCancelModal(actionUrl) {
        // 1. Lấy thẻ modal và form
        const modal = document.getElementById('cancel-modal');
        const form = document.getElementById('cancel-form');

        // 2. Gán đường dẫn action cho form
        if(form) form.action = actionUrl;

        // 3. Hiển thị modal
        if(modal) modal.classList.remove('hidden');
    }

    // Hàm đóng Modal
    function closeCancelModal() {
        const modal = document.getElementById('cancel-modal');
        if(modal) modal.classList.add('hidden');
    }

    // Xử lý hiện ô nhập text khi chọn "Lý do khác"
    document.addEventListener("DOMContentLoaded", function() {
        const radioButtons = document.querySelectorAll('input[name="reason_option"]');
        if(radioButtons) {
            radioButtons.forEach(radio => {
                radio.addEventListener('change', function() {
                    const otherInput = document.getElementById('other-reason-input');
                    if (otherInput) {
                        if (this.value === 'Khác') {
                            otherInput.classList.remove('hidden');
                            otherInput.required = true;
                        } else {
                            otherInput.classList.add('hidden');
                            otherInput.required = false;
                        }
                    }
                });
            });
        }
    });
</script>


{{-- 2. SCRIPT REALTIME (Giữ nguyên module nếu dùng Vite/Echo) --}}
<script type="module">
    // Cấu hình màu sắc & text cho JS
    const statusClasses = {
        'pending':    'bg-amber-50 text-amber-700 border-amber-200',
        'confirmed':  'bg-blue-50 text-blue-700 border-blue-200',
        'processing': 'bg-indigo-50 text-indigo-700 border-indigo-200',
        'shipping':   'bg-cyan-50 text-cyan-700 border-cyan-200',
        'completed':  'bg-emerald-50 text-emerald-700 border-emerald-200',
        'cancelled':  'bg-rose-50 text-rose-700 border-rose-200',
        'refunded':   'bg-purple-50 text-purple-700 border-purple-200',
        'failed':     'bg-red-50 text-red-700 border-red-200',
        'returned':   'bg-gray-100 text-gray-700 border-gray-200',
    };

    const statusLabelMap = {
        'pending':    'Chờ xử lý',
        'confirmed':  'Đã xác nhận',
        'processing': 'Đang đóng gói',
        'shipping':   'Đang vận chuyển',
        'completed':  'Giao thành công',
        'cancelled':  'Đã hủy',
        'refunded':   'Đã hoàn tiền',
        'failed':     'Giao thất bại',
        'returned':   'Đã trả hàng',
    };

    const orderId = "{{ $order->id }}";

    // Lắng nghe sự kiện từ Laravel Echo
    if (typeof Echo !== 'undefined') {
        Echo.private(`orders.${orderId}`)
            .listen('.OrderStatusUpdated', (e) => {
                console.log('🔔 Order Update:', e);

                // A. CẬP NHẬT BADGE TRẠNG THÁI
                if (e.order && e.order.status) {
                    const badge = document.getElementById('order-status-badge');
                    if (badge) {
                        const statusKey = e.order.status.toLowerCase();
                        const newClass = statusClasses[statusKey] || 'bg-slate-50 text-slate-700 border-slate-200';
                        const newLabel = statusLabelMap[statusKey] || e.order.status;

                        badge.style.opacity = '0.5';
                        setTimeout(() => {
                            badge.className = `inline-block px-4 py-2 rounded-lg border font-bold text-sm shadow-sm transition-all duration-300 ${newClass}`;
                            badge.innerText = newLabel;
                            badge.style.opacity = '1';
                        }, 200);
                    }
                }

                // B. CẬP NHẬT THANH TOÁN
                if (e.order && e.order.payment_status) {
                    const paymentLabel = document.getElementById('payment-status-label');
                    if (paymentLabel) {
                        const isPaid = e.order.payment_status === 'paid';
                        let labelText = isPaid ? 'Đã thanh toán' : 'Chưa thanh toán';
                        if (e.order.payment_status === 'refunded') labelText = 'Đã hoàn tiền';
                        
                        let labelClass = isPaid ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-600';
                        if (e.order.payment_status === 'refunded') labelClass = 'bg-purple-100 text-purple-700';

                        let iconHtml = isPaid ? '<i class="fa-solid fa-check mr-1"></i>' : '';

                        paymentLabel.className = `text-[10px] font-bold px-2 py-1 rounded transition-colors duration-500 ${labelClass}`;
                        paymentLabel.innerHTML = `${iconHtml} ${labelText}`;
                    }
                }

                // C. THÊM LOG MỚI VÀO TIMELINE
                if (e.history) {
                    const container = document.getElementById('timeline-container');
                    if (container) {
                        const timeString = e.history.created_at || 'Vừa xong';
                        
                        const newLogHtml = `
                            <div class="pl-6 relative group animate-[pulse_1.5s_ease-in-out_1]">
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
                        container.insertAdjacentHTML('afterbegin', newLogHtml);
                    }
                }
            });
    }
</script>
@endpush