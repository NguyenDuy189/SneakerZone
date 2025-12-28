@extends('client.layouts.app')
@section('title', 'Thông tin tài khoản - Sneaker Zone')

@section('content')
<div class="bg-slate-50 min-h-screen pb-20">
    {{-- Header Background & User Info --}}
    <div class="bg-slate-900 pt-16 pb-24 relative overflow-hidden">
        {{-- Decoration --}}
        <div class="absolute top-0 right-0 w-96 h-96 bg-indigo-600 rounded-full blur-3xl opacity-20 transform translate-x-1/2 -translate-y-1/2"></div>
        
        <div class="container mx-auto px-4 text-center relative z-10">
            <div class="w-28 h-28 mx-auto rounded-full border-4 border-white/20 shadow-xl overflow-hidden bg-slate-800 flex items-center justify-center">
                @if($user->avatar)
                    <img src="{{ Storage::url($user->avatar) }}" class="w-full h-full object-cover">
                @else
                    <span class="text-3xl font-bold text-white uppercase">{{ substr($user->full_name, 0, 1) }}</span>
                @endif
            </div>
            <h1 class="text-3xl font-display font-black text-white mt-4">{{ $user->full_name }}</h1>
            <p class="text-slate-400 text-sm mt-1">{{ $user->email }}</p>
            <div class="mt-2">
                @if($user->role === 'admin')
                    <span class="px-3 py-1 rounded-full bg-rose-500/20 text-rose-300 text-xs font-bold border border-rose-500/30">Admin</span>
                @else
                    <span class="px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-300 text-xs font-bold border border-emerald-500/30">Thành viên</span>
                @endif
            </div>
        </div>
    </div>

    <div class="container mx-auto px-4 -mt-12 relative z-20">
        {{-- Navigation Tabs --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-2 max-w-2xl mx-auto flex justify-center mb-8">
            <a href="{{ route('client.account.profile') }}" class="flex-1 py-3 px-6 text-center rounded-lg text-sm font-bold transition-all bg-slate-900 text-white shadow-md">
                Thông tin cá nhân
            </a>
            <a href="{{ route('client.account.orders') }}" class="flex-1 py-3 px-6 text-center rounded-lg text-sm font-bold transition-all text-slate-500 hover:text-indigo-600 hover:bg-indigo-50">
                Lịch sử đơn hàng
            </a>
        </div>

        {{-- Success Alert --}}
        @if(session('success'))
            <div class="max-w-5xl mx-auto mb-6 bg-emerald-50 text-emerald-700 p-4 rounded-xl flex items-center gap-3 border border-emerald-100 shadow-sm animate-bounce-in">
                <i class="fa-solid fa-circle-check text-xl"></i>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
        @endif

        <div class="max-w-5xl mx-auto grid grid-cols-1 md:grid-cols-3 gap-6">
            {{-- Cột trái: Thông tin chính --}}
            <div class="md:col-span-2 space-y-6">
                <div class="bg-white p-8 rounded-2xl shadow-sm border border-slate-100">
                    <div class="flex items-center justify-between mb-6 border-b border-slate-50 pb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-600">
                                <i class="fa-regular fa-id-card"></i>
                            </div>
                            <h2 class="text-xl font-bold text-slate-900">Thông tin chung</h2>
                        </div>
                        <a href="{{ route('client.account.edit') }}" class="inline-flex items-center gap-2 text-sm font-bold text-indigo-600 hover:text-indigo-800 hover:bg-indigo-50 px-3 py-1.5 rounded-lg transition-colors">
                            <i class="fa-solid fa-pen-to-square"></i> Chỉnh sửa
                        </a>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-6 gap-x-8">
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Họ và tên</label>
                            <p class="text-lg font-medium text-slate-900">{{ $user->full_name }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Số điện thoại</label>
                            <p class="text-lg font-medium text-slate-900">{{ $user->phone ?? 'Chưa cập nhật' }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Giới tính</label>
                            <p class="text-lg font-medium text-slate-900">
                                @if($user->gender === 'male') Nam
                                @elseif($user->gender === 'female') Nữ
                                @else Khác
                                @endif
                            </p>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Ngày sinh</label>
                            <p class="text-lg font-medium text-slate-900">
                                {{ $user->birthday ? \Carbon\Carbon::parse($user->birthday)->format('d/m/Y') : 'Chưa cập nhật' }}
                            </p>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Địa chỉ</label>
                            <p class="text-lg font-medium text-slate-900">{{ $user->address ?? 'Chưa cập nhật' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Cột phải: Bảo mật & Tài khoản --}}
            <div class="space-y-6">
                <div class="bg-white p-8 rounded-2xl shadow-sm border border-slate-100">
                    <div class="flex items-center gap-3 mb-6 border-b border-slate-50 pb-4">
                        <div class="w-10 h-10 rounded-full bg-rose-50 flex items-center justify-center text-rose-600">
                            <i class="fa-solid fa-shield-halved"></i>
                        </div>
                        <h2 class="text-xl font-bold text-slate-900">Bảo mật</h2>
                    </div>

                    <div class="space-y-6">
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Email đăng nhập</label>
                            <div class="flex items-center gap-2">
                                <p class="text-base font-medium text-slate-900 truncate">{{ $user->email }}</p>
                                @if($user->email_verified_at)
                                    <span class="text-emerald-500 text-xs" title="Đã xác thực"><i class="fa-solid fa-circle-check"></i></span>
                                @else
                                    <span class="text-amber-500 text-xs" title="Chưa xác thực"><i class="fa-solid fa-circle-exclamation"></i></span>
                                @endif
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Mật khẩu</label>
                            <p class="text-lg font-medium text-slate-900">••••••••</p>
                        </div>
                        <div class="pt-4 border-t border-slate-50">
                            <a href="{{ route('client.account.edit') }}" class="text-sm font-bold text-rose-600 hover:text-rose-800 hover:underline">
                                Đổi mật khẩu
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Thống kê nhỏ --}}
                <div class="bg-indigo-600 rounded-2xl p-6 text-white shadow-lg shadow-indigo-200">
                    <p class="text-indigo-200 text-xs font-bold uppercase tracking-widest mb-2">Thành viên từ</p>
                    <p class="text-2xl font-black">{{ $user->created_at->format('d/m/Y') }}</p>
                    <p class="mt-4 text-sm text-indigo-100">Cảm ơn bạn đã đồng hành cùng Sneaker Zone!</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection