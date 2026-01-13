@extends('admin.layouts.app')

@section('title', 'Chi tiết đơn hàng #' . $order->order_code)

@section('content')
{{-- 
    1. CẤU HÌNH GLOBAL CHO VIEW
--}}
@php
    // Danh sách trạng thái & hiển thị
    $statusConfig = [
        'pending'    => ['label' => 'Chờ xử lý',      'class' => 'bg-yellow-100 text-yellow-700 border-yellow-200', 'icon' => '🟡'],
        'processing' => ['label' => 'Đang đóng gói',  'class' => 'bg-blue-100 text-blue-700 border-blue-200',       'icon' => '🔵'],
        'shipping'   => ['label' => 'Đang giao hàng', 'class' => 'bg-purple-100 text-purple-700 border-purple-200', 'icon' => '🟣'],
        'completed'  => ['label' => 'Hoàn thành',     'class' => 'bg-emerald-100 text-emerald-700 border-emerald-200', 'icon' => '🟢'],
        'cancelled'  => ['label' => 'Đã hủy',         'class' => 'bg-rose-100 text-rose-700 border-rose-200',       'icon' => '🔴'],
        'returned'   => ['label' => 'Trả hàng',       'class' => 'bg-slate-100 text-slate-700 border-slate-200',     'icon' => '↩️'],
    ];

    // Quy tắc chuyển đổi trạng thái
    $transitions = [
        'pending'    => ['processing', 'cancelled'],             // Chờ xử lý -> Đóng gói hoặc Hủy
        'processing' => ['shipping', 'cancelled'],               // Đóng gói -> Giao hàng hoặc Hủy
        'shipping'   => ['completed', 'cancelled'],              // Giao hàng -> Xong hoặc Hủy
        'completed'  => [],                                      // KHÓA
        'cancelled'  => [],                                      // KHÓA
        'returned'   => [],                                      // KHÓA
    ];

    $currentStatus = $order->status;
    
    // Kiểm tra trạng thái cuối (Terminal State)
    $isOrderLocked = empty($transitions[$currentStatus]);
    
    // Kiểm tra thanh toán
    $isPaid = $order->payment_status === 'paid';
@endphp

<div class="container px-6 mx-auto mb-20 fade-in">
    
    {{-- 2. HEADER & ACTIONS --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8 pt-6">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.orders.index') }}" class="w-10 h-10 flex items-center justify-center rounded-xl bg-white border border-slate-200 text-slate-500 hover:bg-slate-50 hover:text-indigo-600 transition-all shadow-sm">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-2xl font-extrabold text-slate-800 flex items-center gap-3">
                    #{{ $order->order_code }}
                    
                    {{-- Status Badge --}}
                    <span id="order-status-badge" class="px-3 py-1 rounded-lg text-sm font-bold border {{ $statusConfig[$currentStatus]['class'] ?? 'bg-gray-100' }}">
                        {{ $statusConfig[$currentStatus]['label'] ?? $currentStatus }}
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
            
            {{-- 3. ORDER ITEMS --}}
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
                                        {{-- 1. ẢNH SẢN PHẨM --}}
                                        <div class="w-14 h-14 rounded-lg border border-slate-100 bg-white p-0.5 shadow-sm flex-shrink-0 overflow-hidden relative group">
                                            @php
                                                // Khởi tạo ảnh mặc định
                                                $imgUrl = 'https://placehold.co/100x100?text=No+Img';

                                                // 1. Ưu tiên lấy ảnh Snapshot đã lưu trong order_items (để tránh mất ảnh khi xóa SP/Variant)
                                                if (!empty($item->thumbnail)) {
                                                    $imgUrl = $item->thumbnail;
                                                }
                                                // 2. Nếu không có snapshot, thử lấy ảnh của Variant hiện tại
                                                elseif ($item->variant && $item->variant->image) {
                                                    $imgUrl = $item->variant->image;
                                                }
                                                // 3. Nếu không có ảnh Variant, thử lấy ảnh của Product cha thông qua Variant
                                                elseif ($item->variant && $item->variant->product && $item->variant->product->thumbnail) {
                                                    $imgUrl = $item->variant->product->thumbnail;
                                                }
                                                // 4. Fallback cuối cùng: Lấy ảnh trực tiếp từ Product (nếu item có quan hệ product)
                                                elseif ($item->product && $item->product->thumbnail) {
                                                    $imgUrl = $item->product->thumbnail;
                                                }

                                                // Xử lý đường dẫn ảnh (nếu lưu local path thì thêm asset storage)
                                                if ($imgUrl && !Str::startsWith($imgUrl, ['http', 'https']) && $imgUrl !== 'https://placehold.co/100x100?text=No+Img') {
                                                    $imgUrl = asset('storage/' . $imgUrl); 
                                                }
                                            @endphp
                                            
                                            <img src="{{ $imgUrl }}" 
                                                alt="{{ $item->product_name ?? 'Product Image' }}"
                                                class="w-full h-full object-cover rounded-md group-hover:scale-110 transition-transform duration-300">
                                        </div>

                                        {{-- 2. THÔNG TIN SẢN PHẨM & BIẾN THỂ --}}
                                        <div>
                                            {{-- Tên sản phẩm --}}
                                            <div class="font-bold text-slate-800 text-sm mb-1 line-clamp-1" 
                                                title="{{ $item->product_name }}">
                                                {{ $item->product_name ?? 'Unknown Product' }}
                                            </div>
                                            
                                            <div class="flex flex-wrap gap-1 items-center">
                                                {{-- SKU --}}
                                                @if($item->sku)
                                                    <span class="text-[10px] font-mono bg-slate-100 text-slate-500 px-1.5 py-0.5 rounded border border-slate-200" title="SKU">
                                                        {{ $item->sku }}
                                                    </span>
                                                @endif

                                                {{-- LOGIC LẤY BIẾN THỂ (SIZE / COLOR) TỪ DB --}}
                                                @php
                                                    $size = null;
                                                    $color = null;

                                                    // Kiểm tra nếu Variant còn tồn tại và có các thuộc tính
                                                    if ($item->variant && $item->variant->attributeValues) {
                                                        foreach ($item->variant->attributeValues as $av) {
                                                            // Kiểm tra code của attribute cha (size hoặc color)
                                                            // Cần đảm bảo relation 'attribute' được eager load trong Controller
                                                            if ($av->attribute && strtolower($av->attribute->code) == 'size') {
                                                                $size = $av->value;
                                                            }
                                                            if ($av->attribute && strtolower($av->attribute->code) == 'color') {
                                                                $color = $av->value;
                                                            }
                                                        }
                                                    }
                                                    
                                                    // Fallback: Nếu không tìm thấy qua quan hệ (do xóa variant), thử parse từ tên sản phẩm
                                                    // Ví dụ tên: "Giày Nike - Size 40" -> Cố gắng lấy "40" (Cách này không khuyến khích nhưng là phương án cuối)
                                                    if (!$size && preg_match('/Size\s*(\d+)/i', $item->product_name, $matches)) {
                                                        $size = $matches[1];
                                                    }
                                                @endphp

                                                @if($size || $color)
                                                    <span class="text-[10px] font-bold bg-indigo-50 text-indigo-600 px-1.5 py-0.5 rounded border border-indigo-100 flex items-center gap-1">
                                                        {{-- Hiển thị Size --}}
                                                        @if($size)
                                                            <span>Size: {{ $size }}</span>
                                                        @endif
                                                        
                                                        {{-- Dấu gạch ngăn cách --}}
                                                        @if($size && $color) <span class="opacity-50">|</span> @endif
                                                        
                                                        {{-- Hiển thị Color --}}
                                                        @if($color)
                                                            <span>Màu: {{ $color }}</span>
                                                        @endif
                                                    </span>
                                                @endif
                                                
                                                {{-- Cảnh báo nếu Variant đã bị xóa (quan hệ null) --}}
                                                @if(!$item->variant)
                                                    <span class="text-[9px] text-rose-500 bg-rose-50 px-1 rounded border border-rose-100" title="Sản phẩm gốc đã bị xóa khỏi hệ thống">
                                                        ⚠ Đã xóa SP gốc
                                                    </span>
                                                @endif
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

            {{-- 4. TIMELINE --}}
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
            
            {{-- 5. UPDATE STATUS FORM --}}
            <div class="bg-white rounded-2xl shadow-md shadow-indigo-500/10 border border-slate-200 overflow-hidden relative">
                <div class="h-1 bg-indigo-600 w-full absolute top-0 left-0"></div>
                <div class="p-6">
                    <h3 class="font-bold text-slate-800 mb-4">Cập nhật trạng thái</h3>
                    
                    @if($isOrderLocked)
                        <div class="p-3 mb-4 bg-slate-100 text-slate-500 text-xs rounded-lg border border-slate-200 flex items-start gap-2">
                            <i class="fa-solid fa-lock mt-0.5"></i>
                            <div>
                                Đơn hàng đã kết thúc ở trạng thái <strong class="text-slate-700">{{ $statusConfig[$currentStatus]['label'] }}</strong>.
                                <br>Không thể thay đổi.
                            </div>
                        </div>
                    @else
                        @if(!empty($transitions[$currentStatus]))
                        <div class="mb-4 text-xs text-indigo-600 bg-indigo-50 p-2.5 rounded-lg border border-indigo-100 flex gap-2">
                            <i class="fa-regular fa-lightbulb mt-0.5"></i>
                            <div>
                                <span class="font-bold">Gợi ý tiếp theo:</span>
                                @foreach($transitions[$currentStatus] as $next)
                                     {{ $statusConfig[$next]['label'] }}@if(!$loop->last), @endif
                                @endforeach
                            </div>
                        </div>
                        @endif
                    @endif

                    <form action="{{ route('admin.orders.update_status', $order->id) }}" method="POST" class="space-y-4">
                        @csrf
                        @method('PUT')
                        
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Trạng thái đơn hàng</label>
                            <div class="relative">
                                <select name="status" id="select-status" 
                                        class="w-full rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 py-2.5 pl-3 pr-8 font-medium text-slate-700 cursor-pointer disabled:bg-slate-100 disabled:text-slate-400 disabled:cursor-not-allowed"
                                        {{ $isOrderLocked ? 'disabled' : '' }}>
                                    
                                    @foreach($statusConfig as $key => $config)
                                        @php
                                            if ($key === 'returned' && $currentStatus !== 'returned') continue; 
                                            $isCurrent = ($key === $currentStatus);
                                            $isAllowed = $isCurrent || in_array($key, $transitions[$currentStatus] ?? []);
                                        @endphp
                                        <option value="{{ $key }}" {{ $isCurrent ? 'selected' : '' }} {{ !$isAllowed ? 'disabled' : '' }} class="{{ !$isAllowed ? 'bg-slate-100 text-slate-400' : 'font-bold text-slate-700' }}">
                                            {{ $config['icon'] }} {{ $config['label'] }} 
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Thanh toán</label>
                            <select name="payment_status" id="select-payment" 
                                    class="w-full rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 py-2.5 font-medium text-slate-700 cursor-pointer disabled:bg-slate-100 disabled:text-slate-400 disabled:cursor-not-allowed"
                                    {{ $isOrderLocked ? 'disabled' : '' }}>
                                
                                <option value="unpaid" {{ $order->payment_status == 'unpaid' ? 'selected' : '' }} {{ $isPaid ? 'disabled' : '' }}>⏳ Chưa thanh toán</option>
                                <option value="paid" {{ $order->payment_status == 'paid' ? 'selected' : '' }}>✅ Đã thanh toán</option>
                                @php $allowRefund = in_array($currentStatus, ['cancelled', 'returned']); @endphp
                                <option value="refunded" {{ $order->payment_status == 'refunded' ? 'selected' : '' }} {{ !$allowRefund && $order->payment_status != 'refunded' ? 'disabled' : '' }} class="{{ !$allowRefund ? 'bg-slate-100 text-slate-400' : '' }}">↩️ Hoàn tiền {{ !$allowRefund ? '(Chỉ khi Hủy/Trả)' : '' }}</option>
                            </select>
                            @if($isPaid && !$isOrderLocked)
                                <p class="text-[10px] text-emerald-600 mt-1 flex items-center gap-1 font-medium"><i class="fa-solid fa-check-circle"></i> Đã thanh toán (Không thể hoàn tác).</p>
                            @endif
                        </div>

                        @if(!$isOrderLocked)
                            <button type="submit" class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-lg shadow-indigo-600/20 transition-all active:scale-95 flex items-center justify-center gap-2">
                                <i class="fa-solid fa-floppy-disk"></i> Cập nhật
                            </button>
                        @endif
                    </form>
                </div>
            </div>

            {{-- 6. PAYMENT INFO --}}
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

            {{-- 
                7. [NÂNG CẤP] CUSTOMER INFO 
                Logic cải tiến: Fallback tên, hiển thị email, copy clipboard, link tới User.
            --}}
            @php
                // Logic định danh khách hàng an toàn
                $customerUser = $order->user; // Relation user (nếu có)
                
                // 1. Tên: Ưu tiên tên người nhận -> Tên User -> Mặc định
                $displayName = $order->receiver_name 
                                ?? ($customerUser ? $customerUser->full_name : null) 
                                ?? 'Khách vãng lai';

                // 2. Email: Ưu tiên email order -> Email user -> Mặc định
                $displayEmail = $order->receiver_email // Nếu DB có cột này
                                ?? ($customerUser ? $customerUser->email : null)
                                ?? 'Chưa cập nhật email';

                // 3. Phone & Address
                $displayPhone = $order->receiver_phone ?? 'N/A';
                $displayAddress = $order->full_address ?? 'Chưa cập nhật địa chỉ';
            @endphp

            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                <h3 class="font-bold text-slate-800 mb-4 pb-3 border-b border-slate-100 flex items-center justify-between">
                    <span class="flex items-center gap-2"><i class="fa-solid fa-user-circle text-indigo-500"></i> Khách hàng</span>
                    
                    {{-- Badge phân loại khách --}}
                    @if($order->user_id)
                        <span class="text-[10px] font-bold bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded border border-indigo-200">Thành viên</span>
                    @else
                        <span class="text-[10px] font-bold bg-slate-100 text-slate-500 px-2 py-0.5 rounded border border-slate-200">Khách lẻ</span>
                    @endif
                </h3>
                
                {{-- A. Header Khách hàng --}}
                <div class="flex items-start gap-4 mb-5">
                    {{-- A. LOGIC XỬ LÝ ẢNH & INITIALS --}}
                    @php
                        $avatarSrc = null;
                        if ($customerUser && !empty($customerUser->avatar_url)) {
                            // Kiểm tra: Nếu là link online (http) thì giữ nguyên, nếu là path local thì thêm storage
                            $avatarSrc = Str::startsWith($customerUser->avatar_url, ['http', 'https']) 
                                        ? $customerUser->avatar_url 
                                        : asset('storage/' . $customerUser->avatar_url);
                        }

                        // Lấy chữ cái đầu, in hoa (Hỗ trợ tiếng Việt)
                        $initial = $displayName ? mb_strtoupper(mb_substr($displayName, 0, 1)) : 'K';
                    @endphp

                    {{-- B. HIỂN THỊ --}}
                    @if($avatarSrc)
                        {{-- Trường hợp 1: Có ảnh --}}
                        <img src="{{ $avatarSrc }}" 
                            alt="{{ $displayName }}" 
                            class="w-12 h-12 rounded-full border border-slate-200 object-cover shadow-sm flex-shrink-0"
                            {{-- Fallback: Nếu ảnh lỗi (404) thì ẩn ảnh đi và hiện avatar chữ cái (xử lý bằng JS inline đơn giản) --}}
                            onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        
                        {{-- Fallback ẩn (chỉ hiện khi ảnh trên bị lỗi load) --}}
                        <div class="w-12 h-12 rounded-full bg-indigo-50 border border-indigo-100 items-center justify-center text-xl font-bold text-indigo-600 uppercase flex-shrink-0 hidden">
                            {{ $initial }}
                        </div>
                    @else
                        {{-- Trường hợp 2: Không có ảnh trong DB -> Hiện chữ cái --}}
                        <div class="w-12 h-12 rounded-full bg-indigo-50 border border-indigo-100 flex items-center justify-center text-xl font-bold text-indigo-600 uppercase flex-shrink-0 shadow-sm">
                            {{ $initial }}
                        </div>
                    @endif
                    
                    <div class="overflow-hidden">
                        {{-- Tên khách có link nếu là Member --}}
                        @if($order->user_id)
                            <a href="{{ route('admin.users.show', $order->user_id) }}" class="font-bold text-slate-800 hover:text-indigo-600 transition-colors block truncate" title="Xem chi tiết thành viên">
                                {{ $displayName }} <i class="fa-solid fa-arrow-up-right-from-square text-[10px] ml-1 opacity-50"></i>
                            </a>
                        @else
                            <div class="font-bold text-slate-800 truncate" title="{{ $displayName }}">
                                {{ $displayName }}
                            </div>
                        @endif

                        {{-- Email --}}
                        <div class="text-xs text-slate-500 truncate flex items-center gap-1 mt-0.5" title="{{ $displayEmail }}">
                            <i class="fa-regular fa-envelope"></i> {{ $displayEmail }}
                        </div>
                    </div>
                </div>

                {{-- B. Chi tiết liên hệ (Grid layout) --}}
                <div class="space-y-4 text-sm bg-slate-50/50 p-4 rounded-xl border border-slate-100">
                    {{-- Phone --}}
                    <div class="flex gap-3 group">
                        <div class="w-5 flex-shrink-0 flex justify-center text-slate-400 mt-0.5"><i class="fa-solid fa-phone"></i></div>
                        <div class="flex-1 min-w-0">
                            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mb-0.5">Điện thoại</p>
                            <div class="flex items-center justify-between">
                                <p class="font-medium text-slate-700 font-mono text-base">{{ $displayPhone }}</p>
                                <button onclick="copyToClipboard('{{ $displayPhone }}')" class="text-slate-400 hover:text-indigo-600 transition-colors opacity-0 group-hover:opacity-100" title="Copy SĐT">
                                    <i class="fa-regular fa-copy"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <hr class="border-slate-100 dashed">

                    {{-- Address --}}
                    <div class="flex gap-3 group">
                        <div class="w-5 flex-shrink-0 flex justify-center text-slate-400 mt-0.5"><i class="fa-solid fa-location-dot"></i></div>
                        <div class="flex-1 min-w-0">
                            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mb-0.5">Địa chỉ giao hàng</p>
                            <div class="flex items-start justify-between gap-2">
                                <p class="font-medium text-slate-700 leading-relaxed text-xs md:text-sm">
                                    {{ $displayAddress }}
                                </p>
                                <button onclick="copyToClipboard('{{ $displayAddress }}')" class="text-slate-400 hover:text-indigo-600 transition-colors opacity-0 group-hover:opacity-100 mt-0.5" title="Copy Địa chỉ">
                                    <i class="fa-regular fa-copy"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 8. NOTE --}}
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
    // --- UTILS: COPY TO CLIPBOARD ---
    function copyToClipboard(text) {
        if (!text || text === 'N/A') return;
        navigator.clipboard.writeText(text).then(() => {
            // Có thể thêm toast notification ở đây nếu muốn
            alert('Đã copy: ' + text); 
        }).catch(err => {
            console.error('Không thể copy text: ', err);
        });
    }

    // --- 1. SETUP ECHO ---
    const echo = new Echo({
        broadcaster: 'pusher',
        key: '{{ env('PUSHER_APP_KEY') }}',
        cluster: '{{ env('PUSHER_APP_CLUSTER') }}',
        forceTLS: true,
    });

    const orderId = {{ $order->id }};

    // --- 2. LISTEN REALTIME EVENTS ---
    echo.private(`orders.${orderId}`)
        .listen('OrderStatusUpdated', (data) => {
            console.log('Realtime Update:', data);

            // A. Update Status Badge (Header)
            const statusBadge = document.getElementById('order-status-badge');
            if (statusBadge && data.status) {
                const statusMap = {
                    'pending':    { label: 'Chờ xử lý',      class: 'bg-yellow-100 text-yellow-700 border-yellow-200' },
                    'processing': { label: 'Đang đóng gói',  class: 'bg-blue-100 text-blue-700 border-blue-200' },
                    'shipping':   { label: 'Đang giao hàng', class: 'bg-purple-100 text-purple-700 border-purple-200' },
                    'completed':  { label: 'Hoàn thành',     class: 'bg-emerald-100 text-emerald-700 border-emerald-200' },
                    'cancelled':  { label: 'Đã hủy',         class: 'bg-rose-100 text-rose-700 border-rose-200' },
                    'returned':   { label: 'Trả hàng',       class: 'bg-slate-100 text-slate-700 border-slate-200' }
                };

                const config = statusMap[data.status] || { label: data.status, class: 'bg-gray-100' };
                statusBadge.innerText = config.label;
                statusBadge.className = `px-3 py-1 rounded-lg text-sm font-bold border ${config.class}`;
            }

            // B. Update Payment Badge
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

            // C. Add Timeline Item
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

            // D. Tự động reload trang sau 1.5s
            setTimeout(() => {
                location.reload();
            }, 1500);
        });
</script>
@endpush
@endsection