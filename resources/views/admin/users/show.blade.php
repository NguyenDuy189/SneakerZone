@extends('admin.layouts.app')

@section('title', 'Hồ sơ: ' . $user->full_name)

@section('content')

{{-- CẤU HÌNH HIỂN THỊ (Mapping dữ liệu) --}}
@php
    // 1. Map Giới tính
    $genderMap = [
        'male'   => ['label' => 'Nam', 'icon' => 'fa-solid fa-mars text-blue-500'],
        'female' => ['label' => 'Nữ',  'icon' => 'fa-solid fa-venus text-rose-500'],
        'other'  => ['label' => 'Khác','icon' => 'fa-solid fa-genderless text-purple-500'],
    ];
    $gender = $genderMap[$user->gender] ?? ['label' => 'Chưa cập nhật', 'icon' => 'fa-solid fa-question text-slate-400'];

    // 2. Map Trạng thái Đơn hàng
    $orderStatusMap = [
        'pending'    => ['class' => 'bg-yellow-100 text-yellow-800', 'label' => 'Chờ xử lý'],
        'processing' => ['class' => 'bg-blue-100 text-blue-800',     'label' => 'Đang xử lý'],
        'shipping'   => ['class' => 'bg-purple-100 text-purple-800', 'label' => 'Đang giao'],
        'completed'  => ['class' => 'bg-emerald-100 text-emerald-800','label' => 'Hoàn thành'],
        'cancelled'  => ['class' => 'bg-rose-100 text-rose-800',     'label' => 'Đã hủy'],
        'returned'   => ['class' => 'bg-slate-100 text-slate-800',   'label' => 'Trả hàng'],
    ];

    // 3. Thống kê
    $totalSpent = $user->orders->where('payment_status', 'paid')->sum('total_amount');
    $totalOrders = $user->orders->count();
@endphp

<div class="container px-6 mx-auto mb-20 fade-in">
    
    {{-- HEADER --}}
    <div class="flex items-center justify-between mb-8 pt-6">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.users.index') }}" class="w-10 h-10 flex items-center justify-center rounded-xl bg-white border border-slate-200 text-slate-500 hover:bg-slate-50 hover:text-indigo-600 transition-all shadow-sm">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-2xl font-extrabold text-slate-800">Hồ sơ người dùng</h1>
                {{-- [BỔ SUNG] Hiển thị ID và Created_at --}}
                <p class="text-sm text-slate-500">ID: <span class="font-mono font-bold">#{{ $user->id }}</span> • Tham gia: {{ $user->created_at->format('d/m/Y') }}</p>
            </div>
        </div>
        
        {{-- Nút thao tác --}}
        <div class="flex gap-2">
            <a href="{{ route('admin.users.edit', $user->id) }}" class="px-4 py-2 bg-indigo-600 text-white text-sm font-bold rounded-lg hover:bg-indigo-700 shadow-sm shadow-indigo-200 transition-all flex items-center gap-2">
                <i class="fa-solid fa-pen-to-square"></i> Chỉnh sửa
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        {{-- CỘT TRÁI (4 cols): TỔNG QUAN --}}
        <div class="lg:col-span-4 space-y-6">
            
            {{-- Card 1: Avatar & Status --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden relative">
                {{-- Cover background --}}
                <div class="h-24 bg-gradient-to-r from-slate-800 to-slate-600"></div>
                
                <div class="px-6 pb-6 text-center -mt-12">
                    {{-- Avatar Logic --}}
                    <div class="relative w-24 h-24 mx-auto rounded-full border-4 border-white shadow-md bg-white flex items-center justify-center overflow-hidden">
                        @if($user->avatar)
                            <img src="{{ Str::startsWith($user->avatar, 'http') ? $user->avatar : asset('storage/' . $user->avatar) }}" 
                                 class="w-full h-full object-cover"
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <span class="hidden font-bold text-3xl text-slate-400">{{ substr($user->full_name, 0, 1) }}</span>
                        @else
                            <span class="font-bold text-3xl text-slate-600">{{ substr($user->full_name, 0, 1) }}</span>
                        @endif
                    </div>

                    <h2 class="mt-3 text-xl font-bold text-slate-800">{{ $user->full_name }}</h2>
                    <div class="text-sm text-slate-500 mb-4">{{ $user->email }}</div>

                    {{-- Badges --}}
                    <div class="flex justify-center gap-2 flex-wrap">
                        {{-- Role --}}
                        <span class="px-3 py-1 rounded-full text-xs font-bold border {{ $user->role == 'admin' ? 'bg-indigo-50 text-indigo-600 border-indigo-100' : 'bg-slate-100 text-slate-600 border-slate-200' }}">
                            {{ ucfirst($user->role) }}
                        </span>

                        {{-- Status --}}
                        <span class="px-3 py-1 rounded-full text-xs font-bold border {{ $user->status == 'active' ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-rose-50 text-rose-600 border-rose-100' }}">
                            {{ $user->status == 'active' ? 'Hoạt động' : 'Đã khóa' }}
                        </span>

                        {{-- Email Verification --}}
                        @if($user->email_verified_at)
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-600 border border-blue-100" title="Đã xác thực email">
                                <i class="fa-solid fa-check-circle"></i> Xác thực
                            </span>
                        @else
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-600 border border-amber-100" title="Chưa xác thực email">
                                <i class="fa-solid fa-circle-exclamation"></i> Chưa xác thực
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Stats Mini --}}
                <div class="grid grid-cols-2 border-t border-slate-100">
                    <div class="p-4 text-center border-r border-slate-100">
                        <div class="text-xs text-slate-400 uppercase font-bold">Tổng đơn</div>
                        <div class="text-lg font-bold text-slate-700">{{ $totalOrders }}</div>
                    </div>
                    <div class="p-4 text-center">
                        <div class="text-xs text-slate-400 uppercase font-bold">Đã chi tiêu</div>
                        <div class="text-lg font-bold text-emerald-600">{{ number_format($totalSpent) }}đ</div>
                    </div>
                </div>
            </div>

            {{-- Card 2: Thông tin liên hệ --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                <h3 class="font-bold text-slate-800 mb-4 flex items-center gap-2 border-b border-slate-100 pb-3">
                    <i class="fa-solid fa-address-book text-slate-400"></i> Chi tiết liên hệ
                </h3>
                <ul class="space-y-4 text-sm">
                    <li>
                        <div class="text-xs text-slate-400 font-bold uppercase mb-1">Số điện thoại</div>
                        <div class="font-medium text-slate-700 flex items-center gap-2">
                            <i class="fa-solid fa-phone text-slate-300"></i>
                            {{ $user->phone ?? 'Chưa cập nhật' }}
                        </div>
                    </li>
                    <li>
                        <div class="text-xs text-slate-400 font-bold uppercase mb-1">Email</div>
                        <div class="font-medium text-slate-700 flex items-center gap-2">
                            <i class="fa-solid fa-envelope text-slate-300"></i>
                            {{ $user->email }}
                        </div>
                    </li>
                    <li>
                        <div class="text-xs text-slate-400 font-bold uppercase mb-1">Địa chỉ chính</div>
                        <div class="font-medium text-slate-700 flex items-start gap-2">
                            <i class="fa-solid fa-location-dot text-slate-300 mt-1"></i>
                            {{ $user->address ?? 'Chưa cập nhật địa chỉ' }}
                        </div>
                    </li>
                </ul>
            </div>

        </div>

        {{-- CỘT PHẢI (8 cols): CHI TIẾT & LỊCH SỬ --}}
        <div class="lg:col-span-8 space-y-6">
            
            {{-- Card 3: Thông tin cá nhân (Gender, Birthday, Updated_at) --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                <h3 class="font-bold text-slate-800 mb-4 flex items-center gap-2 border-b border-slate-100 pb-3">
                    <i class="fa-solid fa-user-shield text-slate-400"></i> Thông tin cá nhân
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Giới tính --}}
                    <div class="flex items-center gap-4 p-4 rounded-xl bg-slate-50 border border-slate-100">
                        <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center text-lg shadow-sm">
                            <i class="{{ $gender['icon'] }}"></i>
                        </div>
                        <div>
                            <div class="text-xs text-slate-400 font-bold uppercase">Giới tính</div>
                            <div class="font-bold text-slate-700">{{ $gender['label'] }}</div>
                        </div>
                    </div>

                    {{-- Sinh nhật --}}
                    <div class="flex items-center gap-4 p-4 rounded-xl bg-slate-50 border border-slate-100">
                        <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center text-lg shadow-sm text-pink-500">
                            <i class="fa-solid fa-cake-candles"></i>
                        </div>
                        <div>
                            <div class="text-xs text-slate-400 font-bold uppercase">Sinh nhật</div>
                            <div class="font-bold text-slate-700">
                                @if($user->birthday)
                                    {{ \Carbon\Carbon::parse($user->birthday)->format('d/m/Y') }}
                                    <span class="text-xs text-slate-400 font-normal">({{ \Carbon\Carbon::parse($user->birthday)->age }} tuổi)</span>
                                @else
                                    Chưa cập nhật
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- [BỔ SUNG] Updated At - Cập nhật lần cuối --}}
                    <div class="flex items-center gap-4 p-4 rounded-xl bg-slate-50 border border-slate-100 md:col-span-2">
                        <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center text-lg shadow-sm text-amber-500">
                            <i class="fa-solid fa-clock-rotate-left"></i>
                        </div>
                        <div>
                            <div class="text-xs text-slate-400 font-bold uppercase">Cập nhật lần cuối</div>
                            <div class="font-bold text-slate-700">
                                {{ $user->updated_at ? $user->updated_at->format('H:i - d/m/Y') : 'Chưa có thông tin' }}
                                <span class="text-xs text-slate-400 font-normal ml-1">({{ $user->updated_at ? $user->updated_at->diffForHumans() : '' }})</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Card 4: Lịch sử đơn hàng --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                    <h3 class="font-bold text-slate-800 flex items-center gap-2">
                        <i class="fa-solid fa-receipt text-slate-400"></i> Đơn hàng gần đây
                    </h3>
                    <a href="{{ route('admin.orders.index', ['keyword' => $user->full_name]) }}" class="text-xs font-bold text-indigo-600 hover:underline">
                        Xem tất cả
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50 text-slate-500 font-bold text-xs uppercase border-b border-slate-100">
                            <tr>
                                <th class="px-6 py-3">Mã đơn</th>
                                <th class="px-6 py-3">Ngày đặt</th>
                                <th class="px-6 py-3 text-right">Tổng tiền</th>
                                <th class="px-6 py-3 text-center">Trạng thái</th>
                                <th class="px-6 py-3 text-right">Hành động</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @forelse($user->orders()->latest()->take(5)->get() as $order)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4">
                                    <a href="{{ route('admin.orders.show', $order->id) }}" class="font-mono font-bold text-indigo-600 hover:text-indigo-800">
                                        #{{ $order->order_code }}
                                    </a>
                                </td>
                                <td class="px-6 py-4 text-slate-600">
                                    {{ $order->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-6 py-4 text-right font-bold text-slate-700">
                                    {{ number_format($order->total_amount, 0, ',', '.') }}đ
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @php
                                        $status = $orderStatusMap[$order->status] ?? ['class' => 'bg-gray-100 text-gray-600', 'label' => $order->status];
                                    @endphp
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold border {{ $status['class'] }}">
                                        {{ $status['label'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('admin.orders.show', $order->id) }}" class="text-slate-400 hover:text-indigo-600 transition-colors">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-slate-400 italic">
                                    Chưa có đơn hàng nào.
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