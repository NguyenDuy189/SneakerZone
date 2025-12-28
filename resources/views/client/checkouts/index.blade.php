@extends('client.layouts.app')

@section('title', 'Thanh Toán Đơn Hàng')

@section('content')
<div class="bg-gray-50 min-h-screen py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        {{-- HEADER --}}
        <div class="text-center mb-10" data-aos="fade-down">
            <h1 class="text-3xl font-display font-black text-slate-900 uppercase tracking-tight">Thanh Toán</h1>
            <p class="mt-2 text-slate-500">Hoàn tất đơn hàng của bạn ngay bây giờ</p>
        </div>

        {{-- ALERT ERROR (Hiển thị lỗi từ Controller trả về) --}}
        @if(session('error'))
            <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded shadow-sm" role="alert">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fa-solid fa-circle-exclamation text-red-500"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-red-700">{{ session('error') }}</p>
                    </div>
                </div>
            </div>
        @endif

        {{-- FORM CHÍNH: Bao quanh cả 2 cột để Submit được dữ liệu --}}
        {{-- LƯU Ý QUAN TRỌNG: method="POST" và route đúng tên trong route:list --}}
        <form action="{{ route('client.checkouts.process') }}" method="POST" class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            @csrf

            {{-- CỘT TRÁI: THÔNG TIN GIAO HÀNG --}}
            <div class="lg:col-span-7 space-y-6" data-aos="fade-right">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-100 flex items-center gap-3">
                        <i class="fa-regular fa-address-card text-indigo-600 text-xl"></i>
                        <h2 class="text-lg font-bold text-slate-800">Thông tin giao hàng</h2>
                    </div>
                    
                    {{-- AlpineJS Data: Quản lý trạng thái chọn địa chỉ & API Tỉnh Thành --}}
                    <div class="p-6 space-y-5" 
                         x-data="{ 
                             addressMode: '{{ old('address_id', $addresses->isNotEmpty() ? $addresses->first()->id : 'new') }}',
                             provinces: [], districts: [], wards: [],
                             selectedProvince: '{{ old('selectedProvince') }}', 
                             selectedDistrict: '{{ old('selectedDistrict') }}', 
                             selectedWard: '{{ old('selectedWard') }}',
                             provinceName: '{{ old('province_name') }}', 
                             districtName: '{{ old('district_name') }}', 
                             wardName: '{{ old('ward_name') }}',
                             
                             init() {
                                 // Lấy danh sách Tỉnh/TP ngay khi load
                                 fetch('https://esgoo.net/api-tinhthanh/1/0.htm')
                                     .then(response => response.json())
                                     .then(data => {
                                         if(data.error === 0) {
                                            this.provinces = data.data;
                                            // Nếu có old data, trigger load lại huyện/xã
                                            if(this.selectedProvince) this.fetchDistricts(true);
                                         }
                                     })
                                     .catch(error => console.error('Lỗi tải Tỉnh/Thành:', error));
                             },
                             fetchDistricts(isOldData = false) {
                                 if(!this.selectedProvince) {
                                     this.districts = []; this.wards = []; 
                                     return;
                                 }
                                 
                                 // Lưu tên Tỉnh (nếu không phải load lại từ old data)
                                 if(!isOldData) {
                                     let p = this.provinces.find(x => x.id == this.selectedProvince);
                                     this.provinceName = p ? p.full_name : '';
                                     // Reset District/Ward khi chọn tỉnh mới
                                     this.districts = []; this.wards = []; 
                                     this.selectedDistrict = ''; this.selectedWard = '';
                                 }
                                 
                                 // Gọi API lấy Quận/Huyện
                                 fetch(`https://esgoo.net/api-tinhthanh/2/${this.selectedProvince}.htm`)
                                     .then(response => response.json())
                                     .then(data => {
                                         if(data.error === 0) {
                                            this.districts = data.data;
                                            if(isOldData && this.selectedDistrict) this.fetchWards(true);
                                         }
                                     });
                             },
                             fetchWards(isOldData = false) {
                                 if(!this.selectedDistrict) {
                                     this.wards = []; 
                                     return;
                                 }

                                 // Lưu tên Quận
                                 if(!isOldData) {
                                     let d = this.districts.find(x => x.id == this.selectedDistrict);
                                     this.districtName = d ? d.full_name : '';
                                     // Reset Ward
                                     this.wards = []; this.selectedWard = '';
                                 }

                                 // Gọi API lấy Phường/Xã
                                 fetch(`https://esgoo.net/api-tinhthanh/3/${this.selectedDistrict}.htm`)
                                     .then(response => response.json())
                                     .then(data => {
                                         if(data.error === 0) {
                                            this.wards = data.data;
                                         }
                                     });
                             },
                             updateWardName() {
                                 let w = this.wards.find(x => x.id == this.selectedWard);
                                 this.wardName = w ? w.full_name : '';
                             }
                          }">
                        
                        {{-- LIST ĐỊA CHỈ CŨ (Nếu user đã đăng nhập và có địa chỉ lưu) --}}
                        @if($addresses->isNotEmpty())
                            <div class="space-y-3 mb-6">
                                <p class="text-sm font-medium text-slate-700">Chọn địa chỉ:</p>
                                @foreach($addresses as $addr)
                                    <label class="relative flex cursor-pointer rounded-lg border bg-white p-4 shadow-sm focus:outline-none hover:border-indigo-500 transition-all"
                                           :class="addressMode == '{{ $addr->id }}' ? 'border-indigo-600 ring-1 ring-indigo-600 bg-indigo-50' : 'border-gray-200'">
                                        <input type="radio" name="address_id" value="{{ $addr->id }}" class="sr-only" x-model="addressMode">
                                        <span class="flex flex-1">
                                            <span class="flex flex-col">
                                                <span class="block text-sm font-bold text-gray-900">
                                                    {{ $addr->name }} <span class="text-gray-500 font-normal mx-1">|</span> {{ $addr->phone }}
                                                    @if($addr->is_default)
                                                        <span class="ml-2 inline-flex items-center rounded-full bg-indigo-100 px-2 py-0.5 text-[10px] font-medium text-indigo-800 uppercase">Mặc định</span>
                                                    @endif
                                                </span>
                                                <span class="mt-1 flex items-center text-sm text-gray-500">
                                                    <i class="fa-solid fa-location-dot mr-2 text-indigo-400"></i>
                                                    {{ $addr->full_address ?? ($addr->address . ', ' . $addr->city) }}
                                                </span>
                                            </span>
                                        </span>
                                        <i class="fa-solid fa-circle-check text-indigo-600 text-xl" x-show="addressMode == '{{ $addr->id }}'"></i>
                                        <i class="fa-regular fa-circle text-gray-300 text-xl" x-show="addressMode != '{{ $addr->id }}'"></i>
                                    </label>
                                @endforeach

                                {{-- NÚT CHỌN NHẬP MỚI --}}
                                <label class="relative flex cursor-pointer rounded-lg border bg-white p-4 shadow-sm focus:outline-none hover:border-indigo-500 transition-all"
                                       :class="addressMode == 'new' ? 'border-indigo-600 ring-1 ring-indigo-600 bg-indigo-50' : 'border-gray-200'">
                                    <input type="radio" name="address_id" value="new" class="sr-only" x-model="addressMode">
                                    <span class="flex flex-1 items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-gray-500"><i class="fa-solid fa-plus"></i></div>
                                        <span class="block text-sm font-bold text-gray-900">Giao đến địa chỉ khác (Nhập mới)</span>
                                    </span>
                                    <i class="fa-solid fa-circle-check text-indigo-600 text-xl" x-show="addressMode == 'new'"></i>
                                    <i class="fa-regular fa-circle text-gray-300 text-xl" x-show="addressMode != 'new'"></i>
                                </label>
                            </div>
                        @else
                            {{-- Nếu không có địa chỉ cũ nào, mặc định là new --}}
                            <input type="hidden" name="address_id" value="new">
                        @endif

                        {{-- FORM NHẬP ĐỊA CHỈ MỚI --}}
                        <div x-show="addressMode === 'new'" 
                             x-transition:enter="transition ease-out duration-300"
                             x-transition:enter-start="opacity-0 transform -translate-y-2"
                             x-transition:enter-end="opacity-100 transform translate-y-0"
                             class="space-y-5 border-t border-gray-100 pt-5 mt-2">
                            
                            <div class="flex items-center gap-2 mb-2">
                                <span class="w-1 h-6 bg-indigo-500 rounded-full"></span>
                                <h3 class="font-bold text-slate-800">Thông tin người nhận mới</h3>
                            </div>
                            
                            {{-- Họ tên & SĐT --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div>
                                    <label for="customer_name" class="block text-sm font-medium text-slate-700 mb-1">Họ và tên <span class="text-red-500">*</span></label>
                                    <input type="text" name="customer_name" id="customer_name" 
                                           value="{{ old('customer_name', Auth::user()->name ?? '') }}"
                                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2.5 px-3">
                                    @error('customer_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="customer_phone" class="block text-sm font-medium text-slate-700 mb-1">Số điện thoại <span class="text-red-500">*</span></label>
                                    <input type="text" name="customer_phone" id="customer_phone" 
                                           value="{{ old('customer_phone', Auth::user()->phone ?? '') }}"
                                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2.5 px-3">
                                    @error('customer_phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            {{-- SELECT ĐỊA CHỈ API ESGOO --}}
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                                {{-- Tỉnh / Thành --}}
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Tỉnh / Thành phố <span class="text-red-500">*</span></label>
                                    <select name="selectedProvince" x-model="selectedProvince" @change="fetchDistricts()" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2.5 px-3 bg-white">
                                        <option value="">-- Chọn Tỉnh/TP --</option>
                                        <template x-for="prov in provinces" :key="prov.id">
                                            <option :value="prov.id" x-text="prov.full_name" :selected="prov.id == selectedProvince"></option>
                                        </template>
                                    </select>
                                    {{-- Input ẩn lưu tên để gửi về server --}}
                                    <input type="hidden" name="province_name" :value="provinceName">
                                    @error('province_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>

                                {{-- Quận / Huyện --}}
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Quận / Huyện <span class="text-red-500">*</span></label>
                                    <select name="selectedDistrict" x-model="selectedDistrict" @change="fetchWards()" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2.5 px-3 bg-white">
                                        <option value="">-- Chọn Quận/Huyện --</option>
                                        <template x-for="dist in districts" :key="dist.id">
                                            <option :value="dist.id" x-text="dist.full_name" :selected="dist.id == selectedDistrict"></option>
                                        </template>
                                    </select>
                                    <input type="hidden" name="district_name" :value="districtName">
                                    @error('district_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>

                                {{-- Phường / Xã --}}
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Phường / Xã <span class="text-red-500">*</span></label>
                                    <select name="selectedWard" x-model="selectedWard" @change="updateWardName()" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2.5 px-3 bg-white">
                                        <option value="">-- Chọn Phường/Xã --</option>
                                        <template x-for="ward in wards" :key="ward.id">
                                            <option :value="ward.id" x-text="ward.full_name" :selected="ward.id == selectedWard"></option>
                                        </template>
                                    </select>
                                    <input type="hidden" name="ward_name" :value="wardName">
                                    @error('ward_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            {{-- Địa chỉ chi tiết --}}
                            <div>
                                <label for="specific_address" class="block text-sm font-medium text-slate-700 mb-1">Địa chỉ cụ thể (Số nhà, tên đường...) <span class="text-red-500">*</span></label>
                                <input type="text" name="specific_address" id="specific_address" 
                                       value="{{ old('specific_address') }}"
                                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2.5 px-3"
                                       placeholder="Ví dụ: Số 12 ngõ 34 đường ABC">
                                @error('specific_address') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        {{-- Email --}}
                        <div class="border-t border-gray-100 pt-5">
                            <label for="customer_email" class="block text-sm font-medium text-slate-700 mb-1">Email nhận hóa đơn <span class="text-red-500">*</span></label>
                            <input type="email" name="customer_email" id="customer_email" 
                                   value="{{ old('customer_email', Auth::user()->email ?? '') }}"
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2.5 px-3 bg-slate-50">
                            @error('customer_email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        {{-- Ghi chú --}}
                        <div>
                            <label for="note" class="block text-sm font-medium text-slate-700 mb-1">Ghi chú (Tùy chọn)</label>
                            <textarea name="note" id="note" rows="2" 
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2.5 px-3">{{ old('note') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- CỘT PHẢI: ĐƠN HÀNG & THANH TOÁN --}}
            <div class="lg:col-span-5 space-y-6" data-aos="fade-left">
                
                {{-- Tóm tắt đơn hàng --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 bg-slate-900 text-white flex items-center gap-3">
                        <i class="fa-solid fa-cart-shopping"></i>
                        <h2 class="text-lg font-bold">Đơn hàng của bạn</h2>
                    </div>

                    <div class="p-6">
                        <ul class="divide-y divide-gray-100 mb-4 max-h-80 overflow-y-auto pr-2 custom-scrollbar">
                            @foreach($cart->items as $item)
                                @php
                                    $price = $item->variant->sale_price > 0 ? $item->variant->sale_price : $item->variant->original_price;
                                    $imgUrl = $item->variant->image_url ?? $item->variant->product->img_thumbnail ?? 'https://via.placeholder.com/150';
                                @endphp
                                <li class="py-4 flex gap-4">
                                    <div class="h-16 w-16 flex-shrink-0 overflow-hidden rounded-md border border-gray-200">
                                        <img src="{{ $imgUrl }}" 
                                             alt="{{ $item->variant->product->name }}" 
                                             class="h-full w-full object-cover object-center">
                                    </div>
                                    <div class="flex flex-1 flex-col">
                                        <div>
                                            <div class="flex justify-between text-base font-medium text-gray-900">
                                                <h3 class="line-clamp-1"><a href="#">{{ $item->variant->product->name }}</a></h3>
                                                <p class="ml-4">{{ number_format($price * $item->quantity, 0, ',', '.') }}₫</p>
                                            </div>
                                            <p class="mt-1 text-sm text-gray-500">
                                                @if($item->variant->attributeValues)
                                                    {{ $item->variant->attributeValues->implode('value', ' / ') }}
                                                @endif
                                            </p>
                                        </div>
                                        <div class="flex flex-1 items-end justify-between text-sm">
                                            <p class="text-gray-500">x{{ $item->quantity }}</p>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>

                        <div class="border-t border-gray-100 pt-4 space-y-2">
                            <div class="flex justify-between text-sm text-gray-600">
                                <p>Tạm tính</p>
                                <p class="font-medium text-gray-900">{{ number_format($subtotal, 0, ',', '.') }}₫</p>
                            </div>
                            @if($discount > 0)
                            <div class="flex justify-between text-sm text-green-600">
                                <p>Giảm giá</p>
                                <p class="font-medium">-{{ number_format($discount, 0, ',', '.') }}₫</p>
                            </div>
                            @endif
                            <div class="flex justify-between text-base font-bold text-slate-900 pt-2 border-t border-gray-100 mt-2">
                                <p>Tổng cộng</p>
                                <p class="text-xl text-indigo-600">{{ number_format($total, 0, ',', '.') }}₫</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Phương thức thanh toán --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-100 flex items-center gap-3">
                        <i class="fa-regular fa-credit-card text-indigo-600 text-xl"></i>
                        <h2 class="text-lg font-bold text-slate-800">Thanh toán</h2>
                    </div>

                    <div class="p-6">
                        @if ($errors->has('payment_method'))
                            <div class="mb-4 text-sm text-red-600 bg-red-50 p-2 rounded flex items-center gap-2">
                                <i class="fa-solid fa-circle-exclamation"></i>
                                {{ $errors->first('payment_method') }}
                            </div>
                        @endif

                        <div class="space-y-3">
                            {{-- 1. COD --}}
                            <label class="relative flex cursor-pointer rounded-lg border bg-white p-4 shadow-sm focus:outline-none hover:border-indigo-500 transition-all peer-checked:border-indigo-600 peer-checked:ring-1 peer-checked:ring-indigo-600"
                                   :class="$el.querySelector('input').checked ? 'border-indigo-600 ring-1 ring-indigo-600' : ''">
                                <input type="radio" name="payment_method" value="cod" class="sr-only peer" {{ old('payment_method') == 'cod' || !old('payment_method') ? 'checked' : '' }}>
                                <span class="flex flex-1">
                                    <span class="flex flex-col">
                                        <span class="block text-sm font-bold text-gray-900 flex items-center gap-2">
                                            <i class="fa-solid fa-money-bill-wave text-green-500 w-6 text-center"></i> 
                                            Thanh toán khi nhận hàng (COD)
                                        </span>
                                        <span class="mt-1 ml-8 flex items-center text-xs text-gray-500">Thanh toán tiền mặt khi shipper giao tới.</span>
                                    </span>
                                </span>
                                <i class="fa-solid fa-circle-check text-indigo-600 text-xl hidden peer-checked:block"></i>
                                <i class="fa-regular fa-circle text-gray-300 text-xl block peer-checked:hidden"></i>
                            </label>

                            {{-- 2. VNPAY --}}
                            <label class="relative flex cursor-pointer rounded-lg border bg-white p-4 shadow-sm focus:outline-none hover:border-indigo-500 transition-all">
                                <input type="radio" name="payment_method" value="vnpay" class="sr-only peer" {{ old('payment_method') == 'vnpay' ? 'checked' : '' }}>
                                <span class="flex flex-1">
                                    <span class="flex flex-col">
                                        <span class="block text-sm font-bold text-gray-900 flex items-center gap-2">
                                            <img src="https://vnpay.vn/assets/images/logo-icon/logo-primary.svg" alt="VNPAY" class="h-5 w-6 object-contain"> 
                                            Ví VNPAY
                                        </span>
                                    </span>
                                </span>
                                <i class="fa-solid fa-circle-check text-indigo-600 text-xl hidden peer-checked:block"></i>
                                <i class="fa-regular fa-circle text-gray-300 text-xl block peer-checked:hidden"></i>
                            </label>

                            {{-- 3. MOMO --}}
                            <label class="relative flex cursor-pointer rounded-lg border bg-white p-4 shadow-sm focus:outline-none hover:border-indigo-500 transition-all">
                                <input type="radio" name="payment_method" value="momo" class="sr-only peer" {{ old('payment_method') == 'momo' ? 'checked' : '' }}>
                                <span class="flex flex-1">
                                    <span class="flex flex-col">
                                        <span class="block text-sm font-bold text-gray-900 flex items-center gap-2">
                                            <img src="https://upload.wikimedia.org/wikipedia/vi/f/fe/MoMo_Logo.png" alt="MoMo" class="h-5 w-6 object-contain"> 
                                            Ví MoMo
                                        </span>
                                    </span>
                                </span>
                                <i class="fa-solid fa-circle-check text-indigo-600 text-xl hidden peer-checked:block"></i>
                                <i class="fa-regular fa-circle text-gray-300 text-xl block peer-checked:hidden"></i>
                            </label>

                            {{-- 4. ZALOPAY --}}
                            <label class="relative flex cursor-pointer rounded-lg border bg-white p-4 shadow-sm focus:outline-none hover:border-indigo-500 transition-all">
                                <input type="radio" name="payment_method" value="zalopay" class="sr-only peer" {{ old('payment_method') == 'zalopay' ? 'checked' : '' }}>
                                <span class="flex flex-1">
                                    <span class="flex flex-col">
                                        <span class="block text-sm font-bold text-gray-900 flex items-center gap-2">
                                            <img src="https://cdn.haitrieu.com/wp-content/uploads/2022/10/Logo-ZaloPay-Square.png" alt="ZaloPay" class="h-5 w-6 object-contain"> 
                                            Ví ZaloPay
                                        </span>
                                    </span>
                                </span>
                                <i class="fa-solid fa-circle-check text-indigo-600 text-xl hidden peer-checked:block"></i>
                                <i class="fa-regular fa-circle text-gray-300 text-xl block peer-checked:hidden"></i>
                            </label>

                        </div>

                        {{-- BUTTON SUBMIT: Nằm trong form nên nó sẽ kích hoạt POST --}}
                        <button type="submit" class="mt-8 w-full rounded-xl bg-indigo-600 px-6 py-4 text-sm font-bold text-white shadow-lg shadow-indigo-200 hover:bg-indigo-700 hover:shadow-xl hover:-translate-y-0.5 transition-all uppercase tracking-widest">
                            Xác nhận thanh toán
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection