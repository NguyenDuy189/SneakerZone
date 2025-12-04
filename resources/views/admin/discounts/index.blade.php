@extends('admin.layouts.app')
@section('title', 'Quản lý mã giảm giá')

@section('content')
<div class="container px-6 mx-auto mb-10 fade-in">

    {{-- 1. THỐNG KÊ NHANH --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Card 1 -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition-shadow">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Tổng mã</p>
                    <h3 class="text-3xl font-extrabold text-slate-800 mt-2">
                        {{ \App\Models\Discount::count() }}
                    </h3>
                </div>
                <div class="p-3 bg-indigo-50 rounded-xl text-indigo-500">
                    <i class="fa-solid fa-ticket text-xl"></i>
                </div>
            </div>
        </div>
        <!-- Card 2 -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition-shadow">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Đang chạy</p>
                    <h3 class="text-3xl font-extrabold text-emerald-600 mt-2">
                        {{ \App\Models\Discount::where('end_date', '>=', now())->orWhereNull('end_date')->count() }}
                    </h3>
                </div>
                <div class="p-3 bg-emerald-50 rounded-xl text-emerald-500">
                    <i class="fa-solid fa-clock text-xl"></i>
                </div>
            </div>
        </div>
        <!-- Card 3 -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition-shadow">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Hết hạn</p>
                    <h3 class="text-3xl font-extrabold text-rose-600 mt-2">
                        {{ \App\Models\Discount::where('end_date', '<', now())->count() }}
                    </h3>
                </div>
                <div class="p-3 bg-rose-50 rounded-xl text-rose-500">
                    <i class="fa-solid fa-calendar-xmark text-xl"></i>
                </div>
            </div>
        </div>
        <!-- Card 4 -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition-shadow">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Đã sử dụng</p>
                    <h3 class="text-3xl font-extrabold text-slate-800 mt-2">
                        {{ \App\Models\Discount::sum('used_count') }}
                    </h3>
                </div>
                <div class="p-3 bg-blue-50 rounded-xl text-blue-500">
                    <i class="fa-solid fa-users text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- 2. HEADER --}}
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Danh sách mã giảm giá</h2>
            <p class="text-sm text-slate-500 mt-1">Quản lý các chương trình khuyến mãi</p>
        </div>
        <a href="{{ route('admin.discounts.create') }}" class="flex items-center px-5 py-2.5 text-sm font-bold text-white bg-indigo-600 rounded-xl hover:bg-indigo-700 shadow-lg shadow-indigo-200 transition-all transform active:scale-95">
            <i class="fa-solid fa-plus mr-2"></i> Thêm mã mới
        </a>
    </div>

    {{-- 3. FILTER BAR --}}
    <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200 mb-6">
        <form action="{{ route('admin.discounts.index') }}" method="GET">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                {{-- Tìm kiếm --}}
                <div class="md:col-span-4 relative group">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fa-solid fa-magnifying-glass text-slate-400 group-focus-within:text-indigo-500 transition-colors"></i>
                    </div>
                    <input type="text" name="keyword" value="{{ request('keyword') }}" placeholder="Tìm theo mã..." class="pl-10 pr-4 py-2.5 w-full border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all shadow-sm text-slate-700">
                </div>
                {{-- Loại --}}
                <div class="md:col-span-2">
                    <select name="type" class="w-full border border-slate-200 rounded-xl py-2.5 px-3 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 cursor-pointer shadow-sm text-slate-600 font-medium">
                        <option value="">-- Loại --</option>
                        <option value="percentage" {{ request('type') == 'percentage' ? 'selected' : '' }}>Phần trăm (%)</option>
                        <option value="fixed" {{ request('type') == 'fixed' ? 'selected' : '' }}>Số tiền cố định</option>
                    </select>
                </div>
                {{-- Trạng thái --}}
                <div class="md:col-span-2">
                    <select name="status" class="w-full border border-slate-200 rounded-xl py-2.5 px-3 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 cursor-pointer shadow-sm text-slate-600 font-medium">
                        <option value="">-- Trạng thái --</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Đang hiệu lực</option>
                        <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Hết hạn</option>
                    </select>
                </div>
                {{-- Ngày --}}
                <div class="md:col-span-2">
                    <input type="date" name="from_date" value="{{ request('from_date') }}" class="w-full border border-slate-200 rounded-xl py-2.5 px-3 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 shadow-sm text-slate-600">
                </div>
                {{-- Buttons --}}
                <div class="md:col-span-2 flex gap-2 justify-end">
                    <button type="submit" class="flex-1 px-4 py-2.5 text-sm font-bold text-white bg-slate-900 rounded-xl hover:bg-slate-800 transition-all shadow-lg shadow-slate-900/20 flex items-center justify-center">
                        <i class="fa-solid fa-filter mr-2"></i> Lọc
                    </button>
                    @if(request()->hasAny(['keyword', 'type', 'status', 'from_date']))
                        <a href="{{ route('admin.discounts.index') }}" class="px-4 py-2.5 text-slate-500 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 hover:text-rose-500 transition-colors flex items-center justify-center">
                            <i class="fa-solid fa-rotate-left"></i>
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    {{-- 4. BẢNG DỮ LIỆU --}}
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="w-full overflow-x-auto">
            <table class="w-full whitespace-nowrap text-left">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100 text-xs font-bold text-slate-500 uppercase tracking-wider">
                        <th class="px-6 py-4 w-12">#</th>
                        <th class="px-6 py-4">Mã Code</th>
                        <th class="px-6 py-4">Giá trị giảm</th>
                        <th class="px-6 py-4 text-center">Đã dùng</th>
                        <th class="px-6 py-4 text-right">Đơn tối thiểu</th>
                        <th class="px-6 py-4 text-right">Giảm tối đa</th> {{-- Cột Mới --}}
                        <th class="px-6 py-4">Thời gian</th>
                        <th class="px-6 py-4 text-center">Trạng thái</th>
                        <th class="px-6 py-4 text-center w-24">Hành động</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($discounts as $idx => $item)
                    <tr class="hover:bg-slate-50/80 transition-colors text-sm text-slate-700">
                        
                        {{-- 1. Index --}}
                        <td class="px-6 py-4 text-slate-500">{{ $discounts->firstItem() + $idx }}</td>

                        {{-- 2. Mã Code --}}
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600">
                                    <i class="fa-solid fa-ticket"></i>
                                </div>
                                <span class="font-mono font-bold text-base text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded border border-indigo-100 border-dashed">
                                    {{ $item->code }}
                                </span>
                            </div>
                        </td>

                        {{-- 3. Giá trị --}}
                        <td class="px-6 py-4">
                            @if($item->type === 'percentage')
                                <div class="font-bold text-slate-800 text-base">{{ $item->value }}%</div>
                                <span class="text-[10px] font-bold text-blue-600 bg-blue-50 px-1.5 py-0.5 rounded border border-blue-100 uppercase">Theo %</span>
                            @else
                                <div class="font-bold text-slate-800 text-base">{{ number_format($item->value, 0, ',', '.') }} đ</div>
                                <span class="text-[10px] font-bold text-emerald-600 bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-100 uppercase">Tiền mặt</span>
                            @endif
                        </td>

                        {{-- 4. Đã dùng --}}
                        <td class="px-6 py-4 text-center">
                            <div class="inline-flex flex-col items-center">
                                <span class="font-bold text-slate-700">{{ $item->used_count }}</span>
                                <span class="text-[10px] text-slate-400">/ {{ $item->max_usage > 0 ? $item->max_usage : '∞' }}</span>
                            </div>
                        </td>

                        {{-- 5. Đơn tối thiểu --}}
                        <td class="px-6 py-4 text-right font-medium text-slate-600">
                            {{ number_format($item->min_order_amount, 0, ',', '.') }} đ
                        </td>

                        {{-- 6. GIẢM TỐI ĐA (CỘT MỚI) --}}
                        <td class="px-6 py-4 text-right">
                            @if($item->type === 'percentage')
                                @if($item->max_discount_value > 0)
                                    <span class="font-bold text-slate-700">{{ number_format($item->max_discount_value, 0, ',', '.') }} đ</span>
                                @else
                                    <span class="text-xs text-slate-400 italic">Không giới hạn</span>
                                @endif
                            @else
                                <span class="text-slate-300">—</span>
                            @endif
                        </td>

                        {{-- 7. Thời gian --}}
                        <td class="px-6 py-4">
                            <div class="text-xs space-y-1">
                                <div class="flex items-center gap-2">
                                    <span class="text-slate-400 w-12">Bắt đầu:</span> 
                                    <span class="font-medium text-slate-700">{{ $item->start_date ? $item->start_date->format('d/m/Y') : 'Ngay lập tức' }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-slate-400 w-12">Kết thúc:</span>
                                    <span class="font-medium {{ $item->end_date && $item->end_date->isPast() ? 'text-rose-500' : 'text-slate-700' }}">
                                        {{ $item->end_date ? $item->end_date->format('d/m/Y') : 'Vô hạn' }}
                                    </span>
                                </div>
                            </div>
                        </td>

                        {{-- 8. Trạng thái --}}
                        <td class="px-6 py-4 text-center">
                            @php
                                $now = \Carbon\Carbon::now();
                                $isExpired = $item->end_date && $now->gt($item->end_date);
                                $isNotStarted = $item->start_date && $item->start_date->isFuture();
                                $isFull = $item->max_usage > 0 && $item->used_count >= $item->max_usage;
                            @endphp

                            @if($isExpired)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-700 border border-rose-200">Hết hạn</span>
                            @elseif($isNotStarted)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-700 border border-amber-200">Chưa chạy</span>
                            @elseif($isFull)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-600 border border-slate-200">Hết lượt</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700 border border-emerald-200">Hoạt động</span>
                            @endif
                        </td>

                        {{-- 9. Hành động --}}
                        <td class="px-6 py-4 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('admin.discounts.edit', $item->id) }}" class="w-8 h-8 flex items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white transition-all shadow-sm">
                                    <i class="fa-solid fa-pen"></i>
                                </a>
                                <form action="{{ route('admin.discounts.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Xóa mã này?');">
                                    @csrf @method('DELETE')
                                    <button class="w-8 h-8 flex items-center justify-center rounded-lg bg-white text-slate-400 border border-slate-200 hover:bg-rose-50 hover:text-rose-600 hover:border-rose-200 transition-all shadow-sm">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="py-12 text-center text-slate-400">
                            <div class="flex flex-col items-center">
                                <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mb-3">
                                    <i class="fa-solid fa-ticket text-3xl text-slate-300 opacity-50"></i>
                                </div>
                                <p class="font-medium text-slate-500">Chưa có mã giảm giá nào.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
            {{ $discounts->withQueryString()->links() }}
        </div>
    </div>
</div>
@endsection