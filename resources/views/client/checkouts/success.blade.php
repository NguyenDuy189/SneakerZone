@extends('client.layouts.app')

@section('title', 'Đặt hàng thành công')

@section('content')
<div class="bg-gray-50 min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 bg-white p-10 rounded-2xl shadow-xl text-center">
        <div class="rounded-full bg-green-100 p-4 w-20 h-20 mx-auto flex items-center justify-center">
            <i class="fa-solid fa-check text-4xl text-green-600"></i>
        </div>
        
        <h2 class="mt-6 text-3xl font-extrabold text-gray-900">
            Đặt hàng thành công!
        </h2>
        
        <p class="mt-2 text-sm text-gray-600">
            Cảm ơn bạn đã mua sắm tại SneakerZone. <br>
            Đơn hàng của bạn đang được xử lý.
        </p>

        <div class="mt-8 space-y-3">
            <a href="{{ route('client.products.index') }}" 
               class="w-full flex items-center justify-center px-4 py-3 border border-transparent text-base font-medium rounded-xl text-white bg-indigo-600 hover:bg-indigo-700 md:py-4 md:text-lg transition-all shadow-lg shadow-indigo-200">
                <i class="fa-solid fa-arrow-left mr-2"></i> Tiếp tục mua sắm
            </a>
            
            {{-- Sửa dòng này --}}
            <a href="{{ url('/') }}" 
               class="w-full flex items-center justify-center px-4 py-3 border border-gray-200 text-base font-medium rounded-xl text-gray-700 bg-white hover:bg-gray-50 md:py-4 md:text-lg transition-all">
                Về trang chủ
            </a>
        </div>
    </div>
</div>
@endsection