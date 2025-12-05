@extends('admin.layouts.app')
@section('title', 'Danh sách cấu hình')

@section('content')
<div class="container px-6 mx-auto grid pb-12">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center my-6 gap-4">
        <div class="flex items-center gap-4">
            <!-- Nút Quay lại -->
            <a href="{{ route('admin.dashboard') }}" class="group w-12 h-12 flex items-center justify-center bg-white border border-gray-200 rounded-xl shadow-sm hover:shadow-md hover:bg-gray-50 hover:border-indigo-300 transition-all duration-200">
                <i class="fa-solid fa-arrow-left text-xl text-gray-500 group-hover:text-indigo-600"></i>
            </a>

            <!-- Tiêu đề -->
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Danh sách cấu hình</h2>
                <p class="text-sm text-gray-500 mt-1">Tổng quan các thiết lập hiện tại của hệ thống.</p>
            </div>
        </div>

        <!-- Action Button -->
        <div class="w-full md:w-auto">
            <a href="{{ route('admin.settings.edit') }}" class="inline-flex items-center justify-center w-full md:w-auto px-5 py-2.5 bg-indigo-600 text-white rounded-lg shadow-sm hover:bg-indigo-700 focus:ring-4 focus:ring-indigo-300 transition-all transform hover:-translate-y-0.5">
                <i class="fa-solid fa-pen-to-square mr-2"></i> Chỉnh sửa tất cả
            </a>
        </div>
    </div>

    <!-- Flash Messages -->
    @if(session('success'))
    <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-xl flex items-start shadow-sm">
        <i class="fa-solid fa-circle-check text-green-500 mt-0.5 mr-3 text-lg"></i>
        <div>
            <h3 class="text-sm font-medium text-green-800">Thành công!</h3>
            <p class="text-sm text-green-700 mt-1">{{ session('success') }}</p>
        </div>
    </div>
    @endif

    <!-- Main Content: Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tên cấu hình (Label)</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Mã (Key)</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Giá trị hiện tại</th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Loại</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @php
                        // Tái sử dụng bộ từ điển để hiển thị Label đẹp ở trang index
                        $vnLabels = [
                            'site_name' => 'Tên Website',
                            'site_title' => 'Tiêu đề trang',
                            'site_description' => 'Mô tả SEO',
                            'site_keyword' => 'Từ khóa SEO',
                            'site_logo' => 'Logo Website',
                            'site_favicon' => 'Favicon',
                            'email' => 'Email Liên hệ',
                            'admin_email' => 'Email Quản trị',
                            'hotline' => 'Hotline',
                            'support_phone' => 'SĐT Hỗ trợ',
                            'address' => 'Địa chỉ cửa hàng',
                            'company_address' => 'Địa chỉ công ty',
                            'facebook_url' => 'Facebook URL',
                            'facebook_link' => 'Facebook URL',
                            'youtube_link' => 'Youtube URL',
                            'twitter_link' => 'Twitter URL',
                            'instagram_link' => 'Instagram URL',
                            'zalo_phone' => 'Zalo OA',
                            'freeship_threshold' => 'Mức Freeship',
                            'shipping_fee' => 'Phí ship',
                            'tax_rate' => 'Thuế (%)',
                            'currency_symbol' => 'Tiền tệ',
                            'maintenance_mode' => 'Bảo trì',
                            'google_analytics_id' => 'GA ID',
                            'facebook_pixel_id' => 'Pixel ID',
                        ];
                    @endphp

                    @foreach($settings as $setting)
                        @php
                            $displayLabel = $setting->label ?? ($vnLabels[$setting->key] ?? ucfirst(str_replace('_', ' ', $setting->key)));
                        @endphp
                    <tr class="hover:bg-gray-50 transition-colors">
                        <!-- Label -->
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ $displayLabel }}
                        </td>
                        
                        <!-- Key -->
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-md bg-gray-100 text-gray-600 font-mono border border-gray-200">
                                {{ $setting->key }}
                            </span>
                        </td>

                        <!-- Value -->
                        <td class="px-6 py-4 text-sm text-gray-500">
                            @if($setting->type === 'boolean')
                                @if($setting->value)
                                    <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 border border-green-200">
                                        <i class="fa-solid fa-check mr-1 mt-0.5"></i> Đang Bật
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 border border-red-200">
                                        <i class="fa-solid fa-xmark mr-1 mt-0.5"></i> Đang Tắt
                                    </span>
                                @endif

                            @elseif($setting->type === 'json')
                                <div class="relative group">
                                    <pre class="bg-gray-800 text-gray-200 p-2 rounded-lg text-xs font-mono max-w-xs overflow-hidden truncate cursor-help border border-gray-700" title="Click edit to view full JSON">JSON Object ({{ count(json_decode($setting->value, true) ?? []) }} items)...</pre>
                                </div>

                            @elseif($setting->type === 'color')
                                <div class="flex items-center gap-2">
                                    <span class="h-6 w-10 rounded border border-gray-300 shadow-sm" style="background-color: {{ $setting->value }}"></span>
                                    <span class="font-mono text-xs uppercase">{{ $setting->value }}</span>
                                </div>

                            @else
                                <div class="max-w-xs truncate" title="{{ $setting->value }}">
                                    {{ Str::limit($setting->value, 50) ?: '(Trống)' }}
                                </div>
                            @endif
                        </td>

                        <!-- Type -->
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            @php
                                $typeClasses = [
                                    'string' => 'bg-blue-50 text-blue-700 border-blue-100',
                                    'boolean' => 'bg-purple-50 text-purple-700 border-purple-100',
                                    'number' => 'bg-yellow-50 text-yellow-700 border-yellow-100',
                                    'integer' => 'bg-yellow-50 text-yellow-700 border-yellow-100',
                                    'json' => 'bg-gray-100 text-gray-700 border-gray-200',
                                    'color' => 'bg-pink-50 text-pink-700 border-pink-100',
                                ];
                                $class = $typeClasses[$setting->type] ?? 'bg-gray-50 text-gray-600 border-gray-200';
                            @endphp
                            <span class="px-2 py-1 text-[10px] font-bold uppercase tracking-wider rounded border {{ $class }}">
                                {{ $setting->type }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <!-- Footer / Pagination if needed -->
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 text-xs text-gray-500 flex justify-between items-center">
            <span>Hiển thị {{ count($settings) }} tham số cấu hình</span>
            <span>Hệ thống quản trị v1.0</span>
        </div>
    </div>
</div>
@endsection