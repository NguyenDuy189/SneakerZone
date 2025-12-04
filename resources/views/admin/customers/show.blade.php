@extends('admin.layouts.app')
@section('title', 'Hồ sơ: ' . $customer->full_name)

@section('content')
<div class="container px-6 mx-auto mb-20 fade-in">
    
    {{-- HEADER --}}
    <div class="flex items-center gap-4 my-6">
        <a href="{{ route('admin.customers.index') }}" class="w-10 h-10 flex items-center justify-center rounded-xl bg-white border border-slate-200 text-slate-500 hover:bg-slate-50 hover:text-indigo-600 transition-all shadow-sm">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Hồ sơ khách hàng</h2>
            <p class="text-sm text-slate-500 mt-0.5">ID: #{{ $customer->id }} — Đăng ký: {{ $customer->created_at->format('d/m/Y') }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        {{-- CỘT TRÁI: THÔNG TIN CÁ NHÂN (PROFILE CARD) --}}
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                {{-- Banner nền --}}
                <div class="h-24 bg-gradient-to-r from-indigo-500 to-purple-600"></div>
                
                <div class="px-6 pb-6 relative">
                    {{-- Avatar --}}
                    <div class="w-20 h-20 rounded-full bg-white p-1 absolute -top-10 left-6 shadow-md">
                        <div class="w-full h-full rounded-full bg-slate-100 flex items-center justify-center text-2xl font-bold text-indigo-600 border border-slate-200">
                            {{ substr($customer->full_name, 0, 1) }}
                        </div>
                    </div>

                    {{-- Nút Action góc phải --}}
                    <div class="flex justify-end pt-4">
                        @if($customer->status === 'active')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700 border border-emerald-200">
                                <i class="fa-solid fa-check-circle mr-1.5"></i> Hoạt động
                            </span>
                        @else
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-700 border border-rose-200">
                                <i class="fa-solid fa-ban mr-1.5"></i> Đã khóa
                            </span>
                        @endif
                    </div>

                    {{-- Thông tin --}}
                    <div class="mt-4">
                        <h3 class="text-xl font-bold text-slate-800">{{ $customer->full_name }}</h3>
                        <p class="text-slate-500 text-sm">{{ $customer->email }}</p>
                    </div>

                    <div class="border-t border-slate-100 my-5"></div>

                    <div class="space-y-4">
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-lg bg-slate-50 flex items-center justify-center text-slate-400 flex-shrink-0">
                                <i class="fa-solid fa-phone"></i>
                            </div>
                            <div>
                                <p class="text-xs text-slate-400 font-bold uppercase">Điện thoại</p>
                                <p class="text-sm font-medium text-slate-700">{{ $customer->phone ?? 'Chưa cập nhật' }}</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-lg bg-slate-50 flex items-center justify-center text-slate-400 flex-shrink-0">
                                <i class="fa-solid fa-location-dot"></i>
                            </div>
                            <div>
                                <p class="text-xs text-slate-400 font-bold uppercase">Địa chỉ</p>
                                <p class="text-sm font-medium text-slate-700 leading-relaxed">
                                    {{ $customer->address ?? 'Chưa cập nhật địa chỉ giao hàng.' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6">
                        <a href="{{ route('admin.customers.edit', $customer->id) }}" class="w-full flex items-center justify-center px-4 py-2.5 bg-slate-900 text-white text-sm font-bold rounded-xl hover:bg-slate-800 transition-all shadow-lg shadow-slate-900/20">
                            <i class="fa-solid fa-pen-to-square mr-2"></i> Chỉnh sửa hồ sơ
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- CỘT PHẢI: STATS & LỊCH SỬ MUA HÀNG --}}
        <div class="lg:col-span-2 space-y-6">
            
            {{-- 3 Mini Cards --}}
            <div class="grid grid-cols-3 gap-4">
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex flex-col items-center justify-center text-center hover:border-indigo-200 transition-colors">
                    <div class="w-10 h-10 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center mb-2">
                        <i class="fa-solid fa-sack-dollar"></i>
                    </div>
                    <p class="text-xs text-slate-400 font-bold uppercase tracking-wide">Tổng chi tiêu</p>
                    <p class="text-xl font-extrabold text-slate-800 mt-1">
                        {{ number_format($customer->orders_sum_total_amount ?? 0, 0, ',', '.') }} <span class="text-sm text-slate-400 font-normal">đ</span>
                    </p>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex flex-col items-center justify-center text-center hover:border-blue-200 transition-colors">
                    <div class="w-10 h-10 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center mb-2">
                        <i class="fa-solid fa-cart-shopping"></i>
                    </div>
                    <p class="text-xs text-slate-400 font-bold uppercase tracking-wide">Đơn hàng</p>
                    <p class="text-xl font-extrabold text-slate-800 mt-1">{{ $customer->orders_count }}</p>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex flex-col items-center justify-center text-center hover:border-amber-200 transition-colors">
                    <div class="w-10 h-10 rounded-full bg-amber-50 text-amber-600 flex items-center justify-center mb-2">
                        <i class="fa-solid fa-star"></i>
                    </div>
                    <p class="text-xs text-slate-400 font-bold uppercase tracking-wide">Đánh giá</p>
                    <p class="text-xl font-extrabold text-slate-800 mt-1">{{ $customer->reviews_count }}</p>
                </div>
            </div>

            {{-- Lịch sử đơn hàng --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                    <h3 class="font-bold text-slate-800 flex items-center gap-2">
                        <i class="fa-solid fa-clock-rotate-left text-slate-400"></i> Lịch sử đơn hàng gần đây
                    </h3>
                    <a href="{{ route('admin.orders.index', ['keyword' => $customer->email]) }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 hover:underline">
                        Xem tất cả
                    </a>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left whitespace-nowrap">
                        <thead class="text-xs font-bold text-slate-500 uppercase bg-slate-50 border-b border-slate-100">
                            <tr>
                                <th class="px-6 py-3">Mã đơn</th>
                                <th class="px-6 py-3">Ngày đặt</th>
                                <th class="px-6 py-3 text-right">Tổng tiền</th>
                                <th class="px-6 py-3 text-center">Trạng thái</th>
                                <th class="px-6 py-3 text-right">Chi tiết</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($orders as $order)
                            <tr class="hover:bg-slate-50/50 transition-colors text-sm text-slate-700">
                                <td class="px-6 py-3">
                                    <a href="{{ route('admin.orders.show', $order->id) }}" class="font-mono text-indigo-600 font-bold hover:underline">
                                        #{{ $order->order_code }}
                                    </a>
                                </td>
                                <td class="px-6 py-3 text-slate-500">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                <td class="px-6 py-3 text-right font-bold">{{ number_format($order->total_amount, 0, ',', '.') }} đ</td>
                                <td class="px-6 py-3 text-center">
                                    @php
                                        $badges = [
                                            'pending' => 'bg-amber-100 text-amber-700',
                                            'processing' => 'bg-blue-100 text-blue-700',
                                            'shipping' => 'bg-purple-100 text-purple-700',
                                            'completed' => 'bg-emerald-100 text-emerald-700',
                                            'cancelled' => 'bg-rose-100 text-rose-700',
                                        ];
                                        $label = [
                                            'pending' => 'Chờ xử lý', 'processing' => 'Đang xử lý',
                                            'shipping' => 'Đang giao', 'completed' => 'Hoàn thành', 
                                            'cancelled' => 'Đã hủy'
                                        ];
                                    @endphp
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold {{ $badges[$order->status] ?? 'bg-slate-100 text-slate-600' }}">
                                        {{ $label[$order->status] ?? $order->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-3 text-right">
                                    <a href="{{ route('admin.orders.show', $order->id) }}" class="w-8 h-8 inline-flex items-center justify-center rounded-lg bg-slate-50 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 transition-colors">
                                        <i class="fa-solid fa-arrow-right"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="py-12 text-center text-slate-400">
                                    <i class="fa-solid fa-box-open text-2xl mb-2 opacity-30 block"></i>
                                    Khách hàng này chưa có đơn hàng nào.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
```

Bạn hãy copy 2 file này vào đúng đường dẫn `resources/views/admin/customers/` nhé. Giao diện sẽ rất chuyên nghiệp và đồng bộ.