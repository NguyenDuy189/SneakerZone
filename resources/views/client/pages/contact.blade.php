@extends('client.layouts.app')

@section('title', 'Liên hệ - Sneaker Zone')

@section('content')
    <div class="bg-white py-12">
        <div class="container mx-auto px-4">
            {{-- Header --}}
            <div class="text-center mb-16">
                <h1 class="text-3xl md:text-4xl font-display font-black text-slate-900 mb-2">LIÊN HỆ VỚI CHÚNG TÔI</h1>
                <p class="text-slate-500">Chúng tôi luôn sẵn sàng lắng nghe bạn.</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-12 max-w-6xl mx-auto">
                
                {{-- Cột Thông tin liên hệ --}}
                <div class="lg:col-span-1 space-y-8">
                    {{-- Box 1 --}}
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-600 flex-shrink-0">
                            <i class="fa-solid fa-location-dot text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-900 text-lg">Địa chỉ cửa hàng</h3>
                            <p class="text-slate-600 mt-1">123 Đường Nguyễn Văn Linh, Quận Thanh Khê, TP. Đà Nẵng</p>
                        </div>
                    </div>

                    {{-- Box 2 --}}
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-600 flex-shrink-0">
                            <i class="fa-solid fa-phone text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-900 text-lg">Hotline hỗ trợ</h3>
                            <p class="text-slate-600 mt-1">0905 123 456</p>
                            <p class="text-slate-500 text-sm">(8:00 - 21:00 hàng ngày)</p>
                        </div>
                    </div>

                    {{-- Box 3 --}}
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-600 flex-shrink-0">
                            <i class="fa-solid fa-envelope text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-900 text-lg">Email</h3>
                            <p class="text-slate-600 mt-1">support@sneakerzone.vn</p>
                        </div>
                    </div>

                    {{-- Map (Nhúng Google Map) --}}
                    <div class="rounded-xl overflow-hidden shadow-sm border border-slate-200 mt-6">
                        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3833.802545582969!2d108.22067831468434!3d16.07573298887672!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x314218307d81c91d%3A0xbc7c14ff5a92c805!2zVHJ1bmcgdMOibSBIw6BuaCBjaMOtbmggVFAuIMSQw6CgIE7hurVuZw!5e0!3m2!1svi!2s!4v1680000000000!5m2!1svi!2s" 
                            width="100%" height="250" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                    </div>
                </div>

                {{-- Cột Form liên hệ --}}
                <div class="lg:col-span-2 bg-slate-50 p-8 rounded-2xl border border-slate-100">
                    <h3 class="text-xl font-bold text-slate-900 mb-6">Gửi tin nhắn cho chúng tôi</h3>
                    
                    <form action="#" method="POST" class="space-y-6">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Họ tên của bạn</label>
                                <input type="text" class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all" placeholder="Nhập họ tên...">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Email</label>
                                <input type="email" class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all" placeholder="Nhập email...">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Số điện thoại</label>
                            <input type="text" class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all" placeholder="Nhập số điện thoại...">
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Nội dung tin nhắn</label>
                            <textarea rows="4" class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all" placeholder="Bạn cần hỗ trợ gì..."></textarea>
                        </div>

                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-8 rounded-lg transition-colors w-full md:w-auto shadow-lg shadow-indigo-200">
                            Gửi tin nhắn
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection