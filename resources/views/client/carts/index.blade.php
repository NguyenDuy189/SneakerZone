@extends('client.layouts.app')

@section('title', 'Giỏ hàng của bạn')

@section('content')
<div class="bg-slate-50 min-h-screen py-10">
    <div class="container mx-auto px-4">
        
        <h1 class="text-3xl font-black text-slate-900 mb-8 uppercase">Giỏ hàng của bạn</h1>

        @if(session('success'))
            <div class="mb-4 p-4 bg-emerald-100 text-emerald-700 rounded-lg border border-emerald-200">
                <i class="fa-solid fa-check-circle mr-2"></i> {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 p-4 bg-rose-100 text-rose-700 rounded-lg border border-rose-200">
                <i class="fa-solid fa-circle-exclamation mr-2"></i> {{ session('error') }}
            </div>
        @endif

        @if($cartItems->count() > 0)
            <div class="flex flex-col lg:flex-row gap-8">
                
                {{-- DANH SÁCH SẢN PHẨM (CỘT TRÁI) --}}
                <div class="w-full lg:w-2/3">
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                        {{-- Header bảng --}}
                        <div class="hidden md:grid grid-cols-12 gap-4 p-4 bg-slate-50 border-b border-slate-100 text-xs font-bold text-slate-500 uppercase">
                            <div class="col-span-6">Sản phẩm</div>
                            <div class="col-span-2 text-center">Đơn giá</div>
                            <div class="col-span-2 text-center">Số lượng</div>
                            <div class="col-span-2 text-right">Tổng</div>
                        </div>

                        {{-- Loop Items --}}
                        @php $totalCart = 0; @endphp
                        @foreach($cartItems as $item)
                            @php 
                                $price = $item->variant->price ?? $item->variant->product->price_min;
                                $subtotal = $price * $item->quantity;
                                $totalCart += $subtotal;
                            @endphp
                            
                            <div class="p-4 border-b border-slate-100 last:border-0 hover:bg-slate-50/50 transition-colors">
                                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-center">
                                    
                                    {{-- Thông tin SP --}}
                                    <div class="col-span-1 md:col-span-6 flex items-center gap-4">
                                        {{-- Ảnh --}}
                                        <div class="w-20 h-20 flex-shrink-0 rounded-lg border border-slate-200 overflow-hidden bg-slate-100">
                                            @php
                                                $img = $item->variant->image ?? $item->variant->product->image;
                                            @endphp
                                            <img src="{{ $img ? asset('storage/'.$img) : asset('img/no-image.png') }}" class="w-full h-full object-cover">
                                        </div>
                                        
                                        {{-- Tên & Thuộc tính --}}
                                        <div>
                                            <h3 class="font-bold text-slate-900 line-clamp-1">
                                                <a href="{{ route('client.products.show', $item->variant->product->slug) }}" class="hover:text-indigo-600 transition-colors">
                                                    {{ $item->variant->product->name }}
                                                </a>
                                            </h3>
                                            
                                            {{-- Hiển thị Size / Màu --}}
                                            <div class="text-xs text-slate-500 mt-1 space-x-2">
                                                @foreach($item->variant->attributeValues as $attr)
                                                    <span class="bg-slate-100 px-2 py-1 rounded border border-slate-200">
                                                        {{ $attr->attribute->name }}: <strong>{{ $attr->value }}</strong>
                                                    </span>
                                                @endforeach
                                            </div>

                                            {{-- Nút xóa mobile --}}
                                            <a href="{{ route('client.cart.remove', $item->id) }}" class="md:hidden text-rose-500 text-xs mt-2 inline-block font-medium">
                                                <i class="fa-solid fa-trash"></i> Xóa
                                            </a>
                                        </div>
                                    </div>

                                    {{-- Đơn giá --}}
                                    <div class="col-span-1 md:col-span-2 text-left md:text-center text-sm font-medium text-slate-600">
                                        <span class="md:hidden text-xs text-slate-400">Giá: </span>
                                        {{ number_format($price, 0, ',', '.') }}đ
                                    </div>

                                    {{-- Số lượng (Form cập nhật) --}}
                                    <div class="col-span-1 md:col-span-2 flex justify-center">
                                        <form action="{{ route('client.cart.update') }}" method="POST" class="flex items-center border border-slate-300 rounded-lg h-9">
                                            @csrf
                                            <input type="hidden" name="id" value="{{ $item->id }}">
                                            
                                            {{-- Giảm --}}
                                            <button type="submit" name="quantity" value="{{ $item->quantity - 1 }}" 
                                                    class="px-2 text-slate-500 hover:text-slate-900 hover:bg-slate-100 h-full rounded-l-lg {{ $item->quantity <= 1 ? 'opacity-50 pointer-events-none' : '' }}">
                                                <i class="fa-solid fa-minus text-xs"></i>
                                            </button>
                                            
                                            {{-- Input hiển thị --}}
                                            <input type="text" value="{{ $item->quantity }}" class="w-10 text-center text-sm font-bold text-slate-900 outline-none border-x border-slate-200 h-full" readonly>
                                            
                                            {{-- Tăng --}}
                                            <button type="submit" name="quantity" value="{{ $item->quantity + 1 }}" 
                                                    class="px-2 text-slate-500 hover:text-slate-900 hover:bg-slate-100 h-full rounded-r-lg">
                                                <i class="fa-solid fa-plus text-xs"></i>
                                            </button>
                                        </form>
                                    </div>

                                    {{-- Thành tiền & Xóa --}}
                                    <div class="col-span-1 md:col-span-2 text-right">
                                        <div class="text-slate-900 font-bold">
                                            {{ number_format($subtotal, 0, ',', '.') }}đ
                                        </div>
                                        <a href="{{ route('client.cart.remove', $item->id) }}" class="hidden md:inline-block text-slate-400 hover:text-rose-500 text-lg mt-1 transition-colors" title="Xóa sản phẩm">
                                            <i class="fa-regular fa-trash-can"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    <div class="mt-6">
                        <a href="{{ route('client.home') }}" class="inline-flex items-center gap-2 text-indigo-600 font-bold hover:underline">
                            <i class="fa-solid fa-arrow-left"></i> Tiếp tục mua sắm
                        </a>
                    </div>
                </div>

                {{-- TỔNG KẾT ĐƠN HÀNG (CỘT PHẢI) --}}
                <div class="w-full lg:w-1/3">
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 sticky top-24">
                        <h3 class="font-bold text-lg text-slate-900 mb-6 uppercase">Tổng đơn hàng</h3>
                        
                        <div class="flex justify-between items-center mb-4 text-slate-600">
                            <span>Tạm tính</span>
                            <span class="font-medium">{{ number_format($totalCart, 0, ',', '.') }}đ</span>
                        </div>
                        
                        <div class="flex justify-between items-center mb-6 pt-4 border-t border-slate-100">
                            <span class="font-bold text-slate-900 text-lg">Tổng cộng</span>
                            <span class="font-black text-indigo-600 text-2xl">{{ number_format($totalCart, 0, ',', '.') }}đ</span>
                        </div>

                        <a href="#" class="block w-full bg-slate-900 text-white text-center font-bold py-4 rounded-xl hover:bg-indigo-600 transition-all shadow-lg shadow-slate-900/20 uppercase tracking-wide">
                            Tiến hành thanh toán
                        </a>

                        <div class="mt-4 text-xs text-slate-400 text-center">
                            Phí vận chuyển sẽ được tính tại trang thanh toán.
                        </div>
                    </div>
                </div>
            </div>
        @else
            {{-- GIỎ HÀNG TRỐNG --}}
            <div class="text-center py-20 bg-white rounded-2xl border border-dashed border-slate-300">
                <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-slate-50 text-slate-300 mb-6">
                    <i class="fa-solid fa-cart-shopping text-4xl"></i>
                </div>
                <h2 class="text-2xl font-bold text-slate-900 mb-2">Giỏ hàng của bạn đang trống</h2>
                <p class="text-slate-500 mb-8">Hãy chọn những món đồ yêu thích để lấp đầy giỏ hàng nhé!</p>
                <a href="{{ route('client.home') }}" class="inline-block px-8 py-3 bg-indigo-600 text-white font-bold rounded-lg hover:bg-indigo-700 transition-colors">
                    Mua sắm ngay
                </a>
            </div>
        @endif
    </div>
</div>
@endsection