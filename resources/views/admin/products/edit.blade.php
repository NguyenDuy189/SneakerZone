@extends('admin.layouts.app')

@section('title', 'Cập nhật sản phẩm')
@section('header', 'Cập nhật: ' . $product->name)

@section('content')
<!-- Khởi tạo Alpine x-data -->
<div class="container px-6 mx-auto mb-20" 
     x-data="{ 
        showVariantModal: false, 
        isEditMode: false,
        variantAction: '{{ route('admin.products.variants.store', $product->id) }}',
        variantData: { sku: '', stock: 10, original_price: {{ $product->price_min }}, sale_price: {{ $product->price_min }}, attributes: {} },
        
        openCreateModal() {
            this.isEditMode = false;
            this.variantAction = '{{ route('admin.products.variants.store', $product->id) }}';
            this.variantData = { sku: '', stock: 10, original_price: {{ $product->price_min }}, sale_price: {{ $product->price_min }}, attributes: [] };
            this.showVariantModal = true;
        },

        openEditModal(id, sku, stock, original, sale, attrs) {
            this.isEditMode = true;
            this.variantAction = '/admin/products/variants/' + id; 
            this.variantData = { sku: sku, stock: stock, original_price: original, sale_price: sale, attributes: attrs };
            this.showVariantModal = true;
        }
     }">

    <!-- Breadcrumb -->
    <div class="mb-6">
        <a href="{{ route('admin.products.index') }}" class="inline-flex items-center text-sm font-medium text-slate-500 hover:text-indigo-600 transition-colors">
            <i class="fa-solid fa-arrow-left mr-2"></i> Quay lại danh sách
        </a>
    </div>

    <!-- Alerts -->
    @if(session('success'))
        <div class="p-4 mb-6 text-sm text-emerald-700 bg-emerald-50 rounded-lg border border-emerald-200 shadow-sm flex items-center animate-fade-in-down">
            <i class="fa-solid fa-circle-check mr-2"></i> {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="p-4 mb-6 text-sm text-rose-700 bg-rose-50 rounded-lg border border-rose-200 shadow-sm flex items-center animate-fade-in-down">
            <i class="fa-solid fa-circle-xmark mr-2"></i> {{ session('error') }}
        </div>
    @endif
    @if($errors->any())
        <div class="p-4 mb-6 text-sm text-rose-700 bg-rose-50 rounded-lg border border-rose-200 shadow-sm animate-fade-in-down">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $err) <li>{{ $err }}</li> @endforeach
            </ul>
        </div>
    @endif

    <!-- MAIN FORM -->
    <form action="{{ route('admin.products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
        <div class="flex flex-col lg:flex-row gap-8">
            
            <!-- CỘT CHÍNH -->
            <div class="w-full lg:w-2/3 flex flex-col gap-8">
                
                <!-- 1. Thông tin chung -->
                <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
                    <h3 class="text-lg font-bold text-slate-800 mb-5 pb-3 border-b border-slate-100 flex items-center">
                        <i class="fa-solid fa-circle-info mr-2 text-indigo-500"></i> Thông tin chung
                    </h3>
                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Tên sản phẩm <span class="text-rose-500">*</span></label>
                            <input name="name" type="text" 
                                class="w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-slate-800 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 shadow-sm transition-all" 
                                value="{{ old('name', $product->name) }}" required>
                        </div>
                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Mã SKU</label>
                                <input name="sku_code" type="text" 
                                    class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 font-mono text-slate-600 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 shadow-sm" 
                                    value="{{ $product->sku_code }}">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Giá bán (Min) <span class="text-rose-500">*</span></label>
                                <div class="relative">
                                    <input name="price_min" type="number" 
                                        class="w-full rounded-lg border border-slate-300 bg-white pl-4 pr-12 py-2.5 font-bold text-slate-800 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 shadow-sm" 
                                        value="{{ old('price_min', $product->price_min) }}" required>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none text-slate-500 font-bold bg-slate-100 border-l border-slate-300 rounded-r-lg px-3">VNĐ</div>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Mô tả ngắn</label>
                            <textarea name="short_description" rows="3" 
                                class="w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 shadow-sm">{{ old('short_description', $product->short_description) }}</textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Mô tả chi tiết</label>
                            <textarea name="description" id="editor">{{ old('description', $product->description) }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- 2. Quản lý Biến thể (Variants) -->
                <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
                    <div class="flex justify-between items-center mb-5 pb-3 border-b border-slate-100">
                        <h3 class="text-lg font-bold text-slate-800 flex items-center">
                            <i class="fa-solid fa-list-ul mr-2 text-indigo-500"></i> Các phiên bản
                        </h3>
                        <button type="button" @click="openCreateModal()" class="px-4 py-2 bg-indigo-50 text-indigo-600 rounded-lg text-sm font-bold hover:bg-indigo-100 transition-colors flex items-center border border-indigo-200 shadow-sm">
                            <i class="fa-solid fa-plus mr-1"></i> Thêm phiên bản
                        </button>
                    </div>

                    <!-- Danh sách biến thể -->
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
                                                    <span class="text-slate-400 mr-1">{{ $val->attribute->name }}:</span> {{ $val->value }}
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
                                        <div class="flex justify-end gap-1 opacity-60 group-hover:opacity-100 transition-opacity">
                                            <!-- Edit Btn -->
                                            <button type="button" 
                                                @click="openEditModal({{ $variant->id }}, '{{ $variant->sku }}', {{ $variant->stock_quantity }}, {{ $variant->original_price }}, {{ $variant->sale_price }}, {{ $variant->attributeValues->pluck('id')->toJson() }})"
                                                class="text-white bg-indigo-500 hover:bg-indigo-600 p-1.5 rounded shadow-sm transition-colors" title="Sửa">
                                                <i class="fa-solid fa-pen text-xs"></i>
                                            </button>
                                            <!-- Delete Btn -->
                                            <button type="button" onclick="deleteVariant({{ $variant->id }})" class="text-white bg-rose-500 hover:bg-rose-600 p-1.5 rounded shadow-sm transition-colors" title="Xóa">
                                                <i class="fa-solid fa-trash-can text-xs"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center text-slate-400 italic bg-slate-50">
                                        Chưa có biến thể nào.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- 3. Quản lý Ảnh -->
                <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
                    <h3 class="text-lg font-bold text-slate-800 mb-5 pb-3 border-b border-slate-100 flex items-center">
                        <i class="fa-solid fa-images mr-2 text-indigo-500"></i> Hình ảnh
                    </h3>
                    
                    <!-- Thumbnail -->
                    <div class="flex items-start gap-6 p-4 bg-slate-50 rounded-xl border border-slate-200 mb-8">
                        <div class="w-32 h-32 flex-shrink-0 bg-white rounded-lg border border-slate-200 p-1 shadow-sm">
                            <img src="{{ asset('storage/' . $product->thumbnail) }}" class="w-full h-full object-cover rounded-md" onerror="this.src='https://placehold.co/150x150'">
                        </div>
                        <div class="flex-1">
                            <label class="block text-sm font-bold text-slate-700 mb-2">Thay đổi ảnh đại diện</label>
                            <input type="file" name="thumbnail" class="block w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 transition-colors cursor-pointer border border-indigo-100">
                            <p class="text-xs text-slate-400 mt-2">Định dạng JPG, PNG. Tối đa 3MB.</p>
                        </div>
                    </div>

                    <!-- Gallery -->
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-3">Thư viện ảnh (Gallery)</label>
                        
                        @if(!empty($product->gallery))
                            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-4 mb-6">
                                @foreach($product->gallery as $image)
                                <div class="relative group rounded-lg overflow-hidden border border-slate-200 shadow-sm aspect-square bg-white">
                                    <img src="{{ asset('storage/' . $image) }}" class="w-full h-full object-contain">
                                    
                                    <!-- Overlay Delete -->
                                    <div class="absolute inset-0 bg-slate-900/70 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col items-center justify-center text-white cursor-pointer select-none backdrop-blur-[1px]" onclick="document.getElementById('chk-{{ $loop->index }}').click()">
                                        <i class="fa-solid fa-trash-can text-2xl mb-2 text-rose-400"></i>
                                        <span class="text-xs font-bold uppercase tracking-wider">Xóa ảnh</span>
                                        <input type="checkbox" id="chk-{{ $loop->index }}" name="remove_gallery[]" value="{{ $image }}" class="mt-2 w-5 h-5 text-rose-500 rounded focus:ring-rose-500 accent-rose-500 cursor-pointer">
                                    </div>
                                    
                                    <div class="absolute top-2 right-2 hidden peer-checked:block">
                                        <span class="bg-rose-500 text-white text-[10px] font-bold px-2 py-1 rounded-full shadow-md border border-white">ĐÃ CHỌN XÓA</span>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        @else
                            <div class="p-4 text-center border-2 border-dashed border-slate-200 rounded-lg mb-6 bg-slate-50">
                                <p class="text-sm text-slate-400 italic">Sản phẩm này chưa có ảnh phụ.</p>
                            </div>
                        @endif

                        <div class="border-t border-slate-100 pt-4">
                            <label class="block text-sm font-medium text-slate-700 mb-2">Tải thêm ảnh:</label>
                            <input type="file" name="gallery[]" multiple class="block w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 transition-colors cursor-pointer border border-emerald-100">
                        </div>
                    </div>
                </div>
            </div>

            <!-- CỘT PHỤ (PHẢI) -->
            <div class="w-full lg:w-1/3 flex flex-col gap-6">
                <!-- Actions -->
                <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
                    <h3 class="font-bold text-slate-800 mb-4 pb-2 border-b border-slate-100">Cập nhật</h3>
                    
                    <div class="mb-5">
                        <label class="block text-sm font-bold text-slate-700 mb-2">Trạng thái</label>
                        <select name="status" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 cursor-pointer shadow-sm">
                            <option value="published" {{ $product->status == 'published' ? 'selected' : '' }} class="font-bold text-emerald-600">Đang bán</option>
                            <option value="draft" {{ $product->status == 'draft' ? 'selected' : '' }} class="text-slate-600">Bản nháp</option>
                            <option value="archived" {{ $product->status == 'archived' ? 'selected' : '' }} class="text-rose-500">Lưu trữ</option>
                        </select>
                    </div>

                    <div class="flex items-center mb-6 p-3 bg-indigo-50 rounded-lg border border-indigo-100">
                        <input type="checkbox" name="is_featured" class="w-5 h-5 text-indigo-600 border-slate-300 rounded focus:ring-indigo-500 cursor-pointer bg-white" {{ $product->is_featured ? 'checked' : '' }}>
                        <label class="ml-3 text-sm font-bold text-indigo-700 cursor-pointer select-none">Sản phẩm nổi bật</label>
                    </div>

                    <button type="submit" class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-lg shadow-md hover:shadow-lg transition-all transform active:scale-[0.98] flex items-center justify-center">
                        <i class="fa-solid fa-pen-to-square mr-2"></i> Cập nhật thay đổi
                    </button>
                </div>

                <!-- Phân loại -->
                <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
                    <h3 class="font-bold text-slate-800 mb-5 pb-3 border-b border-slate-100">Phân loại</h3>
                    
                    <div class="mb-6">
                        <label class="block text-sm font-bold text-slate-700 mb-2">Thương hiệu</label>
                        <select name="brand_id" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 cursor-pointer shadow-sm">
                            @foreach($brands as $brand)
                                <option value="{{ $brand->id }}" {{ $product->brand_id == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Danh mục</label>
                        <div class="max-h-64 overflow-y-auto border border-slate-300 rounded-lg p-3 bg-slate-50 space-y-2 custom-scrollbar shadow-inner">
                            @foreach($categories as $cat)
                                <div class="flex items-center p-1.5 hover:bg-white rounded transition-colors group">
                                    <input type="checkbox" name="category_ids[]" value="{{ $cat->id }}" id="cat_{{ $cat->id }}" 
                                        class="w-4 h-4 text-indigo-600 border-slate-300 rounded focus:ring-indigo-500 cursor-pointer bg-white"
                                        {{ in_array($cat->id, $selectedCategories) ? 'checked' : '' }}>
                                    <label for="cat_{{ $cat->id }}" class="ml-2 text-sm text-slate-700 group-hover:text-indigo-600 cursor-pointer w-full select-none font-medium">
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

    <!-- Hidden Form Delete -->
    <form id="delete-variant-form" method="POST" style="display: none;">
        @csrf @method('DELETE')
    </form>

    <!-- MODAL VARIANT (Dynamic) -->
    <div x-show="showVariantModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display: none;" x-cloak>
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900 bg-opacity-75 transition-opacity backdrop-blur-sm" 
                 x-show="showVariantModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" 
                 x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                 @click="showVariantModal = false"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full"
                 x-show="showVariantModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                
                <div class="bg-white px-6 pt-5 pb-4 sm:p-6 sm:pb-4 border-b border-slate-100">
                    <h3 class="text-xl leading-6 font-bold text-slate-800 flex items-center">
                        <i class="fa-solid fa-cube mr-2 text-indigo-500"></i> 
                        <span x-text="isEditMode ? 'Cập nhật phiên bản' : 'Tạo phiên bản mới'"></span>
                    </h3>
                </div>

                <form :action="variantAction" method="POST" enctype="multipart/form-data">
                    @csrf
                    <template x-if="isEditMode">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    <div class="px-6 py-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Attributes -->
                            <div class="md:col-span-2">
                                <label class="block text-sm font-bold text-slate-700 mb-3">Thuộc tính phiên bản</label>
                                <div class="grid grid-cols-2 gap-4 bg-slate-50 p-4 rounded-xl border border-slate-200 shadow-inner">
                                    @foreach($attributes as $attr)
                                        <div>
                                            <span class="block text-xs font-bold text-slate-500 uppercase mb-1 tracking-wider">{{ $attr->name }}</span>
                                            <select name="attribute_values[]" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-sm px-3 py-2 bg-white">
                                                <option value=""> Chọn </option>
                                                @foreach($attr->values as $val)
                                                    <option value="{{ $val->id }}" 
                                                        :selected="isEditMode && variantData.attributes.includes({{ $val->id }})">
                                                        {{ $val->value }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- SKU & Stock -->
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">SKU (Mã phiên bản)</label>
                                <input type="text" name="sku" x-model="variantData.sku" class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 shadow-sm px-4 py-2" required placeholder="VD: NIKE-RED-40">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Tồn kho</label>
                                <input type="number" name="stock_quantity" x-model="variantData.stock" class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 shadow-sm px-4 py-2" required min="0">
                            </div>

                            <!-- Prices -->
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Giá gốc (VNĐ)</label>
                                <input type="number" name="original_price" x-model="variantData.original_price" class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 shadow-sm px-4 py-2">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Giá bán thực tế (VNĐ) <span class="text-rose-500">*</span></label>
                                <input type="number" name="sale_price" x-model="variantData.sale_price" class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 shadow-sm px-4 py-2 font-bold text-slate-800" required>
                            </div>

                            <!-- Image -->
                            <div class="md:col-span-2">
                                <label class="block text-sm font-bold text-slate-700 mb-2">Ảnh phiên bản (Tùy chọn)</label>
                                <input type="file" name="variant_image" class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer border border-indigo-100 rounded-lg">
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-slate-50 px-6 py-4 sm:flex sm:flex-row-reverse border-t border-slate-200">
                        <button type="submit" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2.5 bg-indigo-600 text-base font-bold text-white hover:bg-indigo-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm transition-all transform active:scale-95" x-text="isEditMode ? 'Cập nhật' : 'Lưu mới'"></button>
                        <button type="button" @click="showVariantModal = false" class="mt-3 w-full inline-flex justify-center rounded-lg border border-slate-300 shadow-sm px-4 py-2.5 bg-white text-base font-medium text-slate-700 hover:bg-slate-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-colors">Hủy</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
<script>
    ClassicEditor.create(document.querySelector('#editor')).catch(error => { console.error(error); });

    function deleteVariant(id) {
        if(confirm('Bạn có chắc chắn muốn xóa biến thể này không? Hành động không thể hoàn tác.')) {
            const form = document.getElementById('delete-variant-form');
            form.action = `/admin/products/variants/${id}`;
            form.submit();
        }
    }
</script>
<style>
    .ck-editor__editable_inline { min-height: 250px; }
    .custom-scrollbar::-webkit-scrollbar { width: 5px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background-color: #cbd5e1; border-radius: 10px; }
</style>
@endsection