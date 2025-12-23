@extends('admin.layouts.app')

@section('title', 'Cập nhật địa chỉ')

@section('content')
<div class="container px-6 mx-auto pb-20 max-w-4xl">

    {{-- HEADER: Title & Back Button --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8 pt-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Cập nhật địa chỉ</h1>
            <p class="text-slate-500 text-sm mt-1">
                Sửa thông tin giao hàng cho khách: <span class="font-bold text-indigo-600">{{ $customer->full_name }}</span>
            </p>
        </div>
        <a href="{{ route('admin.customers.edit', $customer->id) }}" 
           class="group inline-flex items-center px-5 py-2.5 bg-white border border-slate-200 rounded-xl text-slate-600 hover:border-indigo-300 hover:text-indigo-600 font-medium transition-all shadow-sm">
            <i class="fa-solid fa-arrow-left mr-2 group-hover:-translate-x-1 transition-transform"></i> 
            Quay lại hồ sơ
        </a>
    </div>

    {{-- ALERT ERROR --}}
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
    <form action="{{ route('admin.customers.addresses.update', [$customer->id, $address->id]) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            
            {{-- Header Card --}}
            <div class="px-8 py-6 border-b border-slate-100 flex items-center gap-3 bg-slate-50/50">
                <div class="w-10 h-10 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-600">
                    <i class="fa-solid fa-pen-to-square text-lg"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-slate-800">Chi tiết địa chỉ</h3>
                    <p class="text-slate-500 text-xs">Cập nhật thông tin người nhận và địa điểm giao hàng.</p>
                </div>
            </div>

            <div class="p-8 space-y-8">
                
                {{-- 1. THÔNG TIN NGƯỜI NHẬN --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Tên người nhận --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Tên người nhận <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <input type="text" name="contact_name" 
                                   class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition-all font-medium text-slate-800 placeholder:text-slate-400"
                                   value="{{ old('contact_name', $address->contact_name) }}" 
                                   placeholder="Nhập tên người nhận">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <i class="fa-solid fa-user"></i>
                            </div>
                        </div>
                    </div>

                    {{-- Số điện thoại --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Số điện thoại <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <input type="text" name="phone" 
                                   class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition-all placeholder:text-slate-400"
                                   value="{{ old('phone', $address->phone) }}"
                                   placeholder="09xxxxxxxx">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <i class="fa-solid fa-phone"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="border-t border-slate-100"></div>

                {{-- 2. ĐỊA CHỈ CHI TIẾT --}}
                <div class="space-y-6">
                    <h4 class="text-sm font-bold text-slate-400 uppercase tracking-wider">Khu vực vận chuyển</h4>
                    
                    {{-- Tỉnh/Thành - Quận/Huyện - Phường/Xã --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        {{-- Tỉnh/Thành --}}
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 mb-1.5 uppercase">Tỉnh / Thành phố</label>
                            <input type="text" name="city" 
                                   class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition-all placeholder:text-slate-400"
                                   value="{{ old('city', $address->city) }}" placeholder="VD: Hà Nội">
                        </div>

                        {{-- Quận/Huyện --}}
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 mb-1.5 uppercase">Quận / Huyện</label>
                            <input type="text" name="district" 
                                   class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition-all placeholder:text-slate-400"
                                   value="{{ old('district', $address->district) }}" placeholder="VD: Cầu Giấy">
                        </div>

                        {{-- Phường/Xã --}}
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 mb-1.5 uppercase">Phường / Xã</label>
                            <input type="text" name="ward" 
                                   class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition-all placeholder:text-slate-400"
                                   value="{{ old('ward', $address->ward) }}" placeholder="VD: Dịch Vọng">
                        </div>
                    </div>

                    {{-- Địa chỉ cụ thể --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Địa chỉ cụ thể (Số nhà, đường)</label>
                        <div class="relative">
                            <input type="text" name="address" 
                                   class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition-all placeholder:text-slate-400"
                                   value="{{ old('address', $address->address) }}" 
                                   placeholder="VD: Số 123, Đường ABC...">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <i class="fa-solid fa-map-location-dot"></i>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 3. CÀI ĐẶT MẶC ĐỊNH --}}
                <div class="p-4 rounded-xl border border-indigo-100 bg-indigo-50/50 flex items-start gap-3 transition-colors hover:bg-indigo-50">
                    <div class="flex items-center h-5 mt-0.5">
                        <input id="is_default" name="is_default" type="checkbox" value="1" 
                               class="w-5 h-5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 cursor-pointer"
                               {{ old('is_default', $address->is_default) ? 'checked' : '' }}>
                    </div>
                    <div>
                        <label for="is_default" class="font-bold text-slate-800 cursor-pointer select-none">Đặt làm địa chỉ mặc định</label>
                        <p class="text-slate-500 text-xs mt-1">Khi khách đặt hàng, hệ thống sẽ tự động chọn địa chỉ này đầu tiên.</p>
                    </div>
                </div>

            </div>

            {{-- FOOTER BUTTONS --}}
            <div class="px-8 py-5 bg-slate-50 border-t border-slate-200 flex justify-end gap-3">
                <a href="{{ route('admin.customers.edit', $customer->id) }}" 
                   class="px-6 py-2.5 bg-white border border-slate-300 text-slate-700 font-bold rounded-xl hover:bg-slate-100 hover:text-slate-900 transition-colors shadow-sm">
                    Hủy bỏ
                </a>
                <button type="submit" 
                        class="px-8 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-bold rounded-xl shadow-lg shadow-indigo-500/30 transition-all transform hover:scale-[1.02] active:scale-[0.98] flex items-center">
                    <i class="fa-solid fa-floppy-disk mr-2"></i> Lưu cập nhật
                </button>
            </div>

        </div>
    </form>
</div>
@endsection