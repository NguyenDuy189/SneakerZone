<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hóa đơn #{{ $order->order_code }}</title>
    <!-- Sử dụng Tailwind CSS qua CDN để đảm bảo style khi in -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Cấu hình Font chữ và CSS in ấn -->
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');
        
        body {
            font-family: 'Inter', sans-serif;
            -webkit-print-color-adjust: exact; /* Giữ màu sắc khi in */
            print-color-adjust: exact;
        }

        @media print {
            @page { margin: 0; size: A4; }
            body { margin: 1.6cm; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body class="bg-white text-slate-900 text-sm" onload="window.print()">

    <!-- 1. HEADER: Logo & Thông tin công ty -->
    <div class="flex justify-between items-start mb-12 pb-8 border-b border-slate-200">
        <div>
            <!-- Logo (Giả lập bằng Text hoặc thay bằng thẻ img) -->
            <div class="flex items-center gap-2 mb-4">
                <div class="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                </div>
                <span class="text-2xl font-bold tracking-tight text-slate-900">SNEAKER<span class="text-indigo-600">ZONE</span></span>
            </div>
            
            <div class="text-slate-500 text-xs leading-relaxed">
                <p>Tòa P, Trường Cao đẳng FPT PolyTechnic, Phan Tây Nhạc, Nam Từ Liêm, Hà Nội</p>
                <p>Thành phố Hà Nội, Việt Nam</p>
                <p>support@sneakerzone.vn</p>
                <p>(+84) 1900 1234</p>
            </div>
        </div>

        <div class="text-right">
            <h1 class="text-4xl font-extrabold text-slate-900 mb-2">HÓA ĐƠN</h1>
            <p class="text-slate-500 mb-1">Mã đơn hàng: <span class="font-mono font-bold text-slate-800 text-base">#{{ $order->order_code }}</span></p>
            <p class="text-slate-500">Ngày đặt: {{ $order->created_at->format('d/m/Y') }}</p>
            
            <!-- Trạng thái thanh toán -->
            <div class="mt-4">
                @if($order->payment_status == 'paid')
                    <span class="px-3 py-1 bg-green-100 text-green-700 rounded font-bold border border-green-200 uppercase text-xs">
                        ĐÃ THANH TOÁN
                    </span>
                @else
                    <span class="px-3 py-1 bg-slate-100 text-slate-600 rounded font-bold border border-slate-200 uppercase text-xs">
                        CHƯA THANH TOÁN
                    </span>
                @endif
            </div>
        </div>
    </div>

    <!-- 2. INFO GRID: Khách hàng & Giao hàng -->
    <div class="flex justify-between mb-12">
        <div class="w-1/2 pr-10">
            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Khách hàng</h3>
            <p class="font-bold text-base text-slate-800">{{ $order->shipping_address['contact_name'] ?? 'Khách lẻ' }}</p>
            <p class="text-slate-600 mt-1">{{ $order->shipping_address['phone'] ?? '' }}</p>
            @if($order->user && $order->user->email)
                <p class="text-slate-600">{{ $order->user->email }}</p>
            @endif
        </div>

        <div class="w-1/2">
            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Địa chỉ giao hàng</h3>
            <p class="text-slate-700 leading-relaxed font-medium">
                {{ $order->shipping_address['address'] ?? '' }}<br>
                {{ $order->shipping_address['ward'] ?? '' }}, {{ $order->shipping_address['district'] ?? '' }}<br>
                {{ $order->shipping_address['city'] ?? '' }}
            </p>
        </div>
    </div>

    <!-- 3. ITEMS TABLE -->
    <div class="mb-10">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b-2 border-slate-800">
                    <th class="py-3 text-xs font-bold text-slate-800 uppercase tracking-wider w-1/2">Sản phẩm</th>
                    <th class="py-3 text-xs font-bold text-slate-800 uppercase tracking-wider text-center">Số lượng</th>
                    <th class="py-3 text-xs font-bold text-slate-800 uppercase tracking-wider text-right">Đơn giá</th>
                    <th class="py-3 text-xs font-bold text-slate-800 uppercase tracking-wider text-right">Thành tiền</th>
                </tr>
            </thead>
            <tbody class="text-slate-600">
                @foreach($order->items as $item)
                <tr class="border-b border-slate-200">
                    <td class="py-4 pr-4">
                        <p class="font-bold text-slate-800">{{ $item->product_name }}</p>
                        <div class="text-xs text-slate-500 mt-1 flex gap-2">
                            <span class="font-mono bg-slate-100 px-1 rounded">SKU: {{ $item->sku }}</span>
                            @if($item->productVariant)
                                <span>• {{ $item->productVariant->attribute_string }}</span>
                            @endif
                        </div>
                    </td>
                    <td class="py-4 text-center align-top pt-5">{{ $item->quantity }}</td>
                    <td class="py-4 text-right align-top pt-5">{{ number_format($item->price, 0, ',', '.') }}</td>
                    <td class="py-4 text-right align-top pt-5 font-bold text-slate-800">{{ number_format($item->total_line, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- 4. TOTALS (Căn phải) -->
    <div class="flex justify-end mb-16">
        <div class="w-5/12">
            <div class="flex justify-between py-2 text-slate-600">
                <span>Tạm tính:</span>
                <span class="font-medium">{{ number_format($order->items->sum('total_line'), 0, ',', '.') }} ₫</span>
            </div>
            <div class="flex justify-between py-2 text-slate-600 border-b border-slate-200 pb-4">
                <span>Phí vận chuyển:</span>
                <span class="font-medium">{{ number_format($order->shipping_fee, 0, ',', '.') }} ₫</span>
            </div>
            <div class="flex justify-between py-4 align-middle">
                <span class="text-lg font-bold text-slate-900">Tổng cộng:</span>
                <span class="text-2xl font-bold text-indigo-600">{{ number_format($order->total_amount, 0, ',', '.') }} ₫</span>
            </div>
        </div>
    </div>

    <!-- 5. FOOTER & NOTE -->
    <div class="border-t-2 border-slate-100 pt-8">
        <div class="flex justify-between gap-10">
            <div class="w-2/3">
                <h4 class="font-bold text-slate-800 text-sm mb-2">Ghi chú:</h4>
                <p class="text-slate-500 text-xs italic bg-slate-50 p-3 rounded border border-slate-100">
                    "{{ $order->note ?? 'Không có ghi chú.' }}"
                </p>
            </div>
            <div class="w-1/3 text-right">
                <p class="font-bold text-slate-800 mb-8">Người lập phiếu</p>
                <p class="text-slate-400 italic text-xs">(Ký và ghi rõ họ tên)</p>
            </div>
        </div>
        
        <div class="text-center mt-16 text-xs text-slate-400">
            <p>Cảm ơn quý khách đã mua sắm tại Sneaker Zone!</p>
            <p>Mọi thắc mắc xin vui lòng liên hệ hotline 1900 1234 hoặc website sneakerzone.vn</p>
        </div>
    </div>

    <!-- Nút in (Chỉ hiện khi chưa in, ẩn khi in giấy) -->
    <div class="fixed bottom-8 right-8 no-print">
        <button onclick="window.print()" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-6 rounded-full shadow-lg flex items-center gap-2 transition-transform hover:scale-105">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
            In hóa đơn
        </button>
    </div>

</body>
</html>