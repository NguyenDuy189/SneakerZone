@extends('client.layouts.app')

@section('title', 'Giỏ hàng của bạn')

@section('content')

{{-- DATA ALPINEJS QUẢN LÝ GIỎ HÀNG --}}
<div x-data="cartManager({
        csrfToken: '{{ csrf_token() }}',
        updateRoute: '{{ route('client.cart.update') }}',
        removeRoute: '{{ route('client.cart.remove', ':id') }}',
        applyRoute: '{{ route('client.cart.apply_discount') }}',
        removeDiscountRoute: '{{ route('client.cart.remove_discount') }}',
        
        {{-- Tính toán Subtotal ban đầu từ PHP --}}
        subtotal: {{ isset($cartItems) ? $cartItems->sum(fn($i) => ($i->variant->price ?: $i->variant->product->price_min) * $i->quantity) : 0 }},
        
        {{-- Lấy dữ liệu Discount từ DB Cart --}}
        discount: {{ $cart->discount_amount ?? 0 }},
        currentCode: '{{ $cart->discount_code ?? '' }}'
    })" 
    class="bg-[#F8F9FA] min-h-screen pb-20 font-sans">

    <div class="container mx-auto px-4 max-w-6xl">
        
        <h1 class="text-2xl font-black uppercase tracking-tight mb-8 flex items-center gap-3 pt-6">
            <i class="fa-solid fa-bag-shopping text-indigo-600"></i> Giỏ hàng của bạn
        </h1>

        @if(isset($cartItems) && $cartItems->count() > 0)
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                
                {{-- === DANH SÁCH SẢN PHẨM (8 cột) === --}}
                <div class="lg:col-span-8 space-y-4">
                    
                    {{-- Header Table --}}
                    <div class="hidden md:grid grid-cols-12 gap-4 text-xs font-bold text-slate-400 uppercase tracking-wider px-4">
                        <div class="col-span-5">Sản phẩm</div>
                        <div class="col-span-2 text-center">Đơn giá</div>
                        <div class="col-span-2 text-center">Số lượng</div>
                        <div class="col-span-3 text-right">Tạm tính</div>
                    </div>

                    {{-- Loop Items --}}
                    @foreach($cartItems as $item)
                        @php
                            $product = $item->variant->product;
                            $price = $item->variant->price ?: $product->price_min;
                        @endphp

                        <div class="bg-white rounded-xl p-4 shadow-sm border border-slate-100 relative group transition-all hover:shadow-md"
                            id="cart-item-{{ $item->id }}">
                            
                            {{-- Loading Overlay --}}
                            <div class="absolute inset-0 bg-white/60 z-10 flex items-center justify-center backdrop-blur-[1px]" 
                                x-show="isLoading === {{ $item->id }}" x-transition style="display: none;">
                                <i class="fa-solid fa-circle-notch fa-spin text-indigo-600 text-xl"></i>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-center">
                                
                                {{-- 1. Info Sản phẩm --}}
                                <div class="md:col-span-5 flex gap-4">
                                    {{-- Ảnh --}}
                                    <div class="w-20 h-20 rounded-lg bg-slate-50 overflow-hidden border border-slate-200 flex-shrink-0">
                                        <img src="{{ $product->image ? asset('storage/'.$product->image) : asset('img/no-image.png') }}" 
                                             class="w-full h-full object-cover mix-blend-multiply">
                                    </div>
                                    
                                    {{-- Tên & Biến thể --}}
                                    <div class="flex flex-col justify-center min-w-0">
                                        <a href="{{ route('client.products.show', $product->slug ?? $product->id) }}" 
                                           class="font-bold text-slate-900 hover:text-indigo-600 truncate transition-colors" title="{{ $product->name }}">
                                            {{ $product->name }}
                                        </a>
                                        
                                        <div class="flex flex-wrap gap-1 mt-1">
                                            @foreach($item->variant->attributeValues as $val)
                                                <span class="text-[10px] font-bold text-slate-500 bg-slate-100 px-1.5 py-0.5 rounded uppercase whitespace-nowrap">
                                                    {{ $val->attribute->name }}: {{ $val->value }}
                                                </span>
                                            @endforeach
                                        </div>

                                        <div class="md:hidden mt-2 text-sm font-bold text-slate-900">
                                            {{ number_format($price) }}đ
                                        </div>
                                    </div>
                                </div>

                                {{-- 2. Đơn giá (Desktop) --}}
                                <div class="hidden md:block md:col-span-2 text-center text-sm font-medium text-slate-600">
                                    {{ number_format($price) }}đ
                                </div>

                                {{-- 3. Bộ tăng giảm số lượng --}}
                                <div class="md:col-span-2 flex justify-center">
                                    <div class="flex items-center border border-slate-200 rounded-lg h-9 w-24">
                                        <button @click="updateQty({{ $item->id }}, -1)" 
                                                class="w-7 h-full flex items-center justify-center text-slate-400 hover:text-slate-900 hover:bg-slate-50 rounded-l-lg transition-colors">
                                            <i class="fa-solid fa-minus text-[10px]"></i>
                                        </button>
                                        
                                        {{-- QUAN TRỌNG: Đã thêm id="qty-..." --}}
                                        <input type="number" 
                                               id="qty-{{ $item->id }}" 
                                               value="{{ $item->quantity }}" 
                                               readonly
                                               class="w-full h-full text-center text-xs font-bold text-slate-900 bg-transparent outline-none">
                                        
                                        <button @click="updateQty({{ $item->id }}, 1)" 
                                                class="w-7 h-full flex items-center justify-center text-slate-400 hover:text-slate-900 hover:bg-slate-50 rounded-r-lg transition-colors">
                                            <i class="fa-solid fa-plus text-[10px]"></i>
                                        </button>
                                    </div>
                                </div>

                                {{-- 4. Thành tiền & Nút xóa --}}
                                <div class="md:col-span-3 flex items-center justify-between md:justify-end gap-6 pl-2">
                                    <span class="text-sm font-black text-indigo-600 truncate" id="item-subtotal-{{ $item->id }}">
                                        {{ number_format($item->quantity * $price) }}đ
                                    </span>
                                    
                                    <button @click="removeItem({{ $item->id }})" 
                                            class="text-slate-300 hover:text-rose-500 transition-colors p-2 flex-shrink-0"
                                            title="Xóa sản phẩm">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    {{-- Nút Quay lại Shop --}}
                    <div class="mt-6">
                        <a href="{{ route('client.products.index') }}" class="text-xs font-bold text-slate-500 hover:text-indigo-600 flex items-center gap-1">
                            <i class="fa-solid fa-arrow-left"></i> Tiếp tục mua sắm
                        </a>
                    </div>
                </div>

                {{-- === SUMMARY BOX (Cột phải - 4 cột) === --}}
                <div class="lg:col-span-4">
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 sticky top-24">
                        <h3 class="text-lg font-black text-slate-900 uppercase mb-6">Tổng đơn hàng</h3>

                        {{-- FORM NHẬP MÃ --}}
                        <div class="mb-6">
                            <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Mã giảm giá</label>
                            
                            {{-- Input --}}
                            <div x-show="!currentCode" class="flex gap-2 transition-all">
                                <input type="text" x-model="inputCode" 
                                    @keydown.enter.prevent="applyDiscount()"
                                    placeholder="Nhập mã giảm giá" 
                                    class="flex-1 bg-slate-50 border border-slate-200 rounded-lg px-3 text-sm font-bold uppercase focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                                <button @click="applyDiscount()" 
                                        class="bg-slate-900 text-white px-4 py-2.5 rounded-lg text-xs font-bold uppercase tracking-wide hover:bg-indigo-600 transition-colors shadow-lg shadow-slate-900/10">
                                    Áp dụng
                                </button>
                            </div>

                            {{-- Hiển thị mã đã dùng --}}
                            <div x-show="currentCode" style="display: none;" 
                                class="flex justify-between items-center bg-emerald-50 border border-emerald-100 p-3 rounded-lg mt-2 animate-pulse-once">
                                <div class="flex items-center gap-2">
                                    <i class="fa-solid fa-ticket text-emerald-600"></i>
                                    <span class="font-bold text-emerald-700 text-sm" x-text="currentCode"></span>
                                </div>
                                <button @click="removeDiscount()" class="text-xs font-bold text-rose-500 hover:text-rose-700">
                                    <i class="fa-solid fa-xmark mr-1"></i>Gỡ
                                </button>
                            </div>

                            {{-- Thông báo lỗi/thành công --}}
                            <p x-show="msgText" x-text="msgText" class="text-[11px] font-bold mt-2"
                            :class="msgType === 'success' ? 'text-emerald-600' : 'text-rose-500'"></p>
                        </div>

                        <hr class="border-dashed border-slate-200 my-6">

                        {{-- TÍNH TIỀN --}}
                        <div class="space-y-3">
                            <div class="flex justify-between text-sm text-slate-600">
                                <span>Tạm tính</span>
                                <span class="font-bold" x-text="formatMoney(subtotal)"></span>
                            </div>
                            
                            <div class="flex justify-between text-sm text-emerald-600" x-show="discount > 0">
                                <span>Giảm giá</span>
                                <span class="font-bold" x-text="'-' + formatMoney(discount)"></span>
                            </div>

                            <div class="flex justify-between text-base font-black text-slate-900 pt-3 border-t border-slate-100">
                                <span>Tổng cộng</span>
                                <span class="text-indigo-600 text-xl" x-text="formatMoney(subtotal - discount)"></span>
                            </div>
                        </div>

                        {{-- Nút Thanh toán --}}
                        <a href="{{ route('client.checkouts.index') }}" 
                           class="block w-full py-4 mt-6 bg-slate-900 text-white font-bold text-center rounded-xl hover:bg-indigo-600 transition-all shadow-lg shadow-indigo-200 uppercase tracking-widest text-xs">
                            Thanh toán ngay
                        </a>

                        <div class="mt-4 flex items-center justify-center gap-2 text-slate-400 text-xl opacity-70">
                            <i class="fa-brands fa-cc-visa"></i>
                            <i class="fa-brands fa-cc-mastercard"></i>
                            <i class="fa-brands fa-cc-paypal"></i>
                        </div>
                    </div>
                </div>

            </div>
        @else
            {{-- EMPTY STATE (Khi giỏ hàng trống) --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-12 text-center my-10">
                <div class="w-24 h-24 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-6 text-slate-300">
                    <i class="fa-solid fa-basket-shopping text-4xl"></i>
                </div>
                <h2 class="text-xl font-bold text-slate-900 mb-2">Giỏ hàng của bạn đang trống</h2>
                <p class="text-slate-500 mb-8 max-w-md mx-auto">Có vẻ như bạn chưa chọn được món đồ nào ưng ý. Hãy dạo một vòng cửa hàng để tìm kiếm đôi giày yêu thích nhé!</p>
                <a href="{{ route('client.products.index') }}" class="inline-flex items-center justify-center px-8 py-3 bg-slate-900 text-white font-bold rounded-xl hover:bg-indigo-600 transition-colors uppercase text-xs tracking-widest">
                    Mua sắm ngay
                </a>
            </div>
        @endif

    </div>
</div>

{{-- SCRIPT XỬ LÝ CART --}}
<script>
    function cartManager(config) {
        return {
            isLoading: null, 
            subtotal: config.subtotal, 
            discount: config.discount,
            currentCode: config.currentCode,
            
            inputCode: '', 
            msgText: '',
            msgType: '',

            formatMoney(amount) {
                return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount);
            },

            // --- 1. CẬP NHẬT SỐ LƯỢNG ---
            updateQty(itemId, change) {
                let inputEl = document.getElementById('qty-' + itemId); // Đã sửa view để có ID này
                if(!inputEl) return;

                let currentQty = parseInt(inputEl.value);
                let newQty = currentQty + change;

                if (newQty < 1) return;

                this.isLoading = itemId;

                fetch(config.updateRoute, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': config.csrfToken
                    },
                    body: JSON.stringify({
                        id: itemId,
                        quantity: newQty
                    })
                })
                .then(res => res.json())
                .then(data => {
                    this.isLoading = null;

                    if (data.status === 'success') {
                        // Cập nhật input
                        inputEl.value = newQty;

                        // Cập nhật giá dòng đó
                        let itemSubtotalEl = document.getElementById('item-subtotal-' + itemId);
                        if(itemSubtotalEl) {
                            itemSubtotalEl.innerText = this.formatMoney(data.item_subtotal);
                        }

                        // Cập nhật Tổng tiền (Ưu tiên dùng cart_subtotal từ server trả về)
                        if (data.cart_subtotal !== undefined) {
                            this.subtotal = data.cart_subtotal;
                            
                            // Cập nhật lại discount nếu server tính lại
                            if(data.discount_amount !== undefined) {
                                this.discount = data.discount_amount;
                            }
                        } 
                        // Fallback nếu controller chưa sửa (chỉ trả về cart_total)
                        else if (data.cart_total !== undefined) {
                            this.subtotal = data.cart_total + this.discount;
                        }
                    } else {
                        alert(data.message);
                        inputEl.value = currentQty;
                    }
                })
                .catch(err => {
                    this.isLoading = null;
                    console.error(err);
                });
            },

            // --- 2. XÓA SẢN PHẨM ---
            removeItem(itemId) {
                if(!confirm('Bạn có chắc muốn xóa sản phẩm này?')) return;
                this.isLoading = itemId;

                let url = config.removeRoute.replace(':id', itemId);

                fetch(url, {
                    method: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': config.csrfToken,
                        'Accept': 'application/json'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        let itemRow = document.getElementById('cart-item-' + itemId);
                        if(itemRow) itemRow.remove();

                        // Cập nhật lại tổng tiền
                         if (data.cart_subtotal !== undefined) {
                            this.subtotal = data.cart_subtotal;
                            if(data.discount_amount !== undefined) this.discount = data.discount_amount;
                        } else if (data.cart_total !== undefined) {
                            this.subtotal = data.cart_total + this.discount;
                        }
                        
                        // Reload nếu giỏ trống
                        if (data.item_count === 0) { // Hoặc kiểm tra logic khác
                            window.location.reload();
                        }
                    }
                });
            },

            // --- 3. VOUCHER ---
            applyDiscount() {
                if (!this.inputCode) return;

                fetch(config.applyRoute, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': config.csrfToken
                    },
                    body: JSON.stringify({ code: this.inputCode })
                })
                .then(res => res.json())
                .then(data => {
                    this.msgText = data.message;
                    this.msgType = data.status;

                    if (data.status === 'success') {
                        this.discount = data.discount;
                        this.currentCode = data.discount_code;
                        this.inputCode = ''; 
                    }
                });
            },

            removeDiscount() {
                fetch(config.removeDiscountRoute, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': config.csrfToken
                    }
                })
                .then(res => res.json())
                .then(data => {
                    this.discount = 0;
                    this.currentCode = '';
                    this.msgText = '';
                });
            }
        }
    }
</script>
@endsection