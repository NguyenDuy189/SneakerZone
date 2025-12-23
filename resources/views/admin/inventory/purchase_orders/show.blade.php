@extends('admin.layouts.app')
@section('title', 'Chi tiết phiếu nhập: ' . $po->code)

@section('content')
<div class="container px-6 mx-auto mb-20 fade-in">
    
    {{-- HEADER --}}
    <div class="flex items-center justify-between my-6">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.purchase_orders.index') }}" class="w-10 h-10 flex items-center justify-center rounded-xl bg-white border border-slate-200 text-slate-500 hover:bg-slate-50 hover:text-indigo-600 transition-all shadow-sm">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <div>
                <h2 class="text-2xl font-bold text-slate-800 flex items-center gap-3">
                    {{ $po->code }}
                    @if($po->status === 'completed')
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700 border border-emerald-200">Đã nhập kho</span>
                    @elseif($po->status === 'cancelled')
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-700 border border-rose-200">Đã hủy</span>
                    @else
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-700 border border-amber-200">Chờ nhập kho</span>
                    @endif
                </h2>
                <p class="text-sm text-slate-500 mt-1">Ngày tạo: {{ $po->created_at->format('d/m/Y H:i') }} bởi <b>{{ $po->creator->full_name ?? 'N/A' }}</b></p>
            </div>
        </div>

        {{-- ACTION BUTTONS (Chỉ hiện khi chưa hoàn thành/hủy) --}}
        @if($po->status === 'pending')
        <div class="flex gap-3">
            <form action="{{ route('admin.purchase_orders.update_status', $po->id) }}" method="POST" onsubmit="return confirm('Xác nhận HỦY phiếu nhập này?');">
                @csrf @method('PUT')
                <input type="hidden" name="status" value="cancelled">
                <button type="submit" class="px-5 py-2.5 bg-white border border-rose-200 text-rose-600 font-bold rounded-xl hover:bg-rose-50 transition-colors">
                    Hủy phiếu
                </button>
            </form>

            <form action="{{ route('admin.purchase_orders.update_status', $po->id) }}" method="POST" onsubmit="return confirm('Xác nhận NHẬP KHO? Tồn kho sẽ được cộng ngay lập tức.');">
                @csrf @method('PUT')
                <input type="hidden" name="status" value="completed">
                <button type="submit" class="px-5 py-2.5 bg-emerald-600 text-white font-bold rounded-xl hover:bg-emerald-700 shadow-lg shadow-emerald-200 transition-all transform active:scale-95 flex items-center">
                    <i class="fa-solid fa-check-double mr-2"></i> Xác nhận nhập kho
                </button>
            </form>
        </div>
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        {{-- CỘT TRÁI: CHI TIẾT SẢN PHẨM --}}
        <div class="lg:col-span-2">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 font-bold text-slate-700">
                    Chi tiết sản phẩm
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="text-xs font-bold text-slate-500 uppercase bg-slate-50 border-b border-slate-100">
                            <tr>
                                <th class="px-6 py-3">Sản phẩm</th>
                                <th class="px-6 py-3 text-right">Đơn giá</th>
                                <th class="px-6 py-3 text-center">SL</th>
                                <th class="px-6 py-3 text-right">Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($po->items as $item)
                            <tr class="hover:bg-slate-50/50 text-sm text-slate-700">
                                <td class="px-6 py-4">
                                    <div class="font-bold">{{ $item->variant->product->name ?? 'SP đã xóa' }}</div>
                                    <div class="text-xs text-slate-500 mt-0.5">
                                        {{ $item->variant->size ?? '' }} / {{ $item->variant->color ?? '' }} — SKU: {{ $item->variant->sku ?? '' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right">{{ number_format($item->import_price, 0, ',', '.') }}</td>
                                <td class="px-6 py-4 text-center font-bold">x{{ $item->quantity }}</td>
                                <td class="px-6 py-4 text-right font-bold">{{ number_format($item->total, 0, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-slate-50 border-t border-slate-200">
                            <tr>
                                <td colspan="3" class="px-6 py-4 text-right font-bold text-slate-600 uppercase">Tổng cộng</td>
                                <td class="px-6 py-4 text-right font-extrabold text-xl text-indigo-600">
                                    {{ number_format($po->total_amount, 0, ',', '.') }} ₫
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        {{-- CỘT PHẢI: THÔNG TIN NCC --}}
        <div class="space-y-6">
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
                <h3 class="font-bold text-slate-800 mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-truck-field text-indigo-500"></i> Nhà cung cấp
                </h3>
                <div class="space-y-3 text-sm">
                    <div>
                        <p class="text-xs text-slate-400 font-bold uppercase">Tên NCC</p>
                        <p class="font-bold text-slate-700 text-base">{{ $po->supplier->name }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-400 font-bold uppercase">Liên hệ</p>
                        <p class="text-slate-600">{{ $po->supplier->contact_name }} - {{ $po->supplier->phone }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-400 font-bold uppercase">Địa chỉ</p>
                        <p class="text-slate-600">{{ $po->supplier->address }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
                <h3 class="font-bold text-slate-800 mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-circle-info text-blue-500"></i> Thông tin thêm
                </h3>
                <div class="space-y-3 text-sm">
                    <div>
                        <p class="text-xs text-slate-400 font-bold uppercase">Ghi chú</p>
                        <p class="text-slate-600 italic bg-slate-50 p-3 rounded-lg border border-slate-100 mt-1">
                            "{{ $po->note ?? 'Không có ghi chú' }}"
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-400 font-bold uppercase">Dự kiến về</p>
                        <p class="text-slate-700 font-medium">
                            {{ $po->expected_at ? $po->expected_at->format('d/m/Y') : 'Không xác định' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection