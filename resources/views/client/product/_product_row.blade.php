<div class="row">
    @foreach($items as $item) {{-- LƯU Ý: Biến ở đây là $item --}}
    <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
        <div class="card product-card h-100 border-0 shadow-sm">
            
            {{-- ẢNH SẢN PHẨM --}}
            <div class="position-relative overflow-hidden" style="padding-top: 100%; background: #f8f9fa;">
                {{-- SỬA $product -> $item --}}
                <a href="{{ route('client.products.show', $item->slug) }}">
                    @php
                        // SỬA $product -> $item
                        $currentImage = $item->thumbnail ?? $item->image;
                        
                        // Logic xử lý ảnh (Giữ nguyên logic, chỉ đổi tên biến)
                        if (empty($currentImage)) {
                            $imgUrl = asset('img/no-image.png');
                        } elseif (Str::startsWith($currentImage, 'http')) {
                            $imgUrl = $currentImage;
                        } elseif (Str::startsWith($currentImage, '/img/products/') || Str::startsWith($currentImage, 'img/products/')) {
                            $imgUrl = asset($currentImage);
                        } else {
                            $imgUrl = asset('img/products/' . $currentImage);
                        }
                    @endphp

                    <img 
                        src="{{ $imgUrl }}"
                        class="card-img-top position-absolute top-0 start-0 w-100 h-100"
                        style="object-fit: contain; background-color: #f8f9fa;"
                        alt="{{ $item->name }}" {{-- SỬA $product -> $item --}}
                        onerror="this.onerror=null; this.src='{{ asset('img/no-image.png') }}';"
                    >
                </a>
            </div>
            
            <div class="card-body">
                {{-- Tên sản phẩm --}}
                <h5 class="card-title" style="font-size: 16px;">
                    {{-- SỬA $product -> $item --}}
                    <a href="{{ route('client.products.show', $item->slug) }}" class="text-dark text-decoration-none fw-bold">
                        {{ $item->name }}
                    </a>
                </h5>
                
                {{-- GIÁ SẢN PHẨM --}}
                <p class="card-text text-danger fw-bold">
                    {{-- SỬA $product -> $item --}}
                    {{ $item->price_min ? number_format($item->price_min) . ' VNĐ' : 'Liên hệ' }}
                </p>
                
                <a href="#" class="btn btn-dark btn-sm w-100">Thêm vào giỏ</a>
            </div>
        </div>
    </div>
    @endforeach
</div>