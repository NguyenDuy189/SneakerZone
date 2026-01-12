@extends('admin.layouts.app')

@section('title', 'Thêm sản phẩm mới')

@section('content')
<div class="container px-6 mx-auto mb-20 min-h-screen">
    
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Tạo sản phẩm mới</h2>
            <nav class="text-sm text-slate-500 mt-1">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-indigo-600">Dashboard</a>
                <span class="mx-2">/</span>
                <a href="{{ route('admin.products.index') }}" class="hover:text-indigo-600">Sản phẩm</a>
                <span class="mx-2">/</span>
                <span class="text-slate-800 font-medium">Thêm mới</span>
            </nav>
        </div>
        <a href="{{ route('admin.products.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-slate-300 rounded-lg text-slate-700 text-sm font-medium hover:bg-slate-50 transition shadow-sm">
            <i class="fa-solid fa-arrow-left mr-2"></i> Quay lại
        </a>
    </div>

    @if ($errors->any())
        <div class="mb-6 p-4 bg-rose-50 border-l-4 border-rose-500 rounded-r-lg shadow-sm animate-fade-in-down">
            <div class="flex items-start">
                <i class="fa-solid fa-circle-exclamation text-rose-500 mt-0.5 mr-3"></i>
                <div>
                    <h3 class="font-bold text-rose-800">Vui lòng kiểm tra lại dữ liệu:</h3>
                    <ul class="list-disc list-inside text-sm text-rose-700 mt-1 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data" id="productForm">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <div class="lg:col-span-2 space-y-8">
                
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="p-4 border-b border-slate-100 bg-slate-50/50 flex items-center gap-2">
                        <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center">
                            <i class="fa-solid fa-pen"></i>
                        </div>
                        <h3 class="font-bold text-slate-800">Thông tin chung</h3>
                    </div>
                    
                    <div class="p-6 space-y-6">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Tên sản phẩm <span class="text-rose-500">*</span></label>
                            <input type="text" name="name" value="{{ old('name') }}" 
                                class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500 transition-all placeholder-slate-400"
                                placeholder="Nhập tên sản phẩm (VD: Nike Air Force 1)" required>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Mã SKU</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                        <i class="fa-solid fa-barcode"></i>
                                    </div>
                                    <input type="text" name="sku_code" value="{{ old('sku_code') }}" 
                                        class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-slate-300 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500 transition-all font-mono text-slate-600"
                                        placeholder="Để trống tự sinh mã">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Giá bán (Min) <span class="text-rose-500">*</span></label>
                                <div class="relative">
                                    <input type="number" name="price_min" value="{{ old('price_min') }}" 
                                        class="w-full pl-4 pr-12 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500 transition-all font-bold text-slate-700"
                                        placeholder="0" min="0" required>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-500 font-bold bg-slate-100 border-l rounded-r-lg px-3">
                                        đ
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Mô tả ngắn (SEO)</label>
                            <textarea name="short_description" rows="3" 
                                class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500 transition-all text-sm"
                                placeholder="Tóm tắt sản phẩm...">{{ old('short_description') }}</textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Chi tiết sản phẩm</label>
                            <textarea name="description" id="editor">{{ old('description') }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="p-4 border-b border-slate-100 bg-slate-50/50 flex items-center gap-2">
                        <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center">
                            <i class="fa-regular fa-images"></i>
                        </div>
                        <h3 class="font-bold text-slate-800">Quản lý hình ảnh</h3>
                    </div>

                    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Ảnh đại diện <span class="text-rose-500">*</span></label>
                            <div class="relative border-2 border-dashed border-slate-300 rounded-xl p-1 hover:bg-slate-50 transition-colors h-64 group">
                                <input type="file" name="thumbnail" accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-20" onchange="previewSingle(this, 'thumb-preview', 'thumb-placeholder')">
                                
                                <div id="thumb-placeholder" class="absolute inset-0 flex flex-col items-center justify-center text-slate-400 group-hover:text-indigo-500 transition-colors z-10">
                                    <i class="fa-solid fa-cloud-arrow-up text-4xl mb-2"></i>
                                    <span class="text-sm font-medium">Click để tải ảnh</span>
                                </div>
                                
                                <img id="thumb-preview" class="hidden w-full h-full object-contain rounded-lg z-10 bg-white relative" src="" alt="Thumbnail preview">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Bộ sưu tập (Gallery)</label>
                            <div class="border-2 border-dashed border-slate-300 rounded-xl p-4 hover:bg-slate-50 transition-colors relative min-h-[16rem]">
                                <input type="file" name="gallery[]" multiple accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-20" onchange="previewGallery(this, 'gallery-container')">
                                
                                <div class="text-center text-slate-400 py-4 pointer-events-none">
                                    <i class="fa-solid fa-layer-group text-3xl mb-2"></i>
                                    <p class="text-sm">Chọn nhiều ảnh (Giữ Ctrl)</p>
                                </div>

                                <div id="gallery-container" class="grid grid-cols-3 gap-2 mt-2 pointer-events-none relative z-10">
                                    </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-6 text-center relative overflow-hidden">
                    <div class="relative z-10">
                        <div class="inline-flex items-center justify-center w-12 h-12 bg-white text-indigo-600 rounded-full shadow-sm mb-3">
                            <i class="fa-solid fa-shirt"></i>
                        </div>
                        <h4 class="font-bold text-indigo-900 text-lg">Quản lý Biến thể (Size, Màu)</h4>
                        <p class="text-indigo-700 text-sm mb-4 max-w-md mx-auto">
                            Để thêm biến thể, bạn cần lưu sản phẩm cơ bản trước. Tính năng này sẽ được mở khóa sau khi tạo thành công.
                        </p>
                        <button type="submit" class="inline-flex items-center px-6 py-2 bg-indigo-600 text-white rounded-lg font-bold hover:bg-indigo-700 transition shadow-md text-sm">
                            <i class="fa-solid fa-save mr-2"></i> Lưu & Thêm biến thể
                        </button>
                    </div>
                    <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-indigo-200 rounded-full opacity-20 blur-2xl"></div>
                    <div class="absolute bottom-0 left-0 -mb-4 -ml-4 w-32 h-32 bg-indigo-300 rounded-full opacity-20 blur-2xl"></div>
                </div>
            </div>

            <div class="lg:col-span-1 space-y-8">
                
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 sticky top-6">
                    <h3 class="font-bold text-slate-800 mb-4 pb-2 border-b border-slate-100">Xuất bản</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">Trạng thái</label>
                            <select name="status" class="w-full rounded-lg border-slate-300 focus:ring-indigo-500 cursor-pointer">
                                <option value="published">Công khai</option>
                                <option value="draft">Bản nháp</option>
                                <option value="archived">Lưu trữ</option>
                            </select>
                        </div>

                        <div class="flex items-center p-3 bg-slate-50 rounded-lg border border-slate-200">
                            <input type="checkbox" name="is_featured" id="is_featured" value="1" class="w-5 h-5 text-indigo-600 border-slate-300 rounded focus:ring-indigo-500 cursor-pointer">
                            <label for="is_featured" class="ml-3 text-sm font-medium text-slate-700 cursor-pointer select-none">
                                Sản phẩm nổi bật
                            </label>
                        </div>

                        <div class="pt-4 border-t border-slate-100 flex flex-col gap-3">
                            <button type="submit" class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-lg shadow-md hover:shadow-lg transition-all flex items-center justify-center">
                                <i class="fa-solid fa-floppy-disk mr-2"></i> Lưu sản phẩm
                            </button>
                            <a href="{{ route('admin.products.index') }}" class="w-full py-3 bg-white border border-slate-300 text-slate-700 font-bold rounded-lg hover:bg-slate-50 text-center transition">
                                Hủy bỏ
                            </a>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                    <h3 class="font-bold text-slate-800 mb-4 pb-2 border-b border-slate-100">Phân loại</h3>

                    <div class="space-y-5">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Thương hiệu <span class="text-rose-500">*</span></label>
                            <select name="brand_id" class="w-full rounded-lg border-slate-300 focus:ring-indigo-500" required>
                                <option value="">-- Chọn thương hiệu --</option>
                                @foreach($brands as $brand)
                                    <option value="{{ $brand->id }}" {{ old('brand_id') == $brand->id ? 'selected' : '' }}>
                                        {{ $brand->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <div class="flex justify-between items-center mb-2">
                                <label class="block text-sm font-bold text-slate-700">Danh mục <span class="text-rose-500">*</span></label>
                                <span class="text-[10px] bg-slate-100 text-slate-500 px-2 py-0.5 rounded border border-slate-200">
                                    Mục đầu tiên = Danh mục chính
                                </span>
                            </div>
                            
                            <div class="max-h-64 overflow-y-auto border border-slate-300 rounded-lg bg-slate-50 p-2 custom-scrollbar shadow-inner">
                                @if(isset($categories) && count($categories) > 0)
                                    @foreach($categories as $cat)
                                        <div class="flex items-center p-2 hover:bg-white rounded transition group">
                                            <input type="checkbox" name="category_ids[]" value="{{ $cat->id }}" id="cat_{{ $cat->id }}"
                                                class="w-4 h-4 text-indigo-600 border-slate-300 rounded focus:ring-indigo-500 cursor-pointer"
                                                {{ (is_array(old('category_ids')) && in_array($cat->id, old('category_ids'))) ? 'checked' : '' }}>
                                            
                                            <label for="cat_{{ $cat->id }}" class="ml-2 text-sm text-slate-700 group-hover:text-indigo-600 cursor-pointer select-none flex-1">
                                                {{-- Kiểm tra an toàn nếu controller không gửi level --}}
                                                @php $level = $cat->level ?? 0; @endphp
                                                <span style="padding-left: {{ $level * 15 }}px">
                                                    {{ $level > 0 ? '↳' : '' }} {{ $cat->name }}
                                                </span>
                                            </label>
                                        </div>
                                    @endforeach
                                @else
                                    <p class="text-xs text-slate-500 text-center py-4">Chưa có danh mục nào.</p>
                                @endif
                            </div>
                            @error('category_ids')
                                <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </form>
</div>

@endsection

@push('scripts')
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
<script>
    // 1. Khởi tạo CKEditor
    ClassicEditor
        .create(document.querySelector('#editor'), {
            toolbar: [ 'heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote', '|', 'undo', 'redo' ]
        })
        .catch(error => { console.error(error); });

    // 2. Preview Ảnh Đơn (Thumbnail)
    function previewSingle(input, imgId, placeholderId) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.getElementById(imgId);
                const placeholder = document.getElementById(placeholderId);
                
                img.src = e.target.result;
                img.classList.remove('hidden');
                placeholder.classList.add('opacity-0'); // Ẩn placeholder
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    // 3. Preview Nhiều Ảnh (Gallery)
    function previewGallery(input, containerId) {
        const container = document.getElementById(containerId);
        container.innerHTML = ''; // Xóa ảnh cũ
        
        if (input.files) {
            Array.from(input.files).forEach(file => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const div = document.createElement('div');
                    div.className = 'relative aspect-square rounded overflow-hidden border border-slate-200 shadow-sm bg-white';
                    div.innerHTML = `<img src="${e.target.result}" class="w-full h-full object-cover">`;
                    container.appendChild(div);
                }
                reader.readAsDataURL(file);
            });
        }
    }
</script>
@endpush

@push('styles')
<style>
    /* Chỉnh chiều cao CKEditor */
    .ck-editor__editable_inline { min-height: 250px; }
    
    /* Scrollbar đẹp cho danh mục */
    .custom-scrollbar::-webkit-scrollbar { width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
</style>
@endpush