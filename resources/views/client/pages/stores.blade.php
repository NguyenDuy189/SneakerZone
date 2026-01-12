@extends('client.layouts.app')

@section('title', 'Hệ Thống Cửa Hàng - Sneaker Zone')

@section('content')
<div class="bg-slate-50 py-12">
    <div class="container mx-auto px-4">
        <h1 class="text-3xl font-bold text-slate-900 mb-8 text-center">Hệ thống cửa hàng Sneaker Zone</h1>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            {{-- Store 1 --}}
            <div class="bg-white rounded-2xl overflow-hidden shadow-sm border border-slate-100 group">
                <div class="h-48 overflow-hidden relative">
                    <img src="https://placehold.co/600x400?text=Store+HCM" alt="Store HCM" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    <div class="absolute top-4 right-4 bg-green-500 text-white text-xs font-bold px-2 py-1 rounded">Đang mở cửa</div>
                </div>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-slate-900 mb-2">Sneaker Zone Flagship</h3>
                    <p class="text-slate-600 text-sm mb-4"><i class="fa-solid fa-location-dot mr-2 text-indigo-600"></i> 123 Nguyễn Huệ, Quận 1, TP. HCM</p>
                    <p class="text-slate-600 text-sm mb-4"><i class="fa-solid fa-phone mr-2 text-indigo-600"></i> 0909 123 456</p>
                    <a href="https://maps.google.com" target="_blank" class="block w-full text-center bg-slate-100 text-slate-700 font-semibold py-2 rounded-lg hover:bg-indigo-600 hover:text-white transition-colors">
                        Chỉ đường
                    </a>
                </div>
            </div>

            {{-- Store 2 --}}
            <div class="bg-white rounded-2xl overflow-hidden shadow-sm border border-slate-100 group">
                <div class="h-48 overflow-hidden relative">
                    <img src="https://placehold.co/600x400?text=Store+Hanoi" alt="Store HN" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    <div class="absolute top-4 right-4 bg-green-500 text-white text-xs font-bold px-2 py-1 rounded">Đang mở cửa</div>
                </div>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-slate-900 mb-2">Sneaker Zone Hà Nội</h3>
                    <p class="text-slate-600 text-sm mb-4"><i class="fa-solid fa-location-dot mr-2 text-indigo-600"></i> 456 Phố Huế, Hai Bà Trưng, Hà Nội</p>
                    <p class="text-slate-600 text-sm mb-4"><i class="fa-solid fa-phone mr-2 text-indigo-600"></i> 0909 654 321</p>
                    <a href="https://maps.google.com" target="_blank" class="block w-full text-center bg-slate-100 text-slate-700 font-semibold py-2 rounded-lg hover:bg-indigo-600 hover:text-white transition-colors">
                        Chỉ đường
                    </a>
                </div>
            </div>

             {{-- Store 3 --}}
             <div class="bg-white rounded-2xl overflow-hidden shadow-sm border border-slate-100 group">
                <div class="h-48 overflow-hidden relative">
                    <img src="https://placehold.co/600x400?text=Store+DaNang" alt="Store DN" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    <div class="absolute top-4 right-4 bg-gray-500 text-white text-xs font-bold px-2 py-1 rounded">Sắp khai trương</div>
                </div>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-slate-900 mb-2">Sneaker Zone Đà Nẵng</h3>
                    <p class="text-slate-600 text-sm mb-4"><i class="fa-solid fa-location-dot mr-2 text-indigo-600"></i> 789 Bạch Đằng, Hải Châu, Đà Nẵng</p>
                    <p class="text-slate-600 text-sm mb-4"><i class="fa-solid fa-phone mr-2 text-indigo-600"></i> ---</p>
                    <button disabled class="block w-full text-center bg-slate-100 text-slate-400 font-semibold py-2 rounded-lg cursor-not-allowed">
                        Chỉ đường
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection