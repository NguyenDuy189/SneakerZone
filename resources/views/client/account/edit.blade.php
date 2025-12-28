@extends('client.layouts.app')
@section('title', 'Cập nhật hồ sơ - Sneaker Zone')

@section('content')
<div class="bg-slate-50 min-h-screen pb-20">
    {{-- Simple Header --}}
    <div class="bg-white border-b border-slate-100 pt-12 pb-12">
        <div class="container mx-auto px-4 text-center">
            <h1 class="text-3xl font-display font-black text-slate-900">Cập nhật hồ sơ</h1>
            <p class="text-slate-500 mt-2">Thay đổi thông tin cá nhân và bảo mật tài khoản</p>
        </div>
    </div>

    <div class="container mx-auto px-4 mt-8 max-w-5xl">
        {{-- Back Button --}}
        <div class="mb-6">
            <a href="{{ route('client.account.profile') }}" class="inline-flex items-center gap-2 text-slate-500 hover:text-indigo-600 font-bold text-sm transition-colors group">
                <i class="fa-solid fa-arrow-left group-hover:-translate-x-1 transition-transform"></i> Quay lại hồ sơ
            </a>
        </div>

        {{-- Error Alert Global --}}
        @if(session('error'))
            <div class="mb-6 p-4 rounded-lg bg-rose-50 text-rose-700 border border-rose-200 flex items-center gap-2">
                <i class="fa-solid fa-circle-exclamation"></i> {{ session('error') }}
            </div>
        @endif

        {{-- Main Form --}}
        <form action="{{ route('client.account.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                {{-- Cột trái: Avatar --}}
                <div class="md:col-span-1">
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 text-center sticky top-8">
                        <div class="relative inline-block group mb-4">
                            <div class="w-36 h-36 mx-auto rounded-full border-4 border-slate-50 overflow-hidden bg-slate-100 flex items-center justify-center relative">
                                @if($user->avatar)
                                    <img id="avatar-preview" src="{{ Storage::url($user->avatar) }}" class="w-full h-full object-cover">
                                @else
                                    <span id="avatar-placeholder" class="text-5xl font-bold text-slate-300 uppercase">{{ substr($user->full_name, 0, 1) }}</span>
                                    <img id="avatar-preview" src="" class="w-full h-full object-cover hidden">
                                @endif
                                
                                {{-- Overlay khi hover --}}
                                <div class="absolute inset-0 bg-black/30 hidden group-hover:flex items-center justify-center transition-all cursor-pointer" onclick="document.getElementById('avatar_upload').click()">
                                    <span class="text-white text-xs font-bold">Thay đổi</span>
                                </div>
                            </div>
                            
                            {{-- Input file --}}
                            <input type="file" name="avatar" id="avatar_upload" class="hidden" accept="image/*" onchange="previewImage(this)">
                            
                            <label for="avatar_upload" class="absolute bottom-1 right-2 bg-indigo-600 text-white w-10 h-10 rounded-full flex items-center justify-center cursor-pointer shadow-lg hover:bg-indigo-700 transition-all transform hover:scale-105">
                                <i class="fa-solid fa-camera"></i>
                            </label>
                        </div>
                        <h3 class="font-bold text-slate-900">{{ $user->full_name }}</h3>
                        <p class="text-xs text-slate-400 mt-1">Dung lượng tối đa: 2MB</p>
                        <p class="text-xs text-slate-400">Định dạng: JPG, PNG, JPEG</p>
                    </div>
                </div>

                {{-- Cột phải: Inputs --}}
                <div class="md:col-span-2 space-y-8">
                    {{-- Thông tin cơ bản --}}
                    <div class="bg-white p-8 rounded-2xl shadow-sm border border-slate-100">
                        <h2 class="text-lg font-bold text-slate-900 mb-6 flex items-center gap-2 border-b border-slate-50 pb-4">
                            <i class="fa-regular fa-id-card text-indigo-500"></i> Thông tin cá nhân
                        </h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- Full Name --}}
                            <div class="md:col-span-2">
                                <label class="block text-sm font-bold text-slate-700 mb-2">Họ và tên <span class="text-rose-500">*</span></label>
                                <input type="text" name="full_name" value="{{ old('full_name', $user->full_name) }}" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 focus:bg-white focus:border-indigo-500 rounded-xl transition-all font-medium text-slate-900 focus:ring-4 focus:ring-indigo-500/10 outline-none">
                                @error('full_name') <p class="text-rose-500 text-xs mt-1 font-bold">{{ $message }}</p> @enderror
                            </div>

                            {{-- Phone --}}
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Số điện thoại</label>
                                <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="VD: 0912345678" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 focus:bg-white focus:border-indigo-500 rounded-xl transition-all font-medium text-slate-900 focus:ring-4 focus:ring-indigo-500/10 outline-none">
                                @error('phone') <p class="text-rose-500 text-xs mt-1 font-bold">{{ $message }}</p> @enderror
                            </div>

                            {{-- Birthday --}}
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Ngày sinh</label>
                                <input type="date" name="birthday" value="{{ old('birthday', $user->birthday) }}" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 focus:bg-white focus:border-indigo-500 rounded-xl transition-all font-medium text-slate-900 focus:ring-4 focus:ring-indigo-500/10 outline-none">
                                @error('birthday') <p class="text-rose-500 text-xs mt-1 font-bold">{{ $message }}</p> @enderror
                            </div>

                            {{-- Gender --}}
                            <div class="md:col-span-2">
                                <label class="block text-sm font-bold text-slate-700 mb-2">Giới tính</label>
                                <div class="flex gap-6">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="radio" name="gender" value="male" class="w-5 h-5 text-indigo-600 focus:ring-indigo-500 border-gray-300" {{ old('gender', $user->gender) == 'male' ? 'checked' : '' }}>
                                        <span class="text-slate-700 font-medium">Nam</span>
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="radio" name="gender" value="female" class="w-5 h-5 text-indigo-600 focus:ring-indigo-500 border-gray-300" {{ old('gender', $user->gender) == 'female' ? 'checked' : '' }}>
                                        <span class="text-slate-700 font-medium">Nữ</span>
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="radio" name="gender" value="other" class="w-5 h-5 text-indigo-600 focus:ring-indigo-500 border-gray-300" {{ old('gender', $user->gender) == 'other' ? 'checked' : '' }}>
                                        <span class="text-slate-700 font-medium">Khác</span>
                                    </label>
                                </div>
                            </div>

                            {{-- Address --}}
                            <div class="md:col-span-2">
                                <label class="block text-sm font-bold text-slate-700 mb-2">Địa chỉ</label>
                                <textarea name="address" rows="3" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 focus:bg-white focus:border-indigo-500 rounded-xl transition-all font-medium text-slate-900 focus:ring-4 focus:ring-indigo-500/10 outline-none" placeholder="Nhập địa chỉ nhận hàng mặc định...">{{ old('address', $user->address) }}</textarea>
                                @error('address') <p class="text-rose-500 text-xs mt-1 font-bold">{{ $message }}</p> @enderror
                            </div>

                            {{-- Email (Read only) --}}
                            <div class="md:col-span-2">
                                <label class="block text-sm font-bold text-slate-700 mb-2">Email</label>
                                <div class="relative">
                                    <input type="email" name="email" value="{{ old('email', $user->email) }}" class="w-full px-4 py-3 bg-slate-100 border border-slate-200 text-slate-500 rounded-xl font-medium pl-10 cursor-not-allowed" readonly>
                                    <i class="fa-solid fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                </div>
                                <p class="text-xs text-slate-400 mt-1">Để thay đổi email, vui lòng liên hệ CSKH.</p>
                            </div>
                        </div>
                    </div>

                    {{-- Đổi mật khẩu --}}
                    <div class="bg-white p-8 rounded-2xl shadow-sm border border-slate-100">
                        <h2 class="text-lg font-bold text-slate-900 mb-6 flex items-center gap-2 border-b border-slate-50 pb-4">
                            <i class="fa-solid fa-lock text-rose-500"></i> Bảo mật
                        </h2>
                        
                        <div class="p-4 bg-yellow-50 text-yellow-800 text-sm rounded-lg mb-6 border border-yellow-100 flex items-start gap-2">
                            <i class="fa-solid fa-circle-info mt-0.5"></i> 
                            <span>Chỉ điền vào các ô bên dưới nếu bạn muốn thay đổi mật khẩu hiện tại. Bỏ trống nếu không đổi.</span>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Mật khẩu hiện tại</label>
                                <input type="password" name="current_password" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 focus:bg-white focus:border-indigo-500 rounded-xl transition-all font-medium focus:ring-4 focus:ring-indigo-500/10 outline-none">
                                @error('current_password') <p class="text-rose-500 text-xs mt-1 font-bold">{{ $message }}</p> @enderror
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-bold text-slate-700 mb-2">Mật khẩu mới</label>
                                    <input type="password" name="new_password" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 focus:bg-white focus:border-indigo-500 rounded-xl transition-all font-medium focus:ring-4 focus:ring-indigo-500/10 outline-none">
                                    @error('new_password') <p class="text-rose-500 text-xs mt-1 font-bold">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-slate-700 mb-2">Xác nhận mật khẩu mới</label>
                                    <input type="password" name="new_password_confirmation" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 focus:bg-white focus:border-indigo-500 rounded-xl transition-all font-medium focus:ring-4 focus:ring-indigo-500/10 outline-none">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center justify-end gap-4 pt-4 border-t border-slate-100">
                        <a href="{{ route('client.account.profile') }}" class="px-6 py-3 rounded-xl font-bold text-slate-500 hover:bg-slate-100 transition-all">
                            Hủy bỏ
                        </a>
                        <button type="submit" class="px-8 py-3 rounded-xl font-bold bg-slate-900 text-white hover:bg-indigo-600 shadow-lg hover:shadow-indigo-200 transform hover:-translate-y-1 transition-all">
                            Lưu thay đổi
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    function previewImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                var preview = document.getElementById('avatar-preview');
                var placeholder = document.getElementById('avatar-placeholder');
                
                preview.src = e.target.result;
                preview.classList.remove('hidden');
                
                if(placeholder) {
                    placeholder.classList.add('hidden');
                }
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script
@endsection