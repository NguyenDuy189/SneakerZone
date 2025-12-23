@extends('admin.layouts.app')

@section('title', 'Đơn hàng #' . $order->order_code)

@section('content')
<div class="container px-6 mx-auto mb-20 fade-in">
    
    {{-- 1. HEADER & ACTIONS --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8 pt-6">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.orders.index') }}" class="w-10 h-10 flex items-center justify-center rounded-xl bg-white border border-slate-200 text-slate-500 hover:bg-slate-50 hover:text-indigo-600 transition-all shadow-sm">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-2xl font-extrabold text-slate-800 flex items-center gap-3">
                    #{{ $order->order_code }}
                    
                    {{-- Status Badge --}}
                    @php
                        $statusLabels = [
                            'pending'    => 'Chờ xử lý',
                            'processing' => 'Đang đóng gói',
                            'shipping'   => 'Đang giao hàng',
                            'completed'  => 'Hoàn thành',
                            'cancelled'  => 'Đã hủy',
                            'returned'   => 'Trả hàng'
                        ];
                        $statusClasses = [
                            'pending'    => 'bg-yellow-100 text-yellow-700 border-yellow-200',
                            'processing' => 'bg-blue-100 text-blue-700 border-blue-200',
                            'shipping'   => 'bg-purple-100 text-purple-700 border-purple-200',
                            'completed'  => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                            'cancelled'  => 'bg-rose-100 text-rose-700 border-rose-200',
                            'returned'   => 'bg-slate-100 text-slate-700 border-slate-200',
                        ];
                    @endphp
                    <span id="order-status-badge" class="px-3 py-1 rounded-lg text-sm font-bold border {{ $statusClasses[$order->status] ?? '' }}">
                        {{ $statusLabels[$order->status] ?? ucfirst($order->status) }}
                    </span>
                </h1>
                <p class="text-sm text-slate-500 mt-1 flex items-center gap-2">
                    <i class="fa-regular fa-clock text-xs"></i> {{ $order->created_at->format('d/m/Y - H:i') }}
                </p>
            </div>
        </div>

        <div class="flex gap-3">
            <a href="{{ route('admin.orders.print', $order->id) }}" target="_blank" class="px-5 py-2.5 bg-white border border-slate-200 text-slate-700 font-bold rounded-xl hover:bg-slate-50 hover:text-indigo-600 shadow-sm transition-all flex items-center">
                <i class="fa-solid fa-print mr-2"></i> In Hóa Đơn
            </a>
        </div>
    </div>

    {{-- ALERT MESSAGES --}}
    @if(session('success'))
        <div class="p-4 mb-6 rounded-xl bg-emerald-50 border border-emerald-100 flex items-center gap-3 animate-fade-in-down shadow-sm">
            <i class="fa-solid fa-circle-check text-emerald-600 text-lg"></i>
            <span class="text-emerald-800 font-medium">{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="p-4 mb-6 rounded-xl bg-rose-50 border border-rose-100 flex items-center gap-3 animate-fade-in-down shadow-sm">
            <i class="fa-solid fa-circle-exclamation text-rose-600 text-lg"></i>
            <span class="text-rose-800 font-medium">{{ session('error') }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        {{-- LEFT COLUMN: ITEMS & TIMELINE --}}
        <div class="lg:col-span-2 space-y-8">
            
            {{-- 2. ORDER ITEMS --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                    <h3 class="font-bold text-slate-800 flex items-center gap-2">
                        <i class="fa-solid fa-basket-shopping text-indigo-500"></i> Danh sách sản phẩm
                    </h3>
                    <span class="text-xs font-bold bg-slate-200 text-slate-600 px-2 py-0.5 rounded">{{ $order->items->count() }} món</span>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-slate-50 text-slate-500 font-bold text-xs uppercase border-b border-slate-100">
                            <tr>
                                <th class="px-6 py-3">Sản phẩm</th>
                                <th class="px-6 py-3 text-right">Đơn giá</th>
                                <th class="px-6 py-3 text-center">SL</th>
                                <th class="px-6 py-3 text-right">Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @foreach($order->items as $item)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-4">
                                        {{-- Ảnh sản phẩm (Fallback) --}}
                                        <div class="w-14 h-14 rounded-lg border border-slate-100 bg-white p-0.5 shadow-sm flex-shrink-0 overflow-hidden">
                                            @php
                                                $imgUrl = 'https://placehold.co/100x100?text=No+Img';
                                                if($item->variant && $item->variant->image_url) {
                                                    $imgUrl = asset('storage/' . $item->variant->image_url);
                                                } elseif($item->variant && $item->variant->product && $item->variant->product->thumbnail) {
                                                    $imgUrl = asset('storage/' . $item->variant->product->thumbnail);
                                                }
                                            @endphp
                                            <img src="{{ $imgUrl }}" class="w-full h-full object-cover rounded-md">
                                        </div>
                                        <div>
                                            <div class="font-bold text-slate-800 text-sm mb-1 line-clamp-1" title="{{ $item->product_name }}">
                                                {{ $item->product_name }}
                                            </div>
                                            <div class="flex flex-wrap gap-1">
                                                <span class="text-[10px] font-mono bg-slate-100 text-slate-500 px-1.5 py-0.5 rounded border border-slate-200">
                                                    {{ $item->sku }}
                                                </span>
                                                <span class="text-[10px] font-bold bg-indigo-50 text-indigo-600 px-1.5 py-0.5 rounded border border-indigo-100">
                                                    {{ $item->size ?? '-' }} / {{ $item->color ?? '-' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right text-sm font-medium text-slate-600">
                                    {{ number_format($item->price, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 text-center text-sm font-bold text-slate-800">
                                    x{{ $item->quantity }}
                                </td>
                                <td class="px-6 py-4 text-right text-sm font-bold text-slate-800">
                                    {{ number_format($item->total, 0, ',', '.') }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- FINANCIAL SUMMARY --}}
                <div class="bg-slate-50/50 px-8 py-6 border-t border-slate-200">
                    <div class="flex flex-col items-end gap-3 w-full md:w-1/2 ml-auto">
                        <div class="flex justify-between w-full text-slate-500 text-sm">
                            <span>Tạm tính:</span>
                            <span class="font-medium text-slate-800">{{ number_format($order->items->sum('total'), 0, ',', '.') }} đ</span>
                        </div>
                        <div class="flex justify-between w-full text-slate-500 text-sm">
                            <span>Phí vận chuyển:</span>
                            <span class="font-medium text-slate-800">{{ number_format($order->shipping_fee, 0, ',', '.') }} đ</span>
                        </div>
                        
                        <div class="w-full border-t border-slate-200 my-1"></div>
                        <div class="flex justify-between w-full items-center">
                            <span class="font-extrabold text-slate-800 text-base">TỔNG THANH TOÁN</span>
                            <span class="font-extrabold text-2xl text-indigo-600">{{ number_format($order->total_amount, 0, ',', '.') }} đ</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 3. TIMELINE --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                <h3 class="font-bold text-slate-800 mb-6 flex items-center gap-2">
                    <i class="fa-solid fa-clock-rotate-left text-slate-400"></i> Lịch sử đơn hàng
                </h3>
                <div class="relative pl-4 border-l-2 border-slate-100 space-y-6" id="order-timeline">
                    @foreach($order->histories->sortByDesc('created_at') as $history)
                        <div class="relative timeline-item">
                            <div class="absolute -left-[21px] top-1.5 w-3 h-3 bg-indigo-500 rounded-full border-2 border-white shadow-sm"></div>
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="text-sm font-bold text-slate-800">
                                        {{ match($history->action) {
                                            'created' => 'Tạo đơn hàng',
                                            'update_status' => 'Cập nhật trạng thái',
                                            'payment' => 'Thanh toán',
                                            default => 'Hệ thống'
                                        } }}
                                    </p>
                                    <p class="text-xs text-slate-500 mt-0.5">{{ $history->description }}</p>
                                    <p class="text-[10px] text-slate-400 mt-1">
                                        Bởi: <span class="font-medium text-slate-600">{{ $history->user->full_name ?? 'Hệ thống' }}</span>
                                    </p>
                                </div>
                                <span class="text-xs text-slate-400 font-mono">{{ $history->created_at->format('H:i d/m') }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- RIGHT COLUMN: ACTIONS & INFO --}}
        <div class="lg:col-span-1 space-y-8">
            
            {{-- 4. UPDATE STATUS FORM --}}
            <div class="bg-white rounded-2xl shadow-md shadow-indigo-500/10 border border-slate-200 overflow-hidden relative">
                <div class="h-1 bg-indigo-600 w-full absolute top-0 left-0"></div>
                <div class="p-6">
                    <h3 class="font-bold text-slate-800 mb-4">Cập nhật trạng thái</h3>
                    
                    @php
                        // 1. ĐỊNH NGHĨA DANH SÁCH ĐẦY ĐỦ
                        $allStatuses = [
                            'pending'    => '🟡 Chờ xử lý',
                            'processing' => '🔵 Đang đóng gói',
                            'shipping'   => '🟣 Đang giao hàng',
                            'completed'  => '🟢 Hoàn thành',
                            'cancelled'  => '🔴 Hủy đơn hàng',
                            'returned'   => '↩️ Trả hàng',
                        ];

                        // 2. ĐỊNH NGHĨA QUY TẮC CHUYỂN ĐỔI (Logic)
                        $allowedTransitions = [
                            'pending'    => ['processing', 'cancelled'],           
                            'processing' => ['shipping', 'cancelled'],             
                            'shipping'   => ['completed', 'returned', 'cancelled'], 
                            'completed'  => [], // Kết thúc                                   
                            'cancelled'  => [], // Kết thúc                                   
                            'returned'   => [], // Kết thúc                                   
                        ];

                        $currentStatus = $order->status;
                        
                        // Kiểm tra xem đơn hàng có bị khóa hoàn toàn không (đã xong/hủy/trả)
                        $isOrderLocked = empty($allowedTransitions[$currentStatus]); 
                        
                        // Kiểm tra thanh toán
                        $isPaid = $order->payment_status === 'paid';
                    @endphp

                    {{-- Thông báo nếu đơn hàng đã kết thúc --}}
                    @if($isOrderLocked)
                        <div class="p-3 mb-4 bg-slate-100 text-slate-500 text-xs rounded-lg border border-slate-200 flex items-start gap-2">
                            <i class="fa-solid fa-lock mt-0.5"></i>
                            Đơn hàng đã kết thúc ở trạng thái <strong>{{ $allStatuses[$currentStatus] }}</strong>.
                        </div>
                    @endif

                    <form action="{{ route('admin.orders.update_status', $order->id) }}" method="POST" class="space-y-4">
                        @csrf
                        @method('PUT')
                        
                        {{-- A. SELECT TRẠNG THÁI ĐƠN HÀNG --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Trạng thái đơn hàng</label>
                            <select name="status" id="select-status" 
                                    class="w-full rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 py-2.5 font-medium text-slate-700 cursor-pointer disabled:bg-slate-100 disabled:text-slate-400 disabled:cursor-not-allowed"
                                    {{ $isOrderLocked ? 'disabled' : '' }}>
                                
                                @foreach($allStatuses as $key => $label)
                                    @php
                                        // Logic kiểm tra từng option
                                        $isCurrent = $key === $currentStatus;
                                        // Được phép chọn nếu: Là chính nó HOẶC nằm trong danh sách cho phép
                                        $isAllowed = $isCurrent || in_array($key, $allowedTransitions[$currentStatus] ?? []);
                                    @endphp

                                    <option value="{{ $key }}" 
                                            {{ $isCurrent ? 'selected' : '' }} 
                                            {{ !$isAllowed ? 'disabled' : '' }}
                                            class="{{ !$isAllowed ? 'bg-slate-100 text-slate-400' : '' }}">
                                        {{ $label }} {{ $isCurrent ? '(Hiện tại)' : '' }}
                                    </option>
                                @endforeach

                            </select>
                            @if(!$isOrderLocked)
                                <p class="text-[10px] text-slate-400 mt-1 italic">* Các trạng thái không hợp lệ đã bị khóa.</p>
                            @endif
                        </div>

                        {{-- B. SELECT THANH TOÁN --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Thanh toán</label>
                            <select name="payment_status" id="select-payment" 
                                    class="w-full rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 py-2.5 font-medium text-slate-700 cursor-pointer disabled:bg-slate-100 disabled:text-slate-400 disabled:cursor-not-allowed"
                                    {{ $isOrderLocked ? 'disabled' : '' }}>
                                
                                {{-- Option: Chưa thanh toán (Khóa nếu đã thanh toán) --}}
                                <option value="unpaid" {{ $order->payment_status == 'unpaid' ? 'selected' : '' }} {{ $isPaid ? 'disabled' : '' }}>
                                    Chưa thanh toán
                                </option>
                                
                                {{-- Option: Đã thanh toán --}}
                                <option value="paid" {{ $order->payment_status == 'paid' ? 'selected' : '' }}>
                                    ✅ Đã thanh toán
                                </option>
                                
                                {{-- Option: Hoàn tiền (Chỉ cho phép chọn nếu đơn bị Hủy hoặc Trả) --}}
                                @php
                                    // Chỉ cho phép hoàn tiền nếu trạng thái hiện tại là Cancelled hoặc Returned
                                    $allowRefund = in_array($currentStatus, ['cancelled', 'returned']);
                                @endphp
                                <option value="refunded" {{ $order->payment_status == 'refunded' ? 'selected' : '' }} {{ !$allowRefund && $order->payment_status != 'refunded' ? 'disabled' : '' }}>
                                    ↩️ Hoàn tiền
                                </option>

                            </select>
                            
                            @if($isPaid && !$isOrderLocked)
                                <p class="text-[10px] text-emerald-600 mt-1 flex items-center gap-1">
                                    <i class="fa-solid fa-check-circle"></i> Đã thanh toán (Không thể hoàn tác).
                                </p>
                            @endif
                        </div>

                        @if(!$isOrderLocked)
                            <button type="submit" class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-lg shadow-indigo-600/20 transition-all active:scale-95 flex items-center justify-center gap-2">
                                <i class="fa-solid fa-floppy-disk"></i> Lưu thay đổi
                            </button>
                        @endif
                    </form>
                </div>
            </div>

            {{-- 5. PAYMENT INFO --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                <h3 class="font-bold text-slate-800 mb-4 pb-3 border-b border-slate-100 flex items-center gap-2">
                    <i class="fa-regular fa-credit-card text-indigo-500"></i> Thông tin thanh toán
                </h3>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-slate-500">Phương thức</span>
                        <span class="font-bold text-slate-700 uppercase bg-slate-100 px-2 py-1 rounded text-xs">
                            {{ match($order->payment_method) {
                                'cod' => 'Tiền mặt (COD)',
                                'vnpay' => 'VNPay',
                                'momo' => 'Momo',
                                'banking' => 'Chuyển khoản',
                                default => $order->payment_method
                            } }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-slate-500">Trạng thái</span>
                        <span id="payment-status-badge" class="font-bold text-sm px-2 py-1 rounded border 
                            {{ match($order->payment_status) {
                                'paid' => 'bg-emerald-50 text-emerald-600 border-emerald-100',
                                'refunded' => 'bg-rose-50 text-rose-600 border-rose-100',
                                default => 'bg-amber-50 text-amber-600 border-amber-100'
                            } }}">
                            {{ match($order->payment_status) {
                                'paid' => 'Đã thanh toán',
                                'refunded' => 'Đã hoàn tiền',
                                'unpaid' => 'Chưa thanh toán',
                                default => $order->payment_status
                            } }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- 6. CUSTOMER INFO --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                <h3 class="font-bold text-slate-800 mb-4 pb-3 border-b border-slate-100 flex items-center gap-2">
                    <i class="fa-solid fa-user-circle text-indigo-500"></i> Khách hàng
                </h3>
                
                <div class="flex items-center gap-4 mb-6">
                    <div class="w-12 h-12 rounded-full bg-indigo-50 border border-indigo-100 flex items-center justify-center text-xl font-bold text-indigo-600 uppercase">
                        {{ substr($order->shipping_address['contact_name'] ?? 'U', 0, 1) }}
                    </div>
                    <div>
                        <div class="font-bold text-slate-800">{{ $order->shipping_address['contact_name'] ?? 'Khách lẻ' }}</div>
                        <div class="text-xs text-slate-500 bg-slate-100 px-2 py-0.5 rounded inline-block mt-1">
                            {{ $order->user ? 'Thành viên' : 'Khách vãng lai' }}
                        </div>
                    </div>
                </div>

                <div class="space-y-4 text-sm">
                    <div class="flex gap-3">
                        <div class="w-6 flex-shrink-0 flex justify-center text-slate-400"><i class="fa-solid fa-phone"></i></div>
                        <div>
                            <p class="text-xs text-slate-400 font-bold uppercase">Điện thoại</p>
                            <p class="font-medium text-slate-700">{{ $order->shipping_address['phone'] ?? '---' }}</p>
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <div class="w-6 flex-shrink-0 flex justify-center text-slate-400"><i class="fa-solid fa-location-dot"></i></div>
                        <div>
                            <p class="text-xs text-slate-400 font-bold uppercase">Địa chỉ giao hàng</p>
                            <p class="font-medium text-slate-700 leading-relaxed">
                                {{ $order->shipping_address['address'] ?? '' }}<br>
                                {{ $order->shipping_address['ward'] ?? '' }} - {{ $order->shipping_address['district'] ?? '' }}<br>
                                {{ $order->shipping_address['city'] ?? '' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 7. NOTE --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                <h3 class="font-bold text-slate-800 mb-2 flex items-center gap-2">
                    <i class="fa-solid fa-note-sticky text-amber-500"></i> Ghi chú
                </h3>
                <div class="bg-amber-50 border border-amber-100 rounded-xl p-4 text-sm text-amber-800 italic">
                    "{{ $order->note ?? 'Khách hàng không để lại ghi chú.' }}"
                </div>
            </div>

        </div>
    </div>
</div>

@push('scripts')
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/laravel-echo/dist/echo.iife.js"></script>
<script>
    // --- 1. KHỞI TẠO ECHO ---
    const echo = new Echo({
        broadcaster: 'pusher',
        key: '{{ env('PUSHER_APP_KEY') }}',
        cluster: '{{ env('PUSHER_APP_CLUSTER') }}',
        forceTLS: true,
    });

    const orderId = {{ $order->id }};

    // --- 2. LẮNG NGHE KÊNH RIÊNG CỦA ĐƠN HÀNG ---
    echo.private(`orders.${orderId}`)
        .listen('OrderStatusUpdated', (data) => {
            console.log('Realtime Update:', data);

            // A. Cập nhật Badge Trạng Thái Đơn Hàng (Header)
            const statusBadge = document.getElementById('order-status-badge');
            if (statusBadge && data.status) {
                // Map trạng thái sang tiếng Việt và Class màu
                const statusMap = {
                    'pending':    { label: 'Chờ xử lý',    class: 'bg-yellow-100 text-yellow-700 border-yellow-200' },
                    'processing': { label: 'Đang đóng gói',class: 'bg-blue-100 text-blue-700 border-blue-200' },
                    'shipping':   { label: 'Đang giao hàng',class: 'bg-purple-100 text-purple-700 border-purple-200' },
                    'completed':  { label: 'Hoàn thành',   class: 'bg-emerald-100 text-emerald-700 border-emerald-200' },
                    'cancelled':  { label: 'Đã hủy',       class: 'bg-rose-100 text-rose-700 border-rose-200' },
                    'returned':   { label: 'Trả hàng',     class: 'bg-slate-100 text-slate-700 border-slate-200' }
                };

                const config = statusMap[data.status] || { label: data.status, class: 'bg-gray-100' };
                statusBadge.innerText = config.label;
                statusBadge.className = `px-3 py-1 rounded-lg text-sm font-bold border ${config.class}`;
            }

            // B. Cập nhật Badge Thanh Toán (Sidebar)
            const paymentBadge = document.getElementById('payment-status-badge');
            if (paymentBadge && data.payment_status) {
                const paymentMap = {
                    'paid':     { label: 'Đã thanh toán',   class: 'bg-emerald-50 text-emerald-600 border-emerald-100' },
                    'refunded': { label: 'Đã hoàn tiền',    class: 'bg-rose-50 text-rose-600 border-rose-100' },
                    'unpaid':   { label: 'Chưa thanh toán', class: 'bg-amber-50 text-amber-600 border-amber-100' }
                };
                
                const config = paymentMap[data.payment_status] || { label: data.payment_status, class: 'bg-gray-50' };
                paymentBadge.innerText = config.label;
                paymentBadge.className = `font-bold text-sm px-2 py-1 rounded border ${config.class}`;
            }

            // C. Thêm dòng Lịch sử mới vào Timeline
            if (data.history) {
                const timeline = document.getElementById('order-timeline');
                if (timeline) {
                    const newHistoryHtml = `
                        <div class="relative timeline-item animate-fade-in-down">
                            <div class="absolute -left-[21px] top-1.5 w-3 h-3 bg-indigo-500 rounded-full border-2 border-white shadow-sm"></div>
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="text-sm font-bold text-slate-800">${data.history.action_text}</p>
                                    <p class="text-xs text-slate-500 mt-0.5">${data.history.description}</p>
                                    <p class="text-[10px] text-slate-400 mt-1">Bởi: <span class="font-medium text-slate-600">${data.history.user_name}</span></p>
                                </div>
                                <span class="text-xs text-slate-400 font-mono">${data.history.time}</span>
                            </div>
                        </div>
                    `;
                    timeline.insertAdjacentHTML('afterbegin', newHistoryHtml);
                }
            }

            // D. Reload trang nếu đơn hàng bị khóa (Để disable form input)
            if (['completed', 'cancelled', 'returned'].includes(data.status)) {
                setTimeout(() => location.reload(), 2000); // Reload sau 2s để user kịp nhìn thấy thông báo
            }
        });
</script>
@endpush
@endsection