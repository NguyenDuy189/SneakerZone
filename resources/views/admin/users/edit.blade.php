@extends('admin.layouts.app')
@section('title', 'Chỉnh sửa: ' . $user->full_name)

@section('content')
<div class="container px-6 mx-auto mb-20 fade-in">
    
    {{-- HEADER --}}
    <div class="flex items-center gap-4 my-6">
        <a href="{{ route('admin.users.index') }}" class="w-10 h-10 flex items-center justify-center rounded-xl bg-white border border-slate-200 text-slate-500 hover:bg-slate-50 hover:text-indigo-600 transition-all shadow-sm">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Sửa thông tin nhân sự</h2>
            <p class="text-sm text-slate-500 mt-0.5">ID: #{{ $user->id }}</p>
        </div>
    </div>

    {{-- FORM --}}
    <div class="max-w-2xl mx-auto">
        <form action="{{ route('admin.users.update', $user->id) }}" method="POST" class="bg-white p-8 rounded-2xl shadow-sm border border-slate-200">
            @csrf
            @method('PUT')
            
            <h3 class="text-lg font-bold text-slate-800 mb-6 flex items-center gap-2">
                <i class="fa-solid fa-user-pen text-indigo-500"></i> Thông tin tài khoản
            </h3>

            <div class="space-y-5">
                
                {{-- Họ tên --}}
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1.5">Họ và tên</label>
                    <input type="text" name="full_name" value="{{ old('full_name', $user->full_name) }}" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 shadow-sm">
                    @error('full_name') <p class="text-xs text-rose-500 mt-1 font-medium">{{ $message }}</p> @enderror
                </div>

                {{-- Email & Phone --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1.5">Email</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 shadow-sm">
                        @error('email') <p class="text-xs text-rose-500 mt-1 font-medium">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1.5">Số điện thoại</label>
                        <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 shadow-sm">
                        @error('phone') <p class="text-xs text-rose-500 mt-1 font-medium">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="border-t border-slate-100 my-4"></div>

                {{-- Đổi mật khẩu (Optional) --}}
                <div class="bg-slate-50 p-4 rounded-xl border border-slate-100">
                    <h4 class="text-sm font-bold text-slate-700 mb-3 flex items-center gap-2">
                        <i class="fa-solid fa-key text-slate-400"></i> Đổi mật khẩu
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <input type="password" name="password" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 shadow-sm bg-white" placeholder="Nhập mật khẩu mới...">
                            @error('password') <p class="text-xs text-rose-500 mt-1 font-medium">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <input type="password" name="password_confirmation" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 shadow-sm bg-white" placeholder="Nhập lại mật khẩu mới...">
                        </div>
                    </div>
                    <p class="text-[11px] text-slate-400 mt-2 italic">* Chỉ nhập nếu bạn muốn đổi mật khẩu. Bỏ trống để giữ nguyên.</p>
                </div>

                {{-- Vai trò & Trạng thái --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1.5">Vai trò</label>
                        @if($user->id === 1)
                            {{-- Super Admin ID 1 không thể đổi Role --}}
                            <input type="hidden" name="role" value="admin">
                            <input type="text" value="Quản trị viên (Super Admin)" disabled class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm bg-slate-100 text-slate-500 cursor-not-allowed">
                            <p class="text-[10px] text-rose-500 mt-1 font-medium">Không thể thay đổi quyền hạn của tài khoản gốc.</p>
                        @else
                            <select name="role" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 shadow-sm bg-white cursor-pointer">
                                <option value="staff" {{ old('role', $user->role) == 'staff' ? 'selected' : '' }}>Nhân viên (Staff)</option>
                                <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Quản trị viên (Admin)</option>
                            </select>
                        @endif
                        @error('role') <p class="text-xs text-rose-500 mt-1 font-medium">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1.5">Trạng thái</label>
                        @if($user->id === 1)
                            {{-- Super Admin ID 1 không thể Banned --}}
                            <input type="hidden" name="status" value="active">
                            <input type="text" value="Hoạt động" disabled class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm bg-slate-100 text-slate-500 cursor-not-allowed">
                            <p class="text-[10px] text-rose-500 mt-1 font-medium">Không thể khóa tài khoản gốc.</p>
                        @else
                            <select name="status" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 shadow-sm bg-white cursor-pointer">
                                <option value="active" {{ old('status', $user->status) == 'active' ? 'selected' : '' }}>Hoạt động</option>
                                <option value="banned" {{ old('status', $user->status) == 'banned' ? 'selected' : '' }}>Khóa tài khoản</option>
                            </select>
                        @endif
                        @error('status') <p class="text-xs text-rose-500 mt-1 font-medium">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="pt-4">
                    <button type="submit" class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-lg shadow-indigo-600/20 transition-all active:scale-95 flex items-center justify-center gap-2">
                        <i class="fa-solid fa-floppy-disk"></i> Lưu thay đổi
                    </button>
                </div>

            </div>
        </form>
    </div>
</div>
@endsection