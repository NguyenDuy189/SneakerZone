@extends('admin.layouts.app')

@section('title', 'Thêm khách hàng mới')

@section('content')
<div class="container px-6 mx-auto pb-20 max-w-6xl">

    {{-- HEADER: Title & Back Button --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8 pt-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Thêm khách hàng mới</h1>
            <p class="text-slate-500 text-sm mt-1">Tạo hồ sơ thành viên và thiết lập thông tin đăng nhập.</p>
        </div>
        <a href="{{ route('admin.customers.index') }}" 
           class="group inline-flex items-center px-5 py-2.5 bg-white border border-slate-200 rounded-xl text-slate-600 hover:border-indigo-300 hover:text-indigo-600 font-medium transition-all shadow-sm">
            <i class="fa-solid fa-arrow-left mr-2 group-hover:-translate-x-1 transition-transform"></i> 
            Quay lại danh sách
        </a>
    </div>

    {{-- ERROR ALERT --}}
    @if ($errors->any())
        <div class="p-4 mb-6 rounded-xl bg-rose-50 border border-rose-100 flex items-start gap-3 animate-fade-in-down">
            <div class="flex-shrink-0 w-8 h-8 rounded-full bg-rose-100 flex items-center justify-center text-rose-600 mt-0.5">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <div>
                <h3 class="text-rose-800 font-bold text-sm">Vui lòng kiểm tra lại dữ liệu</h3>
                <ul class="list-disc list-inside text-sm text-rose-600 mt-1 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    {{-- FORM START --}}
    <form action="{{ route('admin.customers.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="grid grid-cols-12 gap-8">

            {{-- ================= TRÁI: AVATAR & TÀI KHOẢN (Chiếm 4/12 cột) ================= --}}
            <div class="col-span-12 lg:col-span-4 space-y-6">
                
                {{-- Card 1: Avatar --}}
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 text-center">
                    <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-6 text-left">Ảnh đại diện</h3>
                    
                    <div class="relative group mx-auto w-40 h-40">
                        {{-- Vòng tròn bao ngoài --}}
                        <div class="absolute -inset-1 bg-gradient-to-tr from-indigo-500 to-purple-500 rounded-full opacity-0 group-hover:opacity-100 transition duration-500 blur-sm"></div>
                        
                        {{-- Container ảnh --}}
                        <div class="relative w-40 h-40 rounded-full overflow-hidden border-4 border-white shadow-lg bg-slate-50">
                            <img id="avatar-preview" 
                                src="https://placehold.co/200x200/e2e8f0/64748b?text=Avatar" 
                                class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" 
                                alt="Avatar Preview">
                                                        
                            {{-- Overlay khi hover --}}
                            <label for="avatar-input" class="absolute inset-0 bg-slate-900/40 flex flex-col items-center justify-center text-white opacity-0 group-hover:opacity-100 transition-opacity duration-300 cursor-pointer">
                                <i class="fa-solid fa-camera text-2xl mb-1"></i>
                                <span class="text-xs font-medium">Tải ảnh lên</span>
                            </label>
                        </div>
                        <input type="file" name="avatar" id="avatar-input" class="hidden" accept="image/*" onchange="previewImage(this)">
                    </div>
                    <p class="text-xs text-slate-400 mt-4">Hỗ trợ: JPG, PNG, WEBP. Tối đa 2MB.</p>
                </div>

                {{-- Card 2: Thiết lập đăng nhập --}}
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                    <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-4">Thiết lập tài khoản</h3>
                    
                    <div class="space-y-5">
                        {{-- Trạng thái --}}
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Trạng thái</label>
                            <div class="relative">
                                <select name="status" class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition-all appearance-none">
                                    <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Đang hoạt động</option>
                                    <option value="banned" {{ old('status') == 'banned' ? 'selected' : '' }}>Đang bị khóa</option>
                                </select>
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                    <i class="fa-solid fa-toggle-on"></i>
                                </div>
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-slate-400">
                                    <i class="fa-solid fa-chevron-down text-xs"></i>
                                </div>
                            </div>
                        </div>

                        <div class="border-t border-slate-100 my-4"></div>

                        {{-- Mật khẩu --}}
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Mật khẩu <span class="text-rose-500">*</span></label>
                            <div class="relative">
                                <input type="password" name="password" 
                                       class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition-all placeholder:text-slate-400"
                                       placeholder="••••••••">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                    <i class="fa-solid fa-lock"></i>
                                </div>
                            </div>
                        </div>

                        {{-- Xác nhận mật khẩu --}}
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Xác nhận mật khẩu <span class="text-rose-500">*</span></label>
                            <div class="relative">
                                <input type="password" name="password_confirmation" 
                                       class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition-all placeholder:text-slate-400"
                                       placeholder="••••••••">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                    <i class="fa-solid fa-shield-halved"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ================= PHẢI: THÔNG TIN CHI TIẾT (Chiếm 8/12 cột) ================= --}}
            <div class="col-span-12 lg:col-span-8 space-y-6">
                
                {{-- Card: Thông tin chung --}}
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8">
                    <div class="flex items-center gap-3 mb-6 pb-4 border-b border-slate-100">
                        <div class="w-10 h-10 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-600">
                            <i class="fa-regular fa-id-card text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-slate-800">Thông tin cá nhân</h3>
                            <p class="text-slate-500 text-xs">Thông tin cơ bản để liên hệ và giao hàng.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        
                        {{-- Họ tên --}}
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Họ và tên <span class="text-rose-500">*</span></label>
                            <input type="text" name="full_name" 
                                   class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition-all font-medium text-slate-800 placeholder:text-slate-400"
                                   value="{{ old('full_name') }}"
                                   placeholder="Ví dụ: Nguyễn Văn An">
                        </div>

                        {{-- Email --}}
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Email <span class="text-rose-500">*</span></label>
                            <div class="relative">
                                <input type="email" name="email" 
                                       class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition-all placeholder:text-slate-400"
                                       value="{{ old('email') }}"
                                       placeholder="email@example.com">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                    <i class="fa-solid fa-envelope"></i>
                                </div>
                            </div>
                        </div>

                        {{-- Phone --}}
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Số điện thoại</label>
                            <div class="relative">
                                <input type="text" name="phone" 
                                       class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition-all placeholder:text-slate-400"
                                       value="{{ old('phone') }}"
                                       placeholder="0912xxxxxx">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                    <i class="fa-solid fa-phone"></i>
                                </div>
                            </div>
                        </div>

                        {{-- Gender --}}
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Giới tính</label>
                            <div class="relative">
                                <select name="gender" class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition-all appearance-none cursor-pointer">
                                    <option value=""> Chọn giới tính </option>
                                    <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Nam</option>
                                    <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Nữ</option>
                                    <option value="other" {{ old('gender') == 'other' ? 'selected' : '' }}>Khác</option>
                                </select>
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                    <i class="fa-solid fa-venus-mars"></i>
                                </div>
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-slate-400">
                                    <i class="fa-solid fa-chevron-down text-xs"></i>
                                </div>
                            </div>
                        </div>

                        {{-- Birthday --}}
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Ngày sinh</label>
                            <div class="relative">
                                <input type="date" name="birthday" 
                                       class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition-all text-slate-600 placeholder:text-slate-400 cursor-pointer"
                                       value="{{ old('birthday') }}">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                    <i class="fa-solid fa-cake-candles"></i>
                                </div>
                            </div>
                        </div>

                        {{-- Address (Optional) --}}
                        <div class="md:col-span-2 pt-2">
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Địa chỉ (Tùy chọn)</label>
                            <div class="relative">
                                <textarea name="address" rows="3"
                                          class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition-all placeholder:text-slate-400 resize-none"
                                          placeholder="Nhập địa chỉ nhà riêng, cơ quan..."
                                          >{{ old('address') }}</textarea>
                                <div class="absolute top-3.5 left-3.5 pointer-events-none text-slate-400">
                                    <i class="fa-solid fa-map-location-dot"></i>
                                </div>
                            </div>
                            <p class="text-xs text-slate-400 mt-2 flex items-center">
                                <i class="fa-solid fa-circle-info mr-1.5"></i> 
                                Bạn có thể thêm nhiều địa chỉ giao hàng khác sau khi tạo tài khoản.
                            </p>
                        </div>

                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex items-center justify-end gap-4 pt-4">
                    <a href="{{ route('admin.customers.index') }}" class="px-6 py-3 bg-white border border-slate-300 text-slate-700 font-bold rounded-xl hover:bg-slate-50 transition-colors shadow-sm">
                        Hủy bỏ
                    </a>
                    <button type="submit" class="px-8 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-bold rounded-xl shadow-lg shadow-indigo-500/30 transition-all transform hover:scale-[1.02] active:scale-[0.98] flex items-center">
                        <i class="fa-solid fa-check-circle mr-2"></i> 
                        Tạo khách hàng
                    </button>
                </div>

            </div>
        </div>
    </form>
</div>

{{-- SCRIPT: PREVIEW IMAGE --}}
<script>
    function previewImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('avatar-preview').src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endsection