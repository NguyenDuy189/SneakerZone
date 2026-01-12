@extends('admin.layouts.app')

@section('title', 'Cập nhật sản phẩm')
@section('header', 'Cập nhật: ' . $product->name)

@section('content')
{{-- Thư viện SweetAlert2 & CKEditor --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>

<div class="container px-6 mx-auto mb-20" 
     x-data="variantManager({{ $product->id }}, {{ $product->price_min }})">

    {{-- Breadcrumb / Back Button --}}
    <div class="mb-6 flex justify-between items-center">
        <a href="{{ route('admin.products.index') }}" class="inline-flex items-center text-sm font-medium text-slate-500 hover:text-indigo-600 transition-colors">
            <i class="fa-solid fa-arrow-left mr-2"></i> Quay lại danh sách
        </a>
    </div>

    {{-- FORM CHÍNH --}}
    <form action="{{ route('admin.products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
        <div class="flex flex-col lg:flex-row gap-8">
            
            {{-- CỘT TRÁI: Nội dung chính --}}
            <div class="w-full lg:w-2/3 flex flex-col gap-8">
                
                {{-- 1. THÔNG TIN CHUNG --}}
                <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
                    <h3 class="text-lg font-bold text-slate-800 mb-5 pb-3 border-b border-slate-100 flex items-center">
                        <i class="fa-solid fa-circle-info mr-2 text-indigo-500"></i> Thông tin cơ bản
                    </h3>
                    <div class="space-y-5">
                        {{-- Tên sản phẩm --}}
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Tên sản phẩm <span class="text-rose-500">*</span></label>
                            <input name="name" type="text" 
                                class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 shadow-sm @error('name') border-rose-500 @enderror" 
                                value="{{ old('name', $product->name) }}" placeholder="Nhập tên sản phẩm...">
                            @error('name') <p class="text-rose-500 text-xs mt-1 italic">{{ $message }}</p> @enderror
                        </div>

                        {{-- SKU & Giá --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Mã SKU (Chính)</label>
                                <input name="sku_code" type="text" 
                                    class="w-full rounded-lg border-slate-300 bg-slate-50 font-mono text-slate-600 focus:bg-white transition-colors" 
                                    value="{{ old('sku_code', $product->sku_code) }}" placeholder="Để trống tự sinh mã">
                                @error('sku_code') <p class="text-rose-500 text-xs mt-1 italic">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Giá hiển thị (Min) <span class="text-rose-500">*</span></label>
                                <div class="relative">
                                    <input name="price_min" type="number" 
                                        class="w-full rounded-lg border-slate-300 font-bold text-slate-800 pr-12 focus:border-indigo-500" 
                                        value="{{ old('price_min', $product->price_min) }}" required>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-400 font-bold bg-slate-100 border-l border-slate-300 rounded-r-lg px-3">VNĐ</div>
                                </div>
                                @error('price_min') <p class="text-rose-500 text-xs mt-1 italic">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        {{-- Mô tả --}}
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Mô tả ngắn</label>
                            <textarea name="short_description" rows="3" class="w-full rounded-lg border-slate-300 focus:border-indigo-500">{{ old('short_description', $product->short_description) }}</textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Mô tả chi tiết</label>
                            <textarea name="description" id="editor">{{ old('description', $product->description) }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- 2. QUẢN LÝ BIẾN THỂ (Variants) --}}
                <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
                    <div class="flex justify-between items-center mb-5 pb-3 border-b border-slate-100">
                        <h3 class="text-lg font-bold text-slate-800 flex items-center">
                            <i class="fa-solid fa-list-ul mr-2 text-indigo-500"></i> Các phiên bản
                        </h3>
                        <button type="button" @click="openCreateModal()" 
                            class="px-4 py-2 bg-indigo-50 text-indigo-600 rounded-lg text-sm font-bold hover:bg-indigo-100 border border-indigo-200 transition-all shadow-sm">
                            <i class="fa-solid fa-plus mr-1"></i> Thêm phiên bản
                        </button>
                    </div>

                    <div class="overflow-x-auto rounded-lg border border-slate-200 bg-slate-50/30">
                        <table class="w-full text-sm text-left text-slate-600">
                            <thead class="bg-slate-50 text-slate-700 font-bold uppercase text-xs">
                                <tr>
                                    <th class="px-4 py-3 w-16">Ảnh</th>
                                    <th class="px-4 py-3">Thuộc tính</th>
                                    <th class="px-4 py-3">SKU</th>
                                    <th class="px-4 py-3">Giá bán</th>
                                    <th class="px-4 py-3">Tồn kho</th>
                                    <th class="px-4 py-3 text-right">Hành động</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                @forelse($product->variants as $variant)
                                <tr class="hover:bg-slate-50 transition-colors group">
                                    <td class="px-4 py-3">
                                        <div class="w-10 h-10 rounded border border-slate-200 bg-white overflow-hidden shadow-sm">
                                            <img src="{{ $variant->image_url ? asset('storage/' . $variant->image_url) : asset('storage/' . $product->thumbnail) }}" 
                                                 class="w-full h-full object-cover" 
                                        onerror="this.onerror=null; this.src='https://placehold.co/50x50?text=No+Img';"
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($variant->attributeValues as $val)
                                                <span class="px-2 py-0.5 rounded text-xs font-medium bg-slate-100 border border-slate-200 text-slate-600">
                                                    {{ $val->attribute->name }}: <span class="text-slate-900">{{ $val->value }}</span>
                                                </span>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 font-mono text-xs text-slate-500">{{ $variant->sku }}</td>
                                    <td class="px-4 py-3 font-bold text-slate-800">{{ number_format($variant->sale_price) }}</td>
                                    <td class="px-4 py-3">
                                        @if($variant->stock_quantity > 0)
                                            <span class="text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded text-xs font-bold border border-emerald-100">{{ $variant->stock_quantity }}</span>
                                        @else
                                            <span class="text-rose-500 bg-rose-50 px-2 py-0.5 rounded text-xs font-bold border border-rose-100">Hết hàng</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="flex justify-end gap-2 opacity-60 group-hover:opacity-100 transition-opacity">
                                            <button type="button" 
                                                @click="openEditModal({{ $variant->id }}, '{{ route('admin.products.variants.update', $variant->id) }}', '{{ $variant->sku }}', {{ $variant->stock_quantity }}, {{ $variant->original_price }}, {{ $variant->sale_price }}, {{ json_encode($variant->attributeValues->pluck('id')) }})" 
                                                class="text-indigo-600 hover:text-indigo-800" title="Sửa">
                                                <i class="fa-solid fa-pen-to-square text-lg"></i>
                                            </button>
                                            <button type="button" 
                                                onclick="confirmDeleteVariant('{{ route('admin.products.variants.destroy', $variant->id) }}')" 
                                                class="text-rose-500 hover:text-rose-700" title="Xóa">
                                                <i class="fa-solid fa-trash-can text-lg"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="p-6 text-center text-slate-400 italic bg-slate-50">
                                        Chưa có phiên bản nào. Hãy thêm phiên bản mới!
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- 3. QUẢN LÝ HÌNH ẢNH (Logic đã fix hiển thị & xóa) --}}
                <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
                    <h3 class="text-lg font-bold text-slate-800 mb-5 pb-3 border-b border-slate-100 flex items-center">
                        <i class="fa-solid fa-images mr-2 text-indigo-500"></i> Hình ảnh sản phẩm
                    </h3>
                    
                    {{-- Ảnh đại diện (Thumbnail) --}}
                    <div class="flex flex-col sm:flex-row items-start gap-6 mb-8 p-4 bg-slate-50 rounded-lg border border-slate-200 border-dashed">
                        <div class="w-32 h-32 flex-shrink-0 bg-white border border-slate-300 rounded-lg p-1 shadow-sm relative group">
                            {{-- Logic hiển thị ảnh: Nếu có trong DB -> asset, nếu không -> placeholder --}}
                            <img src="{{ $product->thumbnail ? asset('storage/' . $product->thumbnail) : 'https://placehold.co/150x150?text=No+Image' }}" 
                                 class="w-full h-full object-cover rounded" 
                                 id="preview-thumbnail"
                                 onerror="this.onerror=null; this.src='https://placehold.co/150x150?text=No+Image';"                            <div class="absolute inset-0 bg-black/10 hidden group-hover:flex items-center justify-center rounded transition-all">
                                <span class="text-xs text-white bg-black/50 px-2 py-1 rounded">Hiện tại</span>
                            </div>
                        </div>
                        <div class="flex-1 w-full">
                            <label class="block text-sm font-bold text-slate-700 mb-2">Ảnh đại diện chính <span class="text-rose-500">*</span></label>
                            <input type="file" name="thumbnail" accept="image/*" 
                                onchange="document.getElementById('preview-thumbnail').src = window.URL.createObjectURL(this.files[0])"
                                class="block w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 transition-colors border border-slate-300 rounded-lg cursor-pointer">
                            <p class="text-xs text-slate-400 mt-2">Định dạng: JPG, PNG, WEBP. Tối đa 3MB.</p>
                            @error('thumbnail') <p class="text-rose-500 text-xs mt-1 italic">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- Thư viện ảnh (Gallery) --}}
                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <label class="block text-sm font-bold text-slate-700">Thư viện ảnh (Gallery)</label>
                            <span class="text-xs text-slate-500">Chọn nhiều ảnh để thêm vào bộ sưu tập</span>
                        </div>
                        
                        <input type="file" name="gallery[]" multiple accept="image/*" 
                            class="block w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 transition-colors border border-slate-300 rounded-lg cursor-pointer mb-6">
                        
                        {{-- Grid hiển thị ảnh cũ --}}
                        @if($product->gallery_images->count() > 0)
                            <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-4" id="gallery-container">
                                @foreach($product->gallery_images as $img)
                                    <div class="relative group aspect-square rounded-lg border border-slate-200 overflow-hidden bg-slate-100 shadow-sm transition-all hover:shadow-md" id="gallery-item-{{ $img->id }}">
                                        <img src="{{ asset('storage/' . $img->image_path) }}" class="w-full h-full object-cover transition-transform group-hover:scale-105">
                                        
                                        {{-- Nút xóa AJAX --}}
                                        <button type="button" onclick="deleteGalleryImage({{ $img->id }})" 
                                                class="absolute top-2 right-2 bg-white/90 text-rose-500 w-8 h-8 rounded-full shadow-md hover:bg-rose-500 hover:text-white transition-all flex items-center justify-center opacity-0 group-hover:opacity-100 z-10"
                                                title="Xóa ảnh này">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="p-8 text-center border-2 border-dashed border-slate-200 rounded-lg bg-slate-50">
                                <p class="text-slate-400 italic text-sm">Chưa có ảnh nào trong thư viện.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- CỘT PHẢI: Sidebar (Cấu hình & Danh mục) --}}
            <div class="w-full lg:w-1/3 flex flex-col gap-6">
                
                {{-- Panel Publish (Sticky) --}}
                <div class="bg-white p-6 rounded-xl shadow-lg border border-indigo-100 sticky top-4 z-10">
                    <h3 class="font-bold text-slate-800 mb-4 pb-2 border-b border-slate-100 flex items-center justify-between">
                        <span>Hành động</span>
                        <span class="text-xs font-normal text-slate-400">ID: {{ $product->id }}</span>
                    </h3>
                    
                    <div class="mb-5">
                        <label class="block text-sm font-bold text-slate-700 mb-2">Trạng thái</label>
                        <select name="status" class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 cursor-pointer">
                            <option value="published" {{ $product->status == 'published' ? 'selected' : '' }}>🟢 Đang bán</option>
                            <option value="draft" {{ $product->status == 'draft' ? 'selected' : '' }}>⚪ Bản nháp</option>
                            <option value="archived" {{ $product->status == 'archived' ? 'selected' : '' }}>⚫ Lưu trữ</option>
                        </select>
                    </div>

                    <div class="mb-6 flex items-center p-3 bg-slate-50 rounded-lg border border-slate-200">
                        <input type="checkbox" name="is_featured" id="is_featured" value="1" 
                            {{ $product->is_featured ? 'checked' : '' }} 
                            class="w-5 h-5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                        <label for="is_featured" class="ml-3 text-sm text-slate-700 font-bold cursor-pointer select-none">Sản phẩm nổi bật?</label>
                    </div>

                    <button type="submit" class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-lg shadow-md hover:shadow-lg transition-all transform active:scale-95 flex justify-center items-center">
                        <i class="fa-solid fa-floppy-disk mr-2"></i> Lưu thay đổi
                    </button>
                    
                    <div class="mt-4 pt-4 border-t border-slate-100 text-center">
                        <p class="text-xs text-slate-400">Cập nhật lần cuối:<br> {{ $product->updated_at->format('H:i d/m/Y') }}</p>
                    </div>
                </div>

                {{-- Panel Danh mục (CHECKBOX ĐA CẤP) --}}
                <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
                    <h3 class="font-bold text-slate-800 mb-4 pb-2 border-b border-slate-100 flex items-center">
                        <i class="fa-solid fa-layer-group mr-2 text-indigo-500"></i> Danh mục
                    </h3>
                    
                    <div class="max-h-80 overflow-y-auto pr-2 custom-scrollbar border border-slate-100 rounded-lg p-2 bg-slate-50/50">
                        @if(isset($categories) && $categories->count() > 0)
                            <ul class="space-y-3">
                                @foreach($categories as $cat)
                                    <li class="bg-white p-2 rounded border border-slate-200 shadow-sm">
                                        {{-- Parent --}}
                                        <div class="flex items-center">
                                            <input type="checkbox" name="category_ids[]" value="{{ $cat->id }}" id="cat-{{ $cat->id }}"
                                                class="w-4 h-4 text-indigo-600 border-slate-300 rounded focus:ring-indigo-500 cursor-pointer"
                                                {{ in_array($cat->id, $selectedCategories) ? 'checked' : '' }}>
                                            <label for="cat-{{ $cat->id }}" class="ml-2 text-sm font-bold text-slate-800 cursor-pointer select-none flex-1">{{ $cat->name }}</label>
                                        </div>

                                        {{-- Children --}}
                                        @if($cat->children && $cat->children->count() > 0)
                                            <ul class="pl-6 mt-2 space-y-2 border-l-2 border-slate-100 ml-2">
                                                @foreach($cat->children as $child)
                                                    <div class="flex items-center hover:bg-slate-50 p-1 rounded -ml-1">
                                                        <input type="checkbox" name="category_ids[]" value="{{ $child->id }}" id="cat-{{ $child->id }}"
                                                            class="w-4 h-4 text-indigo-600 border-slate-300 rounded focus:ring-indigo-500 cursor-pointer"
                                                            {{ in_array($child->id, $selectedCategories) ? 'checked' : '' }}>
                                                        <label for="cat-{{ $child->id }}" class="ml-2 text-sm text-slate-600 cursor-pointer select-none flex-1">{{ $child->name }}</label>
                                                    </div>
                                                @endforeach
                                            </ul>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-sm text-slate-400 italic text-center py-4">Chưa có danh mục nào.</p>
                        @endif
                    </div>
                    <p class="text-xs text-slate-400 mt-2 italic">* Bạn có thể chọn nhiều danh mục</p>
                    @error('category_ids') <p class="text-rose-500 text-xs mt-2 italic font-bold"><i class="fa-solid fa-triangle-exclamation"></i> {{ $message }}</p> @enderror
                </div>

                {{-- Panel Thương hiệu --}}
                <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
                    <h3 class="font-bold text-slate-800 mb-4 pb-2 border-b border-slate-100">Thương hiệu</h3>
                    <select name="brand_id" class="w-full rounded-lg border-slate-300 focus:border-indigo-500 shadow-sm">
                        <option value="">-- Không chọn --</option>
                        @foreach($brands as $brand)
                            <option value="{{ $brand->id }}" {{ $product->brand_id == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </form>
    
    {{-- Form ẩn để submit xóa Variant --}}
    <form id="delete-variant-form" method="POST" style="display: none;">
        @csrf @method('DELETE')
    </form>

    {{-- ================= MODAL BIẾN THỂ (ALPINE JS) ================= --}}
    <div x-show="showVariantModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" x-cloak>
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            {{-- Backdrop --}}
            <div class="fixed inset-0 bg-slate-900 bg-opacity-75 transition-opacity backdrop-blur-sm" 
                 @click="showVariantModal = false"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full">
                
                {{-- Header --}}
                <div class="bg-white px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                    <h3 class="text-lg font-bold text-slate-800 flex items-center">
                        <i class="fa-solid fa-cube mr-2 text-indigo-500"></i> 
                        <span x-text="isEditMode ? 'Cập nhật phiên bản' : 'Tạo phiên bản mới'"></span>
                    </h3>
                    <button @click="showVariantModal = false" class="text-slate-400 hover:text-rose-500 transition-colors"><i class="fa-solid fa-xmark text-2xl"></i></button>
                </div>

                {{-- Form AJAX --}}
                <form id="variantForm" @submit.prevent="submitVariantForm" enctype="multipart/form-data">
                    <div class="px-6 py-6 max-h-[70vh] overflow-y-auto custom-scrollbar">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            
                            {{-- SKU --}}
                            <div class="md:col-span-2">
                                <label class="block text-sm font-bold text-slate-700 mb-1">SKU <span class="text-rose-500">*</span></label>
                                <input type="text" name="sku" x-model="variantData.sku" 
                                    class="w-full rounded border-slate-300 focus:border-indigo-500 uppercase font-mono"
                                    :class="{'border-rose-500 bg-rose-50': errors.sku}" placeholder="VD: SP-RED-L">
                                <template x-if="errors.sku"><p class="text-rose-500 text-xs mt-1 italic" x-text="errors.sku[0]"></p></template>
                            </div>

                            {{-- Giá & Kho --}}
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-1">Giá bán <span class="text-rose-500">*</span></label>
                                <input type="number" name="sale_price" x-model="variantData.sale_price" class="w-full rounded border-slate-300 font-bold text-slate-800">
                                <template x-if="errors.sale_price"><p class="text-rose-500 text-xs mt-1" x-text="errors.sale_price[0]"></p></template>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-1">Tồn kho <span class="text-rose-500">*</span></label>
                                <input type="number" name="stock_quantity" x-model="variantData.stock" class="w-full rounded border-slate-300">
                                <template x-if="errors.stock_quantity"><p class="text-rose-500 text-xs mt-1" x-text="errors.stock_quantity[0]"></p></template>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-1">Giá gốc (nhập hàng)</label>
                                <input type="number" name="original_price" x-model="variantData.original_price" class="w-full rounded border-slate-300 text-slate-500">
                            </div>

                            {{-- Thuộc tính (Màu, Size) --}}
                            <div class="md:col-span-2 border-t border-slate-100 pt-4">
                                <h4 class="font-bold text-slate-700 mb-3 text-sm">Thuộc tính biến thể</h4>
                                <div class="grid grid-cols-2 gap-4">
                                    @foreach($attributes as $idx => $attribute)
                                    <div>
                                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">{{ $attribute->name }} <span class="text-rose-500">*</span></label>
                                        <select name="attribute_values[]" class="w-full rounded border-slate-300 text-sm focus:border-indigo-500">
                                            <option value="">-- Chọn --</option>
                                            @foreach($attribute->values as $val)
                                                <option value="{{ $val->id }}">{{ $val->value }}</option>
                                            @endforeach
                                        </select>
                                        {{-- Hiển thị lỗi theo index --}}
                                        <template x-if="errors['attribute_values.{{ $idx }}']">
                                            <p class="text-rose-500 text-xs mt-1">Vui lòng chọn {{ $attribute->name }}</p>
                                        </template>
                                    </div>
                                    @endforeach
                                </div>
                                <template x-if="errors.attribute_values"><p class="text-rose-500 text-xs mt-2 text-center italic" x-text="errors.attribute_values[0]"></p></template>
                            </div>

                            {{-- Ảnh biến thể --}}
                            <div class="md:col-span-2 border-t border-slate-100 pt-4">
                                <label class="block text-sm font-bold text-slate-700 mb-2">Ảnh riêng cho phiên bản này (Tùy chọn)</label>
                                <input type="file" name="image" id="variantImageInput" class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 border border-slate-300 rounded">
                            </div>
                        </div>
                    </div>
                    
                    {{-- Footer --}}
                    <div class="bg-slate-50 px-6 py-4 sm:flex sm:flex-row-reverse border-t border-slate-200">
                        <button type="submit" 
                            class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 transition-all"
                            :disabled="isLoading">
                            <span x-show="isLoading" class="mr-2"><i class="fa-solid fa-circle-notch fa-spin"></i></span>
                            <span x-text="isEditMode ? 'Lưu cập nhật' : 'Tạo mới'"></span>
                        </button>
                        <button type="button" @click="showVariantModal = false" class="mt-3 w-full inline-flex justify-center rounded-lg border border-slate-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-slate-700 hover:bg-slate-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Hủy bỏ</button>
                    </div>
                </form>            
            </div>
        </div>
    </div>
</div>

{{-- SCRIPT XỬ LÝ LOGIC --}}
<script>
    // 1. Khởi tạo CKEditor
    ClassicEditor.create(document.querySelector('#editor')).catch(error => console.error(error));

    // 2. Cấu hình Toast Notification
    const Toast = Swal.mixin({
        toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, timerProgressBar: true
    });
    @if(session('success')) Toast.fire({ icon: 'success', title: "{{ session('success') }}" }); @endif
    @if(session('error')) Toast.fire({ icon: 'error', title: "{{ session('error') }}" }); @endif

    // 3. Hàm Xác nhận Xóa Variant (Form Submit)
    function confirmDeleteVariant(url) {
        Swal.fire({
            title: 'Xóa phiên bản này?',
            text: "Dữ liệu sẽ mất vĩnh viễn không thể khôi phục!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e11d48',
            confirmButtonText: 'Xóa ngay',
            cancelButtonText: 'Hủy'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.getElementById('delete-variant-form');
                form.action = url; 
                form.submit();
            }
        })
    }

    // 4. Hàm Xóa Ảnh Gallery (AJAX FETCH)
    function deleteGalleryImage(id) {
        Swal.fire({
            title: 'Xóa ảnh này?', text: "Ảnh sẽ bị xóa khỏi thư viện ngay lập tức.", icon: 'warning',
            showCancelButton: true, confirmButtonColor: '#e11d48', confirmButtonText: 'Xóa', cancelButtonText: 'Hủy'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`/admin/products/images/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        document.getElementById(`gallery-item-${id}`).remove();
                        Toast.fire({ icon: 'success', title: data.message });
                    } else {
                        Toast.fire({ icon: 'error', title: data.message || 'Lỗi không xác định' });
                    }
                })
                .catch(err => {
                    console.error(err);
                    Toast.fire({ icon: 'error', title: 'Lỗi kết nối Server' });
                });
            }
        })
    }

    // 5. AlpineJS Data Logic
    document.addEventListener('alpine:init', () => {
        Alpine.data('variantManager', (productId, defaultPrice) => ({
            showVariantModal: false, 
            isEditMode: false,
            isLoading: false, 
            errors: {}, 
            variantAction: '',
            
            variantData: { 
                id: null, sku: '', stock: 0, original_price: 0, sale_price: 0
            },

            // Mở modal tạo mới
            openCreateModal() {
                this.isEditMode = false;
                this.errors = {}; 
                // Route Laravel tạo mới
                this.variantAction = `{{ route('admin.products.variants.store', ':id') }}`.replace(':id', productId);
                
                this.variantData = { 
                    id: null, sku: '', stock: 0, 
                    original_price: defaultPrice, 
                    sale_price: defaultPrice 
                };
                
                // Reset input file
                if(document.getElementById('variantImageInput')) document.getElementById('variantImageInput').value = '';
                // Reset Selects
                document.querySelectorAll('#variantForm select').forEach(el => el.value = '');

                this.showVariantModal = true;
            },

            // Mở modal chỉnh sửa
            openEditModal(id, url, sku, stock, original, sale, currentAttrIds) {
                this.isEditMode = true;
                this.errors = {}; 
                this.variantAction = url; 
                
                this.variantData = { 
                    id: id, sku: sku, stock: stock, 
                    original_price: original, sale_price: sale
                };

                if(document.getElementById('variantImageInput')) document.getElementById('variantImageInput').value = '';

                this.showVariantModal = true;

                // Auto select attributes logic
                this.$nextTick(() => {
                    const selects = document.querySelectorAll('#variantForm select[name="attribute_values[]"]');
                    selects.forEach(select => {
                        select.value = ''; 
                        Array.from(select.options).forEach(option => {
                            // Convert value sang int để so sánh với array ID
                            if (currentAttrIds.includes(parseInt(option.value))) {
                                select.value = option.value;
                            }
                        });
                    });
                });
            },

            // Submit Form Ajax
            submitVariantForm() {
                this.isLoading = true;
                this.errors = {}; 

                const form = document.getElementById('variantForm');
                const formData = new FormData(form);

                if(this.isEditMode) formData.append('_method', 'PUT');

                fetch(this.variantAction, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: formData
                })
                .then(async response => {
                    const data = await response.json().catch(() => ({})); 

                    if (!response.ok) {
                        if (response.status === 422) {
                            this.errors = data.errors;
                            // Lấy lỗi đầu tiên để Toast
                            let firstKey = Object.keys(data.errors)[0];
                            Toast.fire({ icon: 'error', title: 'Lỗi dữ liệu', text: data.errors[firstKey][0] });
                        } else {
                            throw new Error(data.message || 'Server Error');
                        }
                    } else {
                        this.showVariantModal = false;
                        Swal.fire({ 
                            icon: 'success', title: 'Thành công!', text: data.message,
                            timer: 1500, showConfirmButton: false 
                        });
                        setTimeout(() => window.location.reload(), 1500);
                    }
                })
                .catch(error => {
                    console.error(error);
                    if(!this.errors || Object.keys(this.errors).length === 0) {
                        Swal.fire({ icon: 'error', title: 'Lỗi!', text: error.message });
                    }
                })
                .finally(() => { 
                    this.isLoading = false; 
                });
            }
        }));
    });
</script>

<style>
    .custom-scrollbar::-webkit-scrollbar { width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    [x-cloak] { display: none !important; }
</style>
@endsection