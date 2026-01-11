@extends('admin.layouts.app')
@section('title', 'Lịch sử tồn kho')

@section('content')
<div class="container px-6 mx-auto mb-10 fade-in">

    {{-- HEADER --}}
    <div class="flex items-center gap-4 my-6">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Lịch sử tồn kho</h2>
            <p class="text-sm text-slate-500 mt-1">Theo dõi mọi biến động nhập – xuất – kiểm kê</p>
        </div>
    </div>

    {{-- FILTER BAR --}}
    <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200 mb-6">
        <form action="{{ route('admin.inventory.logs.index') }}" method="GET">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                
                {{-- Tìm kiếm --}}
                <div class="md:col-span-5 relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input type="text" name="keyword" value="{{ request('keyword') }}" 
                        placeholder="Tìm theo tên sản phẩm hoặc SKU..." 
                        class="pl-10 pr-4 py-2.5 w-full border border-slate-200 rounded-xl text-sm
                               focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                </div>

                {{-- Loại giao dịch --}}
                <div class="md:col-span-3">
                    <select name="type" class="w-full border border-slate-200 rounded-xl py-2.5 px-3 text-sm 
                        focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 cursor-pointer">
                        <option value=""> Tất cả giao dịch </option>
                        <option value="import" {{ request('type') == 'import' ? 'selected' : '' }}>⬇️ Nhập hàng (Import)</option>
                        <option value="sale" {{ request('type') == 'sale' ? 'selected' : '' }}>⬆️ Bán hàng (Sale)</option>
                        <option value="return" {{ request('type') == 'return' ? 'selected' : '' }}>↩️ Trả hàng (Return)</option>
                        <option value="check" {{ request('type') == 'check' ? 'selected' : '' }}>⚖️ Kiểm kê</option>
                    </select>
                </div>

                {{-- Ngày --}}
                <div class="md:col-span-2">
                    <input type="date" name="date" value="{{ request('date') }}"
                        class="w-full border border-slate-200 rounded-xl py-2.5 px-3 text-sm
                               focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-slate-600">
                </div>

                {{-- Button --}}
                <div class="md:col-span-2 flex gap-2 justify-end">
                    <button type="submit" class="flex-1 px-4 py-2.5 text-sm font-bold text-white bg-slate-900
                            rounded-xl hover:bg-slate-800 shadow-lg shadow-slate-900/20">
                        Lọc
                    </button>

                    @if(request()->hasAny(['keyword','type','date']))
                        <a href="{{ route('admin.inventory.logs') }}"
                            class="px-4 py-2.5 bg-white border border-slate-200 rounded-xl hover:bg-slate-50
                                   text-rose-500 flex items-center justify-center">
                            <i class="fa-solid fa-rotate-left"></i>
                        </a>
                    @endif
                </div>

            </div>
        </form>
    </div>

    {{-- TABLE --}}
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="w-full overflow-x-auto">
            <table class="w-full whitespace-nowrap text-left">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100 text-xs font-bold text-slate-500 uppercase tracking-wider">
                        <th class="px-6 py-4">Thời gian</th>
                        <th class="px-6 py-4">Sản phẩm / SKU</th>
                        <th class="px-6 py-4 text-center">Loại</th>
                        <th class="px-6 py-4 text-center">Thay đổi</th>
                        <th class="px-6 py-4 text-center">Tồn cuối</th>
                        <th class="px-6 py-4">Người thực hiện</th>
                        <th class="px-6 py-4 w-64">Ghi chú / Tham chiếu</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    @forelse($logs as $log)
                    <tr class="hover:bg-slate-50/80 transition-colors text-sm text-slate-700">

                        {{-- Thời gian --}}
                        <td class="px-6 py-4 text-slate-500">
                            {{ $log->created_at->format('d/m/Y') }}<br>
                            <span class="text-xs">{{ $log->created_at->format('H:i:s') }}</span>
                        </td>

                        {{-- Sản phẩm --}}
                        <td class="px-6 py-4">
                            @if($log->variant)
                                <div class="font-bold text-slate-800">{{ $log->variant->product->name ?? 'Sản phẩm đã xóa' }}</div>
                                
                                {{-- SỬA LỖI HIỂN THỊ TẠI ĐÂY --}}
                                <div class="text-xs text-slate-500 mt-1 flex flex-wrap items-center gap-1">
                                    @php
                                        // Xử lý lấy giá trị từ object hoặc string
                                        $v = $log->variant;
                                        $sizeVal  = is_object($v->size) ? ($v->size->value ?? '') : $v->size;
                                        $colorVal = is_object($v->color) ? ($v->color->value ?? '') : $v->color;
                                    @endphp

                                    {{-- Hiển thị Size --}}
                                    @if($sizeVal)
                                        <span class="bg-slate-100 border border-slate-200 px-1.5 py-0.5 rounded text-slate-600 font-medium">
                                            Size: {{ $sizeVal }}
                                        </span>
                                    @endif

                                    {{-- Hiển thị Màu --}}
                                    @if($colorVal)
                                        <span class="bg-slate-100 border border-slate-200 px-1.5 py-0.5 rounded text-slate-600 font-medium">
                                            Màu: {{ $colorVal }}
                                        </span>
                                    @endif
                                    
                                    {{-- SKU --}}
                                    <span class="text-slate-300 mx-1">|</span>
                                    <span class="font-mono text-indigo-600 font-bold">{{ $v->sku }}</span>
                                </div>
                            @else
                                <span class="text-rose-500 italic text-xs bg-rose-50 px-2 py-1 rounded border border-rose-100">
                                    <i class="fa-solid fa-ban mr-1"></i> Biến thể đã bị xóa
                                </span>
                            @endif
                        </td>

                        {{-- Loại --}}
                        <td class="px-6 py-4 text-center">
                            @php
                                $badges = [
                                    'import' => ['bg'=>'bg-emerald-100','text'=>'text-emerald-700','icon'=>'fa-download','label'=>'Nhập kho'],
                                    'sale'   => ['bg'=>'bg-blue-100','text'=>'text-blue-700','icon'=>'fa-cart-shopping','label'=>'Bán hàng'],
                                    'return' => ['bg'=>'bg-amber-100','text'=>'text-amber-700','icon'=>'fa-rotate-left','label'=>'Trả hàng'],
                                    'check'  => ['bg'=>'bg-slate-100','text'=>'text-slate-700','icon'=>'fa-clipboard-check','label'=>'Kiểm kê'],
                                ];
                                $b = $badges[$log->type] ?? ['bg'=>'bg-gray-100','text'=>'text-gray-600','icon'=>'fa-circle','label'=>$log->type];
                            @endphp

                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold
                                         {{ $b['bg'] }} {{ $b['text'] }}">
                                <i class="fa-solid {{ $b['icon'] }} mr-1.5"></i>
                                {{ $b['label'] }}
                            </span>
                        </td>

                        {{-- Thay đổi --}}
                        <td class="px-6 py-4 text-center font-mono font-bold text-base">
                            @if($log->change_amount > 0)
                                <span class="text-emerald-600">+{{ $log->change_amount }}</span>
                            @elseif($log->change_amount < 0)
                                <span class="text-rose-600">{{ $log->change_amount }}</span>
                            @else
                                <span class="text-slate-400">0</span>
                            @endif
                        </td>

                        {{-- Tồn cũ → mới --}}
                        <td class="px-6 py-4 text-center">
                            <div class="flex items-center justify-center gap-2 text-xs">
                                <span class="text-slate-400">{{ $log->old_quantity }}</span>
                                <i class="fa-solid fa-arrow-right text-slate-300 text-[10px]"></i>
                                <span class="font-bold text-slate-800 bg-slate-100 px-2 py-0.5 rounded">
                                    {{ $log->new_quantity }}
                                </span>
                            </div>
                        </td>

                        {{-- Người thực hiện --}}
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center
                                    text-[10px] font-bold border border-indigo-100">
                                    {{ substr($log->user->full_name ?? 'Hệ thống', 0, 1) }}
                                </div>
                                <span class="text-xs font-medium">{{ $log->user->full_name ?? 'Hệ thống' }}</span>
                            </div>
                        </td>

                        {{-- Ghi chú --}}
                        <td class="px-6 py-4">
                            <div class="text-xs text-slate-600 max-w-xs truncate" title="{{ $log->note }}">
                                {{ $log->note }}
                            </div>

                            @if($log->reference_id)
                                <div class="mt-1">
                                    @if($log->type == 'sale')
                                        <a href="{{ route('admin.orders.show', $log->reference_id) }}"
                                            class="text-[10px] text-indigo-500 hover:underline">Xem đơn hàng</a>
                                    @elseif($log->type == 'import')
                                        <a href="{{ route('admin.purchase_orders.show', $log->reference_id) }}"
                                            class="text-[10px] text-indigo-500 hover:underline">Xem phiếu nhập</a>
                                    @endif
                                </div>
                            @endif
                        </td>

                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-slate-400">
                            <i class="fa-solid fa-clock-rotate-left text-3xl mb-3 opacity-30 block"></i>
                            Chưa có dữ liệu lịch sử kho.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
            {{ $logs->withQueryString()->links() }}
        </div>

    </div>
</div>
@endsection
