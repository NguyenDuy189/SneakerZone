@extends('admin.layouts.app')

@section('title', 'Thêm sản phẩm mới')
@section('header', 'Tạo sản phẩm')

@section('content')
<div class="container px-6 mx-auto mb-20">
    <!-- Breadcrumb -->
    <div class="mb-6">
        <a href="{{ route('admin.products.index') }}" class="inline-flex items-center text-sm font-medium text-slate-500 hover:text-indigo-600 transition-colors">
            <i class="fa-solid fa-arrow-left mr-2"></i> Quay lại danh sách
        </a>
    </div>

    <!-- Alert Errors -->
    @if ($errors->any())
        <div class="mb-6 p-4 bg-rose-50 border-l-4 border-rose-500 text-rose-800 rounded-lg shadow-sm">
            <div class="flex items-center mb-1">
                <i class="fa-solid fa-circle-exclamation mr-2"></i>
                <p class="font-bold">Vui lòng kiểm tra lại các lỗi sau:</p>
            </div>
            <ul class="list-disc list-inside text-sm pl-6">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        
        <div class="flex flex-col lg:flex-row gap-8">
            
            <!-- CỘT CHÍNH (TRÁI) - Chiếm 2/3 -->
            <div class="w-full lg:w-2/3 flex flex-col gap-8">
                
                <!-- 1. Thông tin cơ bản -->
                <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
                    <h3 class="text-lg font-bold text-slate-800 mb-5 pb-3 border-b border-slate-100 flex items-center">
                        <i class="fa-solid fa-circle-info mr-2 text-indigo-500"></i> Thông tin chung
                    </h3>
                    
                    <div class="space-y-6">
                        <!-- Tên SP -->
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Tên sản phẩm <span class="text-rose-500">*</span></label>
                            <input name="name" type="text" 
                                class="w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-slate-800 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 shadow-sm transition-all placeholder-slate-400" 
                                placeholder="Ví dụ: Giày Nike Air Jordan 1 Low..." 
                                value="{{ old('name') }}" required>
                        </div>

                        <!-- SKU & Giá -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Mã SKU <span class="font-normal text-slate-400 text-xs ml-1">(Để trống tự sinh mã)</span></label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fa-solid fa-barcode text-slate-400"></i>
                                    </div>
                                    <input name="sku_code" type="text" 
                                        class="w-full rounded-lg border border-slate-300 bg-slate-50 pl-10 pr-4 py-2.5 font-mono text-slate-600 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 shadow-sm" 
                                        placeholder="SP-0001" 
                                        value="{{ old('sku_code') }}">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Giá bán hiển thị (Min) <span class="text-rose-500">*</span></label>
                                <div class="relative">
                                    <input name="price_min" type="number" 
                                        class="w-full rounded-lg border border-slate-300 bg-white pl-4 pr-12 py-2.5 font-bold text-slate-700 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 shadow-sm" 
                                        placeholder="0" 
                                        value="{{ old('price_min') }}" required min="0">
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none text-slate-500 font-bold bg-slate-100 border-l border-slate-300 rounded-r-lg px-3">VNĐ</div>
                                </div>
                            </div>
                        </div>

                        <!-- Mô tả ngắn -->
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Mô tả ngắn</label>
                            <textarea name="short_description" rows="3" 
                                class="w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 shadow-sm placeholder-slate-400" 
                                placeholder="Tóm tắt tính năng nổi bật của sản phẩm...">{{ old('short_description') }}</textarea>
                        </div>

                        <!-- Mô tả chi tiết (CKEditor) -->
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Mô tả chi tiết</label>
                            <div class="border border-slate-300 rounded-lg overflow-hidden shadow-sm">
                                <textarea name="description" id="editor">{{ old('description') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. Quản lý Biến thể (Placeholder - Bị khóa) -->
                <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 relative overflow-hidden group">
                    <!-- Overlay -->
                    <div class="absolute inset-0 bg-slate-50/90 backdrop-blur-[2px] z-10 flex flex-col items-center justify-center text-center p-6 cursor-not-allowed transition-all">
                        <div class="bg-white p-6 rounded-xl shadow-lg border border-slate-200 max-w-sm transform group-hover:scale-105 transition-transform duration-300">
                            <div class="w-14 h-14 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center mx-auto mb-4 shadow-inner">
                                <i class="fa-solid fa-lock text-2xl"></i>
                            </div>
                            <h4 class="font-bold text-slate-800 text-lg mb-2">Thêm Biến thể (Size/Màu)</h4>
                            <p class="text-sm text-slate-500 mb-5 px-2 leading-relaxed">Bạn cần lưu thông tin sản phẩm này trước, sau đó hệ thống sẽ mở khóa tính năng thêm biến thể.</p>
                            <button type="submit" class="w-full text-sm font-bold text-white bg-indigo-600 px-6 py-3 rounded-lg hover:bg-indigo-700 transition-colors shadow-md hover:shadow-lg flex items-center justify-center">
                                <i class="fa-solid fa-floppy-disk mr-2"></i> Lưu & Tiếp tục
                            </button>
                        </div>
                    </div>

                    <!-- Dummy UI -->
                    <div class="opacity-20 select-none filter blur-[1px]">
                        <h3 class="text-lg font-bold text-slate-800 mb-5 pb-3 border-b border-slate-100 flex items-center">
                            <i class="fa-solid fa-list-ul mr-2 text-indigo-500"></i> Các phiên bản
                        </h3>
                        <div class="border-2 border-dashed border-slate-300 rounded-xl p-10 text-center bg-slate-50">
                            <p class="font-medium text-slate-400 text-lg">Khu vực quản lý Size, Màu sắc và Tồn kho</p>
                        </div>
                    </div>
                </div>

                <!-- 3. Quản lý Hình ảnh -->
                <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
                    <h3 class="text-lg font-bold text-slate-800 mb-5 pb-3 border-b border-slate-100 flex items-center">
                        <i class="fa-solid fa-images mr-2 text-indigo-500"></i> Hình ảnh
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Thumbnail -->
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Ảnh đại diện (Thumbnail) <span class="text-rose-500">*</span></label>
                            <div class="relative flex flex-col items-center justify-center h-64 border-2 border-dashed border-slate-300 rounded-xl hover:bg-indigo-50 hover:border-indigo-300 transition-all cursor-pointer group bg-slate-50">
                                <input type="file" name="thumbnail" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" accept="image/*" onchange="previewThumbnail(this)">
                                
                                <div class="text-center transition-all duration-300 transform group-hover:scale-105" id="thumb-placeholder">
                                    <div class="w-16 h-16 bg-white text-indigo-500 rounded-full flex items-center justify-center mx-auto mb-3 shadow-sm border border-indigo-100">
                                        <i class="fa-regular fa-image text-3xl"></i>
                                    </div>
                                    <p class="text-sm font-bold text-slate-600">Tải ảnh đại diện</p>
                                    <p class="text-xs text-slate-400 mt-1">PNG, JPG, WEBP (Max 3MB)</p>
                                </div>
                                
                                <img id="thumb-preview" class="hidden absolute inset-0 w-full h-full object-contain rounded-xl p-2 z-0 bg-white" />
                            </div>
                        </div>

                        <!-- Gallery -->
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Thư viện ảnh (Gallery)</label>
                            <div class="relative flex flex-col items-center justify-center h-32 border-2 border-dashed border-slate-300 rounded-xl hover:bg-emerald-50 hover:border-emerald-400 transition-all cursor-pointer mb-4 group bg-slate-50">
                                <input type="file" name="gallery[]" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" accept="image/*" multiple onchange="previewGallery(this)">
                                <div class="text-center group-hover:scale-105 transition-transform">
                                    <i class="fa-solid fa-layer-group text-2xl text-slate-400 group-hover:text-emerald-500 mb-2 transition-colors"></i>
                                    <p class="text-sm font-medium text-slate-500 group-hover:text-emerald-700">Chọn nhiều ảnh (Giữ Ctrl)</p>
                                </div>
                            </div>
                            <!-- Preview Grid -->
                            <div id="gallery-preview" class="grid grid-cols-4 gap-2"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CỘT PHỤ (PHẢI) - Chiếm 1/3 -->
            <div class="w-full lg:w-1/3 flex flex-col gap-6">
                
                <!-- 4. Actions -->
                <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
                    <h3 class="font-bold text-slate-800 mb-4 pb-2 border-b border-slate-100">Xuất bản</h3>
                    
                    <div class="mb-5">
                        <label class="block text-sm font-bold text-slate-700 mb-2">Trạng thái</label>
                        <select name="status" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 shadow-sm cursor-pointer">
                            <option value="published" class="font-bold text-emerald-600">Công khai (Published)</option>
                            <option value="draft" class="text-slate-500">Bản nháp (Draft)</option>
                            <option value="archived" class="text-rose-500">Lưu trữ (Archived)</option>
                        </select>
                    </div>

                    <div class="flex items-center mb-6 p-3 bg-indigo-50 rounded-lg border border-indigo-100">
                        <input type="checkbox" id="is_featured" name="is_featured" class="w-5 h-5 text-indigo-600 border-slate-300 rounded focus:ring-indigo-500 cursor-pointer bg-white">
                        <label for="is_featured" class="ml-3 text-sm font-bold text-indigo-700 cursor-pointer select-none">Sản phẩm Nổi bật (Hot)</label>
                    </div>

                    <div class="flex flex-col gap-3">
                        <button type="submit" class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-lg shadow-md hover:shadow-lg transition-all transform active:scale-[0.98] flex items-center justify-center">
                            <i class="fa-solid fa-floppy-disk mr-2"></i> Lưu sản phẩm
                        </button>
                        <a href="{{ route('admin.products.index') }}" class="w-full py-3 bg-white border border-slate-300 text-slate-700 font-bold rounded-lg hover:bg-slate-50 text-center transition-colors shadow-sm">
                            Hủy bỏ
                        </a>
                    </div>
                </div>

                <!-- 5. Phân loại -->
                <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
                    <h3 class="font-bold text-slate-800 mb-5 pb-3 border-b border-slate-100">Phân loại</h3>
                    
                    <!-- Brand -->
                    <div class="mb-6">
                        <label class="block text-sm font-bold text-slate-700 mb-2">Thương hiệu <span class="text-rose-500">*</span></label>
                        <select name="brand_id" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 shadow-sm cursor-pointer" required>
                            <option value=""> Chọn thương hiệu </option>
                            @foreach($brands as $brand)
                                <option value="{{ $brand->id }}" {{ old('brand_id') == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Categories -->
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Danh mục <span class="text-rose-500">*</span></label>
                        <div class="max-h-64 overflow-y-auto border border-slate-300 rounded-lg p-3 bg-slate-50 space-y-2 custom-scrollbar shadow-inner">
                            @foreach($categories as $cat)
                                <div class="flex items-center p-2 hover:bg-white rounded-md transition-colors group cursor-pointer">
                                    <input type="checkbox" name="category_ids[]" value="{{ $cat->id }}" id="cat_{{ $cat->id }}" 
                                        class="w-4 h-4 text-indigo-600 border-slate-300 rounded focus:ring-indigo-500 cursor-pointer bg-white"
                                        {{ is_array(old('category_ids')) && in_array($cat->id, old('category_ids')) ? 'checked' : '' }}>
                                    <label for="cat_{{ $cat->id }}" class="ml-3 text-sm text-slate-700 group-hover:text-indigo-600 cursor-pointer w-full select-none font-medium">
                                        {{ str_repeat('— ', $cat->level) }} {{ $cat->name }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Scripts -->
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
<script>
    ClassicEditor.create(document.querySelector('#editor')).catch(error => { console.error(error); });

    function previewThumbnail(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                const img = document.getElementById('thumb-preview');
                document.getElementById('thumb-placeholder').classList.add('opacity-0');
                img.src = e.target.result;
                img.classList.remove('hidden');
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    function previewGallery(input) {
        var container = document.getElementById('gallery-preview');
        container.innerHTML = ''; 
        if (input.files) {
            Array.from(input.files).forEach(file => {
                var reader = new FileReader();
                reader.onload = function(e) {
                    var div = document.createElement('div');
                    div.className = 'relative aspect-square rounded-lg overflow-hidden border border-slate-200 shadow-sm';
                    div.innerHTML = `<img src="${e.target.result}" class="w-full h-full object-cover">`;
                    container.appendChild(div);
                }
                reader.readAsDataURL(file);
            });
        }
    }
</script>

<style>
    .ck-editor__editable_inline { min-height: 250px; }
    .custom-scrollbar::-webkit-scrollbar { width: 5px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background-color: #cbd5e1; border-radius: 10px; }
    .animate-fade-in-down { animation: fadeInDown 0.5s ease-out; }
    @keyframes fadeInDown {
        from { opacity: 0; transform: translate3d(0, -20px, 0); }
        to { opacity: 1; transform: translate3d(0, 0, 0); }
    }
</style>
@endsection