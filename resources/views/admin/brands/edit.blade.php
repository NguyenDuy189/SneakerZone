@extends('admin.layouts.app')

@section('title', 'Cập nhật thương hiệu')
@section('header', 'Cập nhật thương hiệu')

@section('content')
<div class="container px-6 mx-auto">
    <!-- Nút quay lại -->
    <div class="my-6">
        <a href="{{ route('admin.brands.index') }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors shadow-sm">
            <i class="fa-solid fa-arrow-left mr-2"></i> Quay lại danh sách
        </a>
    </div>

    <!-- Form Chỉnh sửa -->
    <div class="p-6 mb-8 bg-white rounded-lg shadow-md border border-gray-200">
        <form action="{{ route('admin.brands.update', $brand->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
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
                            value="{{ old('name', $brand->name) }}" required />
                        @error('name') 
                            <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p> 
                        @enderror
                    </div>

                    <!-- 2. Mô tả -->
                    <div>
                        <label class="block mb-2 text-sm font-bold text-gray-800">
                            Mô tả
                        </label>
                        <textarea name="description" 
                            class="w-full rounded-lg border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 focus:bg-white transition-colors" 
                            rows="5">{{ old('description', $brand->description) }}</textarea>
                    </div>
                </div>

                <!-- Cột phải -->
                <div class="space-y-6">
                    <!-- 3. Logo -->
                    <div>
                        <label class="block mb-2 text-sm font-bold text-gray-800">
                            Logo thương hiệu
                        </label>
                        
                        <div class="flex flex-col items-center justify-center rounded-lg border border-dashed border-gray-900/25 px-6 py-10 bg-gray-50 hover:bg-gray-100 transition-colors cursor-pointer relative">
                            <!-- Ảnh hiện tại hoặc Preview -->
                            <div class="mb-4">
                                <img id="preview" 
                                     src="{{ $brand->logo_url ? asset('storage/' . $brand->logo_url) : 'https://placehold.co/150x150?text=No+Logo' }}" 
                                     class="h-32 w-auto object-contain rounded-md border bg-white p-2 shadow-sm" />
                            </div>

                            <div class="text-center">
                                <div class="flex text-sm leading-6 text-gray-600 justify-center">
                                    <label for="file-upload" class="relative cursor-pointer rounded-md bg-white font-semibold text-indigo-600 focus-within:outline-none focus-within:ring-2 focus-within:ring-indigo-600 focus-within:ring-offset-2 hover:text-indigo-500 px-2">
                                        <span>Thay đổi ảnh</span>
                                        <input id="file-upload" name="logo" type="file" class="sr-only" accept="image/*" onchange="previewImage(event)">
                                    </label>
                                </div>
                                <p class="text-xs leading-5 text-gray-500 mt-1">Để trống nếu không muốn thay đổi</p>
                            </div>
                        </div>

                        @error('logo') 
                            <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p> 
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
                    <i class="fa-solid fa-pen-to-square mr-2"></i> Cập nhật
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function previewImage(event) {
        const reader = new FileReader();
        reader.onload = function(){
            const output = document.getElementById('preview');
            output.src = reader.result;
        };
        if(event.target.files[0]) {
            reader.readAsDataURL(event.target.files[0]);
        }
    }
</script>
@endsection