@extends('admin.layouts.app')

@section('title', 'Thêm thương hiệu mới')
@section('header', 'Thêm thương hiệu')

@section('content')
<div class="container px-6 mx-auto">
    <!-- Nút quay lại -->
    <div class="my-6">
        <a href="{{ route('admin.brands.index') }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors shadow-sm">
            <i class="fa-solid fa-arrow-left mr-2"></i> Quay lại danh sách
        </a>
    </div>

    <!-- Form Thêm mới -->
    <div class="p-6 mb-8 bg-white rounded-lg shadow-md border border-gray-200">
        <form action="{{ route('admin.brands.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Cột trái -->
                <div class="space-y-6">
                    <!-- 1. Tên thương hiệu -->
                    <div>
                        <label class="block mb-2 text-sm font-bold text-gray-800">
                            Tên thương hiệu <span class="text-red-500">*</span>
                        </label>
                        <input name="name" type="text" 
                            class="w-full rounded-lg border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 focus:bg-white transition-colors" 
                            placeholder="Ví dụ: Nike, Adidas..." 
                            value="{{ old('name') }}" />
                        @error('name') 
                            <p class="mt-1 text-xs text-red-600 font-medium"><i class="fa-solid fa-circle-exclamation mr-1"></i>{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- 2. Mô tả -->
                    <div>
                        <label class="block mb-2 text-sm font-bold text-gray-800">
                            Mô tả
                        </label>
                        <textarea name="description" 
                            class="w-full rounded-lg border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 focus:bg-white transition-colors" 
                            rows="5" 
                            placeholder="Nhập thông tin mô tả về thương hiệu...">{{ old('description') }}</textarea>
                    </div>
                </div>

                <!-- Cột phải -->
                <div class="space-y-6">
                    <!-- 3. Logo -->
                    <div>
                        <label class="block mb-2 text-sm font-bold text-gray-800">
                            Logo thương hiệu
                        </label>
                        
                        <div class="flex justify-center rounded-lg border border-dashed border-gray-900/25 px-6 py-10 bg-gray-50 hover:bg-gray-100 transition-colors cursor-pointer relative">
                            <div class="text-center">
                                <i class="fa-regular fa-image text-4xl text-gray-300 mb-3"></i>
                                <div class="mt-4 flex text-sm leading-6 text-gray-600 justify-center">
                                    <label for="file-upload" class="relative cursor-pointer rounded-md bg-white font-semibold text-indigo-600 focus-within:outline-none focus-within:ring-2 focus-within:ring-indigo-600 focus-within:ring-offset-2 hover:text-indigo-500">
                                        <span>Tải ảnh lên</span>
                                        <input id="file-upload" name="logo" type="file" class="sr-only" accept="image/*" onchange="previewImage(event)">
                                    </label>
                                    <p class="pl-1">hoặc kéo thả vào đây</p>
                                </div>
                                <p class="text-xs leading-5 text-gray-600">PNG, JPG, GIF tối đa 2MB</p>
                            </div>
                            <!-- Preview Image Container -->
                            <img id="preview" class="hidden absolute inset-0 w-full h-full object-contain p-2 bg-white rounded-lg" />
                        </div>

                        @error('logo') 
                            <p class="mt-1 text-xs text-red-600 font-medium"><i class="fa-solid fa-circle-exclamation mr-1"></i>{{ $message }}</p></p> 
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Footer Form -->
            <div class="flex items-center justify-end mt-8 pt-6 border-t border-gray-200 gap-4">
                <a href="{{ route('admin.brands.index') }}" class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-4 focus:ring-gray-100">
                    Hủy bỏ
                </a>
                <button type="submit" class="px-5 py-2.5 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-4 focus:ring-indigo-300 shadow-md transform active:scale-95 transition-transform">
                    <i class="fa-solid fa-floppy-disk mr-2"></i> Lưu thương hiệu
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Script xem trước ảnh đơn giản
    function previewImage(event) {
        const reader = new FileReader();
        reader.onload = function(){
            const output = document.getElementById('preview');
            output.src = reader.result;
            output.classList.remove('hidden');
        };
        if(event.target.files[0]) {
            reader.readAsDataURL(event.target.files[0]);
        }
    }
</script>
@endsection