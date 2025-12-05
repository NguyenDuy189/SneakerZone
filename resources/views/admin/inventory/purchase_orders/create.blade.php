@extends('admin.layouts.app')
@section('title', 'Tạo phiếu nhập hàng')

@section('content')
<div class="container px-6 mx-auto mb-20 fade-in" 
     x-data="purchaseOrder()"
     x-init="
        // Truyền errors từ Laravel vào Alpine Store
        $store.errors = {{ json_encode($errors->messages(), JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT) }}
     ">

    <div class="flex items-center gap-4 my-6">
        <a href="{{ route('admin.purchase_orders.index') }}" class="w-10 h-10 flex items-center justify-center rounded-xl bg-white border border-slate-200 text-slate-500 hover:bg-slate-50 hover:text-indigo-600 transition-all shadow-sm">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <h2 class="text-2xl font-bold text-slate-800">Tạo phiếu nhập hàng</h2>
    </div>

    <form action="{{ route('admin.purchase_orders.store') }}" method="POST">
        @csrf
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- CỘT TRÁI --}}
            <div class="lg:col-span-2 bg-white p-6 rounded-2xl shadow-sm border border-slate-200">

                <div class="flex justify-between items-center mb-4">
                    <h3 class="font-bold text-slate-800">Danh sách sản phẩm</h3>
                    <button type="button"
                        @click="addItem()"
                        class="text-sm bg-indigo-50 text-indigo-600 px-3 py-1.5 rounded-lg font-bold hover:bg-indigo-100 transition border border-indigo-100">
                        <i class="fa-solid fa-plus mr-1"></i> Thêm dòng
                    </button>
                </div>

                <div class="space-y-3">

                    {{-- HEADER --}}
                    <div class="grid grid-cols-12 gap-3 text-xs font-bold text-slate-500 uppercase px-2">
                        <div class="col-span-5">Sản phẩm</div>
                        <div class="col-span-2 text-center">Số lượng</div>
                        <div class="col-span-3 text-right">Giá nhập</div>
                        <div class="col-span-2 text-right">Thành tiền</div>
                    </div>

                    {{-- ROWS --}}
                    <template x-for="(item, index) in items" :key="index">
                        <div class="grid grid-cols-12 gap-3 items-start p-3 border border-slate-200 rounded-xl bg-slate-50/50 hover:bg-white transition-colors relative group">

                            {{-- PRODUCT --}}
                            <div class="col-span-5">
                                <select :name="`items[${index}][variant_id]`"
                                        x-model="item.variant_id"
                                        class="w-full text-sm border-slate-200 rounded-lg focus:ring-indigo-500 py-2">
                                    <option value="">-- Chọn sản phẩm --</option>
                                    @foreach($products as $p)
                                        <option value="{{ $p['id'] }}">{{ $p['name'] }} (SKU: {{ $p['sku'] }})</option>
                                    @endforeach
                                </select>

                                {{-- ERROR --}}
                                <template x-if="$store.errors['items.' + index + '.variant_id']">
                                    <p class="text-sm text-rose-600 mt-1"
                                       x-text="$store.errors['items.' + index + '.variant_id'][0]"></p>
                                </template>
                            </div>

                            {{-- QUANTITY --}}
                            <div class="col-span-2">
                                <input type="number"
                                       min="1"
                                       :name="`items[${index}][quantity]`"
                                       x-model="item.quantity"
                                       class="w-full text-sm border-slate-200 rounded-lg text-center py-2">

                                {{-- ERROR --}}
                                <template x-if="$store.errors['items.' + index + '.quantity']">
                                    <p class="text-sm text-rose-600 mt-1"
                                       x-text="$store.errors['items.' + index + '.quantity'][0]"></p>
                                </template>
                            </div>

                            {{-- PRICE --}}
                            <div class="col-span-3">
                                <input type="number"
                                       min="0"
                                       :name="`items[${index}][import_price]`"
                                       x-model="item.import_price"
                                       class="w-full text-sm border-slate-200 rounded-lg text-right py-2">

                                {{-- ERROR --}}
                                <template x-if="$store.errors['items.' + index + '.import_price']">
                                    <p class="text-sm text-rose-600 mt-1"
                                       x-text="$store.errors['items.' + index + '.import_price'][0]"></p>
                                </template>
                            </div>

                            {{-- TOTAL --}}
                            <div class="col-span-2 text-right font-bold text-slate-800 text-sm">
                                <span x-text="formatMoney(item.quantity * item.import_price)"></span>
                            </div>

                            {{-- DELETE --}}
                            <button type="button"
                                @click="removeItem(index)"
                                class="absolute -right-2 -top-2 bg-white text-rose-500 hover:text-white hover:bg-rose-500 border border-slate-200 rounded-full w-6 h-6 flex items-center justify-center shadow-sm opacity-0 group-hover:opacity-100 transition-all">
                                <i class="fa-solid fa-xmark text-xs"></i>
                            </button>
                        </div>
                    </template>
                </div>

                {{-- TOTAL --}}
                <div class="mt-6 flex justify-end items-center gap-4 border-t border-slate-100 pt-4">
                    <span class="text-sm font-bold text-slate-500">Tổng cộng:</span>
                    <span class="text-2xl font-extrabold text-indigo-600" 
                          x-text="formatMoney(totalAmount)">0 ₫</span>
                </div>
            </div>

            {{-- CỘT PHẢI --}}
            <div class="space-y-6">
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
                    <h3 class="font-bold text-slate-800 mb-4">Thông tin phiếu</h3>

                    <div class="space-y-4">

                        {{-- SUPPLIER --}}
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">
                                Nhà cung cấp <span class="text-rose-500">*</span>
                            </label>
                            <select name="supplier_id" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm">
                                <option value="">-- Chọn NCC --</option>
                                @foreach($suppliers as $sup)
                                    <option value="{{ $sup->id }}">{{ $sup->name }}</option>
                                @endforeach
                            </select>

                            @error('supplier_id')
                                <p class="text-sm text-rose-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- EXPECTED DATE --}}
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">
                                Ngày dự kiến về
                            </label>
                            <input type="date" name="expected_at"
                                   class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm">

                            @error('expected_at')
                                <p class="text-sm text-rose-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- NOTE --}}
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">Ghi chú</label>
                            <textarea name="note" rows="3"
                                class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm"
                                placeholder="VD: Nhập hàng Tết..."></textarea>

                            @error('note')
                                <p class="text-sm text-rose-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- SUBMIT --}}
                    <button type="submit"
                        class="w-full mt-6 py-3 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl shadow-lg transition-all active:scale-95 flex items-center justify-center gap-2">
                        <i class="fa-solid fa-floppy-disk"></i> Lưu phiếu (Draft)
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

{{-- ALPINE --}}
<script>
document.addEventListener('alpine:init', () => {
    Alpine.store('errors', {})

    Alpine.data('purchaseOrder', () => ({
        items: [{ variant_id: '', quantity: 1, import_price: 0 }],

        addItem() {
            this.items.push({ variant_id: '', quantity: 1, import_price: 0 });
        },

        removeItem(i) {
            if (this.items.length > 1) this.items.splice(i, 1);
        },

        get totalAmount() {
            return this.items.reduce((s, i) => s + (i.quantity * i.import_price), 0);
        },

        formatMoney(v) {
            return new Intl.NumberFormat('vi-VN', {
                style: 'currency',
                currency: 'VND'
            }).format(v);
        }
    }))
});
</script>

@endsection
