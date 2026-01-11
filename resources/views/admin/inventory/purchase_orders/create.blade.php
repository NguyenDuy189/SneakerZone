@extends('admin.layouts.app')
@section('title', 'Tạo phiếu nhập hàng')

@section('content')
<div class="container px-6 mx-auto mb-20 fade-in" x-data="purchaseOrderForm()">

    {{-- HEADER --}}
    <div class="flex items-center justify-between my-6">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.purchase_orders.index') }}" 
               class="w-10 h-10 flex items-center justify-center rounded-xl bg-white border border-slate-200 text-slate-500 hover:bg-slate-50 hover:text-indigo-600 transition-all shadow-sm">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Tạo phiếu nhập hàng</h2>
                <p class="text-slate-500 text-sm">Nhập kho sản phẩm từ nhà cung cấp</p>
            </div>
        </div>
    </div>

    <form action="{{ route('admin.purchase_orders.store') }}" method="POST">
        @csrf
        
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

            {{-- CỘT TRÁI: DANH SÁCH SẢN PHẨM (DẠNG BẢNG) --}}
            <div class="xl:col-span-2 space-y-6">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="p-5 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                        <h3 class="font-bold text-slate-800">Chi tiết đơn nhập</h3>
                        <button type="button" @click="addItem()" 
                                class="text-sm bg-indigo-600 text-white px-4 py-2 rounded-lg font-bold hover:bg-indigo-700 transition shadow-sm flex items-center gap-2">
                            <i class="fa-solid fa-plus"></i> Thêm dòng
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="text-xs font-bold text-slate-500 uppercase bg-slate-50 border-b border-slate-200">
                                    <th class="px-4 py-3 w-10">#</th>
                                    <th class="px-4 py-3 min-w-[250px]">Sản phẩm <span class="text-rose-500">*</span></th>
                                    <th class="px-4 py-3 w-32 text-center">Số lượng <span class="text-rose-500">*</span></th>
                                    <th class="px-4 py-3 w-40 text-right">Đơn giá nhập <span class="text-rose-500">*</span></th>
                                    <th class="px-4 py-3 w-40 text-right">Thành tiền</th>
                                    <th class="px-4 py-3 w-10"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <template x-for="(item, index) in items" :key="index">
                                    <tr class="hover:bg-slate-50/50 transition-colors group">
                                        {{-- STT --}}
                                        <td class="px-4 py-3 text-slate-400 font-mono text-xs" x-text="index + 1"></td>

                                        {{-- SẢN PHẨM --}}
                                        <td class="px-4 py-3">
                                            <select :name="`items[${index}][variant_id]`"
                                                    x-model="item.variant_id"
                                                    @change="updatePrice(index)"
                                                    class="w-full text-sm border-slate-200 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 py-2">
                                                <option value="">-- Chọn sản phẩm --</option>
                                                <template x-for="p in products" :key="p.id">
                                                    <option :value="p.id" 
                                                            x-text="p.name + ' (SKU: ' + p.sku + ')'"
                                                            :selected="item.variant_id == p.id"></option>
                                                </template>
                                            </select>
                                            {{-- Error Message --}}
                                            <div x-show="getError(`items.${index}.variant_id`)" class="mt-1">
                                                <p class="text-xs text-rose-500" x-text="getError(`items.${index}.variant_id`)"></p>
                                            </div>
                                        </td>

                                        {{-- SỐ LƯỢNG --}}
                                        <td class="px-4 py-3">
                                            <input type="number" min="1"
                                                   :name="`items[${index}][quantity]`"
                                                   x-model="item.quantity"
                                                   class="w-full text-sm border-slate-200 rounded-lg text-center focus:ring-indigo-500 focus:border-indigo-500 py-2">
                                             <div x-show="getError(`items.${index}.quantity`)" class="mt-1 text-center">
                                                <p class="text-xs text-rose-500" x-text="getError(`items.${index}.quantity`)"></p>
                                            </div>
                                        </td>

                                        {{-- GIÁ NHẬP --}}
                                        <td class="px-4 py-3">
                                            <input type="number" min="0"
                                                   :name="`items[${index}][import_price]`"
                                                   x-model="item.import_price"
                                                   class="w-full text-sm border-slate-200 rounded-lg text-right focus:ring-indigo-500 focus:border-indigo-500 py-2 font-mono">
                                            <div x-show="getError(`items.${index}.import_price`)" class="mt-1 text-right">
                                                <p class="text-xs text-rose-500" x-text="getError(`items.${index}.import_price`)"></p>
                                            </div>
                                        </td>

                                        {{-- THÀNH TIỀN --}}
                                        <td class="px-4 py-3 text-right font-bold text-slate-700">
                                            <span x-text="formatMoney(item.quantity * item.import_price)"></span>
                                        </td>

                                        {{-- XÓA --}}
                                        <td class="px-4 py-3 text-center">
                                            <button type="button" @click="removeItem(index)" 
                                                    class="text-slate-300 hover:text-rose-500 transition-colors w-8 h-8 rounded-full hover:bg-rose-50 flex items-center justify-center">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                            {{-- FOOTER TABLE --}}
                            <tfoot class="bg-slate-50 border-t border-slate-200">
                                <tr>
                                    <td colspan="4" class="px-4 py-4 text-right font-bold text-slate-600 uppercase text-xs tracking-wider">
                                        Tổng tiền dự kiến:
                                    </td>
                                    <td class="px-4 py-4 text-right font-extrabold text-indigo-600 text-lg">
                                        <span x-text="formatMoney(totalAmount)"></span>
                                    </td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            {{-- CỘT PHẢI: THÔNG TIN CHUNG --}}
            <div class="xl:col-span-1 space-y-6">
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 sticky top-6">
                    <h3 class="font-bold text-slate-800 mb-5 pb-3 border-b border-slate-100 flex items-center">
                        <i class="fa-regular fa-file-lines mr-2 text-indigo-500"></i> Thông tin phiếu
                    </h3>

                    <div class="space-y-5">
                        {{-- Nhà cung cấp --}}
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">
                                Nhà cung cấp <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <select name="supplier_id" class="w-full pl-4 pr-10 py-2.5 border border-slate-200 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500 appearance-none bg-white">
                                    <option value="">-- Chọn nhà cung cấp --</option>
                                    @foreach($suppliers as $sup)
                                        <option value="{{ $sup->id }}" {{ old('supplier_id') == $sup->id ? 'selected' : '' }}>
                                            {{ $sup->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-slate-500">
                                    <i class="fa-solid fa-chevron-down text-xs"></i>
                                </div>
                            </div>
                            @error('supplier_id')
                                <p class="text-xs text-rose-500 mt-1"><i class="fa-solid fa-circle-exclamation mr-1"></i> {{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Ghi chú --}}
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">Ghi chú</label>
                            <textarea name="note" rows="4" 
                                      class="w-full px-4 py-3 border border-slate-200 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500"
                                      placeholder="Ghi chú về lô hàng, người giao, v.v...">{{ old('note') }}</textarea>
                            @error('note')
                                <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Thông báo tự động --}}
                        <div class="p-4 bg-indigo-50 rounded-xl border border-indigo-100 flex items-start gap-3">
                            <i class="fa-solid fa-clock text-indigo-500 mt-0.5"></i>
                            <div>
                                <h4 class="text-sm font-bold text-indigo-700">Ngày nhập kho</h4>
                                <p class="text-xs text-indigo-600 mt-1">Hệ thống sẽ tự động ghi nhận là thời điểm hiện tại: <strong>{{ date('d/m/Y H:i') }}</strong></p>
                            </div>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="pt-2">
                            <button type="submit" 
                                    class="w-full py-3.5 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transition-all active:scale-95 flex items-center justify-center gap-2">
                                <i class="fa-solid fa-floppy-disk"></i> Lưu phiếu nhập
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </form>
</div>

{{-- SCRIPT: LOGIC ALPINEJS AN TOÀN --}}
<script>
document.addEventListener('alpine:init', () => {
    
    // 1. Dữ liệu từ Laravel được inject an toàn vào biến JS
    const serverData = {
        products: {!! json_encode($products) !!},
        oldItems: {!! json_encode(old('items')) !!},
        errors:   {!! json_encode($errors->messages()) !!}
    };

    Alpine.data('purchaseOrderForm', () => ({
        products: serverData.products,
        items: [],
        errors: serverData.errors,

        init() {
            // Nếu có dữ liệu cũ (do submit lỗi), load lại. Nếu không, tạo 1 dòng trống
            if (serverData.oldItems && serverData.oldItems.length > 0) {
                // Convert các giá trị số về dạng số chuẩn (vì old() trả về string)
                this.items = serverData.oldItems.map(item => ({
                    variant_id: item.variant_id,
                    quantity: parseInt(item.quantity) || 1,
                    import_price: parseFloat(item.import_price) || 0
                }));
            } else {
                this.addItem();
            }
        },

        addItem() {
            this.items.push({ 
                variant_id: '', 
                quantity: 1, 
                import_price: 0 
            });
        },

        removeItem(index) {
            if (this.items.length > 1) {
                this.items.splice(index, 1);
            }
        },

        updatePrice(index) {
            const selectedId = this.items[index].variant_id;
            const product = this.products.find(p => p.id == selectedId);
            
            if (product) {
                // Tự động điền giá
                this.items[index].import_price = product.price;
            } else {
                this.items[index].import_price = 0;
            }
        },

        getError(field) {
            if (this.errors && this.errors[field]) {
                return this.errors[field][0];
            }
            return null;
        },

        get totalAmount() {
            return this.items.reduce((sum, item) => {
                const q = parseFloat(item.quantity) || 0;
                const p = parseFloat(item.import_price) || 0;
                return sum + (q * p);
            }, 0);
        },

        formatMoney(amount) {
            return new Intl.NumberFormat('vi-VN', {
                style: 'currency',
                currency: 'VND'
            }).format(amount);
        }
    }));
});
</script>
@endsection