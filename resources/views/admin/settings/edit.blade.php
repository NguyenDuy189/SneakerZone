@extends('admin.layouts.app')
@section('title', 'Cấu hình hệ thống')

@section('content')
<div class="container px-6 mx-auto grid pb-12">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center my-6 gap-4">
        <div class="flex items-center gap-4">
            <!-- Nút Quay lại (Style Vuông) -->
            <a href="{{ route('admin.settings.index') }}" class="group w-12 h-12 flex items-center justify-center bg-white border border-gray-200 rounded-xl shadow-sm hover:shadow-md hover:bg-gray-50 hover:border-indigo-300 transition-all duration-200">
                <i class="fa-solid fa-arrow-left text-xl text-gray-500 group-hover:text-indigo-600"></i>
            </a>

            <!-- Tiêu đề -->
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Cấu hình hệ thống</h2>
                <p class="text-sm text-gray-500 mt-1">Quản lý các tham số, biến toàn cục và thiết lập website.</p>
            </div>
        </div>

        <!-- Flash Messages -->
        <div class="w-full md:w-auto">
            @if(session('success'))
            <div class="inline-flex items-center px-4 py-2 bg-green-50 text-green-700 border border-green-200 rounded-lg text-sm shadow-sm">
                <i class="fa-solid fa-circle-check mr-2"></i> {{ session('success') }}
            </div>
            @endif
            @if($errors->any())
            <div class="inline-flex items-center px-4 py-2 bg-red-50 text-red-700 border border-red-200 rounded-lg text-sm shadow-sm">
                <i class="fa-solid fa-triangle-exclamation mr-2"></i> Vui lòng kiểm tra lại dữ liệu nhập.
            </div>
            @endif
        </div>
    </div>

    <!-- Main Content -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                <i class="fa-solid fa-sliders text-indigo-600 mr-2"></i> Danh sách tham số
            </h3>
        </div>

        <form action="{{ route('admin.settings.update') }}" method="POST" class="p-6">
            @csrf
            
            @php
                // Danh sách từ điển Việt hóa các key phổ biến (dùng khi DB chưa có label tiếng Việt)
                $vnLabels = [
                    // --- THÔNG TIN CƠ BẢN ---
                    'site_name' => 'Tên Website',
                    'site_title' => 'Tiêu đề trang (Title)',
                    'site_description' => 'Mô tả ngắn (Meta Description)',
                    'site_keyword' => 'Từ khóa (Meta Keywords)',
                    'site_logo' => 'Logo Website',
                    'site_favicon' => 'Favicon',
                    
                    // --- LIÊN HỆ ---
                    'email' => 'Email Liên hệ',
                    'admin_email' => 'Email Quản trị',
                    'hotline' => 'Hotline',
                    'support_phone' => 'Số điện thoại hỗ trợ',
                    'address' => 'Địa chỉ cửa hàng',
                    'company_address' => 'Địa chỉ công ty',
                    
                    // --- MẠNG XÃ HỘI ---
                    'facebook_url' => 'Link Fanpage Facebook',
                    'facebook_link' => 'Link Fanpage Facebook',
                    'youtube_link' => 'Link kênh Youtube',
                    'twitter_link' => 'Link Twitter',
                    'instagram_link' => 'Link Instagram',
                    'zalo_phone' => 'Số Zalo OA',

                    // --- BÁN HÀNG & VẬN CHUYỂN ---
                    'freeship_threshold' => 'Hạn mức Freeship (đ)',
                    'shipping_fee' => 'Phí vận chuyển mặc định',
                    'tax_rate' => 'Thuế suất (%)',
                    'currency_symbol' => 'Đơn vị tiền tệ',
                    
                    // --- KỸ THUẬT & KHÁC ---
                    'maintenance_mode' => 'Chế độ bảo trì',
                    'google_analytics_id' => 'Google Analytics ID',
                    'facebook_pixel_id' => 'Facebook Pixel ID',
                ];
            @endphp

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @foreach($settings as $setting)
                    @php 
                        $val = old($setting->key, $setting->value); 
                        // Kiểm tra nếu là JSON để chiếm full dòng
                        $isFullWidth = $setting->type === 'json' || strlen($val) > 100;

                        // Logic hiển thị label: Ưu tiên Label trong DB -> Mapping tiếng Việt -> Tự tạo từ Key
                        $displayLabel = $setting->label ?? ($vnLabels[$setting->key] ?? ucfirst(str_replace('_', ' ', $setting->key)));
                    @endphp

                    <div class="{{ $isFullWidth ? 'md:col-span-2' : 'md:col-span-1' }}">
                        <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center justify-between">
                            <span>{{ $displayLabel }}</span>
                            <span class="text-xs text-gray-400 font-mono bg-gray-100 px-1.5 py-0.5 rounded" title="Mã cấu hình">{{ $setting->key }}</span>
                        </label>

                        @if($setting->type === 'boolean')
                            <div class="relative">
                                <select name="{{ $setting->key }}" class="block w-full pl-3 pr-10 py-2.5 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-lg shadow-sm">
                                    <option value="1" {{ $val ? 'selected' : '' }}>🟢 Đang Bật</option>
                                    <option value="0" {{ !$val ? 'selected' : '' }}>🔴 Đang Tắt</option>
                                </select>
                            </div>
                        
                        @elseif($setting->type === 'json')
                            <div class="relative rounded-md shadow-sm">
                                <textarea name="{{ $setting->key }}" rows="6" class="form-textarea block w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 text-sm font-mono bg-gray-50 text-gray-600" spellcheck="false">{{ json_encode(json_decode($val), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</textarea>
                                <div class="absolute top-2 right-2">
                                    <span class="text-xs text-gray-400 border border-gray-200 bg-white px-1 rounded">JSON</span>
                                </div>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Nhập cấu trúc JSON hợp lệ.</p>

                        @elseif($setting->type === 'number' || $setting->type === 'integer')
                            <input type="number" name="{{ $setting->key }}" value="{{ $val }}" class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm py-2.5">
                        
                        @elseif($setting->type === 'color')
                            <div class="flex items-center gap-2">
                                <input type="color" name="{{ $setting->key }}" value="{{ $val }}" class="h-10 w-14 border border-gray-300 p-1 rounded bg-white cursor-pointer shadow-sm">
                                <input type="text" name="{{ $setting->key }}" value="{{ $val }}" class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm py-2.5 uppercase font-mono text-gray-600">
                            </div>

                        @else
                            <!-- Default Text Input -->
                            <input type="text" name="{{ $setting->key }}" value="{{ $val }}" class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm py-2.5">
                        @endif

                        @error($setting->key)
                            <p class="mt-1 text-sm text-red-600 flex items-center">
                                <i class="fa-solid fa-circle-exclamation mr-1"></i> {{ $message }}
                            </p>
                        @enderror
                    </div>
                @endforeach
            </div>

            <!-- Action Buttons -->
            <div class="mt-8 pt-5 border-t border-gray-200 flex items-center justify-end gap-3">
                <a href="{{ route('admin.settings.index') }}" class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                    Hủy bỏ
                </a>
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                    <i class="fa-solid fa-save mr-2"></i> Lưu cấu hình
                </button>
            </div>
        </form>
    </div>
</div>
@endsection