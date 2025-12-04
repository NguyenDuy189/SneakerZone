@extends('admin.layouts.app')
@section('title', 'Cấu hình ' . $attribute->name)
@section('header', 'Cấu hình: ' . $attribute->name)

@section('content')
<div class="container px-6 mx-auto">
    <!-- Nút quay lại -->
    <div class="mb-6">
        <a href="{{ route('admin.attributes.index') }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors shadow-sm">
            <i class="fa-solid fa-arrow-left mr-2"></i> Quay lại danh sách
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        
        <!-- CỘT TRÁI: FORM THÊM GIÁ TRỊ -->
        <div class="md:col-span-1">
            <div class="bg-white p-6 rounded-lg shadow-md border border-gray-200 sticky top-20">
                <h3 class="text-lg font-bold text-gray-700 mb-4 border-b pb-2">
                    Thêm {{ $attribute->name }} mới
                </h3>
                
                <form action="{{ route('admin.attributes.values.store', $attribute->id) }}" method="POST">
                    @csrf
                    
                    <!-- 1. Tên hiển thị (Bắt buộc) -->
                    <div class="mb-4">
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            Tên giá trị <span class="text-red-500">*</span>
                        </label>
                        <!-- Thêm ID để JS can thiệp -->
                        <input id="colorNameInput" name="value" type="text" 
                            class="w-full rounded-lg border-gray-300 bg-gray-50 p-2.5 text-sm focus:ring-indigo-500 focus:border-indigo-500 @error('value') border-red-500 @enderror" 
                            placeholder="{{ $attribute->type == 'color' ? 'Chọn màu bên dưới để tự điền tên...' : 'VD: 39, 40, XL...' }}" 
                            required>
                        @error('value')
                            <p class="mt-1 text-xs text-red-600 font-semibold"><i class="fa-solid fa-circle-exclamation mr-1"></i>{{ $message }}</p></p>
                        @enderror
                    </div>

                    <!-- 2. Logic hiển thị Color Picker -->
                    @if($attribute->type == 'color')
                        <div class="mb-6 p-3 bg-gray-50 rounded-lg border border-gray-200">
                            <label class="block text-sm font-bold text-gray-700 mb-2">
                                Chọn màu <span class="text-red-500">*</span>
                            </label>
                            <div class="flex items-center gap-3">
                                <!-- Thêm ID và sự kiện oninput -->
                                <input id="colorPickerInput" name="meta_value" type="color" 
                                    class="h-10 w-16 rounded cursor-pointer border border-gray-300 p-0.5" 
                                    value="#000000"
                                    oninput="autoNamethisColor(this.value)">
                                <span class="text-xs text-gray-500">Chọn màu để tự động điền tên Tiếng Việt.</span>
                            </div>
                        </div>
                    @else
                        <!-- Nếu là Text thì ẩn input color đi -->
                        <input type="hidden" name="meta_value" value="">
                    @endif

                    <button type="submit" class="w-full px-4 py-2.5 text-white bg-green-600 rounded-lg hover:bg-green-700 font-medium shadow-sm transition-transform active:scale-95">
                        <i class="fa-solid fa-plus mr-2"></i> Thêm giá trị
                    </button>
                </form>
            </div>
        </div>

        <!-- CỘT PHẢI: DANH SÁCH GIÁ TRỊ -->
        <div class="md:col-span-2">
            <div class="bg-white rounded-lg shadow-md border border-gray-200">
                <div class="p-4 border-b bg-gray-50 flex justify-between items-center">
                    <span class="font-bold text-gray-700">Danh sách giá trị hiện có ({{ $attribute->values->count() }})</span>
                    @if($attribute->type == 'color')
                        <span class="text-xs bg-purple-100 text-purple-700 px-2 py-1 rounded">Chế độ xem Màu sắc</span>
                    @endif
                </div>
                
                <div class="p-4 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                    @foreach($attribute->values as $val)
                    <div class="relative group flex flex-col items-center justify-center p-4 border rounded-lg bg-white hover:border-indigo-400 hover:shadow-md transition-all">
                        
                        <!-- Hiển thị nội dung -->
                        @if($attribute->type == 'color')
                            <!-- Hiển thị màu -->
                            <span class="w-8 h-8 rounded-full border border-gray-200 shadow-sm mb-2" 
                                  style="background-color: {{ $val->meta_value }};" 
                                  title="{{ $val->meta_value }}"></span>
                            <span class="font-bold text-gray-800 text-sm">{{ $val->value }}</span>
                            <span class="text-xs text-gray-400 uppercase">{{ $val->meta_value }}</span>
                        @else
                            <!-- Hiển thị Text/Số -->
                            <span class="font-bold text-gray-800 text-lg">{{ $val->value }}</span>
                        @endif

                        <!-- Nút Xóa (Hiện khi hover) -->
                        <form action="{{ route('admin.attributes.values.destroy', $val->id) }}" method="POST" class="absolute top-1 right-1 opacity-0 group-hover:opacity-100 transition-opacity">
                            @csrf @method('DELETE')
                            <button class="w-6 h-6 flex items-center justify-center rounded-full bg-red-100 text-red-500 hover:bg-red-500 hover:text-white transition-colors" title="Xóa giá trị này">
                                <i class="fa-solid fa-xmark text-xs"></i>
                            </button>
                        </form>
                    </div>
                    @endforeach

                    @if($attribute->values->isEmpty())
                        <div class="col-span-full py-8 flex flex-col items-center justify-center text-gray-400 border-2 border-dashed border-gray-200 rounded-lg">
                            <i class="fa-regular fa-folder-open text-4xl mb-2"></i>
                            <p class="text-sm">Chưa có giá trị nào.</p>
                            <p class="text-xs">Hãy thêm giá trị mới ở cột bên trái.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- SCRIPT TỰ ĐỘNG ĐẶT TÊN MÀU -->
@if($attribute->type == 'color')
<script>
    // Bảng màu cơ bản Tiếng Việt
    const basicColors = [
        { hex: '#000000', name: 'Đen' },
        { hex: '#FFFFFF', name: 'Trắng' },
        { hex: '#FF0000', name: 'Đỏ' },
        { hex: '#00FF00', name: 'Xanh Lá' },
        { hex: '#0000FF', name: 'Xanh Dương' },
        { hex: '#FFFF00', name: 'Vàng' },
        { hex: '#00FFFF', name: 'Xanh Cyan' },
        { hex: '#FF00FF', name: 'Hồng Cánh Sen' },
        { hex: '#C0C0C0', name: 'Bạc' },
        { hex: '#808080', name: 'Xám' },
        { hex: '#800000', name: 'Đỏ Đậm' },
        { hex: '#808000', name: 'Oliu' },
        { hex: '#008000', name: 'Xanh Lá Đậm' },
        { hex: '#800080', name: 'Tím' },
        { hex: '#008080', name: 'Xanh Teal' },
        { hex: '#000080', name: 'Xanh Navy' },
        { hex: '#FFA500', name: 'Cam' },
        { hex: '#A52A2A', name: 'Nâu' },
        { hex: '#FFC0CB', name: 'Hồng' },
        { hex: '#F5F5DC', name: 'Kem' },
        { hex: '#FFD700', name: 'Vàng Kim' }
    ];

    // Hàm chuyển Hex sang RGB
    function hexToRgb(hex) {
        var result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
        return result ? {
            r: parseInt(result[1], 16),
            g: parseInt(result[2], 16),
            b: parseInt(result[3], 16)
        } : null;
    }

    // Hàm tìm màu gần nhất
    function autoNamethisColor(hex) {
        const inputRGB = hexToRgb(hex);
        let minDistance = Infinity;
        let closestColorName = '';

        basicColors.forEach(color => {
            const targetRGB = hexToRgb(color.hex);
            // Tính khoảng cách màu theo công thức Euclid
            const distance = Math.sqrt(
                Math.pow(inputRGB.r - targetRGB.r, 2) +
                Math.pow(inputRGB.g - targetRGB.g, 2) +
                Math.pow(inputRGB.b - targetRGB.b, 2)
            );

            if (distance < minDistance) {
                minDistance = distance;
                closestColorName = color.name;
            }
        });

        // Cập nhật ô input tên
        document.getElementById('colorNameInput').value = closestColorName;
    }
</script>
@endif

@endsection