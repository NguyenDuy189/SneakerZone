@extends('client.layouts.app')

@section('title', 'Tra Cứu Đơn Hàng - Sneaker Zone')

@section('content')
<div class="bg-slate-50 min-h-[60vh] flex items-center justify-center py-12">
    <div class="w-full max-w-lg px-4">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-slate-900">Tra cứu đơn hàng</h1>
            <p class="text-slate-500 mt-2">Nhập mã đơn hàng để kiểm tra tình trạng vận chuyển</p>
        </div>

        <div class="bg-white p-8 rounded-2xl shadow-xl border border-slate-100">
            <form action="#" method="GET">
                <div class="mb-6">
                    <label class="block text-sm font-bold text-slate-700 mb-2">Mã đơn hàng</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fa-solid fa-box text-slate-400"></i>
                        </div>
                        <input type="text" name="order_code" 
                            class="pl-10 w-full rounded-lg border-slate-300 focus:ring-indigo-500 focus:border-indigo-500 py-3 uppercase tracking-wider font-semibold" 
                            placeholder="VD: SNZ123456">
                    </div>
                    <p class="text-xs text-slate-400 mt-2">* Mã đơn hàng được gửi trong email xác nhận</p>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-bold text-slate-700 mb-2">Số điện thoại / Email đặt hàng</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fa-regular fa-user text-slate-400"></i>
                        </div>
                        <input type="text" class="pl-10 w-full rounded-lg border-slate-300 focus:ring-indigo-500 focus:border-indigo-500 py-3" 
                            placeholder="Nhập SĐT hoặc Email">
                    </div>
                </div>

                <button type="submit" class="w-full bg-indigo-600 text-white font-bold py-3.5 rounded-xl hover:bg-indigo-700 transition-all hover:scale-[1.02] shadow-lg shadow-indigo-200">
                    <i class="fa-solid fa-magnifying-glass mr-2"></i> Tra cứu ngay
                </button>
            </form>
        </div>
        
        <div class="text-center mt-8">
            <a href="{{ route('page.contact') }}" class="text-indigo-600 hover:underline font-medium text-sm">
                Gặp vấn đề khi tra cứu? Liên hệ hỗ trợ
            </a>
        </div>
    </div>
</div>
@endsection