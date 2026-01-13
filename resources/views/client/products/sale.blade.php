@extends('client.layouts.app')

@section('title', $title)

@section('content')
<div class="bg-red-50 py-10 min-h-screen">
    <div class="container mx-auto px-4">
        
        {{-- Header Trang Sale --}}
        <div class="text-center mb-12">
            <h1 class="text-3xl md:text-5xl font-extrabold text-red-600 uppercase tracking-wide drop-shadow-sm">
                <i class="fa-solid fa-fire animate-bounce text-yellow-500"></i> 
                {{ $title }} 
                <i class="fa-solid fa-fire animate-bounce text-yellow-500"></i>
            </h1>
            <p class="text-slate-600 mt-3 text-lg">Cơ hội sở hữu Sneaker chính hãng với giá tốt nhất hôm nay!</p>
        </div>

        {{-- Kiểm tra danh sách sản phẩm --}}
        @if($products->isEmpty())
            <div class="flex flex-col items-center justify-center py-20 bg-white rounded-xl shadow-sm max-w-2xl mx-auto">
                <img src="https://cdn-icons-png.flaticon.com/512/2038/2038854.png" class="w-24 h-24 opacity-40 mb-4" alt="No Sale">
                <p class="text-slate-500 text-lg font-medium">Hiện tại chưa có chương trình khuyến mãi nào.</p>
                <a href="{{ route('client.home') }}" class="mt-4 px-6 py-2 bg-indigo-600 text-white rounded-full hover:bg-indigo-700 transition-colors shadow-md font-semibold">
                    Quay về trang chủ
                </a>
            </div>
        @else
            {{-- Grid Sản phẩm --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                @foreach($products as $product)
                    {{-- Lấy biến thể sale tốt nhất (đã được sort từ Controller) --}}
                    @php
                        $variant = $product->variants->first(); 
                    @endphp

                    {{-- Chỉ hiển thị nếu có biến thể --}}
                    @if($variant)
                        @php
                            $priceOriginal = $variant->original_price; 
                            $priceSale = $variant->sale_price;         
                            
                            // --- SỬA ĐOẠN NÀY ---
                            // Gọi flashSale từ $variant thay vì $product
                            $flashSaleItem = $variant->flashSale->first(); 

                            if ($flashSaleItem && $flashSaleItem->pivot->price > 0) {
                                $priceSale = $flashSaleItem->pivot->price;
                            }
                            // --------------------

                            $discount = 0;
                            if($priceOriginal > 0 && $priceSale < $priceOriginal) {
                                $discount = round((($priceOriginal - $priceSale) / $priceOriginal) * 100);
                            }
                        @endphp
<div class="bg-white rounded-2xl shadow-lg hover:shadow-2xl hover:-translate-y-1 transition-all duration-300 overflow-hidden group relative border border-red-100 flex flex-col h-full">
                            
                            {{-- Badge % Giảm giá --}}
                            <div class="absolute top-0 right-0 bg-red-600 text-white text-sm font-bold px-3 py-1 rounded-bl-xl z-20 shadow-md">
                                -{{ $discount }}%
                            </div>
                            
                            {{-- Badge HOT --}}
                            <div class="absolute top-0 left-0 bg-yellow-400 text-red-700 text-xs font-black px-2 py-1 rounded-br-lg z-20 uppercase tracking-tighter">
                                FLASH SALE
                            </div>

                            {{-- Ảnh sản phẩm --}}
                            <a href="{{ route('client.products.show', $product->slug) }}" class="block relative overflow-hidden pt-[100%]">
                                
                                {{-- SỬA Ở ĐÂY: Thêm 'storage/' và kiểm tra tồn tại --}}
                                <img src="{{ $product->thumbnail ? asset('storage/' . $product->thumbnail) : asset('img/no-image.png') }}" 
                                    alt="{{ $product->name }}" 
                                    class="absolute top-0 left-0 w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                                
                                {{-- Nút xem chi tiết (Overlay) --}}
                                <div class="absolute inset-0 bg-black/10 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                    <span class="bg-white text-red-600 px-4 py-2 rounded-full font-bold text-sm shadow-xl transform translate-y-4 group-hover:translate-y-0 transition-transform">
                                        MUA NGAY <i class="fa-solid fa-arrow-right ml-1"></i>
                                    </span>
                                </div>
                            </a>
                            
                            {{-- Thông tin sản phẩm --}}
                            <div class="p-4 flex flex-col flex-grow">
                                {{-- Tên --}}
                                <h3 class="font-bold text-slate-800 text-lg mb-2 leading-tight line-clamp-2 min-h-[3.25rem]">
                                    <a href="{{ route('client.products.show', $product->slug) }}" class="hover:text-red-600 transition-colors">
                                        {{ $product->name }}
                                    </a>
                                </h3>
                                
                                {{-- Giá --}}
                                <div class="mt-auto">
                                    <div class="flex items-end flex-wrap gap-2 mb-2">
                                        <span class="text-red-600 font-extrabold text-2xl">
{{ number_format($priceSale) }}<span class="text-sm underline">đ</span>
                                        </span>
                                        <span class="text-slate-400 text-sm line-through mb-1">
                                            {{ number_format($priceOriginal) }}đ
                                        </span>
                                    </div>
                                    
                                    {{-- Thanh trạng thái hàng --}}
                                    <div class="w-full bg-red-100 rounded-full h-3 relative mt-2 overflow-hidden">
                                        {{-- Random % bán để tạo hiệu ứng --}}
                                        @php $soldPercent = rand(40, 90); @endphp
                                        <div class="bg-gradient-to-r from-orange-500 to-red-600 h-3 rounded-full animate-pulse" style="width: {{ $soldPercent }}%"></div>
                                        <div class="absolute inset-0 flex items-center justify-center text-[9px] font-bold text-white drop-shadow-md">
                                            ĐÃ BÁN {{ $soldPercent }}%
                                        </div>
                                    </div>
                                    
                                    {{-- Size gợi ý --}}
                                    <div class="mt-2 text-xs text-slate-500">
                                        <i class="fa-solid fa-tag text-red-400 mr-1"></i> Size: {{ $variant->size->name ?? $variant->sku ?? 'Đa dạng' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
            
            {{-- Phân trang --}}
            <div class="mt-12 flex justify-center">
                {{ $products->links() }} 
            </div>
        @endif
    </div>
</div>
@endsection