@extends('admin.layouts.app')

@section('title', 'Chỉnh sửa khách hàng: ' . $customer->full_name)

@section('content')
<div class="container px-6 mx-auto pb-20 max-w-6xl">

    {{-- HEADER: Title & Back Button --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8 pt-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Chỉnh sửa hồ sơ</h1>
            <p class="text-slate-500 text-sm mt-1">
                Cập nhật thông tin cho khách hàng <span class="font-bold text-indigo-600">{{ $customer->full_name }}</span>
            </p>
        </div>
        <a href="{{ route('admin.customers.index') }}" 
           class="group inline-flex items-center px-5 py-2.5 bg-white border border-slate-200 rounded-xl text-slate-600 hover:border-indigo-300 hover:text-indigo-600 font-medium transition-all shadow-sm">
            <i class="fa-solid fa-arrow-left mr-2 group-hover:-translate-x-1 transition-transform"></i> 
            Quay lại danh sách
        </a>
    </div>

    {{-- ALERT MESSAGES --}}
    @if(session('success'))
        <div class="p-4 mb-6 rounded-xl bg-emerald-50 border border-emerald-100 flex items-center gap-3 animate-fade-in-down shadow-sm">
            <div class="flex-shrink-0 w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600">
                <i class="fa-solid fa-check"></i>
            </div>
            <span class="text-emerald-800 font-medium text-sm">{{ session('success') }}</span>
        </div>
    @endif

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

    {{-- FORM CHÍNH (Bao trùm layout) --}}
    <form action="{{ route('admin.customers.update', $customer->id) }}" method="POST" enctype="multipart/form-data" id="main-form">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-12 gap-8">

            {{-- ================= TRÁI: AVATAR & TÀI KHOẢN (Chiếm 4/12 cột) ================= --}}
            <div class="col-span-12 lg:col-span-4 space-y-6">
                
                {{-- Card 1: Avatar --}}
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 text-center">
                    <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-6 text-left">Ảnh đại diện</h3>
                    
                    <div class="relative group mx-auto w-40 h-40">
                        {{-- Vòng tròn bao ngoài (Glow effect) --}}
                        <div class="absolute -inset-1 bg-gradient-to-tr from-indigo-500 to-purple-500 rounded-full opacity-0 group-hover:opacity-100 transition duration-500 blur-sm"></div>
                        
                        {{-- Container ảnh --}}
                        <div class="relative w-40 h-40 rounded-full overflow-hidden border-4 border-white shadow-lg bg-slate-50">
                            {{-- Logic hiển thị ảnh: Nếu có ảnh DB thì hiện, ko thì hiện default --}}
                            <img id="avatar-preview" 
                                 src="{{ $customer->avatar_url ?? asset('images/default-avatar.png') }}" 
                                 class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" 
                                 alt="Avatar Preview">
                            
                            {{-- Overlay khi hover --}}
                            <label for="avatar-input" class="absolute inset-0 bg-slate-900/40 flex flex-col items-center justify-center text-white opacity-0 group-hover:opacity-100 transition-opacity duration-300 cursor-pointer">
                                <i class="fa-solid fa-camera text-2xl mb-1"></i>
                                <span class="text-xs font-medium">Thay đổi ảnh</span>
                            </label>
                        </div>
                        <input type="file" name="avatar" id="avatar-input" class="hidden" accept="image/*" onchange="previewImage(this)">
                    </div>
                    <p class="text-xs text-slate-400 mt-4">Hỗ trợ: JPG, PNG, WEBP. Tối đa 2MB.</p>
                </div>

                {{-- Card 2: Thiết lập đăng nhập --}}
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                    <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-4">Cài đặt tài khoản</h3>
                    
                    <div class="space-y-5">
                        {{-- Trạng thái --}}
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Trạng thái</label>
                            <div class="relative">
                                <select name="status" class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition-all appearance-none cursor-pointer">
                                    <option value="active" {{ old('status', $customer->status) === 'active' ? 'selected' : '' }}>Đang hoạt động</option>
                                    <option value="banned" {{ old('status', $customer->status) === 'banned' ? 'selected' : '' }}>Đang bị khóa</option>
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

                        {{-- Alert Mật khẩu --}}
                        <div class="p-3 bg-indigo-50 rounded-lg border border-indigo-100 flex gap-2">
                            <i class="fa-solid fa-circle-info text-indigo-500 mt-0.5 text-xs"></i>
                            <span class="text-xs text-indigo-800 font-medium">Chỉ nhập bên dưới nếu bạn muốn đổi mật khẩu mới.</span>
                        </div>

                        {{-- Mật khẩu --}}
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Mật khẩu mới</label>
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
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Xác nhận mật khẩu</label>
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

            {{-- ================= PHẢI: THÔNG TIN CHI TIẾT & ĐỊA CHỈ (Chiếm 8/12 cột) ================= --}}
            <div class="col-span-12 lg:col-span-8 space-y-8">
                
                {{-- Card: Thông tin chung --}}
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8">
                    <div class="flex items-center gap-3 mb-6 pb-4 border-b border-slate-100">
                        <div class="w-10 h-10 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-600">
                            <i class="fa-regular fa-id-card text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-slate-800">Thông tin cá nhân</h3>
                            <p class="text-slate-500 text-xs">Thông tin liên hệ chính của khách hàng.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        
                        {{-- Họ tên --}}
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Họ và tên <span class="text-rose-500">*</span></label>
                            <input type="text" name="full_name" 
                                   class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition-all font-medium text-slate-800 placeholder:text-slate-400"
                                   value="{{ old('full_name', $customer->full_name) }}"
                                   placeholder="Ví dụ: Nguyễn Văn An">
                        </div>

                        {{-- Email --}}
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Email <span class="text-rose-500">*</span></label>
                            <div class="relative">
                                <input type="email" name="email" 
                                       class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition-all placeholder:text-slate-400"
                                       value="{{ old('email', $customer->email) }}"
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
                                       value="{{ old('phone', $customer->phone) }}"
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
                                    <option value="" {{ $customer->gender === null ? 'selected' : '' }}> Chọn </option>
                                    <option value="male" {{ old('gender', $customer->gender) === 'male' ? 'selected' : '' }}>Nam</option>
                                    <option value="female" {{ old('gender', $customer->gender) === 'female' ? 'selected' : '' }}>Nữ</option>
                                    <option value="other" {{ old('gender', $customer->gender) === 'other' ? 'selected' : '' }}>Khác</option>
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
                                       value="{{ old('birthday', $customer->birthday ? \Carbon\Carbon::parse($customer->birthday)->format('Y-m-d') : '') }}">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                    <i class="fa-solid fa-cake-candles"></i>
                                </div>
                            </div>
                        </div>

                    </div>

                    {{-- Main Save Button --}}
                    <div class="mt-8 pt-4 border-t border-slate-100 flex justify-end">
                        <button type="submit" class="px-8 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-bold rounded-xl shadow-lg shadow-indigo-500/30 transition-all transform hover:scale-[1.02] active:scale-[0.98] flex items-center">
                            <i class="fa-solid fa-floppy-disk mr-2"></i> Lưu thay đổi
                        </button>
                    </div>
                </div>

                {{-- Card: Sổ địa chỉ --}}
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 pb-4 border-b border-slate-100">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-600">
                                <i class="fa-solid fa-location-dot text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                                    Sổ địa chỉ 
                                    <span class="inline-flex items-center justify-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600 border border-slate-200">
                                        {{ $customer->addresses->count() }}
                                    </span>
                                </h3>
                                <p class="text-slate-500 text-xs">Quản lý các địa chỉ giao hàng.</p>
                            </div>
                        </div>

                        <a href="{{ route('admin.customers.addresses.create', $customer->id) }}" 
                           class="inline-flex items-center px-4 py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-sm font-bold rounded-xl transition-colors border border-indigo-200">
                            <i class="fa-solid fa-plus mr-1.5"></i> Thêm địa chỉ
                        </a>
                    </div>

                    @if($customer->addresses->count() > 0)
                        <div class="grid grid-cols-1 gap-4">
                            @foreach($customer->addresses as $addr)
                                <div class="group relative p-5 rounded-xl border {{ $addr->is_default ? 'border-indigo-200 bg-indigo-50/30' : 'border-slate-200 bg-white hover:border-indigo-200' }} transition-all">
                                    
                                    <div class="flex justify-between items-start">
                                        <div class="space-y-1.5">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <span class="font-bold text-slate-800 text-base">{{ $addr->contact_name }}</span>
                                                <span class="text-slate-300">|</span>
                                                <span class="text-slate-600 font-mono text-sm">{{ $addr->phone }}</span>
                                                
                                                @if($addr->is_default)
                                                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-600 text-white uppercase tracking-wider shadow-sm">
                                                        Mặc định
                                                    </span>
                                                @endif
                                            </div>
                                            
                                            <p class="text-sm text-slate-600 leading-relaxed max-w-xl flex items-start gap-2">
                                                <i class="fa-solid fa-map-pin text-slate-400 mt-1 flex-shrink-0"></i>
                                                <span>
                                                    {{ $addr->address }}<br>
                                                    <span class="text-slate-500">{{ $addr->ward }}, {{ $addr->district }}, {{ $addr->city }}</span>
                                                </span>
                                            </p>
                                        </div>

                                        {{-- Actions --}}
                                        <div class="flex items-center gap-2">
                                            {{-- Sửa --}}
                                            <a href="{{ route('admin.customers.addresses.edit', [$customer->id, $addr->id]) }}" 
                                               class="w-9 h-9 flex items-center justify-center rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-white hover:shadow-md border border-transparent hover:border-slate-100 transition-all" 
                                               title="Sửa địa chỉ">
                                                <i class="fa-solid fa-pen"></i>
                                            </a>

                                            {{-- Xóa (Trigger hidden form) --}}
                                            <button type="button" 
                                                    onclick="if(confirm('Bạn có chắc chắn muốn xóa địa chỉ này?')) document.getElementById('delete-addr-{{ $addr->id }}').submit();"
                                                    class="w-9 h-9 flex items-center justify-center rounded-lg text-slate-400 hover:text-rose-600 hover:bg-white hover:shadow-md border border-transparent hover:border-slate-100 transition-all" 
                                                    title="Xóa địa chỉ">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-10 border-2 border-dashed border-slate-200 rounded-xl bg-slate-50/50">
                            <div class="w-14 h-14 bg-white rounded-full flex items-center justify-center mx-auto mb-3 shadow-sm text-slate-300">
                                <i class="fa-solid fa-map-location-dot text-2xl"></i>
                            </div>
                            <p class="text-slate-500 font-medium">Chưa có địa chỉ nào được lưu.</p>
                            <p class="text-xs text-slate-400 mt-1">Hãy thêm địa chỉ giao hàng để tiện cho việc lên đơn.</p>
                        </div>
                    @endif
                </div>

            </div> {{-- End Col Right --}}
        </div>
    </form> {{-- END FORM CHÍNH --}}

    {{-- KHU VỰC FORM ẨN ĐỂ XÓA ĐỊA CHỈ (Đặt ngoài form chính để tránh lỗi Nested Forms) --}}
    @foreach($customer->addresses as $addr)
        <form id="delete-addr-{{ $addr->id }}" 
              action="{{ route('admin.customers.addresses.destroy', [$customer->id, $addr->id]) }}" 
              method="POST" class="hidden">
            @csrf
            @method('DELETE')
        </form>
    @endforeach

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