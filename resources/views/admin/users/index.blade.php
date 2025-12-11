@extends('admin.layouts.app')
@section('title', 'Quản lý nhân sự')

@section('content')
<div class="container px-6 mx-auto mb-10 fade-in">

    {{-- 1. THỐNG KÊ NHANH --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- Tổng nhân sự -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition-shadow">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Tổng nhân sự</p>
                    <h3 class="text-3xl font-extrabold text-slate-800 mt-2">
                        {{ \App\Models\User::whereIn('role', ['admin', 'staff'])->count() }}
                    </h3>
                </div>
                <div class="p-3 bg-slate-100 rounded-xl text-slate-600">
                    <i class="fa-solid fa-user-shield text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Quản trị viên (Admin) -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition-shadow">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Quản trị viên</p>
                    <h3 class="text-3xl font-extrabold text-indigo-600 mt-2">
                        {{ \App\Models\User::where('role', 'admin')->count() }}
                    </h3>
                </div>
                <div class="p-3 bg-indigo-50 rounded-xl text-indigo-600">
                    <i class="fa-solid fa-crown text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Nhân viên (Staff) -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition-shadow">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Nhân viên</p>
                    <h3 class="text-3xl font-extrabold text-purple-600 mt-2">
                        {{ \App\Models\User::where('role', 'staff')->count() }}
                    </h3>
                </div>
                <div class="p-3 bg-purple-50 rounded-xl text-purple-600">
                    <i class="fa-solid fa-id-card text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- 2. HEADER & NÚT TẠO --}}
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Danh sách nhân sự</h2>
            <p class="text-sm text-slate-500 mt-1">Quản lý quyền truy cập hệ thống</p>
        </div>
        
        {{-- Chỉ Admin mới thấy nút thêm --}}
        @if(Auth::user()->role === 'admin')
        <a href="{{ route('admin.users.create') }}" class="flex items-center px-5 py-2.5 text-sm font-bold text-white bg-slate-900 rounded-xl hover:bg-slate-800 shadow-lg shadow-slate-900/20 transition-all transform active:scale-95">
            <i class="fa-solid fa-user-plus mr-2"></i> Thêm nhân sự
        </a>
        @endif
    </div>

    {{-- 3. BỘ LỌC --}}
    <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200 mb-6">
        <form action="{{ route('admin.users.index') }}" method="GET">
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

                {{-- Vai trò --}}
                <div class="md:col-span-3">
                    <select name="role" class="w-full border border-slate-200 rounded-xl py-2.5 px-3 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 cursor-pointer shadow-sm text-slate-600 font-medium">
                        <option value=""> Tất cả vai trò </option>
                        <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Quản trị viên (Admin)</option>
                        <option value="staff" {{ request('role') == 'staff' ? 'selected' : '' }}>Nhân viên (Staff)</option>
                    </select>
                </div>

                {{-- Nút Lọc --}}
                <div class="md:col-span-3 flex gap-2 justify-end">
                    <button type="submit" class="flex-1 px-4 py-2.5 text-sm font-bold text-white bg-slate-900 rounded-xl hover:bg-slate-800 transition-all shadow-lg shadow-slate-900/20 flex items-center justify-center">
                        <i class="fa-solid fa-filter mr-2"></i> Lọc
                    </button>
                    @if(request()->hasAny(['keyword', 'role', 'status']))
                        <a href="{{ route('admin.users.index') }}" class="px-4 py-2.5 text-slate-500 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 hover:text-rose-500 transition-colors flex items-center justify-center">
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
                        <th class="px-6 py-4">Nhân sự</th>
                        <th class="px-6 py-4">Liên hệ</th>
                        <th class="px-6 py-4 text-center">Vai trò</th>
                        <th class="px-6 py-4 text-center">Trạng thái</th>
                        <th class="px-6 py-4 text-center">Hành động</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($users as $user)
                    <tr class="hover:bg-slate-50/80 transition-colors text-sm text-slate-700">
                        
                        {{-- Avatar & Tên --}}
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-slate-200 border border-white shadow-sm flex items-center justify-center text-slate-600 font-bold text-sm">
                                    {{ substr($user->full_name, 0, 1) }}
                                </div>
                                <div>
                                    <div class="font-bold text-slate-800 flex items-center gap-2">
                                        {{ $user->full_name }}
                                        @if($user->id === Auth::id())
                                            <span class="text-[10px] bg-slate-100 text-slate-500 px-1.5 py-0.5 rounded border border-slate-200">Bạn</span>
                                        @endif
                                    </div>
                                    <div class="text-xs text-slate-400 mt-0.5">ID: #{{ $user->id }}</div>
                                </div>
                            </div>
                        </td>

                        {{-- Liên hệ --}}
                        <td class="px-6 py-4">
                            <div class="flex flex-col gap-1">
                                <div class="flex items-center gap-2 text-xs text-slate-600">
                                    <i class="fa-regular fa-envelope text-slate-400 w-4"></i> {{ $user->email }}
                                </div>
                                <div class="flex items-center gap-2 text-xs text-slate-600">
                                    <i class="fa-solid fa-phone text-slate-400 w-4"></i> {{ $user->phone ?? '---' }}
                                </div>
                            </div>
                        </td>

                        {{-- Vai trò --}}
                        <td class="px-6 py-4 text-center">
                            @if($user->role === 'admin')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-100 text-indigo-700 border border-indigo-200">
                                    <i class="fa-solid fa-crown mr-1.5 text-[10px]"></i> Admin
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-purple-100 text-purple-700 border border-purple-200">
                                    <i class="fa-solid fa-id-card mr-1.5 text-[10px]"></i> Staff
                                </span>
                            @endif
                        </td>

                        {{-- Trạng thái --}}
                        <td class="px-6 py-4 text-center">
                            @if($user->status === 'active')
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700 border border-emerald-200">
                                    Hoạt động
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-700 border border-rose-200">
                                    Đã khóa
                                </span>
                            @endif
                        </td>

                        {{-- Hành động --}}
                        <td class="px-6 py-4 text-center">
                            <div class="flex items-center justify-center gap-2">
                                {{-- Nút Sửa: Staff không được sửa Admin --}}
                                @if(Auth::user()->role === 'admin' || $user->role !== 'admin')
                                    <a href="{{ route('admin.users.edit', $user->id) }}" class="w-8 h-8 flex items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 border border-indigo-100 hover:bg-indigo-600 hover:text-white transition-all shadow-sm">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                @endif

                                {{-- Nút Xóa: 
                                     1. Không xóa ID 1 
                                     2. Không xóa chính mình
                                     3. Staff không xóa Admin 
                                --}}
                                @if($user->id !== 1 && $user->id !== Auth::id())
                                    @if(Auth::user()->role === 'admin' || $user->role !== 'admin')
                                        <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa nhân sự này?');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="w-8 h-8 flex items-center justify-center rounded-lg bg-white text-slate-400 border border-slate-200 hover:bg-rose-50 hover:text-rose-600 hover:border-rose-200 transition-all shadow-sm">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-12 text-center text-slate-400">Không tìm thấy nhân sự nào.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
            {{ $users->withQueryString()->links() }}
        </div>
    </div>
</div>
@endsection