@extends('admin.layouts.app')

@section('title', 'Cập nhật sản phẩm')
@section('header', 'Cập nhật: ' . $product->name)

@section('content')
{{-- Thư viện SweetAlert2 & AlpineJS (nếu chưa có trong layout) --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
{{-- <script src="//unpkg.com/alpinejs" defer></script> --}}

<div class="container px-6 mx-auto mb-20" 
     x-data="{ 
        showVariantModal: false, 
        isEditMode: false,
        isLoading: false, 
        errors: {}, 
        variantAction: '',
        
        // Dữ liệu form
        variantData: { 
            id: null, 
            sku: '', 
            stock: 0, 
            original_price: 0, 
            sale_price: 0
        },

        // 1. Mở Modal Tạo mới
        openCreateModal() {
            this.isEditMode = false;
            this.errors = {}; 
            // Route storeVariant
            this.variantAction = '{{ route('admin.products.variants.store', $product->id) }}';
            
            // Reset dữ liệu
            this.variantData = { 
                id: null, 
                sku: '', 
                stock: 0, 
                original_price: {{ $product->price_min }}, 
                sale_price: {{ $product->price_min }}
            };
            
            // Reset input file
            if(document.getElementById('variantImageInput')) document.getElementById('variantImageInput').value = '';
            
            // Reset các ô select thuộc tính về rỗng
            this.$nextTick(() => {
                document.querySelectorAll('#variantForm select').forEach(el => el.value = '');
            });

            this.showVariantModal = true;
        },

        // 2. Mở Modal Edit
        openEditModal(id, url, sku, stock, original, sale, currentAttributeIds) {
            this.isEditMode = true;
            this.errors = {}; 
            this.variantAction = url; // URL updateVariant truyền từ Blade
            
            this.variantData = { 
                id: id, 
                sku: sku, 
                stock: stock, 
                original_price: original, 
                sale_price: sale
            };

            if(document.getElementById('variantImageInput')) document.getElementById('variantImageInput').value = '';

            this.showVariantModal = true;

            // --- LOGIC TỰ ĐỘNG CHỌN LẠI THUỘC TÍNH ---
            this.$nextTick(() => {
                const selects = document.querySelectorAll('#variantForm select[name=\'attribute_values[]\']');
                
                selects.forEach(select => {
                    select.value = ''; // Reset trước
                    Array.from(select.options).forEach(option => {
                        // So sánh ID (ép kiểu int để chắc chắn)
                        if (currentAttributeIds.includes(parseInt(option.value))) {
                            select.value = option.value;
                        }
                    });
                });
            });
        },

        // 3. Submit Form (AJAX)
        submitVariantForm() {
            this.isLoading = true;
            this.errors = {}; 

            const form = document.getElementById('variantForm');
            const formData = new FormData(form);

            if(this.isEditMode) {
                formData.append('_method', 'PUT');
            }

            fetch(this.variantAction, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'Accept': 'application/json',
                },
                body: formData
            })
            .then(async response => {
                const data = await response.json().catch(() => ({})); 

                if (!response.ok) {
                    // Trường hợp lỗi Validation (422)
                    if (response.status === 422) {
                        this.errors = data.errors;
                        
                        // Lấy lỗi đầu tiên để hiển thị ra Toast
                        let firstError = Object.values(data.errors)[0][0];
                        
                        Toast.fire({ 
                            icon: 'error', 
                            title: 'Lỗi dữ liệu!', // Tiêu đề tiếng Việt
                            text: firstError // Nội dung lỗi tiếng Việt từ Controller
                        });
                    } 
                    // Trường hợp lỗi Server (500) hoặc lỗi logic khác
                    else {
                        Swal.fire({
                            title: 'Đã xảy ra lỗi!',
                            text: data.message || 'Không thể xử lý yêu cầu, vui lòng thử lại.',
                            icon: 'error',
                            confirmButtonText: 'Đóng lại', // Nút bấm tiếng Việt
                            confirmButtonColor: '#d33'
                        });
                    }
                    throw new Error('Validation or Server error');
                }
                
                // --- THÀNH CÔNG ---
                this.showVariantModal = false;
                Swal.fire({ 
                    icon: 'success', 
                    title: 'Thành công!', 
                    text: data.message || 'Dữ liệu đã được lưu.',
                    timer: 1500, 
                    showConfirmButton: false 
                });
                
                setTimeout(() => window.location.reload(), 1500);
            })
            .catch(error => {
                console.error(error);
                // Nếu lỗi mạng hoặc code JS hỏng
                if (!this.errors || Object.keys(this.errors).length === 0) {
                     Swal.fire({
                        title: 'Lỗi kết nối!',
                        text: 'Không thể gửi dữ liệu đến máy chủ.',
                        icon: 'error',
                        confirmButtonText: 'Đóng'
                    });
                }
            })
            .finally(() => { 
                this.isLoading = false; 
            });
        }
    }">

    {{-- Header & Back Link --}}
    <div class="mb-6">
        <a href="{{ route('admin.products.index') }}" class="inline-flex items-center text-sm font-medium text-slate-500 hover:text-indigo-600 transition-colors">
            <i class="fa-solid fa-arrow-left mr-2"></i> Quay lại danh sách
        </a>
    </div>

    {{-- Form chính cập nhật sản phẩm cha --}}
    <form action="{{ route('admin.products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
        <div class="flex flex-col lg:flex-row gap-8">
            
            {{-- CỘT TRÁI --}}
            <div class="w-full lg:w-2/3 flex flex-col gap-8">
                
                {{-- Thông tin chung --}}
                <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
                    <h3 class="text-lg font-bold text-slate-800 mb-5 pb-3 border-b border-slate-100 flex items-center">
                        <i class="fa-solid fa-circle-info mr-2 text-indigo-500"></i> Thông tin chung
                    </h3>
                    <div class="space-y-5">
                        {{-- Tên Sản Phẩm --}}
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Tên sản phẩm <span class="text-rose-500">*</span></label>
                            <input name="name" type="text" 
                                class="w-full rounded-lg border-slate-300 bg-white px-4 py-2.5 text-slate-800 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 shadow-sm transition-all @error('name') border-rose-500 ring-1 ring-rose-500 @enderror" 
                                value="{{ old('name', $product->name) }}" required>
                            @error('name') <p class="text-rose-500 text-xs mt-1 italic">{{ $message }}</p> @enderror
                        </div>

                        {{-- SKU & Giá Min --}}
                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Mã SKU (Chung)</label>
                                <input name="sku_code" type="text" 
                                    class="w-full rounded-lg border-slate-300 bg-slate-50 px-4 py-2.5 font-mono text-slate-600 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 shadow-sm @error('sku_code') border-rose-500 @enderror" 
                                    value="{{ old('sku_code', $product->sku_code) }}">
                                @error('sku_code') <p class="text-rose-500 text-xs mt-1 italic">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Giá hiển thị (Min) <span class="text-rose-500">*</span></label>
                                <div class="relative">
                                    <input name="price_min" type="number" 
                                        class="w-full rounded-lg border-slate-300 bg-white pl-4 pr-12 py-2.5 font-bold text-slate-800 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 shadow-sm @error('price_min') border-rose-500 @enderror" 
                                        value="{{ old('price_min', $product->price_min) }}" required>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none text-slate-500 font-bold bg-slate-100 border-l border-slate-300 rounded-r-lg px-3">VNĐ</div>
                                </div>
                                @error('price_min') <p class="text-rose-500 text-xs mt-1 italic">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        {{-- Mô tả --}}
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Mô tả ngắn</label>
                            <textarea name="short_description" rows="3" class="w-full rounded-lg border-slate-300 bg-white px-4 py-2.5 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 shadow-sm">{{ old('short_description', $product->short_description) }}</textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Mô tả chi tiết</label>
                            <textarea name="description" id="editor">{{ old('description', $product->description) }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- DANH SÁCH BIẾN THỂ --}}
                <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
                    <div class="flex justify-between items-center mb-5 pb-3 border-b border-slate-100">
                        <h3 class="text-lg font-bold text-slate-800 flex items-center">
                            <i class="fa-solid fa-list-ul mr-2 text-indigo-500"></i> Các phiên bản
                        </h3>
                        <button type="button" @click="openCreateModal()" class="px-4 py-2 bg-indigo-50 text-indigo-600 rounded-lg text-sm font-bold hover:bg-indigo-100 transition-colors flex items-center border border-indigo-200 shadow-sm">
                            <i class="fa-solid fa-plus mr-1"></i> Thêm phiên bản
                        </button>
                    </div>

                    <div class="overflow-x-auto rounded-lg border border-slate-200 bg-slate-50/50">
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
                                        <div class="w-10 h-10 rounded-lg border border-slate-200 bg-white overflow-hidden shadow-sm">
                                            <img src="{{ $variant->image_url ? asset('storage/' . $variant->image_url) : asset('storage/' . $product->thumbnail) }}" 
                                                 class="w-full h-full object-cover">
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($variant->attributeValues as $val)
                                                <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-slate-100 border border-slate-200 text-slate-700">
                                                    <span class="text-slate-400 mr-1">{{ $val->attribute->name ?? 'Attr' }}:</span> {{ $val->value }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 font-mono text-xs text-slate-500">{{ $variant->sku }}</td>
                                    <td class="px-4 py-3 font-bold text-slate-700">{{ number_format($variant->sale_price) }}</td>
                                    <td class="px-4 py-3">
                                        @if($variant->stock_quantity > 0)
                                            <span class="text-emerald-600 font-bold bg-emerald-50 px-2 py-0.5 rounded text-xs border border-emerald-100">{{ $variant->stock_quantity }}</span>
                                        @else
                                            <span class="text-rose-500 font-bold bg-rose-50 px-2 py-0.5 rounded text-xs border border-rose-100">Hết hàng</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="flex justify-end gap-1 opacity-80 group-hover:opacity-100 transition-opacity">
                                            {{-- Nút Sửa: Truyền đúng Route UpdateVariant --}}
                                            <button type="button" 
                                                @click="openEditModal(
                                                    {{ $variant->id }}, 
                                                    '{{ route('admin.products.variants.update', $variant->id) }}', 
                                                    '{{ $variant->sku }}', 
                                                    {{ $variant->stock_quantity }}, 
                                                    {{ $variant->original_price ?? 0 }}, 
                                                    {{ $variant->sale_price }}, 
                                                    {{ json_encode($variant->attributeValues->pluck('id')) }} 
                                                )"
                                                class="text-white bg-indigo-500 hover:bg-indigo-600 p-1.5 rounded shadow-sm transition-colors" title="Sửa">
                                                <i class="fa-solid fa-pen text-xs"></i>
                                            </button>
                                            
                                            {{-- Nút Xóa: Truyền đúng Route DestroyVariant --}}
                                            <button type="button" 
                                                onclick="confirmDeleteVariant('{{ route('admin.products.variants.destroy', $variant->id) }}')" 
                                                class="text-white bg-rose-500 hover:bg-rose-600 p-1.5 rounded shadow-sm transition-colors" title="Xóa">
                                                <i class="fa-solid fa-trash-can text-xs"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center text-slate-400 italic bg-slate-50">Chưa có biến thể nào.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-bold mb-2">Mã SKU</label>
                    <input x-model="variantData.sku" name="sku" type="text" class="w-full border rounded px-3 py-2">
                    
                    {{-- Hiển thị lỗi từ AlpineJS --}}
                    <template x-if="errors.sku">
                        <p class="text-rose-500 text-xs mt-1" x-text="errors.sku[0]"></p>
                    </template>
                </div>

                {{-- Hình ảnh sản phẩm (Giữ nguyên) --}}
                <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
                    <h3 class="text-lg font-bold text-slate-800 mb-5 pb-3 border-b border-slate-100 flex items-center">
                        <i class="fa-solid fa-images mr-2 text-indigo-500"></i> Hình ảnh
                    </h3>
                    
                    {{-- Ảnh đại diện --}}
                    <div class="flex items-start gap-6 p-4 bg-slate-50 rounded-xl border border-slate-200 mb-8">
                        <div class="w-32 h-32 flex-shrink-0 bg-white rounded-lg border border-slate-200 p-1 shadow-sm">
                            <img src="{{ asset('storage/' . $product->thumbnail) }}" class="w-full h-full object-cover rounded-md" onerror="this.src='https://placehold.co/150x150'">
                        </div>
                        <div class="flex-1">
                            <label class="block text-sm font-bold text-slate-700 mb-2">Thay đổi ảnh đại diện</label>
                            <input type="file" name="thumbnail" class="block w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 transition-colors cursor-pointer border border-indigo-100">
                            @error('thumbnail') <p class="text-rose-500 text-xs mt-1 italic">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- CỘT PHẢI (Sidebar) --}}
            <div class="w-full lg:w-1/3 flex flex-col gap-6">
                <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 sticky top-4">
                    <h3 class="font-bold text-slate-800 mb-4 pb-2 border-b border-slate-100">Cập nhật</h3>
                    <div class="mb-5">
                        <label class="block text-sm font-bold text-slate-700 mb-2">Trạng thái</label>
                        <select name="status" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 cursor-pointer shadow-sm">
                            <option value="published" {{ $product->status == 'published' ? 'selected' : '' }}>Đang bán</option>
                            <option value="draft" {{ $product->status == 'draft' ? 'selected' : '' }}>Bản nháp</option>
                            <option value="archived" {{ $product->status == 'archived' ? 'selected' : '' }}>Lưu trữ</option>
                        </select>
                    </div>
                    <button type="submit" class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-lg shadow-md transition-all">Cập nhật thay đổi</button>
                </div>

                {{-- Phân loại (Brand, Category) --}}
                <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
                    <h3 class="font-bold text-slate-800 mb-5 pb-3 border-b border-slate-100">Phân loại</h3>
                    <div class="mb-6">
                        <label class="block text-sm font-bold text-slate-700 mb-2">Thương hiệu</label>
                        <select name="brand_id" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 shadow-sm">
                            @foreach($brands as $brand)
                                <option value="{{ $brand->id }}" {{ $product->brand_id == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </form>

    {{-- Form xóa ẩn (Dùng chung) --}}
    <form id="delete-variant-form" method="POST" style="display: none;">
        @csrf @method('DELETE')
    </form>

    {{-- ================= MODAL BIẾN THỂ (AJAX) ================= --}}
    <div x-show="showVariantModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" x-cloak>
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            {{-- Backdrop --}}
            <div class="fixed inset-0 bg-slate-900 bg-opacity-75 transition-opacity backdrop-blur-sm" 
                 x-show="showVariantModal" 
                 x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" 
                 x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                 @click="showVariantModal = false"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full"
                 x-show="showVariantModal" 
                 x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:scale-95">
                
                {{-- Modal Header --}}
                <div class="bg-white px-6 pt-5 pb-4 border-b border-slate-100 flex justify-between items-center">
                    <h3 class="text-xl font-bold text-slate-800 flex items-center">
                        <i class="fa-solid fa-cube mr-2 text-indigo-500"></i> 
                        <span x-text="isEditMode ? 'Cập nhật phiên bản' : 'Tạo phiên bản mới'"></span>
                    </h3>
                    <button @click="showVariantModal = false" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark text-xl"></i></button>
                </div>

                {{-- Modal Body: FORM AJAX --}}
                <form id="variantForm" @submit.prevent="submitVariantForm" enctype="multipart/form-data">
                    <div class="px-6 py-6 max-h-[70vh] overflow-y-auto custom-scrollbar">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            
                            {{-- SKU --}}
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">SKU <span class="text-rose-500">*</span></label>
                                <input type="text" name="sku" x-model="variantData.sku" 
                                    class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 shadow-sm px-4 py-2"
                                    :class="{'border-rose-500 ring-1 ring-rose-500': errors.sku}" 
                                    placeholder="VD: SP-RED-XL">
                                <template x-if="errors.sku"><p class="text-rose-500 text-xs mt-1 italic" x-text="errors.sku[0]"></p></template>
                            </div>

                            {{-- Tồn kho --}}
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Tồn kho <span class="text-rose-500">*</span></label>
                                <input type="number" name="stock_quantity" x-model="variantData.stock" 
                                    class="w-full rounded-lg border-slate-300 focus:border-indigo-500 shadow-sm px-4 py-2"
                                    :class="{'border-rose-500': errors.stock_quantity}">
                                <template x-if="errors.stock_quantity"><p class="text-rose-500 text-xs mt-1 italic" x-text="errors.stock_quantity[0]"></p></template>
                            </div>

                            {{-- Giá Gốc --}}
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Giá gốc</label>
                                <input type="number" name="original_price" x-model="variantData.original_price" 
                                    class="w-full rounded-lg border-slate-300 focus:border-indigo-500 shadow-sm px-4 py-2"
                                    :class="{'border-rose-500': errors.original_price}">
                                <template x-if="errors.original_price"><p class="text-rose-500 text-xs mt-1 italic" x-text="errors.original_price[0]"></p></template>
                            </div>

                            {{-- Giá Bán --}}
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Giá bán <span class="text-rose-500">*</span></label>
                                <input type="number" name="sale_price" x-model="variantData.sale_price" 
                                    class="w-full rounded-lg border-slate-300 focus:border-indigo-500 shadow-sm px-4 py-2 font-bold text-slate-800"
                                    :class="{'border-rose-500 ring-1 ring-rose-500': errors.sale_price}">
                                <template x-if="errors.sale_price"><p class="text-rose-500 text-xs mt-1 italic" x-text="errors.sale_price[0]"></p></template>
                            </div>

                            {{-- CÁC THUỘC TÍNH (Size, Color...) --}}
                            <div class="md:col-span-2 border-t border-slate-100 pt-4 mt-2">
                                <h4 class="font-bold text-slate-700 mb-3">Thuộc tính biến thể</h4>
                                
                                <div class="grid grid-cols-2 gap-4">
                                    @if(isset($attributes) && $attributes->count() > 0)
                                        @foreach($attributes as $attribute)
                                        <div>
                                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">
                                                {{ $attribute->name }} <span class="text-rose-500">*</span>
                                            </label>
                                            
                                            <select name="attribute_values[]" 
                                                    class="w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 shadow-sm transition-all"
                                                    :class="{'border-rose-500 ring-1 ring-rose-500': errors['attribute_values.{{ $loop->index }}']}">
                                                
                                                <option value="">-- Chọn {{ $attribute->name }} --</option>
                                                @foreach($attribute->values as $value)
                                                    <option value="{{ $value->id }}">{{ $value->value }}</option>
                                                @endforeach
                                            </select>

                                            {{-- Hiển thị lỗi cho TỪNG select box --}}
                                            <template x-if="errors['attribute_values.{{ $loop->index }}']">
                                                <p class="text-rose-500 text-xs mt-1 italic" 
                                                x-text="errors['attribute_values.{{ $loop->index }}'][0].replace('attribute_values.{{ $loop->index }}', '{{ $attribute->name }}')">
                                                </p>
                                            </template>
                                        </div>
                                        @endforeach
                                    @else
                                        <p class="text-sm text-slate-400 italic col-span-2">Không có thuộc tính nào được định nghĩa.</p>
                                    @endif
                                </div>

                                {{-- Hiển thị lỗi chung --}}
                                <template x-if="errors.attribute_values">
                                    <p class="text-rose-500 text-xs mt-2 italic" x-text="errors.attribute_values[0]"></p>
                                </template>
                            </div>

                            {{-- Ảnh biến thể --}}
                            <div class="md:col-span-2 border-t border-slate-100 pt-4 mt-2">
                                <label class="block text-sm font-bold text-slate-700 mb-2">Ảnh riêng cho biến thể</label>
                                <input type="file" name="image" id="variantImageInput" class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 transition-colors cursor-pointer border border-indigo-100 rounded-lg">
                                <template x-if="errors.image"><p class="text-rose-500 text-xs mt-1 italic" x-text="errors.image[0]"></p></template>
                            </div>

                        </div>
                    </div>
                    
                    {{-- Modal Footer --}}
                    <div class="bg-slate-50 px-6 py-4 sm:flex sm:flex-row-reverse border-t border-slate-200 rounded-b-xl">
                        <button type="submit" 
                            class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2.5 bg-indigo-600 text-base font-bold text-white hover:bg-indigo-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm transition-all disabled:opacity-50"
                            :disabled="isLoading">
                            <span x-show="isLoading" class="mr-2"><i class="fa-solid fa-spinner fa-spin"></i></span>
                            <span x-text="isEditMode ? 'Cập nhật' : 'Lưu mới'"></span>
                        </button>
                        
                        <button type="button" @click="showVariantModal = false" class="mt-3 w-full inline-flex justify-center rounded-lg border border-slate-300 shadow-sm px-4 py-2.5 bg-white text-base font-medium text-slate-700 hover:bg-slate-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Hủy</button>
                    </div>
                </form>            
            </div>
        </div>
    </div>
</div>

{{-- Scripts --}}
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
<script>
    // CKEditor
    ClassicEditor.create(document.querySelector('#editor')).catch(error => { console.error(error); });

    // SweetAlert2 Toast Global
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
    });

    @if(session('success')) Toast.fire({ icon: 'success', title: "{{ session('success') }}" }); @endif
    @if(session('error')) Toast.fire({ icon: 'error', title: "{{ session('error') }}" }); @endif

    // Confirm Delete (Đã sửa để nhận URL động)
    function confirmDeleteVariant(url) {
        Swal.fire({
            title: 'Xóa phiên bản này?',
            text: "Hành động này không thể hoàn tác!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e11d48',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Vâng, xóa nó!',
            cancelButtonText: 'Hủy'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.getElementById('delete-variant-form');
                form.action = url; // Gán URL động
                form.submit();
            }
        })
    }
</script>

<style>
    /* Custom Scrollbar cho Modal */
    .custom-scrollbar::-webkit-scrollbar { width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    [x-cloak] { display: none !important; }
</style>
@endsection