@extends('admin.layouts.app')

@section('title', 'Thêm danh mục')
@section('header', 'Thêm danh mục')

@section('content')
<div class="container px-6 mx-auto">
    <div class="my-6">
        <a href="{{ route('admin.categories.index') }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors shadow-sm">
            <i class="fa-solid fa-arrow-left mr-2"></i> Quay lại
        </a>
    </div>

    <div class="p-6 mb-8 bg-white rounded-lg shadow-md border border-gray-200">
        <form action="{{ route('admin.categories.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- CỘT TRÁI -->
                <div class="space-y-6">
                    <!-- Tên danh mục -->
                    <div>
                        <label class="block mb-2 text-sm font-bold text-gray-800">Tên danh mục <span class="text-red-500">*</span></label>
                        <input name="name" type="text" class="w-full rounded-lg border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 focus:bg-white" placeholder="VD: Giày nam, Áo thun..." value="{{ old('name') }}" />
                        @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <!-- Chọn Danh mục cha -->
                    <div>
                        <label class="block mb-2 text-sm font-bold text-gray-800">Danh mục cha</label>
                        <select name="parent_id" class="w-full rounded-lg border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 focus:bg-white cursor-pointer">
                            <option value="">-- Là danh mục gốc (Level 0) --</option>
                            @foreach($parents as $parent)
                                <option value="{{ $parent->id }}" {{ old('parent_id') == $parent->id ? 'selected' : '' }}>
                                    {{-- Hiển thị tên có visual level --}}
                                    {{ str_repeat('— ', $parent->level) }} {{ $parent->name }}
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-gray-500">Nếu chọn cha, danh mục này sẽ là con cấp tiếp theo.</p>
                    </div>

                    <!-- Checkbox Hiển thị -->
                    <div class="flex items-center pt-2">
                        <input id="is_visible" name="is_visible" type="checkbox" class="w-4 h-4 text-indigo-600 bg-gray-100 border-gray-300 rounded focus:ring-indigo-500 focus:ring-2 cursor-pointer" checked>
                        <label for="is_visible" class="ml-2 text-sm font-medium text-gray-900 cursor-pointer">Hiển thị danh mục này trên web</label>
                    </div>
                </div>

                <!-- CỘT PHẢI -->
                <div class="space-y-6">
                    <!-- Upload Ảnh -->
                    <div>
                        <label class="block mb-2 text-sm font-bold text-gray-800">Ảnh đại diện danh mục</label>
                        <div class="flex flex-col items-center justify-center rounded-lg border border-dashed border-gray-900/25 px-6 py-10 bg-gray-50 hover:bg-gray-100 transition-colors cursor-pointer relative">
                            <div class="text-center">
                                <i class="fa-regular fa-image text-4xl text-gray-300 mb-3"></i>
                                <div class="mt-4 flex text-sm leading-6 text-gray-600 justify-center">
                                    <label for="file-upload" class="relative cursor-pointer rounded-md bg-white font-semibold text-indigo-600 focus-within:outline-none focus-within:ring-2 focus-within:ring-indigo-600 focus-within:ring-offset-2 hover:text-indigo-500 px-2">
                                        <span>Tải ảnh lên</span>
                                        <input id="file-upload" name="image" type="file" class="sr-only" accept="image/*" onchange="previewImage(event)">
                                    </label>
                                </div>
                                <p class="text-xs leading-5 text-gray-600">PNG, JPG tối đa 2MB</p>
                            </div>
                            <img id="preview" class="hidden absolute inset-0 w-full h-full object-contain p-2 bg-white rounded-lg" />
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end mt-8 pt-6 border-t border-gray-200 gap-4">
                <a href="{{ route('admin.categories.index') }}" class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">Hủy bỏ</a>
                <button type="submit" class="px-5 py-2.5 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 shadow-md">Lưu danh mục</button>
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
            output.classList.remove('hidden');
        };
        if(event.target.files[0]) reader.readAsDataURL(event.target.files[0]);
    }
</script>
@endsection