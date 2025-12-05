@extends('admin.layouts.app')
@section('title', 'Quản lý Nhập hàng')

@section('content')
<div class="container px-6 mx-auto mb-10 fade-in">

    {{-- HEADER --}}
    <div class="flex flex-col md:flex-row justify-between items-center my-6 gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Phiếu nhập hàng (PO)</h2>
            <p class="text-sm text-slate-500 mt-1">Quản lý nhập kho từ nhà cung cấp</p>
        </div>
        <a href="{{ route('admin.purchase_orders.create') }}" class="flex items-center px-5 py-2.5 text-sm font-bold text-white bg-indigo-600 rounded-xl hover:bg-indigo-700 shadow-lg shadow-indigo-200 transition-all active:scale-95">
            <i class="fa-solid fa-plus mr-2"></i> Tạo phiếu nhập
        </a>
    </div>

    {{-- FILTER --}}
    <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200 mb-6">
        <form action="{{ route('admin.purchase_orders.index') }}" method="GET">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                {{-- Tìm kiếm --}}
                <div class="md:col-span-4 relative group">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 transform -translate-y-1/2 text-slate-400"></i>
                    <input type="text" name="keyword" value="{{ request('keyword') }}" placeholder="Nhập mã phiếu PO..." class="pl-10 pr-4 py-2.5 w-full border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                </div>

                {{-- Nhà cung cấp --}}
                <div class="md:col-span-3">
                    <select name="supplier_id" class="w-full border border-slate-200 rounded-xl py-2.5 px-3 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 cursor-pointer">
                        <option value="">-- Tất cả NCC --</option>
                        @foreach($suppliers as $sup)
                            <option value="{{ $sup->id }}" {{ request('supplier_id') == $sup->id ? 'selected' : '' }}>{{ $sup->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Trạng thái --}}
                <div class="md:col-span-3">
                    <select name="status" class="w-full border border-slate-200 rounded-xl py-2.5 px-3 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 cursor-pointer">
                        <option value="">-- Trạng thái --</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>⏳ Chờ nhập kho</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>✅ Đã nhập kho</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>🚫 Đã hủy</option>
                    </select>
                </div>

                {{-- Button --}}
                <div class="md:col-span-2 flex gap-2 justify-end">
                    <button type="submit" class="flex-1 px-4 py-2.5 text-sm font-bold text-white bg-slate-900 rounded-xl hover:bg-slate-800">Lọc</button>
                    @if(request()->hasAny(['keyword', 'status', 'supplier_id']))
                        <a href="{{ route('admin.purchase_orders.index') }}" class="px-4 py-2.5 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 text-rose-500"><i class="fa-solid fa-rotate-left"></i></a>
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
                    <tr class="bg-slate-50 border-b border-slate-100 text-xs font-bold text-slate-500 uppercase">
                        <th class="px-6 py-4">Mã phiếu</th>
                        <th class="px-6 py-4">Nhà cung cấp</th>
                        <th class="px-6 py-4 text-center">Người tạo</th>
                        <th class="px-6 py-4 text-right">Tổng tiền</th>
                        <th class="px-6 py-4">Ngày tạo</th>
                        <th class="px-6 py-4 text-center">Trạng thái</th>
                        <th class="px-6 py-4 text-center">Hành động</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($orders as $po)
                    <tr class="hover:bg-slate-50/80 transition-colors text-sm text-slate-700">
                        <td class="px-6 py-4">
                            <a href="{{ route('admin.purchase_orders.show', $po->id) }}" class="font-mono font-bold text-indigo-600 hover:underline">
                                {{ $po->code }}
                            </a>
                        </td>
                        <td class="px-6 py-4 font-medium">{{ $po->supplier->name }}</td>
                        <td class="px-6 py-4 text-center">
                            <span class="bg-slate-100 text-slate-600 px-2 py-1 rounded text-xs">
                                {{ $po->creator->full_name ?? 'System' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right font-bold text-slate-800">
                            {{ number_format($po->total_amount, 0, ',', '.') }} ₫
                        </td>
                        <td class="px-6 py-4 text-slate-500">
                            {{ $po->created_at->format('d/m/Y') }}
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($po->status === 'completed')
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700 border border-emerald-200">Đã nhập kho</span>
                            @elseif($po->status === 'cancelled')
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-700 border border-rose-200">Đã hủy</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-700 border border-amber-200">Chờ duyệt</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center">
                            <a href="{{ route('admin.purchase_orders.show', $po->id) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white border border-slate-200 text-slate-500 hover:text-indigo-600 hover:border-indigo-200 shadow-sm transition-all">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="py-12 text-center text-slate-400">Không tìm thấy phiếu nhập hàng nào.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
            {{ $orders->withQueryString()->links() }}
        </div>
    </div>
</div>
@endsection