@extends('admin.layouts.app')
@section('title', 'Quản lý Nhà cung cấp')

@section('content')
<div class="container px-6 mx-auto mb-10 fade-in">

    {{-- HEADER & TOOLS --}}
    <div class="flex flex-col md:flex-row justify-between items-center my-6 gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Nhà cung cấp</h2>
            <p class="text-sm text-slate-500 mt-1">Quản lý đối tác và nguồn nhập hàng</p>
        </div>
        <a href="{{ route('admin.suppliers.create') }}" class="flex items-center px-5 py-2.5 text-sm font-bold text-white bg-indigo-600 rounded-xl hover:bg-indigo-700 shadow-lg shadow-indigo-200 transition-all transform active:scale-95">
            <i class="fa-solid fa-plus mr-2"></i> Thêm nhà cung cấp
        </a>
    </div>

    {{-- FILTER BAR --}}
    <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200 mb-6">
        <form action="{{ route('admin.suppliers.index') }}" method="GET">
            <div class="flex flex-col md:flex-row gap-4">
                {{-- Tìm kiếm --}}
                <div class="flex-1 relative group">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fa-solid fa-magnifying-glass text-slate-400 group-focus-within:text-indigo-500 transition-colors"></i>
                    </div>
                    <input type="text" name="keyword" value="{{ request('keyword') }}" 
                        placeholder="Tìm tên, mã, số điện thoại hoặc email..." 
                        class="pl-10 pr-4 py-2.5 w-full border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all shadow-sm text-slate-700">
                </div>

                {{-- Nút Lọc --}}
                <button type="submit" class="px-6 py-2.5 text-sm font-bold text-white bg-slate-900 rounded-xl hover:bg-slate-800 transition-all shadow-lg shadow-slate-900/20 flex items-center justify-center">
                    <i class="fa-solid fa-filter mr-2"></i> Lọc
                </button>

                @if(request('keyword'))
                    <a href="{{ route('admin.suppliers.index') }}" class="px-4 py-2.5 text-slate-500 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 hover:text-rose-500 transition-colors flex items-center justify-center" title="Xóa bộ lọc">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- DATA TABLE --}}
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="w-full overflow-x-auto">
            <table class="w-full whitespace-nowrap text-left">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100 text-xs font-bold text-slate-500 uppercase tracking-wider">
                        <th class="px-6 py-4 w-12">#</th>
                        <th class="px-6 py-4">Nhà cung cấp</th>
                        <th class="px-6 py-4">Liên hệ chính</th>
                        <th class="px-6 py-4">Thông tin liên lạc</th>
                        <th class="px-6 py-4 text-center">Đơn nhập</th>
                        <th class="px-6 py-4 text-center w-24">Hành động</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($suppliers as $idx => $item)
                    <tr class="hover:bg-slate-50/80 transition-colors text-sm text-slate-700">
                        
                        <td class="px-6 py-4 text-slate-500">
                            {{ $suppliers->firstItem() + $idx }}
                        </td>

                        {{-- Tên & Mã --}}
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 text-lg">
                                    <i class="fa-solid fa-building"></i>
                                </div>
                                <div>
                                    <div class="font-bold text-slate-800">{{ $item->name }}</div>
                                    <span class="text-[10px] font-mono font-bold bg-slate-100 text-slate-500 px-1.5 py-0.5 rounded border border-slate-200">
                                        {{ $item->code }}
                                    </span>
                                </div>
                            </div>
                        </td>

                        {{-- Người liên hệ --}}
                        <td class="px-6 py-4">
                            @if($item->contact_name)
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-full bg-slate-200 flex items-center justify-center text-[10px] font-bold text-slate-600">
                                        {{ substr($item->contact_name, 0, 1) }}
                                    </div>
                                    <span class="font-medium">{{ $item->contact_name }}</span>
                                </div>
                            @else
                                <span class="text-slate-400 italic">---</span>
                            @endif
                        </td>

                        {{-- Email & Phone --}}
                        <td class="px-6 py-4">
                            <div class="flex flex-col gap-1">
                                @if($item->email)
                                    <div class="flex items-center gap-2 text-xs text-slate-600">
                                        <i class="fa-regular fa-envelope text-slate-400 w-4"></i> {{ $item->email }}
                                    </div>
                                @endif
                                @if($item->phone)
                                    <div class="flex items-center gap-2 text-xs text-slate-600">
                                        <i class="fa-solid fa-phone text-slate-400 w-4"></i> {{ $item->phone }}
                                    </div>
                                @endif
                                @if(!$item->email && !$item->phone)
                                    <span class="text-slate-400 italic">Chưa cập nhật</span>
                                @endif
                            </div>
                        </td>

                        {{-- Số phiếu nhập --}}
                        <td class="px-6 py-4 text-center">
                            @if($item->purchase_orders_count > 0)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-100">
                                    {{ $item->purchase_orders_count }} phiếu
                                </span>
                            @else
                                <span class="text-slate-400 text-xs">Chưa nhập hàng</span>
                            @endif
                        </td>

                        {{-- Hành động --}}
                        <td class="px-6 py-4 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('admin.suppliers.edit', $item->id) }}" class="w-8 h-8 flex items-center justify-center rounded-lg bg-white border border-slate-200 text-slate-500 hover:text-indigo-600 hover:border-indigo-200 transition-all shadow-sm" title="Chỉnh sửa">
                                    <i class="fa-solid fa-pen"></i>
                                </a>

                                {{-- Chỉ cho xóa nếu chưa có đơn nhập hàng --}}
                                @if($item->purchase_orders_count == 0)
                                    <form action="{{ route('admin.suppliers.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Xóa nhà cung cấp này?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="w-8 h-8 flex items-center justify-center rounded-lg bg-white border border-slate-200 text-slate-500 hover:text-rose-600 hover:border-rose-200 transition-all shadow-sm" title="Xóa">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-12 text-center text-slate-400">
                            <div class="flex flex-col items-center justify-center">
                                <i class="fa-solid fa-building-circle-exclamation text-3xl mb-3 opacity-30"></i>
                                <p>Chưa có nhà cung cấp nào.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
            {{ $suppliers->withQueryString()->links() }}
        </div>
    </div>
</div>
@endsection