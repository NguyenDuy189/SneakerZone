@extends('client.layouts.app')

@section('title', 'Về chúng tôi - Sneaker Zone')

@section('content')
    {{-- Banner / Header --}}
    <div class="bg-slate-50 py-16">
        <div class="container mx-auto px-4 text-center">
            <h1 class="text-4xl md:text-5xl font-display font-black text-slate-900 mb-4 tracking-tight">
                CÂU CHUYỆN CỦA <span class="text-indigo-600">SNEAKER ZONE</span>
            </h1>
            <p class="text-slate-500 max-w-2xl mx-auto text-lg">
                Hơn cả một đôi giày, đó là phong cách sống của bạn.
            </p>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="container mx-auto px-4 py-16">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
            {{-- Hình ảnh (Dùng ảnh placeholder hoặc ảnh thật của shop) --}}
            <div class="relative group">
                <div class="absolute inset-0 bg-indigo-600 rounded-2xl rotate-3 opacity-20 transition-transform group-hover:rotate-6"></div>
                <img src="https://images.unsplash.com/photo-1556906781-9a412961d289?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" 
                     alt="About Sneaker Zone" 
                     class="relative rounded-2xl shadow-xl w-full object-cover h-[500px]">
            </div>

            {{-- Nội dung text --}}
            <div class="space-y-6">
                <h2 class="text-3xl font-display font-bold text-slate-900">
                    Khởi đầu từ niềm đam mê
                </h2>
                <div class="prose text-slate-600 leading-relaxed">
                    <p>
                        Được thành lập vào năm 2024, <strong>Sneaker Zone</strong> bắt đầu với một sứ mệnh đơn giản: mang đến những đôi giày sneaker chính hãng, chất lượng nhất đến tay các bạn trẻ Việt Nam với mức giá hợp lý.
                    </p>
                    <p>
                        Chúng tôi hiểu rằng mỗi đôi giày không chỉ là phụ kiện, mà là tiếng nói của cá tính. Dù bạn là người yêu thích sự năng động của Nike, nét cổ điển của Adidas hay sự phá cách của Jordan, Sneaker Zone luôn có thứ gì đó dành cho bạn.
                    </p>
                </div>

                {{-- Các giá trị cốt lõi --}}
                <div class="grid grid-cols-2 gap-6 pt-4">
                    <div class="bg-indigo-50 p-4 rounded-xl">
                        <i class="fa-solid fa-check-circle text-indigo-600 text-2xl mb-2"></i>
                        <h3 class="font-bold text-slate-900">100% Chính hãng</h3>
                        <p class="text-sm text-slate-500">Cam kết đền bù gấp 10 lần nếu phát hiện hàng giả.</p>
                    </div>
                    <div class="bg-indigo-50 p-4 rounded-xl">
                        <i class="fa-solid fa-truck-fast text-indigo-600 text-2xl mb-2"></i>
                        <h3 class="font-bold text-slate-900">Giao hàng nhanh</h3>
                        <p class="text-sm text-slate-500">Vận chuyển toàn quốc, kiểm tra hàng trước khi thanh toán.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection