@extends('client.layouts.app')

@section('title', 'Hệ thống cửa hàng - Sneaker Zone')

@section('content')
    <div class="bg-slate-50 py-12">
        <div class="container mx-auto px-4">
            <div class="text-center mb-12">
                <h1 class="text-3xl md:text-4xl font-display font-black text-slate-900 mb-4">HỆ THỐNG CỬA HÀNG</h1>
                <p class="text-slate-500">Ghé thăm Sneaker Zone để trải nghiệm trực tiếp sản phẩm.</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                {{-- Danh sách cửa hàng --}}
                <div class="lg:col-span-1 space-y-6">
                    {{-- Store 1 --}}
                    <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 hover:border-indigo-500 transition-colors group cursor-pointer">
                        <h3 class="font-bold text-lg text-slate-900 group-hover:text-indigo-600 transition-colors">Sneaker Zone Đà Nẵng</h3>
                        <p class="text-slate-500 text-sm mt-2"><i class="fa-solid fa-location-dot mr-2 w-4"></i>123 Nguyễn Văn Linh, Q. Thanh Khê</p>
                        <p class="text-slate-500 text-sm mt-1"><i class="fa-solid fa-phone mr-2 w-4"></i>0905 123 456</p>
                        <div class="mt-4 pt-4 border-t border-slate-100 flex justify-between items-center">
                            <span class="text-xs font-semibold text-green-600 bg-green-50 px-2 py-1 rounded">Đang mở cửa</span>
                            <a href="#" class="text-sm text-indigo-600 font-medium hover:underline">Chỉ đường</a>
                        </div>
                    </div>

                    {{-- Store 2 --}}
                    <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 hover:border-indigo-500 transition-colors group cursor-pointer">
                        <h3 class="font-bold text-lg text-slate-900 group-hover:text-indigo-600 transition-colors">Sneaker Zone Hà Nội</h3>
                        <p class="text-slate-500 text-sm mt-2"><i class="fa-solid fa-location-dot mr-2 w-4"></i>456 Phố Huế, Q. Hai Bà Trưng</p>
                        <p class="text-slate-500 text-sm mt-1"><i class="fa-solid fa-phone mr-2 w-4"></i>0987 654 321</p>
                        <div class="mt-4 pt-4 border-t border-slate-100 flex justify-between items-center">
                            <span class="text-xs font-semibold text-slate-500 bg-slate-100 px-2 py-1 rounded">8:00 - 22:00</span>
                            <a href="#" class="text-sm text-indigo-600 font-medium hover:underline">Chỉ đường</a>
                        </div>
                    </div>

                    {{-- Store 3 --}}
                    <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 hover:border-indigo-500 transition-colors group cursor-pointer">
                        <h3 class="font-bold text-lg text-slate-900 group-hover:text-indigo-600 transition-colors">Sneaker Zone TP.HCM</h3>
                        <p class="text-slate-500 text-sm mt-2"><i class="fa-solid fa-location-dot mr-2 w-4"></i>789 Nguyễn Trãi, Q. 5</p>
                        <p class="text-slate-500 text-sm mt-1"><i class="fa-solid fa-phone mr-2 w-4"></i>0933 888 999</p>
                        <div class="mt-4 pt-4 border-t border-slate-100 flex justify-between items-center">
                            <span class="text-xs font-semibold text-slate-500 bg-slate-100 px-2 py-1 rounded">9:00 - 21:30</span>
                            <a href="#" class="text-sm text-indigo-600 font-medium hover:underline">Chỉ đường</a>
                        </div>
                    </div>
                </div>

                {{-- Bản đồ --}}
                <div class="lg:col-span-2 h-[500px] bg-slate-200 rounded-xl overflow-hidden shadow-sm relative">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3834.110435403755!2d108.212000!3d16.059758!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x314219b5dce5f3db%3A0x2f2115e4b2a8b3e8!2zTmd1eeG7hW4gVsSDbiBMaW5oLCDEkMOgIE7hurVuZywgVmnhu4d0IE5hbQ!5e0!3m2!1svi!2s!4v1650000000000!5m2!1svi!2s" 
                        width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                </div>
            </div>
        </div>
    </div>
@endsection