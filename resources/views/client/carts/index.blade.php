@extends('client.layouts.app') 

@section('title', 'Giỏ hàng của bạn')

@section('content')
<div class="container mx-auto px-4 md:px-6 my-8">
    {{-- Header --}}
    <div class="flex items-end gap-3 mb-6">
        <h1 class="text-3xl font-display font-black text-slate-900">Giỏ Hàng</h1>
        <span class="text-slate-500 font-medium mb-1">({{ $cart ? $cart->items->count() : 0 }} sản phẩm)</span>
    </div>

    @if($cart && $cart->items->count() > 0)
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        {{-- CỘT TRÁI: DANH SÁCH SẢN PHẨM --}}
        <div class="lg:col-span-8">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                
                {{-- Chọn tất cả --}}
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex items-center gap-3">
                    <input type="checkbox" id="selectAll" 
                        class="w-5 h-5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer"
                        {{ $cart->items->count() == $totals['count_selected'] ? 'checked' : '' }}>
                    <label for="selectAll" class="font-bold text-slate-700 cursor-pointer select-none">
                        Chọn tất cả ({{ $cart->items->count() }} sản phẩm)
                    </label>
                </div>

                {{-- List Items --}}
                <div class="divide-y divide-slate-100">
                    @foreach($cart->items as $item)
                        @php
                            // Logic lấy giá ưu tiên: Sale Price -> Price
                            $variant = $item->variant;
                            $product = $variant->product;
                            $price = $variant->sale_price > 0 
                                ? $variant->sale_price 
                                : ($variant->price ?: ($product->sale_price ?: $product->price));
                            
                            $maxStock = $variant->quantity;
                            $imagePath = $variant->image 
                                ?? $product->img_thumb 
                                ?? 'images/no-image.jpg';

                            $imageUrl = asset('img/no-image.png');

                            // 1. Ảnh variant
                            if (!empty($variant->image) && Storage::disk('public')->exists($variant->image)) {
                                $imageUrl = asset('storage/' . $variant->image);
                            }
                            // 2. Ảnh thumbnail sản phẩm
                            elseif (!empty($product->thumbnail) && Storage::disk('public')->exists($product->thumbnail)) {
                                $imageUrl = asset('storage/' . $product->thumbnail);
                            }

                        @endphp

                        <div class="p-4 md:p-6 flex gap-4 md:gap-6 group transition-colors hover:bg-slate-50/30 cart-item-row" id="item-row-{{ $item->id }}">
                            {{-- Checkbox --}}
                            <div class="flex items-center">
                                <input type="checkbox" class="item-checkbox w-5 h-5 rounded border-slate-300 text-indigo-600"
                                    data-id="{{ $item->id }}"
                                    {{ $item->is_selected ? 'checked' : '' }}>
                            </div>

                            {{-- Image --}}
                            <div class="w-20 h-20 md:w-24 md:h-24 flex-shrink-0 rounded-xl border border-slate-100 overflow-hidden bg-white">
                                <img
                                    src="{{ $imageUrl }}"
                                    alt="{{ $product->name }}"
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                    loading="lazy"
                                >
                            </div>

                            {{-- Info --}}
                            <div class="flex-grow flex flex-col justify-between">
                                <div>
                                    <h3 class="font-bold text-slate-900 line-clamp-2 hover:text-indigo-600 transition-colors">
                                        <a href="#">{{ $product->name }}</a>
                                    </h3>
                                    
                                    {{-- CODE MỚI: Duyệt qua danh sách thuộc tính động --}}
                                    <div class="flex flex-wrap gap-2 mt-1.5">
                                        @foreach($variant->attributeValues as $attributeValue)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-600 border border-slate-200">
                                                {{-- Ví dụ: Màu sắc: Đỏ hoặc Size: XL --}}
                                                {{ $attributeValue->attribute->name }}: {{ $attributeValue->value }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="flex items-end justify-between mt-2">
                                    <div class="font-display font-bold text-rose-500 text-lg">
                                        {{ number_format($price) }}đ
                                    </div>
                                </div>
                            </div>

                            {{-- Actions (Qty & Delete) --}}
                            <div class="flex flex-col items-end justify-between gap-2">
                                <button class="btn-remove-item text-slate-400 hover:text-rose-500 transition-colors p-1" data-id="{{ $item->id }}" title="Xóa">
                                    <i class="fa-regular fa-trash-can text-lg"></i>
                                </button>

                                <div class="flex items-center border border-slate-200 rounded-lg overflow-hidden bg-white h-9">
                                    <button class="btn-update-qty w-8 h-full flex items-center justify-center bg-slate-50 hover:bg-slate-100 active:bg-slate-200 text-slate-600 transition-colors"
                                        data-id="{{ $item->id }}" data-type="minus">
                                        <i class="fa-solid fa-minus text-xs"></i>
                                    </button>
                                    
                                    <input type="text" class="quantity-input w-10 h-full text-center text-sm font-semibold text-slate-900 border-x border-slate-200 focus:outline-none"
                                        value="{{ $item->quantity }}" readonly 
                                        id="qty-{{ $item->id }}" data-max="{{ $maxStock }}">
                                    
                                    <button
                                        class="btn-update-qty w-8 h-full flex items-center justify-center
                                            bg-slate-50 hover:bg-slate-100 text-slate-600 transition-colors"
                                        data-id="{{ $item->id }}"
                                        data-type="plus">
                                        <i class="fa-solid fa-plus text-xs"></i>
                                    </button>


                                </div>
                                <div class="text-rose-500 text-[10px] font-medium hidden error-msg-{{ $item->id }}"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <a href="/" class="inline-flex items-center gap-2 mt-6 text-slate-600 font-bold hover:text-indigo-600 transition-colors group">
                <i class="fa-solid fa-arrow-left group-hover:-translate-x-1 transition-transform"></i>
                Tiếp tục mua sắm
            </a>
        </div>

        {{-- CỘT PHẢI: TỔNG KẾT --}}
        <div class="lg:col-span-4">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 sticky top-28">
                <h2 class="font-display font-bold text-lg text-slate-900 mb-4">Tổng đơn hàng</h2>

                {{-- --- START: KHỐI MÃ GIẢM GIÁ --- --}}
                <div class="mb-6">
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Mã giảm giá</label>
                    
                    {{-- State 1: Đã áp dụng --}}
                    <div id="couponAppliedBlock" class="flex items-center justify-between p-3 bg-emerald-50 border border-emerald-200 rounded-lg {{ $cart->discount_code ? 'flex' : 'hidden' }}">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-tag text-emerald-600"></i>
                            <span class="font-bold text-emerald-700 uppercase" id="appliedCodeDisplay">{{ $cart->discount_code }}</span>
                        </div>
                        <button type="button" id="btnRemoveCoupon" class="text-slate-400 hover:text-rose-500 transition-colors p-1" title="Gỡ bỏ mã">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>

                    {{-- State 2: Input nhập mã --}}
                    <div id="couponInputBlock" class="flex gap-2 {{ $cart->discount_code ? 'hidden' : 'flex' }}">
                        <input type="text" id="couponCode" 
                            class="flex-1 bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block p-2.5 uppercase placeholder:normal-case transition-all"
                            placeholder="Nhập mã code">
                        <button type="button" id="btnApplyCoupon"
                            class="bg-slate-900 text-white font-bold rounded-lg text-sm px-4 py-2 hover:bg-slate-800 transition-colors whitespace-nowrap">
                            Áp dụng
                        </button>
                    </div>

                    <div id="couponMessage" class="text-xs mt-2 font-medium min-h-[16px]"></div>
                </div>
                {{-- --- END: KHỐI MÃ GIẢM GIÁ --- --}}

                <div class="border-t border-slate-100 my-4"></div>

                <div class="space-y-3 text-sm">
                    <div class="flex justify-between text-slate-600">
                        <span>Tạm tính</span>
                        <span class="font-bold text-slate-900" id="cartSubtotal">{{ $totals['subtotal_formatted'] }}</span>
                    </div>
                    <div class="flex justify-between text-emerald-600">
                        <span>Giảm giá</span>
                        <span class="font-bold" id="cartDiscount">-{{ $totals['discount_formatted'] }}</span>
                    </div>
                </div>

                <div class="border-t border-dashed border-slate-200 my-4"></div>

                <div class="flex justify-between items-end mb-6">
                    <span class="font-bold text-slate-900 text-lg">Tổng cộng</span>
                    <div class="text-right">
                        <span class="block font-display font-black text-2xl text-indigo-600" id="cartTotal">{{ $totals['total_formatted'] }}</span>
                        <span class="text-xs text-slate-400 font-medium">(Đã bao gồm VAT)</span>
                    </div>
                </div>

                <a href="{{ route('client.checkouts.index') }}" id="btnCheckout"
                   class="block w-full py-4 bg-indigo-600 hover:bg-indigo-700 text-white text-center rounded-xl font-bold uppercase tracking-wide transition-all shadow-lg shadow-indigo-200 {{ $totals['count_selected'] == 0 ? 'opacity-50 pointer-events-none grayscale' : '' }}">
                    Thanh toán ngay
                </a>
            </div>
        </div>
    </div>

    @else
    {{-- GIỎ HÀNG TRỐNG --}}
    <div class="flex flex-col items-center justify-center py-20 bg-white rounded-3xl border border-dashed border-slate-200">
        <div class="w-40 h-40 bg-slate-50 rounded-full flex items-center justify-center mb-6">
            <i class="fa-solid fa-cart-arrow-down text-6xl text-slate-300"></i>
        </div>
        <h2 class="text-2xl font-bold text-slate-900 mb-2">Giỏ hàng đang trống</h2>
        <p class="text-slate-500 mb-8 max-w-md text-center">Chưa có sản phẩm nào trong giỏ hàng của bạn. Hãy dạo một vòng và chọn cho mình sản phẩm ưng ý nhé!</p>
        <a href="{{ route('client.products.index') }}" class="px-8 py-3 bg-slate-900 text-white rounded-full font-bold hover:bg-indigo-600 transition-colors shadow-lg">
            Khám phá sản phẩm
        </a>
    </div>
    @endif
</div>

{{-- JAVASCRIPT --}}
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

@push('scripts')
<script>
    $(document).ready(function() {
        // Setup CSRF Token cho tất cả Ajax request
        $.ajaxSetup({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
        });

        // --- HELPER FUNCTIONS ---
        
        // Cập nhật text giá trị tiền tệ
        function updateCartUI(data) {
            $('#cartSubtotal').text(data.subtotal_formatted);
            $('#cartDiscount').text('-' + data.discount_formatted);
            $('#cartTotal').text(data.total_formatted);
            
            // Disable nút thanh toán nếu không chọn sản phẩm nào
            if(data.count_selected > 0) {
                $('#btnCheckout').removeClass('opacity-50 pointer-events-none grayscale');
            } else {
                $('#btnCheckout').addClass('opacity-50 pointer-events-none grayscale');
            }
        }

        // Chuyển đổi giao diện Coupon (Nhập <-> Đã áp dụng)
        function toggleCouponUI(isApplied, code = '') {
            if (isApplied) {
                $('#couponInputBlock').addClass('hidden').removeClass('flex');
                $('#couponAppliedBlock').removeClass('hidden').addClass('flex');
                $('#appliedCodeDisplay').text(code);
                $('#couponCode').val(''); // Clear input
            } else {
                $('#couponAppliedBlock').addClass('hidden').removeClass('flex');
                $('#couponInputBlock').removeClass('hidden').addClass('flex');
                $('#couponCode').val('');
                $('#couponMessage').text('');
            }
        }

        // --- EVENT HANDLERS ---

        // 1. CHỌN SẢN PHẨM LẺ
        $('.item-checkbox').on('change', function() {
            let itemId = $(this).data('id');
            let isSelected = $(this).is(':checked');
            let allChecked = $('.item-checkbox').length === $('.item-checkbox:checked').length;
            $('#selectAll').prop('checked', allChecked);

            $.post("{{ route('client.carts.select') }}", { item_id: itemId, selected: isSelected, is_all: 0 }, function(res) {
                if(res.status === 'success') updateCartUI(res.data);
            });
        });

        // 2. CHỌN TẤT CẢ
        $('#selectAll').on('change', function() {
            let isSelected = $(this).is(':checked');
            $('.item-checkbox').prop('checked', isSelected);
            $.post("{{ route('client.carts.select') }}", { selected: isSelected, is_all: 1 }, function(res) {
                if(res.status === 'success') updateCartUI(res.data);
            });
        });

        // 3. CẬP NHẬT SỐ LƯỢNG (+/-)
        $('.btn-update-qty').on('click', function () {
            let btn = $(this);
            let type = btn.data('type');
            let itemId = btn.data('id');
            let input = $('#qty-' + itemId);
            let currentQty = parseInt(input.val());
            let maxStock = parseInt(input.data('max'));

            let newQty = type === 'plus' ? currentQty + 1 : currentQty - 1;
            if (newQty < 1) return;

            $('.error-msg-' + itemId).addClass('hidden').text('');

            $.post("{{ route('client.carts.update') }}", {
                item_id: itemId,
                quantity: newQty
            }, function (res) {
                if (res.status === 'success') {
                    input.val(newQty);
                    updateCartUI(res.data);

                    // Xử lý enable / disable nút +
                    let plusBtn = btn.parent().find('[data-type="plus"]');
                    if (newQty >= maxStock) {
                        plusBtn.prop('disabled', true);
                    } else {
                        plusBtn.prop('disabled', false);
                    }
                } else {
                    $('.error-msg-' + itemId).removeClass('hidden').text(res.message);
                }
            });
        });


        // 4. XÓA SẢN PHẨM
        $('.btn-remove-item').on('click', function() {
            if(!confirm('Xóa sản phẩm này khỏi giỏ?')) return;
            let btn = $(this);
            let itemId = btn.data('id');
            
            $.post("{{ route('client.carts.remove') }}", { item_id: itemId }, function(res) {
                if (res.status === 'success') {
                    $('#item-row-' + itemId).fadeOut(300, function() { $(this).remove(); });
                    updateCartUI(res.data);
                    // Reload nếu hết sạch sản phẩm
                    if ($('.cart-item-row').length <= 1) location.reload();
                }
            });
        });

        // 5. ÁP DỤNG MÃ GIẢM GIÁ
        function applyCoupon() {
            let input = $('#couponCode');
            let code = input.val().trim();
            let btn = $('#btnApplyCoupon');
            let msgBox = $('#couponMessage');

            // Reset UI lỗi
            input.removeClass('border-rose-500 ring-rose-500 text-rose-500').addClass('border-slate-200');

            if(!code) {
                msgBox.removeClass('text-emerald-600').addClass('text-rose-500').text('Vui lòng nhập mã!');
                input.addClass('border-rose-500 ring-rose-500 text-rose-500').removeClass('border-slate-200').focus();
                return;
            }

            let originalText = btn.text();
            btn.html('<i class="fa-solid fa-circle-notch fa-spin"></i>').prop('disabled', true);
            msgBox.text('');

            $.post("{{ route('client.carts.apply_discount') }}", { code: code }, function(res) {
                btn.text(originalText).prop('disabled', false);
                
                if (res.status === 'success') {
                    msgBox.removeClass('text-rose-500').addClass('text-emerald-600').text(res.message);
                    updateCartUI(res.data);
                    toggleCouponUI(true, code); // Switch giao diện
                } else {
                    msgBox.removeClass('text-emerald-600').addClass('text-rose-500').text(res.message);
                    input.addClass('border-rose-500 ring-rose-500 text-rose-500').removeClass('border-slate-200');
                    if(res.data) updateCartUI(res.data);
                }
            }).fail(function() {
                btn.text(originalText).prop('disabled', false);
                msgBox.addClass('text-rose-500').text('Lỗi kết nối server.');
            });
        }

        // Bắt sự kiện Click Button
        $('#btnApplyCoupon').on('click', applyCoupon);
        
        // Bắt sự kiện Enter Input
        $('#couponCode').on('keypress', function(e) {
            if(e.which == 13) applyCoupon();
        });

        // 6. GỠ BỎ MÃ GIẢM GIÁ
        $(document).on('click', '#btnRemoveCoupon', function() {
            if(!confirm('Bạn muốn gỡ mã giảm giá này?')) return;
            
            let btn = $(this);
            let icon = btn.find('i');
            icon.attr('class', 'fa-solid fa-circle-notch fa-spin'); // Loading icon

            // Gửi code rỗng để Controller hiểu là gỡ mã
            $.post("{{ route('client.carts.apply_discount') }}", { code: '' }, function(res) {
                updateCartUI(res.data);
                toggleCouponUI(false); 
                icon.attr('class', 'fa-solid fa-xmark');
            });
        });
    });
</script>
@endpush
@endsection