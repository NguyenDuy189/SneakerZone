@extends('client.layouts.app')

@section('title', 'Liên Hệ - Sneaker Zone')

@section('content')
<div class="bg-white py-12">
    <div class="container mx-auto px-4">
        <div class="text-center max-w-2xl mx-auto mb-12">
            <h1 class="text-3xl font-bold text-slate-900 mb-4">Liên hệ với chúng tôi</h1>
            <p class="text-slate-500">Chúng tôi luôn sẵn sàng lắng nghe ý kiến của bạn. Hãy gửi tin nhắn hoặc ghé thăm cửa hàng của chúng tôi.</p>
        </div>

        {{-- Đây là div cha dùng Grid 2 cột --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 max-w-6xl mx-auto">
            
            {{-- CỘT 1: Thông tin liên hệ (Sẽ ở bên trái) --}}
            <div class="space-y-8">
                <div class="bg-indigo-50 p-6 rounded-2xl border border-indigo-100">
                    <h3 class="text-lg font-bold text-indigo-900 mb-4">Thông tin liên hệ</h3>
                    <ul class="space-y-4">
                        <li class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center text-indigo-600 shadow-sm flex-shrink-0">
                                <i class="fa-solid fa-location-dot"></i>
                            </div>
                            <div>
                                <span class="block font-medium text-slate-900">Địa chỉ</span>
                                <span class="text-slate-600">123 Đường ABC, Quận 1, TP. Hồ Chí Minh</span>
                            </div>
                        </li>
                        <li class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center text-indigo-600 shadow-sm flex-shrink-0">
                                <i class="fa-solid fa-phone"></i>
                            </div>
                            <div>
                                <span class="block font-medium text-slate-900">Hotline</span>
                                <span class="text-slate-600">1900 123 456</span>
                            </div>
                        </li>
                        <li class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center text-indigo-600 shadow-sm flex-shrink-0">
                                <i class="fa-solid fa-envelope"></i>
                            </div>
                            <div>
                                <span class="block font-medium text-slate-900">Email</span>
                                <span class="text-slate-600">support@sneakerzone.vn</span>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
            
            {{-- CỘT 2: Map Embed (Sẽ ở bên phải) --}}
            <div class="space-y-8">
                {{-- Map Embed (Placeholder) --}}
                {{-- Thêm class lg:h-full để map chiếm hết chiều cao còn trống trên màn hình lớn --}}
                <div class="rounded-2xl overflow-hidden h-64 lg:h-full bg-slate-200 border border-slate-300 relative group">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3919.4946681007846!2d106.69988221474886!3d10.77337426219612!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31752f40a3b49e59%3A0xa1bd14e483a602db!2zQ2hvIELhurcgdCBUaMOgbmggUGjhu5E!5e0!3m2!1svi!2s!4v1646726252934!5m2!1svi!2s" 
                        width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection