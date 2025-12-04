@extends('admin.layouts.app')
@section('title', 'Quản lý khách hàng')

@section('content')
<div class="container px-6 mx-auto mb-10 fade-in">

    {{-- 1. THỐNG KÊ NHANH (STATS) --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- Card 1: Tổng khách -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition-shadow">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Tổng khách hàng</p>
                    <h3 class="text-3xl font-extrabold text-slate-800 mt-2">
                        {{ \App\Models\User::where('role', 'customer')->count() }}
                    </h3>
                </div>
                <div class="p-3 bg-indigo-50 rounded-xl text-indigo-500">
                    <i class="fa-solid fa-users text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Card 2: Khách mới tháng này -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition-shadow">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Khách mới (Tháng {{ now()->month }})</p>
                    <h3 class="text-3xl font-extrabold text-emerald-600 mt-2">
                        {{ \App\Models\User::where('role', 'customer')->whereMonth('created_at', now()->month)->count() }}
                    </h3>
                </div>
                <div class="p-3 bg-emerald-50 rounded-xl text-emerald-500">
                    <i class="fa-solid fa-user-plus text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Card 3: Bị khóa -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition-shadow">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Tài khoản bị khóa</p>
                    <h3 class="text-3xl font-extrabold text-rose-600 mt-2">
                        {{ \App\Models\User::where('role', 'customer')->where('status', 'banned')->count() }}
                    </h3>
                </div>
                <div class="p-3 bg-rose-50 rounded-xl text-rose-500">
                    <i class="fa-solid fa-user-lock text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- 2. BỘ LỌC (FILTER BAR) --}}
    <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200 mb-6">
        <form action="{{ route('admin.customers.index') }}" method="GET">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                
                {{-- Tìm kiếm --}}
                <div class="md:col-span-6 relative group">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fa-solid fa-magnifying-glass text-slate-400 group-focus-within:text-indigo-500 transition-colors"></i>
                    </div>
                    <input type="text" name="keyword" value="{{ request('keyword') }}" 
                        placeholder="Tìm tên, email hoặc số điện thoại..." 
                        class="pl-10 pr-4 py-2.5 w-full border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all shadow-sm text-slate-700">
                </div>

                {{-- Trạng thái --}}
                <div class="md:col-span-3">
                    <select name="status" class="w-full border border-slate-200 rounded-xl py-2.5 px-3 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 cursor-pointer shadow-sm text-slate-600 font-medium">
                        <option value="">-- Tất cả trạng thái --</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Hoạt động</option>
                        <option value="banned" {{ request('status') == 'banned' ? 'selected' : '' }}>Đã khóa</option>
                    </select>
                </div>

                {{-- Buttons --}}
                <div class="md:col-span-3 flex gap-2 justify-end">
                    <button type="submit" class="flex-1 px-4 py-2.5 text-sm font-bold text-white bg-slate-900 rounded-xl hover:bg-slate-800 transition-all shadow-lg shadow-slate-900/20 flex items-center justify-center">
                        <i class="fa-solid fa-filter mr-2"></i> Lọc
                    </button>

                    @if(request()->hasAny(['keyword', 'status']))
                        <a href="{{ route('admin.customers.index') }}" class="px-4 py-2.5 text-slate-500 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 hover:text-rose-500 transition-colors flex items-center justify-center" title="Xóa bộ lọc">
                            <i class="fa-solid fa-rotate-left"></i>
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    {{-- 3. BẢNG DỮ LIỆU (DATA TABLE) --}}
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="w-full overflow-x-auto">
            <table class="w-full whitespace-nowrap text-left">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100 text-xs font-bold text-slate-500 uppercase tracking-wider">
                        <th class="px-6 py-4">Khách hàng</th>
                        <th class="px-6 py-4">Liên hệ</th>
                        <th class="px-6 py-4 text-center">Đơn hàng</th>
                        <th class="px-6 py-4 text-right">Tổng chi tiêu</th>
                        <th class="px-6 py-4 text-center">Trạng thái</th>
                        <th class="px-6 py-4 text-center">Hành động</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($customers as $user)
                    <tr class="hover:bg-slate-50/80 transition-colors text-sm text-slate-700">
                        
                        {{-- Khách hàng (Avatar + Tên) --}}
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 font-bold text-sm shadow-sm">
                                    {{ substr($user->full_name, 0, 1) }}
                                </div>
                                <div>
                                    <div class="font-bold text-slate-800">{{ $user->full_name }}</div>
                                    <div class="text-xs text-slate-400 mt-0.5">
                                        Tham gia: {{ $user->created_at->format('d/m/Y') }}
                                    </div>
                                </div>
                            </div>
                        </td>

                        {{-- Liên hệ --}}
                        <td class="px-6 py-4">
                            <div class="flex flex-col gap-1.5">
                                <div class="flex items-center gap-2 text-xs text-slate-600">
                                    <i class="fa-regular fa-envelope text-slate-400 w-4"></i> 
                                    {{ $user->email }}
                                </div>
                                <div class="flex items-center gap-2 text-xs text-slate-600">
                                    <i class="fa-solid fa-phone text-slate-400 w-4"></i> 
                                    {{ $user->phone ?? '---' }}
                                </div>
                            </div>
                        </td>

                        {{-- Đơn hàng --}}
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex items-center justify-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-100">
                                {{ $user->orders_count }} đơn
                            </span>
                        </td>

                        {{-- Tổng chi tiêu --}}
                        <td class="px-6 py-4 text-right">
                            <span class="font-bold text-slate-800 text-base">
                                {{ number_format($user->orders_sum_total_amount ?? 0, 0, ',', '.') }} đ
                            </span>
                        </td>

                        {{-- Trạng thái --}}
                        <td class="px-6 py-4 text-center">
                            @if($user->status === 'active')
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700 border border-emerald-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span>
                                    Hoạt động
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-700 border border-rose-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500 mr-1.5"></span>
                                    Đã khóa
                                </span>
                            @endif
                        </td>

                        {{-- Hành động --}}
                        <td class="px-6 py-4 text-center">
                            <div class="flex items-center justify-center gap-2">
                                {{-- Nút Xem Chi tiết --}}
                                <a href="{{ route('admin.customers.show', $user->id) }}" class="w-8 h-8 flex items-center justify-center rounded-lg bg-white border border-slate-200 text-slate-500 hover:text-indigo-600 hover:border-indigo-200 transition-all shadow-sm" title="Xem hồ sơ">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                                
                                {{-- Nút Khóa/Mở khóa (Toggle Status) --}}
                                <form action="{{ route('admin.customers.update_status', $user->id) }}" method="POST" class="inline-block">
                                    @csrf @method('PUT')
                                    @if($user->status === 'active')
                                        <button type="submit" class="w-8 h-8 flex items-center justify-center rounded-lg bg-white border border-slate-200 text-slate-500 hover:text-rose-600 hover:border-rose-200 transition-all shadow-sm" title="Khóa tài khoản" onclick="return confirm('Bạn có chắc muốn KHÓA tài khoản này?');">
                                            <i class="fa-solid fa-lock"></i>
                                        </button>
                                    @else
                                        <button type="submit" class="w-8 h-8 flex items-center justify-center rounded-lg bg-white border border-slate-200 text-slate-500 hover:text-emerald-600 hover:border-emerald-200 transition-all shadow-sm" title="Mở khóa tài khoản" onclick="return confirm('Mở khóa tài khoản này?');">
                                            <i class="fa-solid fa-lock-open"></i>
                                        </button>
                                    @endif
                                </form>

                                {{-- Nút Xóa (Chỉ hiện khi chưa có đơn hàng để bảo toàn dữ liệu) --}}
                                @if($user->orders_count == 0)
                                    <form action="{{ route('admin.customers.destroy', $user->id) }}" method="POST" class="inline-block">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="w-8 h-8 flex items-center justify-center rounded-lg bg-white border border-slate-200 text-slate-500 hover:text-rose-600 hover:border-rose-200 transition-all shadow-sm" title="Xóa vĩnh viễn" onclick="return confirm('Xóa vĩnh viễn khách hàng này?');">
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
                                <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mb-3">
                                    <i class="fa-solid fa-users-slash text-3xl text-slate-300 opacity-50"></i>
                                </div>
                                <p class="font-medium text-slate-500">Không tìm thấy khách hàng nào.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
            {{ $customers->withQueryString()->links() }}
        </div>
    </div>
</div>
@endsection