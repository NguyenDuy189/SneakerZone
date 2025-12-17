@extends('client.layouts.app')

@section('title', 'Thanh toán đơn hàng')

@section('content')
<div class="bg-[#F8F9FA] min-h-screen py-10 font-sans">
    <div class="container mx-auto px-4 max-w-6xl">
        
        <h1 class="text-2xl font-black uppercase tracking-tight mb-8 flex items-center gap-3">
            <i class="fa-solid fa-credit-card text-indigo-600"></i> Thanh toán
        </h1>

        <form action="{{ route('client.checkout.process') }}" method="POST">
            @csrf
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                
                {{-- CỘT TRÁI: THÔNG TIN GIAO HÀNG (7 phần) --}}
                <div class="lg:col-span-7 space-y-6">
                    <div class="bg-white rounded-xl p-6 shadow-sm border border-slate-100">
                        <h3 class="text-lg font-bold text-slate-900 mb-4 border-b pb-2">Thông tin giao hàng</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Họ và tên <span class="text-red-500">*</span></label>
                                <input type="text" name="customer_name" value="{{ Auth::user()->name ?? '' }}" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-4 py-2.5 text-sm font-bold focus:ring-2 focus:ring-indigo-500 outline-none" required>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Số điện thoại <span class="text-red-500">*</span></label>
                                <input type="text" name="customer_phone" value="{{ Auth::user()->phone ?? '' }}" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-4 py-2.5 text-sm font-bold focus:ring-2 focus:ring-indigo-500 outline-none" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Email <span class="text-red-500">*</span></label>
                            <input type="email" name="customer_email" value="{{ Auth::user()->email ?? '' }}" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-4 py-2.5 text-sm font-bold focus:ring-2 focus:ring-indigo-500 outline-none" required>
                        </div>

                        <div class="mb-4">
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Địa chỉ nhận hàng <span class="text-red-500">*</span></label>
                            <input type="text" name="shipping_address" value="{{ Auth::user()->address ?? '' }}" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-4 py-2.5 text-sm font-bold focus:ring-2 focus:ring-indigo-500 outline-none" placeholder="Số nhà, tên đường, phường/xã..." required>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Ghi chú đơn hàng</label>
                            <textarea name="note" rows="3" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-4 py-2.5 text-sm font-bold focus:ring-2 focus:ring-indigo-500 outline-none" placeholder="Ví dụ: Giao giờ hành chính..."></textarea>
                        </div>
                    </div>

                    {{-- PHƯƠNG THỨC THANH TOÁN --}}
                    <div class="bg-white rounded-xl p-6 shadow-sm border border-slate-100">
                        <h3 class="text-lg font-bold text-slate-900 mb-4 border-b pb-2">Phương thức thanh toán</h3>
                        
                        <div class="space-y-3">
                            <label class="flex items-center gap-4 p-4 border border-slate-200 rounded-lg cursor-pointer hover:border-indigo-500 transition-colors">
                                <input type="radio" name="payment_method" value="cod" checked class="w-5 h-5 text-indigo-600 focus:ring-indigo-500">
                                <div class="flex-1">
                                    <span class="block font-bold text-slate-900 text-sm">Thanh toán khi nhận hàng (COD)</span>
                                    <span class="block text-xs text-slate-500">Bạn chỉ phải thanh toán khi nhận được món hàng</span>
                                </div>
                                <i class="fa-solid fa-money-bill-wave text-slate-400 text-xl"></i>
                            </label>

                            <label class="flex items-center gap-4 p-4 border border-slate-200 rounded-lg cursor-pointer hover:border-indigo-500 transition-colors">
                                <input type="radio" name="payment_method" value="vnpay" class="w-5 h-5 text-indigo-600 focus:ring-indigo-500">
                                <div class="flex-1">
                                    <span class="block font-bold text-slate-900 text-sm">Thanh toán Online (VNPAY)</span>
                                    <span class="block text-xs text-slate-500">Thanh toán qua thẻ ATM/Internet Banking</span>
                                </div>
                                <i class="fa-regular fa-credit-card text-slate-400 text-xl"></i>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- CỘT PHẢI: TÓM TẮT ĐƠN HÀNG (5 phần) --}}
                <div class="lg:col-span-5">
                    <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6 sticky top-24">
                        <h3 class="text-lg font-bold text-slate-900 mb-6 uppercase">Đơn hàng của bạn</h3>
                        
                        {{-- List sản phẩm rút gọn --}}
                        <div class="space-y-4 mb-6 max-h-60 overflow-y-auto pr-2 custom-scrollbar">
                            @foreach($cart->items as $item)
                                <div class="flex gap-3">
                                    <div class="w-12 h-12 rounded bg-slate-50 border border-slate-200 flex-shrink-0 overflow-hidden">
                                        <img src="{{ asset('storage/'.$item->variant->product->image) }}" class="w-full h-full object-cover">
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h4 class="text-sm font-bold text-slate-900 truncate">{{ $item->variant->product->name }}</h4>
                                        <p class="text-xs text-slate-500">
                                            SL: {{ $item->quantity }} x {{ number_format($item->variant->price ?: $item->variant->product->price_min) }}đ
                                        </p>
                                    </div>
                                    <div class="text-sm font-bold text-indigo-600">
                                        {{ number_format($item->quantity * ($item->variant->price ?: $item->variant->product->price_min)) }}đ
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <hr class="border-dashed border-slate-200 my-4">

                        {{-- Tính tiền --}}
                        <div class="space-y-2">
                            <div class="flex justify-between text-sm text-slate-600">
                                <span>Tạm tính</span>
                                <span class="font-bold">{{ number_format($subtotal) }}đ</span>
                            </div>
                            @if($discount > 0)
                                <div class="flex justify-between text-sm text-emerald-600">
                                    <span>Voucher giảm giá</span>
                                    <span class="font-bold">-{{ number_format($discount) }}đ</span>
                                </div>
                            @endif
                            <div class="flex justify-between text-base font-black text-slate-900 pt-3 border-t border-slate-100">
                                <span>Tổng thanh toán</span>
                                <span class="text-indigo-600 text-xl">{{ number_format($total) }}đ</span>
                            </div>
                        </div>

                        <button type="submit" class="block w-full py-4 mt-6 bg-slate-900 text-white font-bold text-center rounded-xl hover:bg-indigo-600 transition-all shadow-lg shadow-indigo-200 uppercase tracking-widest text-xs">
                            Xác nhận đặt hàng
                        </button>
                        
                        <a href="{{ route('client.carts.index') }}" class="block text-center text-xs font-bold text-slate-400 mt-4 hover:text-slate-600">
                            Quay lại giỏ hàng
                        </a>
                    </div>
                </div>

            </div>
        </form>
    </div>
</div>
@endsection