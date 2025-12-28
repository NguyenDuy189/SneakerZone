@extends('client.layouts.app')

@section('title', 'Đơn hàng của tôi')

@section('content')
<div class="container mx-auto px-4 py-8 min-h-screen">
    <h1 class="text-2xl font-bold mb-6">Lịch sử mua hàng</h1>

    <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-slate-500">
                <thead class="text-xs text-slate-700 uppercase bg-slate-50">
                    <tr>
                        <th class="px-6 py-3">Mã đơn</th>
                        <th class="px-6 py-3">Ngày đặt</th>
                        <th class="px-6 py-3">Tổng tiền</th>
                        <th class="px-6 py-3">Trạng thái</th>
                        <th class="px-6 py-3">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr class="bg-white border-b hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4 font-bold text-slate-900">
                                {{ $order->code }}
                            </td>
                            <td class="px-6 py-4">
                                {{ $order->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-6 py-4 font-bold text-indigo-600">
                                {{ number_format($order->total_amount) }}đ
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $statusColors = [
                                        'pending' => 'bg-yellow-100 text-yellow-800',
                                        'paid' => 'bg-emerald-100 text-emerald-800',
                                        'shipping' => 'bg-blue-100 text-blue-800',
                                        'completed' => 'bg-green-100 text-green-800',
                                        'cancelled' => 'bg-red-100 text-red-800',
                                    ];
                                    $statusNames = [
                                        'pending' => 'Chờ xử lý',
                                        'paid' => 'Đã thanh toán',
                                        'shipping' => 'Đang giao',
                                        'completed' => 'Hoàn thành',
                                        'cancelled' => 'Đã hủy',
                                    ];
                                @endphp
                                <span class="{{ $statusColors[$order->status] ?? 'bg-gray-100' }} px-2.5 py-0.5 rounded text-xs font-bold uppercase">
                                    {{ $statusNames[$order->status] ?? $order->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <a href="{{ route('client.orders.show', $order->code) }}" 
                                   class="text-indigo-600 hover:text-indigo-900 font-bold border border-indigo-200 px-3 py-1.5 rounded-lg hover:bg-indigo-50 transition-colors">
                                    Chi tiết
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-slate-400">
                                <i class="fa-solid fa-box-open text-4xl mb-3 block"></i>
                                Bạn chưa có đơn hàng nào.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        {{-- Phân trang --}}
        <div class="p-4 border-t border-slate-100">
            {{ $orders->links() }}
        </div>
    </div>
</div>
@endsection