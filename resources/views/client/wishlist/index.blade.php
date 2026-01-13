@extends('client.layouts.app')

@section('title', 'Sản phẩm yêu thích')

@section('content')
<div class="bg-gray-50 py-10 min-h-[60vh]">
    <div class="container mx-auto px-4 max-w-6xl">
        
        {{-- Header Tiêu đề --}}
        <div class="mb-8 text-center" data-aos="fade-up">
            <h1 class="text-3xl font-bold text-slate-800 font-display">DANH SÁCH YÊU THÍCH</h1>
            <p class="text-slate-500 mt-2">Lưu giữ những đôi giày bạn quan tâm</p>
        </div>

        {{-- Main Box --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 min-h-[400px]" data-aos="fade-up" data-aos-delay="100">
            
            @if($wishlist->count() > 0)
                {{-- Grid sản phẩm --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                    @foreach($wishlist as $item)
                        @php 
                            $product = $item->product;
                            
                            // 1. Kiểm tra sản phẩm null (đã bị xóa trong admin nhưng wishlist chưa xóa)
                            if(!$product) continue; 

                            // 2. Xử lý ảnh thumbnail
                            $imgUrl = $product->thumbnail 
                                ? asset('storage/' . $product->thumbnail) 
                                : asset('img/no-image.png');

                            // 3. Tính tổng tồn kho từ các biến thể (variants)
                            // Lưu ý: Cần Eager Load 'variants' ở Controller để tránh query chậm
                            $totalStock = $product->variants->sum('stock_quantity');
                        @endphp

                        <div class="group relative bg-white border border-slate-100 rounded-xl overflow-hidden hover:shadow-lg transition-all duration-300" id="wishlist-item-{{ $product->id }}">
                            
                            {{-- Nút Xóa (X) --}}
                            <button onclick="removeWishlist({{ $product->id }})" 
                                    class="absolute top-3 right-3 z-10 h-8 w-8 flex items-center justify-center rounded-full bg-white/90 text-slate-400 hover:text-rose-500 hover:bg-rose-50 shadow-sm backdrop-blur-sm transition-colors"
                                    title="Xóa khỏi yêu thích">
                                <i class="fa-solid fa-xmark"></i>
                            </button>

                            {{-- Ảnh sản phẩm --}}
                            <a href="{{ route('client.products.show', $product->slug) }}" class="block relative aspect-square overflow-hidden bg-slate-50">
                                <img src="{{ $imgUrl }}" 
                                     alt="{{ $product->name }}" 
                                     class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110">
                                
                                {{-- Badge Hết hàng (Dựa trên tổng variants) --}}
                                @if($totalStock <= 0)
                                    <span class="absolute bottom-2 left-2 px-2 py-1 bg-slate-900/80 text-white text-[10px] font-bold rounded uppercase tracking-wider">Hết hàng</span>
                                @elseif($product->price_sale)
                                    <span class="absolute bottom-2 left-2 px-2 py-1 bg-rose-500 text-white text-[10px] font-bold rounded">SALE</span>
                                @endif
                            </a>

                            {{-- Thông tin chi tiết --}}
                            <div class="p-4">
                                {{-- Danh mục --}}
                                <div class="mb-1 text-[10px] text-slate-400 font-bold uppercase tracking-wider">
                                    {{ $product->category->name ?? 'Sneaker' }}
                                </div>
                                
                                {{-- Tên sản phẩm --}}
                                <h3 class="font-bold text-slate-800 truncate mb-2 text-sm hover:text-indigo-600 transition-colors">
                                    <a href="{{ route('client.products.show', $product->slug) }}" title="{{ $product->name }}">{{ $product->name }}</a>
                                </h3>

                                {{-- Giá & Nút Mua --}}
                                <div class="flex items-center justify-between mt-3">
                                    <div class="flex flex-col">
                                        @if($product->price_sale > 0 && $product->price_sale < $product->price_regular)
                                            <span class="text-rose-500 font-bold text-sm">{{ number_format($product->price_sale) }}đ</span>
                                            <span class="text-slate-400 text-[10px] line-through">{{ number_format($product->price_regular) }}đ</span>
                                        @else
                                            <span class="text-slate-800 font-bold text-sm">{{ number_format($product->price_regular) }}đ</span>
                                        @endif
                                    </div>

                                    {{-- Nút sang trang chi tiết (Vì cần chọn size) --}}
                                    <a href="{{ route('client.products.show', $product->slug) }}" 
                                       class="h-8 w-8 flex items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white transition-colors">
                                        <i class="fa-solid fa-cart-shopping text-xs"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Phân trang --}}
                <div class="mt-8">
                    {{ $wishlist->links() }}
                </div>

            @else
                {{-- Màn hình trống (Empty State) --}}
                <div class="flex flex-col items-center justify-center py-16 text-center">
                    <div class="h-24 w-24 bg-indigo-50 rounded-full flex items-center justify-center mb-6 animate-pulse">
                        <i class="fa-regular fa-heart text-4xl text-indigo-300"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-800 font-display">Danh sách trống</h3>
                    <p class="text-slate-500 mt-2 mb-8 max-w-xs mx-auto text-sm">Bạn chưa lưu sản phẩm nào cả. Hãy dạo một vòng cửa hàng để tìm đôi giày ưng ý nhé!</p>
                    <a href="{{ route('client.products.index') }}" class="px-6 py-3 bg-indigo-600 text-white font-bold rounded-xl hover:bg-indigo-700 transition-colors shadow-lg shadow-indigo-200 text-sm">
                        Mua sắm ngay
                    </a>
                </div>
            @endif

        </div>
    </div>
</div>

@push('scripts')
<script>
    function removeWishlist(productId) {
        // Sử dụng SweetAlert2 để xác nhận
        Swal.fire({
            title: 'Bỏ yêu thích?',
            text: "Sản phẩm sẽ bị xóa khỏi danh sách của bạn.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#ef4444', // Màu đỏ
            cancelButtonColor: '#94a3b8',   // Màu xám
            confirmButtonText: 'Xóa ngay',
            cancelButtonText: 'Giữ lại',
            customClass: {
                popup: 'rounded-xl'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                
                // Chuẩn bị dữ liệu gửi đi
                const formData = new FormData();
                formData.append('product_id', productId);

                // Gọi API
                fetch("{{ route('client.wishlist.toggle') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    },
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if(data.success) {
                        // 1. Tìm thẻ div sản phẩm
                        const item = document.getElementById(`wishlist-item-${productId}`);
                        
                        if(item) {
                            // 2. Hiệu ứng biến mất dần
                            item.style.transition = 'all 0.4s ease';
                            item.style.opacity = '0';
                            item.style.transform = 'scale(0.9)';
                            
                            // 3. Xóa khỏi DOM sau khi hiệu ứng chạy xong
                            setTimeout(() => {
                                item.remove();
                                
                                // 4. Kiểm tra nếu xóa hết thì reload để hiện màn hình "Danh sách trống"
                                // Chọn tất cả các thẻ có id bắt đầu bằng wishlist-item-
                                const remainingItems = document.querySelectorAll('[id^="wishlist-item-"]');
                                if(remainingItems.length === 0) {
                                    location.reload(); 
                                }
                            }, 400);
                        }
                        
                        // 5. Hiển thị thông báo nhỏ góc màn hình
                        const Toast = Swal.mixin({
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 2000,
                            timerProgressBar: true
                        });
                        
                        Toast.fire({
                            icon: 'success',
                            title: 'Đã xóa sản phẩm!'
                        });
                    } else {
                        // Xử lý nếu lỗi (ví dụ chưa đăng nhập, lỗi server)
                        Swal.fire('Lỗi!', data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire('Lỗi!', 'Đã có lỗi xảy ra, vui lòng thử lại sau.', 'error');
                });
            }
        });
    }
</script>
@endpush
@endsection