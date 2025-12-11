@extends('admin.layouts.app')

@section('title', 'Thêm địa chỉ mới cho khách hàng')
@section('header', 'Thêm địa chỉ mới')

@section('content')
<div class="container px-6 mx-auto pb-12 max-w-4xl">

    {{-- BREADCRUMB --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Thêm địa chỉ giao hàng</h2>
            <p class="text-slate-500 text-sm mt-1">
                Đang thêm địa chỉ cho khách hàng: <span class="font-bold text-indigo-600">{{ $customer->full_name }}</span>
            </p>
        </div>
        <a href="{{ route('admin.customers.edit', $customer->id) }}" 
           class="inline-flex items-center px-4 py-2 bg-white border border-slate-300 rounded-lg text-slate-700 hover:bg-slate-50 text-sm font-medium transition-colors shadow-sm">
            <i class="fa-solid fa-arrow-left mr-2"></i> Quay lại hồ sơ khách
        </a>
    </div>

    {{-- ALERT ERROR --}}
    @if ($errors->any())
        <div class="p-4 mb-6 bg-rose-50 border border-rose-200 rounded-xl animate-fade-in-down">
            <div class="flex items-center mb-2">
                <i class="fa-solid fa-triangle-exclamation text-rose-600 mr-2"></i>
                <span class="text-rose-700 font-bold">Vui lòng kiểm tra lại thông tin:</span>
            </div>
            <ul class="list-disc list-inside text-sm text-rose-600 ml-4">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- FORM START --}}
    <form action="{{ route('admin.customers.addresses.store', $customer->id) }}" method="POST" 
          class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        @csrf

        <div class="p-6 space-y-8">
            
            {{-- 1. THÔNG TIN LIÊN HỆ --}}
            <div>
                <h3 class="text-base font-bold text-slate-800 mb-4 flex items-center border-b border-slate-100 pb-2">
                    <i class="fa-solid fa-address-card text-indigo-500 mr-2"></i> Thông tin người nhận
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Tên người nhận --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Tên người nhận <span class="text-red-500">*</span></label>
                        <input type="text" name="contact_name" 
                               class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm"
                               value="{{ old('contact_name', $customer->full_name) }}" 
                               placeholder="Nhập tên người nhận hàng">
                        @error('contact_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Số điện thoại --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Số điện thoại <span class="text-red-500">*</span></label>
                        <input type="text" name="phone" 
                               class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm"
                               value="{{ old('phone', $customer->phone) }}"
                               placeholder="09xxxxxxxx">
                        @error('phone') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- 2. ĐỊA CHỈ CHI TIẾT --}}
            <div>
                <h3 class="text-base font-bold text-slate-800 mb-4 flex items-center border-b border-slate-100 pb-2">
                    <i class="fa-solid fa-map-location-dot text-indigo-500 mr-2"></i> Khu vực vận chuyển
                </h3>
                
                {{-- Tỉnh/Thành - Quận/Huyện - Phường/Xã --}}
                {{-- Lưu ý: Nếu bạn chưa có API chọn địa điểm, đây là Input Text. Nếu có, hãy thay bằng Select --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    
                    {{-- Tỉnh/Thành --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Tỉnh / Thành phố</label>
                        <input type="text" name="city" 
                               class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm"
                               value="{{ old('city') }}" placeholder="VD: Hà Nội">
                    </div>

                    {{-- Quận/Huyện --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Quận / Huyện</label>
                        <input type="text" name="district" 
                               class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm"
                               value="{{ old('district') }}" placeholder="VD: Quận Cầu Giấy">
                    </div>

                    {{-- Phường/Xã --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Phường / Xã</label>
                        <input type="text" name="ward" 
                               class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm"
                               value="{{ old('ward') }}" placeholder="VD: Phường Dịch Vọng">
                    </div>
                </div>

                {{-- Địa chỉ cụ thể --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Địa chỉ cụ thể (Số nhà, Tên đường)</label>
                    <input type="text" name="address" 
                           class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm"
                           value="{{ old('address') }}" placeholder="VD: Số 123, Đường ABC...">
                </div>
            </div>

            {{-- 3. CÀI ĐẶT --}}
            <div class="bg-indigo-50/50 rounded-xl p-4 border border-indigo-100 flex items-start gap-3">
                <div class="flex items-center h-5">
                    <input id="is_default" name="is_default" type="checkbox" value="1" 
                           class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                           {{ old('is_default') || $customer->addresses->count() === 0 ? 'checked' : '' }}>
                </div>
                <div class="ml-1 text-sm">
                    <label for="is_default" class="font-medium text-slate-800 cursor-pointer select-none">Đặt làm địa chỉ mặc định</label>
                    <p class="text-slate-500 text-xs mt-1">Địa chỉ này sẽ được ưu tiên chọn khi khách hàng đặt đơn mới.</p>
                </div>
            </div>

        </div>

        {{-- ACTION BUTTONS --}}
        <div class="bg-slate-50 px-6 py-4 border-t border-slate-200 flex justify-end gap-3">
            <a href="{{ route('admin.customers.edit', $customer->id) }}" class="px-5 py-2.5 bg-white border border-slate-300 text-slate-700 font-semibold rounded-xl hover:bg-slate-50 transition-colors">
                Hủy bỏ
            </a>
            <button type="submit" class="px-6 py-2.5 bg-indigo-600 text-white font-bold rounded-xl hover:bg-indigo-700 shadow-lg shadow-indigo-200 transition-all transform active:scale-95 flex items-center">
                <i class="fa-solid fa-plus mr-2"></i> Lưu địa chỉ
            </button>
        </div>

    </form>
</div>
@endsection