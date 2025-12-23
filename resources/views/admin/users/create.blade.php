@extends('admin.layouts.app')
@section('title', 'Thêm nhân sự mới')

@section('content')
<div class="container px-6 mx-auto mb-20 fade-in">
    
    {{-- HEADER --}}
    <div class="flex items-center gap-4 my-6">
        <a href="{{ route('admin.users.index') }}" class="w-10 h-10 flex items-center justify-center rounded-xl bg-white border border-slate-200 text-slate-500 hover:bg-slate-50 hover:text-indigo-600 transition-all shadow-sm">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <h2 class="text-2xl font-bold text-slate-800">Thêm nhân sự</h2>
    </div>

    {{-- FORM --}}
    <div class="max-w-2xl mx-auto">
        <form action="{{ route('admin.users.store') }}" method="POST" class="bg-white p-8 rounded-2xl shadow-sm border border-slate-200">
            @csrf
            
            <h3 class="text-lg font-bold text-slate-800 mb-6 flex items-center gap-2">
                <i class="fa-solid fa-user-shield text-indigo-500"></i> Thông tin tài khoản
            </h3>

            <div class="space-y-5">
                
                {{-- Họ tên --}}
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1.5">Họ và tên <span class="text-rose-500">*</span></label>
                    <input type="text" name="full_name" value="{{ old('full_name') }}" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 shadow-sm" placeholder="Nguyễn Văn A">
                    @error('full_name') <p class="text-xs text-rose-500 mt-1 font-medium">{{ $message }}</p> @enderror
                </div>

                {{-- Email & Phone --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1.5">Email <span class="text-rose-500">*</span></label>
                        <input type="email" name="email" value="{{ old('email') }}" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 shadow-sm" placeholder="email@domain.com">
                        @error('email') <p class="text-xs text-rose-500 mt-1 font-medium">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1.5">Số điện thoại</label>
                        <input type="text" name="phone" value="{{ old('phone') }}" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 shadow-sm" placeholder="09xxxxxxxx">
                        @error('phone') <p class="text-xs text-rose-500 mt-1 font-medium">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Mật khẩu --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1.5">Mật khẩu <span class="text-rose-500">*</span></label>
                        <input type="password" name="password" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 shadow-sm" placeholder="••••••••">
                        @error('password') <p class="text-xs text-rose-500 mt-1 font-medium">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1.5">Xác nhận mật khẩu <span class="text-rose-500">*</span></label>
                        <input type="password" name="password_confirmation" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 shadow-sm" placeholder="••••••••">
                    </div>
                </div>

                <div class="border-t border-slate-100 my-4"></div>

                {{-- Vai trò & Trạng thái --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1.5">Vai trò <span class="text-rose-500">*</span></label>
                        <select name="role" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 shadow-sm bg-white cursor-pointer">
                            <option value="staff" {{ old('role') == 'staff' ? 'selected' : '' }}>Nhân viên (Staff)</option>
                            <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Quản trị viên (Admin)</option>
                        </select>
                        <p class="text-xs text-slate-400 mt-1">Admin có toàn quyền, Staff bị giới hạn.</p>
                        @error('role') <p class="text-xs text-rose-500 mt-1 font-medium">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1.5">Trạng thái <span class="text-rose-500">*</span></label>
                        <select name="status" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 shadow-sm bg-white cursor-pointer">
                            <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Hoạt động</option>
                            <option value="banned" {{ old('status') == 'banned' ? 'selected' : '' }}>Khóa tài khoản</option>
                        </select>
                        @error('status') <p class="text-xs text-rose-500 mt-1 font-medium">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="pt-4">
                    <button type="submit" class="w-full py-3 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl shadow-lg shadow-slate-900/20 transition-all active:scale-95 flex items-center justify-center gap-2">
                        <i class="fa-solid fa-floppy-disk"></i> Tạo tài khoản
                    </button>
                </div>

            </div>
        </form>
    </div>
</div>
@endsection