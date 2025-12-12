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

            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden sticky top-6">
                
                {{-- Banner & Avatar Section --}}
                <div class="relative">
                    {{-- Banner Gradient --}}
                    <div class="h-32 bg-gradient-to-br from-indigo-600 via-purple-600 to-indigo-800 relative overflow-hidden">
                        <div class="absolute inset-0 bg-[url('https://grainy-gradients.vercel.app/noise.svg')] opacity-20"></div>
                    </div>

                    {{-- Avatar Wrapper --}}
                    <div class="absolute -bottom-12 left-1/2 -translate-x-1/2">
                        <div class="w-24 h-24 rounded-full bg-white p-1.5 shadow-xl ring-1 ring-slate-100 relative group">
                            <img src="{{ $customer->avatar_url ?? asset('images/default-avatar.png') }}" 
                                alt="{{ $customer->full_name }}"
                                class="w-full h-full rounded-full object-cover bg-slate-100">
                            
                            {{-- Status Indicator (Online/Offline/Status) --}}
                            <div class="absolute bottom-1 right-1 w-5 h-5 border-4 border-white rounded-full 
                                {{ $customer->status === 'active' ? 'bg-emerald-500' : 'bg-rose-500' }}"
                                title="{{ $customer->status === 'active' ? 'Đang hoạt động' : 'Bị khóa' }}">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Main Info Section --}}
                <div class="pt-14 pb-6 px-6 text-center">
                    <h3 class="text-xl font-bold text-slate-800 flex items-center justify-center gap-2">
                        {{ $customer->full_name }}
                        @if($customer->email_verified_at)
                            <i class="fa-solid fa-circle-check text-blue-500 text-sm" title="Đã xác thực Email"></i>
                        @endif
                    </h3>
                    
                    <div class="mt-1 flex items-center justify-center gap-2 text-sm text-slate-500">
                        <span>{{ $customer->customer_group ?? 'Thành viên' }}</span>
                        <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                        <span class="font-mono">{{ $customer->code ?? '#ID: ' . $customer->id }}</span>
                    </div>

                    {{-- Status Badges --}}
                    <div class="mt-4 flex justify-center gap-2">
                        @if($customer->status === 'active')
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-100">
                                Hoạt động
                            </span>
                        @else
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-100">
                                Đã khóa
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Quick Stats Grid (Thống kê nhanh) --}}
                <div class="grid grid-cols-2 border-y border-slate-100 bg-slate-50/50 divide-x divide-slate-100">
                    <div class="p-4 text-center">
                        <p class="text-xs text-slate-400 font-bold uppercase tracking-wider">Đơn hàng</p>
                        <p class="text-lg font-bold text-slate-700 mt-1">{{ $customer->orders_count ?? 0 }}</p>
                    </div>
                    <div class="p-4 text-center">
                        <p class="text-xs text-slate-400 font-bold uppercase tracking-wider">Tổng chi tiêu</p>
                        <p class="text-lg font-bold text-indigo-600 mt-1">
                            {{ number_format($customer->total_spent ?? 0) }}đ
                        </p>
                    </div>
                </div>

                {{-- Detailed Info List --}}
                <div class="p-6 space-y-5">

                    {{-- Email --}}
                    <div class="flex items-start gap-3 group">
                        <div class="w-9 h-9 rounded-lg bg-slate-50 flex items-center justify-center text-slate-400 group-hover:bg-indigo-50 group-hover:text-indigo-500 transition-colors flex-shrink-0">
                            <i class="fa-solid fa-envelope"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs text-slate-400 font-bold uppercase">Email</p>
                            <p class="text-sm font-medium text-slate-700 truncate" title="{{ $customer->email }}">
                                {{ $customer->email }}
                            </p>
                        </div>
                    </div>

                    {{-- Phone --}}
                    <div class="flex items-start gap-3 group">
                        <div class="w-9 h-9 rounded-lg bg-slate-50 flex items-center justify-center text-slate-400 group-hover:bg-indigo-50 group-hover:text-indigo-500 transition-colors flex-shrink-0">
                            <i class="fa-solid fa-phone"></i>
                        </div>
                        <div>
                            <p class="text-xs text-slate-400 font-bold uppercase">Điện thoại</p>
                            <p class="text-sm font-medium text-slate-700">{{ $customer->phone ?? 'Chưa cập nhật' }}</p>
                        </div>
                    </div>

                    {{-- Gender & Birthday (Two columns row) --}}
                    <div class="grid grid-cols-2 gap-4">
                        {{-- Gender --}}
                        <div class="flex items-start gap-3 group">
                            <div class="w-9 h-9 rounded-lg bg-slate-50 flex items-center justify-center text-slate-400 group-hover:bg-indigo-50 group-hover:text-indigo-500 transition-colors flex-shrink-0">
                                <i class="fa-solid fa-venus-mars"></i>
                            </div>
                            <div>
                                <p class="text-xs text-slate-400 font-bold uppercase">Giới tính</p>
                                <p class="text-sm font-medium text-slate-700">
                                    @if($customer->gender == 'male') Nam
                                    @elseif($customer->gender == 'female') Nữ
                                    @else Khác @endif
                                </p>
                            </div>
                        </div>

                        {{-- Birthday --}}
                        <div class="flex items-start gap-3 group">
                            <div class="w-9 h-9 rounded-lg bg-slate-50 flex items-center justify-center text-slate-400 group-hover:bg-indigo-50 group-hover:text-indigo-500 transition-colors flex-shrink-0">
                                <i class="fa-solid fa-cake-candles"></i>
                            </div>
                            <div>
                                <p class="text-xs text-slate-400 font-bold uppercase">Ngày sinh</p>
                                <p class="text-sm font-medium text-slate-700">
                                    {{ $customer->birthday ? \Carbon\Carbon::parse($customer->birthday)->format('d/m/Y') : '--/--' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-slate-100"></div>

                    {{-- Default Address --}}
                    @php
                        $defaultAddress = $customer->addresses->where('is_default', true)->first();
                    @endphp
                    <div class="flex items-start gap-3 group">
                        <div class="w-9 h-9 rounded-lg bg-slate-50 flex items-center justify-center text-slate-400 group-hover:bg-rose-50 group-hover:text-rose-500 transition-colors flex-shrink-0">
                            <i class="fa-solid fa-location-dot"></i>
                        </div>
                        <div class="flex-1">
                            <div class="flex justify-between items-center mb-1">
                                <p class="text-xs text-slate-400 font-bold uppercase">Địa chỉ mặc định</p>
                                @if($defaultAddress)
                                    <span class="text-[10px] bg-slate-100 text-slate-500 px-1.5 py-0.5 rounded border border-slate-200">Default</span>
                                @endif
                            </div>
                            
                            @if($defaultAddress)
                                <p class="text-sm font-medium text-slate-700 leading-snug">
                                    {{ $defaultAddress->address }}
                                </p>
                                <p class="text-xs text-slate-500 mt-1">
                                    {{ $defaultAddress->ward }}, {{ $defaultAddress->district }}, {{ $defaultAddress->city }}
                                </p>
                                <p class="text-xs text-slate-400 mt-1">
                                    <i class="fa-solid fa-user text-[10px] mr-1"></i> {{ $defaultAddress->contact_name }} 
                                    <span class="mx-1">•</span> 
                                    <i class="fa-solid fa-phone text-[10px] mr-1"></i> {{ $defaultAddress->phone }}
                                </p>
                            @else
                                <p class="text-sm text-slate-500 italic">Chưa thiết lập địa chỉ mặc định.</p>
                            @endif
                        </div>
                    </div>

                    {{-- Other Addresses (Collapsible logic usually goes here, but keeping it simple list) --}}
                    @if($customer->addresses->where('is_default', false)->count() > 0)
                        <div class="flex items-start gap-3 group">
                            <div class="w-9 h-9 rounded-lg bg-slate-50 flex items-center justify-center text-slate-400 group-hover:bg-slate-200 transition-colors flex-shrink-0">
                                <i class="fa-solid fa-map"></i>
                            </div>
                            <div>
                                <p class="text-xs text-slate-400 font-bold uppercase mb-1">Địa chỉ khác ({{ $customer->addresses->where('is_default', false)->count() }})</p>
                                <ul class="text-xs text-slate-600 space-y-2 max-h-32 overflow-y-auto custom-scrollbar">
                                    @foreach($customer->addresses->where('is_default', false) as $addr)
                                        <li class="pl-2 border-l-2 border-slate-200">
                                            <span class="font-medium text-slate-700">{{ $addr->city }}</span> - {{ $addr->district }}
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif

                    <div class="border-t border-slate-100"></div>

                    {{-- System Meta Data --}}
                    <div class="bg-slate-50 rounded-xl p-3 space-y-2">
                        <div class="flex justify-between items-center text-xs">
                            <span class="text-slate-400">Đăng ký:</span>
                            <span class="font-medium text-slate-600">{{ $customer->created_at->format('d/m/Y') }}</span>
                        </div>
                        <div class="flex justify-between items-center text-xs">
                            <span class="text-slate-400">Login cuối:</span>
                            <span class="font-medium text-slate-600">
                                {{ $customer->last_login_at ? \Carbon\Carbon::parse($customer->last_login_at)->diffForHumans() : 'N/A' }}
                            </span>
                        </div>
                        <div class="flex justify-between items-center text-xs">
                            <span class="text-slate-400">IP cuối:</span>
                            <span class="font-mono text-slate-600">{{ $customer->last_login_ip ?? 'Unknown' }}</span>
                        </div>
                    </div>

                </div>

                {{-- Actions --}}
                <div class="px-6 pb-6">
                    <a href="{{ route('admin.customers.edit', $customer->id) }}" 
                    class="flex items-center justify-center w-full py-3 px-4 bg-slate-900 hover:bg-slate-800 text-white text-sm font-semibold rounded-xl shadow-lg shadow-slate-900/10 transition-all hover:scale-[1.02] active:scale-[0.98]">
                        <i class="fa-solid fa-pen-to-square mr-2"></i> Chỉnh sửa hồ sơ
                    </a>
                    
                    {{-- Reset Password Button (Optional nice-to-have) --}}
                    <button type="button" onclick="confirm('Gửi email reset mật khẩu?')" class="mt-3 w-full text-xs text-slate-500 hover:text-indigo-600 font-medium transition-colors">
                        <i class="fa-solid fa-key mr-1"></i> Gửi yêu cầu đặt lại mật khẩu
                    </button>
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