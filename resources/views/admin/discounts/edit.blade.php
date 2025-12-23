@extends('admin.layouts.app')
@section('title', 'Chỉnh sửa mã giảm giá')

@section('content')
<div class="container px-6 mx-auto mb-20 fade-in">
    
    {{-- HEADER --}}
    <div class="flex flex-col md:flex-row justify-between items-center my-6 gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.discounts.index') }}" class="w-10 h-10 flex items-center justify-center rounded-xl bg-white border border-slate-200 text-slate-500 hover:bg-slate-50 hover:text-indigo-600 transition-all shadow-sm">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Chỉnh sửa mã giảm giá</h2>
                <p class="text-sm text-slate-500 mt-1">Cập nhật thông tin chương trình khuyến mãi: <span class="font-bold text-indigo-600">{{ $discount->code }}</span></p>
            </div>
        </div>
    </div>

    {{-- HIỂN THỊ LỖI --}}
    @if($errors->any())
    <div class="p-4 mb-6 bg-rose-50 text-rose-700 border border-rose-200 rounded-xl shadow-sm flex items-start gap-3">
        <i class="fa-solid fa-circle-exclamation mt-0.5"></i>
        <ul class="list-disc pl-5 text-sm">
            @foreach($errors->all() as $error) <li>{{ $error }}</li> @endforeach
        </ul>
    </div>
    @endif

    {{-- FORM --}}
    <form action="{{ route('admin.discounts.update', $discount->id) }}" method="POST" id="discountForm">
        @csrf
        @method('PUT')
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            {{-- CỘT TRÁI: THÔNG TIN CHÍNH --}}
            <div class="lg:col-span-2 space-y-6">
                
                {{-- Card 1: Mã & Mô tả --}}
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
                    <h3 class="font-bold text-slate-800 mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-tag text-indigo-500"></i> Thông tin chung
                    </h3>
                    
                    <div class="grid grid-cols-1 gap-5">
                        {{-- Tên chương trình --}}
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Tên chương trình / Mô tả ngắn</label>
                            <input type="text" name="name" value="{{ old('name', $discount->name) }}" 
                                class="w-full px-4 py-3 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 shadow-sm"
                                placeholder="VD: Khuyến mãi tết...">
                        </div>

                        {{-- Mã Code --}}
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">
                                Mã giảm giá <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative flex items-center">
                                <input type="text" name="code" id="code" value="{{ old('code', $discount->code) }}" 
                                    class="uppercase pl-4 pr-32 py-3 w-full border border-slate-200 rounded-xl text-sm font-bold text-slate-700 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 shadow-sm bg-slate-50"
                                    placeholder="VD: SALE50...">
                                
                                <button type="button" onclick="generateCode()" class="absolute right-2 text-xs bg-white hover:bg-slate-100 text-slate-600 font-bold py-1.5 px-3 rounded-lg transition-colors border border-slate-200 shadow-sm">
                                    <i class="fa-solid fa-shuffle mr-1"></i> Random
                                </button>
                            </div>
                            <p class="text-xs text-slate-400 mt-1">Khách hàng sẽ nhập mã này khi thanh toán.</p>
                        </div>
                    </div>
                </div>

                {{-- Card 2: Cấu hình giá trị --}}
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
                    <h3 class="font-bold text-slate-800 mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-calculator text-emerald-500"></i> Giá trị & Điều kiện
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        
                        {{-- 1. Loại giảm giá --}}
                        <div class="md:col-span-2">
                            <label class="block text-sm font-bold text-slate-700 mb-2">Loại khuyến mãi</label>
                            <div class="grid grid-cols-2 gap-4">
                                <label class="cursor-pointer">
                                    <input type="radio" name="type" value="fixed" class="peer sr-only" onchange="toggleType()" {{ old('type', $discount->type) == 'fixed' ? 'checked' : '' }}>
                                    <div class="p-4 rounded-xl border border-slate-200 hover:border-indigo-500 peer-checked:border-indigo-600 peer-checked:bg-indigo-50 transition-all text-center">
                                        <div class="font-bold text-slate-700 peer-checked:text-indigo-700">Tiền mặt (VNĐ)</div>
                                        <div class="text-xs text-slate-400 mt-1">Giảm số tiền cố định</div>
                                    </div>
                                </label>
                                <label class="cursor-pointer">
                                    {{-- Lưu ý: Kiểm tra DB xem lưu là 'percent' hay 'percentage' --}}
                                    <input type="radio" name="type" value="percentage" class="peer sr-only" onchange="toggleType()" {{ old('type', $discount->type) == 'percent' ? 'checked' : '' }}>
                                    <div class="p-4 rounded-xl border border-slate-200 hover:border-indigo-500 peer-checked:border-indigo-600 peer-checked:bg-indigo-50 transition-all text-center">
                                        <div class="font-bold text-slate-700 peer-checked:text-indigo-700">Phần trăm (%)</div>
                                        <div class="text-xs text-slate-400 mt-1">Giảm theo % đơn hàng</div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        {{-- 2. Giá trị giảm --}}
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Giá trị giảm</label>
                            <div class="relative">
                                <input type="number" name="value" id="valueInput" value="{{ old('value', $discount->value) }}" 
                                    class="pl-4 pr-12 py-3 w-full border border-slate-200 rounded-xl text-sm font-bold text-slate-700 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 shadow-sm"
                                    placeholder="0">
                                <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-slate-500 font-bold" id="valueSuffix">
                                    đ
                                </div>
                            </div>
                        </div>

                        {{-- 3. Giảm tối đa (Chỉ hiện khi chọn %) --}}
                        <div id="maxDiscountGroup" class="hidden transition-all duration-300">
                            <label class="block text-sm font-bold text-slate-700 mb-2">
                                Giảm tối đa <span class="text-slate-400 font-normal text-xs">(Không bắt buộc)</span>
                            </label>
                            <div class="relative">
                                <input type="number" name="max_discount_value" value="{{ old('max_discount_value', $discount->max_discount_value) }}" 
                                    class="pl-4 pr-12 py-3 w-full border border-slate-200 rounded-xl text-sm font-bold text-slate-700 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 shadow-sm"
                                    placeholder="Không giới hạn">
                                <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-slate-500 font-bold">
                                    đ
                                </div>
                            </div>
                            <p class="text-xs text-slate-400 mt-1">VD: Giảm 10% nhưng tối đa 50k.</p>
                        </div>

                        {{-- 4. Đơn tối thiểu --}}
                        <div class="md:col-span-2">
                            <label class="block text-sm font-bold text-slate-700 mb-2">Giá trị đơn hàng tối thiểu</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                    <i class="fa-solid fa-cart-shopping"></i>
                                </div>
                                <input type="number" name="min_order_value" value="{{ old('min_order_value', $discount->min_order_value) }}" 
                                    class="pl-10 pr-4 py-3 w-full border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 shadow-sm"
                                    placeholder="0">
                            </div>
                            <p class="text-xs text-slate-400 mt-1">Nhập 0 nếu áp dụng cho mọi đơn hàng.</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- CỘT PHẢI: CÀI ĐẶT & THỜI GIAN --}}
            <div class="space-y-6">
                
                {{-- Card 3: Thời gian & Giới hạn --}}
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
                    <h3 class="font-bold text-slate-800 mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-clock text-blue-500"></i> Hiệu lực
                    </h3>
                    
                    <div class="space-y-4">
                        {{-- Ngày bắt đầu --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Bắt đầu</label>
                            {{-- Sử dụng format Y-m-d\TH:i để tương thích với input datetime-local --}}
                            <input type="datetime-local" name="start_date" 
                                value="{{ old('start_date', $discount->start_date ? \Carbon\Carbon::parse($discount->start_date)->format('Y-m-d\TH:i') : '') }}" 
                                class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 shadow-sm text-slate-600">
                        </div>

                        {{-- Ngày kết thúc --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Kết thúc (Bỏ trống = Vô hạn)</label>
                            <input type="datetime-local" name="end_date" 
                                value="{{ old('end_date', $discount->end_date ? \Carbon\Carbon::parse($discount->end_date)->format('Y-m-d\TH:i') : '') }}" 
                                class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 shadow-sm text-slate-600">
                        </div>

                        <hr class="border-slate-100 my-4">

                        {{-- Giới hạn lượt dùng --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Tổng lượt dùng tối đa</label>
                            <input type="number" name="usage_limit" value="{{ old('usage_limit', $discount->usage_limit) }}" 
                                class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 shadow-sm text-slate-600"
                                placeholder="VD: 100">
                            <p class="text-[10px] text-slate-400 mt-1">Để trống nếu không giới hạn số lượng.</p>
                        </div>
                    </div>
                </div>

                {{-- Card 4: Action --}}
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
                    <div class="flex items-center justify-between mb-4">
                        <span class="font-bold text-slate-700">Kích hoạt mã</span>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" class="sr-only peer" {{ old('is_active', $discount->is_active) ? 'checked' : '' }}>
                            <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                        </label>
                    </div>
                    
                    <button type="submit" class="w-full py-3 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl shadow-lg shadow-slate-900/20 transition-all active:scale-95 flex items-center justify-center gap-2">
                        <i class="fa-solid fa-floppy-disk"></i> Cập nhật
                    </button>

                    <a href="{{ route('admin.discounts.index') }}" class="block w-full text-center mt-3 py-3 bg-white border border-slate-200 text-slate-600 font-bold rounded-xl hover:bg-slate-50 transition-colors">
                        Hủy bỏ
                    </a>
                </div>

            </div>
        </div>
    </form>
</div>

{{-- SCRIPT --}}
<script>
    // 1. Random Code
    function generateCode() {
        const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        let result = '';
        for (let i = 0; i < 8; i++) {
            result += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        document.getElementById('code').value = result;
    }

    // 2. Toggle Type (Ẩn/Hiện Giảm tối đa)
    function toggleType() {
        const type = document.querySelector('input[name="type"]:checked').value;
        const suffix = document.getElementById('valueSuffix');
        const maxDiscountGroup = document.getElementById('maxDiscountGroup');
        
        // Kiểm tra giá trị là 'percent' hoặc 'percentage' tùy DB
        if (type === 'percent' || type === 'percentage') {
            suffix.innerText = '%';
            maxDiscountGroup.classList.remove('hidden');
        } else {
            suffix.innerText = 'đ';
            maxDiscountGroup.classList.add('hidden');
        }
    }

    // Chạy khi load trang để set trạng thái đúng theo dữ liệu cũ
    document.addEventListener('DOMContentLoaded', function() {
        toggleType();
    });
</script>
@endsection