@extends('client.layouts.app')

@section('title', 'Kho Voucher & Ưu Đãi')

@section('content')
<div class="bg-gray-50 min-h-screen py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        {{-- Header --}}
        <div class="text-center mb-10">
            <h1 class="text-3xl font-bold text-slate-900 mb-2">Kho Mã Giảm Giá</h1>
            <p class="text-slate-500">Săn ngay voucher để mua sắm tiết kiệm hơn tại SneakerZone</p>
        </div>

        {{-- Grid Voucher --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($vouchers as $voucher)
                @php
                    // Tính toán trạng thái
                    $now = now();
                    $isUpcoming = $voucher->start_date > $now;
                    $isExpired  = $voucher->end_date < $now;
                    $isOutStock = $voucher->max_usage > 0 && $voucher->used_count >= $voucher->max_usage;
                    
                    // Xác định trạng thái tổng quan
                    $isValid = !$isUpcoming && !$isExpired && !$isOutStock;

                    // Màu sắc thẻ dựa theo trạng thái
                    $cardClass = $isValid ? 'bg-white border-indigo-100' : 'bg-gray-100 border-gray-200 opacity-75 grayscale-[0.5]';
                    $textClass = $isValid ? 'text-indigo-600' : 'text-gray-500';
                @endphp

                <div class="relative flex flex-col rounded-2xl border-2 {{ $cardClass }} shadow-sm overflow-hidden transition-all hover:shadow-md group">
                    
                    {{-- Phần trang trí (Dải màu bên trái) --}}
                    <div class="absolute left-0 top-0 bottom-0 w-2 {{ $isValid ? 'bg-indigo-500' : 'bg-gray-400' }}"></div>

                    <div class="p-5 pl-7 flex flex-col h-full">
                        {{-- Header Card: Tên & Badge --}}
                        <div class="flex justify-between items-start mb-3">
                            <div>
                                <h3 class="font-black text-2xl {{ $textClass }}">
                                    {{ $voucher->code }}
                                </h3>
                                <span class="text-xs font-medium px-2 py-1 rounded bg-gray-100 text-gray-600 mt-1 inline-block">
                                    {{ $voucher->type == 'percent' ? 'Giảm theo %' : 'Giảm tiền mặt' }}
                                </span>
                            </div>
                            
                            {{-- Badge Trạng Thái --}}
                            @if($isOutStock)
                                <span class="bg-red-100 text-red-700 text-xs font-bold px-2 py-1 rounded-full border border-red-200">
                                    Hết lượt
                                </span>
                            @elseif($isExpired)
                                <span class="bg-gray-200 text-gray-600 text-xs font-bold px-2 py-1 rounded-full">
                                    Đã hết hạn
                                </span>
                            @elseif($isUpcoming)
                                <span class="bg-blue-100 text-blue-700 text-xs font-bold px-2 py-1 rounded-full border border-blue-200">
                                    Sắp diễn ra
                                </span>
                            @else
                                <span class="bg-green-100 text-green-700 text-xs font-bold px-2 py-1 rounded-full border border-green-200 animate-pulse">
                                    Đang hiệu lực
                                </span>
                            @endif
                        </div>

                        {{-- Nội dung chi tiết --}}
                        <div class="flex-1 space-y-2 mb-4">
                            <p class="text-lg font-bold text-gray-800">
                                @if($voucher->type == 'percent')
                                    Giảm {{ $voucher->value }}%
                                    @if($voucher->max_discount_value)
                                        <span class="text-sm font-normal text-gray-500">(Tối đa {{ number_format($voucher->max_discount_value) }}đ)</span>
                                    @endif
                                @else
                                    Giảm {{ number_format($voucher->value) }}đ
                                @endif
                            </p>
                            
                            <div class="text-sm text-gray-500 space-y-1">
                                <p><i class="fa-solid fa-cart-shopping w-5 text-center"></i> Đơn tối thiểu: 
                                    <span class="font-medium text-gray-700">{{ number_format($voucher->min_order_amount) }}đ</span>
                                </p>
                                
                                {{-- Thanh tiến trình lượt sử dụng --}}
                                @if($voucher->max_usage > 0)
                                    @php
                                        $percentUsed = ($voucher->used_count / $voucher->max_usage) * 100;
                                        $colorBar = $percentUsed >= 100 ? 'bg-red-500' : ($percentUsed >= 80 ? 'bg-orange-500' : 'bg-indigo-500');
                                    @endphp
                                    <div class="mt-2">
                                        <div class="flex justify-between text-xs mb-1">
                                            <span>Đã dùng</span>
                                            <span>{{ $voucher->used_count }} / {{ $voucher->max_usage }}</span>
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-1.5">
                                            <div class="{{ $colorBar }} h-1.5 rounded-full" style="width: {{ $percentUsed }}%"></div>
                                        </div>
                                    </div>
                                @else
                                    <p class="text-green-600 text-xs"><i class="fa-solid fa-infinity w-5 text-center"></i> Không giới hạn lượt dùng</p>
                                @endif

                                <p class="text-xs pt-2 border-t border-dashed border-gray-200 mt-2">
                                    <i class="fa-regular fa-clock w-5 text-center"></i>
                                    HSD: {{ \Carbon\Carbon::parse($voucher->end_date)->format('d/m/Y H:i') }}
                                </p>
                            </div>
                        </div>

                        {{-- Nút hành động --}}
                        <div class="mt-auto">
                            @if($isValid)
                                <button onclick="copyToClipboard('{{ $voucher->code }}')" 
                                        class="w-full py-2.5 rounded-xl bg-indigo-50 text-indigo-700 font-bold text-sm hover:bg-indigo-600 hover:text-white transition-all border border-indigo-100 flex items-center justify-center gap-2 group-hover:shadow-lg">
                                    <i class="fa-regular fa-copy"></i> Sao chép mã
                                </button>
                            @elseif($isUpcoming)
                                <button disabled class="w-full py-2.5 rounded-xl bg-gray-100 text-gray-400 font-bold text-sm cursor-not-allowed">
                                    Chưa bắt đầu
                                </button>
                            @else
                                <button disabled class="w-full py-2.5 rounded-xl bg-gray-100 text-gray-400 font-bold text-sm cursor-not-allowed">
                                    Không khả dụng
                                </button>
                            @endif
                        </div>
                    </div>

                    {{-- Trang trí hình tròn cắt vé --}}
                    <div class="absolute -left-3 top-1/2 -mt-3 w-6 h-6 bg-gray-50 rounded-full"></div>
                    <div class="absolute -right-3 top-1/2 -mt-3 w-6 h-6 bg-gray-50 rounded-full"></div>
                </div>
            @endforeach
        </div>
        
        {{-- Empty State --}}
        @if($vouchers->isEmpty())
            <div class="text-center py-20">
                <div class="bg-white p-6 rounded-full inline-block mb-4 shadow-sm">
                    <i class="fa-solid fa-ticket text-4xl text-gray-300"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900">Hiện chưa có mã giảm giá nào</h3>
                <p class="text-gray-500">Vui lòng quay lại sau nhé!</p>
            </div>
        @endif
    </div>
</div>

{{-- Script copy mã --}}
<script>
    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
            // Có thể thay bằng thư viện Toast như SweetAlert2 hoặc Toastr
            alert("Đã sao chép mã: " + text);
        }).catch(err => {
            console.error('Lỗi khi sao chép: ', err);
        });
    }
</script>
@endsection