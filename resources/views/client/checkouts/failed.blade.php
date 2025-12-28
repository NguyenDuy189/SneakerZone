@extends('client.layouts.app')

@section('title', 'Thanh toán thất bại')

@section('content')
<div class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                <i class="fa-solid fa-xmark text-red-600 text-xl"></i>
            </div>
            
            <h2 class="text-2xl font-bold text-gray-900 mb-2">Thanh toán thất bại!</h2>
            <p class="text-gray-500 mb-6">
                Giao dịch của bạn không thể thực hiện hoặc đã bị hủy. Vui lòng kiểm tra lại thông tin hoặc chọn phương thức khác.
            </p>

            <div class="space-y-3">
                <a href="{{ route('client.checkouts.index') }}" 
                   class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none">
                    Thử thanh toán lại
                </a>
                
                <a href="{{ route('client.home') }}" 
                   class="w-full flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    Về trang chủ
                </a>
            </div>
        </div>
    </div>
</div>
@endsection