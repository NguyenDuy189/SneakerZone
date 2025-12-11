@extends('admin.layouts.app')

@section('title', 'Đơn hàng #' . $order->order_code)
@section('header', 'Chi tiết đơn hàng')

@section('content')
<div class="container px-6 mx-auto mb-20 fade-in">
    
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.orders.index') }}" class="w-10 h-10 flex items-center justify-center rounded-xl bg-white border border-slate-200 text-slate-500 hover:bg-slate-50 hover:text-indigo-600 transition-all shadow-sm">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-2xl font-extrabold text-slate-800 flex items-center gap-3">
                    #{{ $order->order_code }}
                    @php
                        $badgeColors = [
                            'pending' => 'bg-amber-100 text-amber-700',
                            'processing' => 'bg-blue-100 text-blue-700',
                            'shipping' => 'bg-purple-100 text-purple-700',
                            'completed' => 'bg-emerald-100 text-emerald-700',
                            'cancelled' => 'bg-rose-100 text-rose-700',
                        ];
                    @endphp
                    <span class="px-3 py-1 rounded-lg text-sm font-bold {{ $badgeColors[$order->status] ?? 'bg-gray-100' }}">
                        {{ match($order->status) {
                            'pending' => 'Chờ xử lý',
                            'processing' => 'Đang xử lý',
                            'shipping' => 'Đang giao',
                            'completed' => 'Hoàn thành',
                            'cancelled' => 'Đã hủy',
                            default => $order->status
                        } }}
                    </span>
                </h1>
                <p class="text-sm text-slate-500 mt-1 flex items-center gap-2">
                    <i class="fa-regular fa-calendar text-xs"></i> {{ $order->created_at->format('d/m/Y - H:i') }}
                </p>
            </div>
        </div>

        <div class="flex gap-3">
            <a href="{{ route('admin.orders.print', $order->id) }}" target="_blank" class="px-5 py-2.5 bg-white border border-slate-200 text-slate-700 font-bold rounded-xl hover:bg-slate-50 hover:text-indigo-600 shadow-sm transition-all flex items-center">
                <i class="fa-solid fa-print mr-2"></i> In Hóa Đơn
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <div class="lg:col-span-2 space-y-8">
            
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                    <h3 class="font-bold text-slate-800 flex items-center gap-2">
                        <i class="fa-solid fa-basket-shopping text-indigo-500"></i> Danh sách sản phẩm
                    </h3>
                    <span class="text-xs font-bold bg-slate-200 text-slate-600 px-2 py-0.5 rounded">{{ $order->items->count() }} món</span>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-slate-50 text-slate-500 font-bold text-xs uppercase border-b border-slate-100">
                            <tr>
                                <th class="px-6 py-3">Sản phẩm</th>
                                <th class="px-6 py-3 text-right">Đơn giá</th>
                                <th class="px-6 py-3 text-center">SL</th>
                                <th class="px-6 py-3 text-right">Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @foreach($order->items as $item)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-4">
                                        <div class="w-16 h-16 rounded-xl border border-slate-100 bg-white p-1 shadow-sm flex-shrink-0">
                                            <img src="{{ $item->thumbnail ? asset('storage/' . $item->thumbnail) : 'https://placehold.co/100x100' }}" 
                                                 class="w-full h-full object-cover rounded-lg">
                                        </div>
                                        <div>
                                            <div class="font-bold text-slate-800 text-sm mb-1">{{ $item->product_name }}</div>
                                            <div class="flex flex-wrap gap-1">
                                                <span class="text-[10px] font-mono bg-slate-100 text-slate-500 px-1.5 py-0.5 rounded border border-slate-200">{{ $item->sku }}</span>
                                                @if($item->variant)
                                                    <span class="text-[10px] font-bold bg-indigo-50 text-indigo-600 px-1.5 py-0.5 rounded border border-indigo-100">
                                                        {{ $item->variant->size ?? '' }} / {{ $item->variant->color ?? '' }}
                                                    </span>
                                                @else
                                                    <span class="text-[10px] bg-rose-50 text-rose-500 px-1.5 py-0.5 rounded">Sản phẩm gốc đã xóa</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right text-sm font-medium text-slate-600">
                                    {{ number_format($item->price, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 text-center text-sm font-bold text-slate-800">
                                    x{{ $item->quantity }}
                                </td>
                                <td class="px-6 py-4 text-right text-sm font-bold text-slate-800">
                                    {{ number_format($item->total_line, 0, ',', '.') }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="bg-slate-50/50 px-8 py-6 border-t border-slate-200">
                    <div class="flex flex-col items-end gap-3 w-full md:w-1/2 ml-auto">
                        <div class="flex justify-between w-full text-slate-500 text-sm">
                            <span>Tạm tính:</span>
                            <span class="font-medium text-slate-800">{{ number_format($order->items->sum('total_line'), 0, ',', '.') }} đ</span>
                        </div>
                        <div class="flex justify-between w-full text-slate-500 text-sm">
                            <span>Phí vận chuyển:</span>
                            <span class="font-medium text-slate-800">{{ number_format($order->shipping_fee, 0, ',', '.') }} đ</span>
                        </div>
                        <div class="w-full border-t border-slate-200 my-1"></div>
                        <div class="flex justify-between w-full items-center">
                            <span class="font-extrabold text-slate-800 text-base">TỔNG THANH TOÁN</span>
                            <span class="font-extrabold text-2xl text-indigo-600">{{ number_format($order->total_amount, 0, ',', '.') }} đ</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                <h3 class="font-bold text-slate-800 mb-4 flex items-center gap-2 pb-3 border-b border-slate-100">
                    <i class="fa-regular fa-credit-card text-indigo-500"></i> Phương thức thanh toán
                </h3>
                <div class="flex items-center gap-4">
                    <div class="px-4 py-2 bg-slate-100 rounded-lg border border-slate-200 font-bold text-slate-600 uppercase text-sm">
                        {{ $order->payment_method }}
                    </div>
                    @if($order->payment_status == 'paid')
                        <div class="flex items-center gap-2 text-emerald-600 font-bold text-sm bg-emerald-50 px-3 py-2 rounded-lg border border-emerald-100">
                            <i class="fa-solid fa-check-circle"></i> Đã thanh toán
                        </div>
                    @elseif($order->payment_status == 'refunded')
                        <div class="flex items-center gap-2 text-rose-600 font-bold text-sm bg-rose-50 px-3 py-2 rounded-lg border border-rose-100">
                            <i class="fa-solid fa-rotate-left"></i> Đã hoàn tiền
                        </div>
                    @else
                        <div class="flex items-center gap-2 text-amber-600 font-bold text-sm bg-amber-50 px-3 py-2 rounded-lg border border-amber-100">
                            <i class="fa-regular fa-clock"></i> Chưa thanh toán
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="lg:col-span-1 space-y-8">
            
            <div class="bg-white rounded-2xl shadow-md shadow-indigo-500/10 border border-slate-200 overflow-hidden relative">
                <div class="h-1 bg-indigo-500 w-full absolute top-0 left-0"></div>
                <div class="p-6">
                    <h3 class="font-bold text-slate-800 mb-4">Cập nhật trạng thái</h3>
                    <form action="{{ route('admin.orders.update_status', $order->id) }}" method="POST" class="space-y-4">
                        @csrf
                        @method('PUT')
                        
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Trạng thái đơn hàng</label>
                            <select name="status" class="w-full rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 py-2.5 font-medium text-slate-700 cursor-pointer">
                                <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }}>🟡 Chờ xử lý</option>
                                <option value="processing" {{ $order->status == 'processing' ? 'selected' : '' }}>🔵 Đang đóng gói</option>
                                <option value="shipping" {{ $order->status == 'shipping' ? 'selected' : '' }}>🟣 Đang giao</option>
                                <option value="completed" {{ $order->status == 'completed' ? 'selected' : '' }}>🟢 Hoàn thành</option>
                                <option value="cancelled" {{ $order->status == 'cancelled' ? 'selected' : '' }}>🔴 Hủy đơn hàng</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Thanh toán</label>
                            <select name="payment_status" class="w-full rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 py-2.5 font-medium text-slate-700 cursor-pointer">
                                <option value="unpaid" {{ $order->payment_status == 'unpaid' ? 'selected' : '' }}>Chưa thanh toán</option>
                                <option value="paid" {{ $order->payment_status == 'paid' ? 'selected' : '' }}>✅ Đã thanh toán</option>
                                <option value="refunded" {{ $order->payment_status == 'refunded' ? 'selected' : '' }}>↩️ Hoàn tiền</option>
                            </select>
                        </div>

                        <button type="submit" class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-lg shadow-indigo-600/20 transition-all active:scale-95 flex items-center justify-center gap-2">
                            <i class="fa-solid fa-floppy-disk"></i> Lưu thay đổi
                        </button>
                    </form>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                <h3 class="font-bold text-slate-800 mb-4 pb-3 border-b border-slate-100 flex items-center gap-2">
                    <i class="fa-solid fa-user-circle text-indigo-500"></i> Khách hàng
                </h3>
                
                <div class="flex items-center gap-4 mb-6">
                    <div class="w-12 h-12 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center text-xl font-bold text-slate-600">
                        {{ substr($order->shipping_address['contact_name'] ?? 'U', 0, 1) }}
                    </div>
                    <div>
                        <div class="font-bold text-slate-800">{{ $order->shipping_address['contact_name'] ?? 'Khách lẻ' }}</div>
                        <div class="text-xs text-slate-500 bg-slate-100 px-2 py-0.5 rounded inline-block mt-1">
                            {{ $order->user ? 'Thành viên' : 'Khách vãng lai' }}
                        </div>
                    </div>
                </div>

                <div class="space-y-4 text-sm">
                    <div class="flex gap-3">
                        <div class="w-8 flex-shrink-0 flex items-center justify-center text-slate-400">
                            <i class="fa-solid fa-phone"></i>
                        </div>
                        <div>
                            <p class="text-xs text-slate-400 font-bold uppercase">Điện thoại</p>
                            <p class="font-medium text-slate-700">{{ $order->shipping_address['phone'] ?? '---' }}</p>
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <div class="w-8 flex-shrink-0 flex items-center justify-center text-slate-400">
                            <i class="fa-solid fa-location-dot"></i>
                        </div>
                        <div>
                            <p class="text-xs text-slate-400 font-bold uppercase">Địa chỉ</p>
                            <p class="font-medium text-slate-700 leading-relaxed">
                                {{ $order->shipping_address['address'] ?? '' }}<br>
                                {{ $order->shipping_address['ward'] ?? '' }} - {{ $order->shipping_address['district'] ?? '' }}<br>
                                {{ $order->shipping_address['city'] ?? '' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                <h3 class="font-bold text-slate-800 mb-2 flex items-center gap-2">
                    <i class="fa-solid fa-note-sticky text-amber-500"></i> Ghi chú
                </h3>
                <div class="bg-amber-50 border border-amber-100 rounded-xl p-4 text-sm text-amber-800 italic">
                    "{{ $order->note ?? 'Khách hàng không để lại ghi chú.' }}"
                </div>
            </div>

        </div>
    </div>
</div>
@endsection