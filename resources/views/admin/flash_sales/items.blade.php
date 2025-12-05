@extends('admin.layouts.app')
@section('title', 'Quản lý Sản Phẩm Flash Sale')

@section('content')
<div class="container px-6 mx-auto grid pb-10">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center my-6 gap-4">
        <div>
            <a href="{{ route('admin.flash_sales.index') }}" class="text-gray-500 hover:text-indigo-600 text-sm mb-2 inline-block"><i class="fa-solid fa-arrow-left"></i> Quay lại</a>
            <h2 class="text-2xl font-bold text-gray-800">Cấu hình: {{ $flashSale->name }}</h2>
            <div class="mt-1 text-sm text-gray-500">
                Thời gian: <span class="font-medium text-gray-700">{{ $flashSale->start_time->format('H:i d/m') }}</span> - <span class="font-medium text-gray-700">{{ $flashSale->end_time->format('H:i d/m/Y') }}</span>
            </div>
        </div>
        
        <!-- Hiển thị thông báo -->
        <div class="text-right">
             @if($errors->any())
             <div class="p-3 bg-red-50 text-red-700 border border-red-200 rounded text-sm text-left max-w-md shadow-sm mb-2">
                 <ul class="list-disc pl-5">
                     @foreach($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                 </ul>
             </div>
             @endif
             @if(session('success'))
             <div class="p-3 bg-green-50 text-green-700 border border-green-200 rounded text-sm text-left shadow-sm mb-2">
                 <i class="fa-solid fa-check-circle mr-1"></i> {{ session('success') }}
             </div>
             @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- CỘT TRÁI: FORM THÊM SẢN PHẨM -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-md p-6 sticky top-6 border border-gray-100">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Thêm Sản Phẩm</h3>
                <form action="{{ route('admin.flash_sales.items.store', $flashSale->id) }}" method="POST">
                    @csrf
                    
                    <!-- 1. Tìm kiếm Ajax -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tìm sản phẩm</label>
                        <!-- Thẻ Select rỗng để TomSelect mount vào -->
                        <select id="select-product" name="product_variant_id" placeholder="Bấm vào đây để chọn sản phẩm..." autocomplete="off" required></select>
                        <p class="text-xs text-gray-500 mt-1">Hệ thống sẽ gợi ý sản phẩm tồn kho cao nhất khi bạn bấm vào ô tìm kiếm.</p>
                    </div>

                    <!-- Thông tin realtime -->
                    <div id="product-info" class="hidden bg-indigo-50 p-4 rounded-md mb-4 border border-indigo-100">
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-xs text-gray-500">Giá gốc:</span>
                            <span class="font-bold text-gray-800 text-sm"><span id="info-price">0</span> ₫</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-xs text-gray-500">Tồn kho:</span>
                            <span id="info-stock" class="font-bold text-indigo-700 text-sm">0</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Giá Flash Sale</label>
                            <input type="number" name="price" class="w-full border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="VNĐ" required min="0">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Số lượng bán</label>
                            <input type="number" name="quantity" class="w-full border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="SL" required min="1">
                        </div>
                    </div>

                    <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none transition-colors">
                        <i class="fa-solid fa-plus-circle mr-2"></i> Thêm vào danh sách
                    </button>
                </form>
            </div>
        </div>

        <!-- CỘT PHẢI: DANH SÁCH -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-100">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sản phẩm</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Cấu hình Sale</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($items as $item)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10 bg-gray-100 rounded flex items-center justify-center text-gray-400 overflow-hidden">
                                        @if($item->productVariant->product->image)
                                            <img src="{{ $item->productVariant->product->image }}" class="w-full h-full object-cover">
                                        @else
                                            <i class="fa-solid fa-box text-lg"></i>
                                        @endif
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900 line-clamp-1 max-w-xs" title="{{ $item->productVariant->product->name ?? '' }}">
                                            {{ $item->productVariant->product->name ?? 'Sản phẩm đã xóa' }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            SKU: <span class="font-mono">{{ $item->productVariant->sku ?? 'N/A' }}</span>
                                        </div>
                                        <div class="text-xs text-gray-500 mt-0.5">
                                            <!-- SỬ DỤNG ACCESSOR ĐỂ HIỂN THỊ GIÁ GỐC ĐÚNG -->
                                            Gốc: <span class="line-through">{{ number_format($item->productVariant->original_price_display ?? 0) }}đ</span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="text-sm font-bold text-red-600">{{ number_format($item->price) }}đ</div>
                                <div class="text-xs text-gray-500 mt-1">
                                    <span class="bg-gray-100 px-2 py-0.5 rounded text-gray-600 border border-gray-200">SL: {{ $item->quantity }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button onclick="openEditModal({{ $item->id }}, {{ $item->price }}, {{ $item->quantity }})" class="text-indigo-600 hover:text-indigo-900 mr-3 transition-colors"><i class="fa-solid fa-pen"></i></button>
                                
                                <form action="{{ route('admin.flash_sales.items.destroy', [$flashSale->id, $item->id]) }}" method="POST" class="inline-block" onsubmit="return confirm('Bạn muốn gỡ sản phẩm này khỏi Flash Sale?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900 transition-colors"><i class="fa-solid fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="px-6 py-10 text-center text-gray-500">
                                <div class="flex flex-col items-center">
                                    <i class="fa-solid fa-basket-shopping text-3xl text-gray-300 mb-2"></i>
                                    <p>Chưa có sản phẩm nào.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="px-4 py-3 border-t bg-gray-50">
                    {{ $items->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL SỬA -->
<div id="editModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-600 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form id="editForm" method="POST">
                @csrf @method('PUT')
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4 flex items-center">
                        <i class="fa-solid fa-pen-to-square text-indigo-500 mr-2"></i> Cập nhật sản phẩm
                    </h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Giá Flash Sale</label>
                            <input type="number" name="price" id="modal_price" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Số lượng chạy</label>
                            <input type="number" name="quantity" id="modal_quantity" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 sm:ml-3 sm:w-auto sm:text-sm">Lưu thay đổi</button>
                    <button type="button" onclick="closeEditModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Hủy</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- TOM SELECT CSS/JS -->
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>

<script>
    // 1. Cấu hình TomSelect (AJAX + Preload Focus)
    new TomSelect("#select-product", {
        valueField: 'id',
        labelField: 'text',
        searchField: 'text',
        
        // QUAN TRỌNG: Load dữ liệu ngay khi bấm vào (Focus)
        preload: 'focus', 
        
        load: function(query, callback) {
            // ĐÃ SỬA: Thêm admin. vào tên route
            var url = '{{ route("admin.flash_sales.product_search") }}?q=' + encodeURIComponent(query);
            fetch(url)
                .then(response => response.json())
                .then(json => {
                    callback(json);
                }).catch(()=>{
                    callback();
                });
        },
        placeholder: 'Bấm để tìm kiếm hoặc xem gợi ý...',
        
        // Hiển thị thông tin chi tiết khi chọn
        onChange: function(value) {
            if(value) {
                var data = this.options[value];
                // Format tiền Việt Nam
                var formattedPrice = new Intl.NumberFormat('vi-VN').format(data.original_price);
                
                document.getElementById('info-price').innerText = formattedPrice;
                document.getElementById('info-stock').innerText = data.stock;
                document.getElementById('product-info').classList.remove('hidden');
            } else {
                document.getElementById('product-info').classList.add('hidden');
            }
        },

        // Custom giao diện Dropdown
        render: {
            option: function(item, escape) {
                var formattedPrice = new Intl.NumberFormat('vi-VN').format(item.original_price);
                var stockClass = item.stock > 0 ? 'text-green-600' : 'text-red-600';
                
                return `<div class="py-2 px-1 border-b border-gray-100 hover:bg-indigo-50">
                            <div class="font-semibold text-gray-800 text-sm">${escape(item.text)}</div>
                            <div class="flex justify-between mt-1 text-xs">
                                <span class="text-gray-500">Giá gốc: <span class="font-medium text-gray-700">${formattedPrice} đ</span></span>
                                <span class="text-gray-500">Kho: <span class="font-bold ${stockClass}">${item.stock}</span></span>
                            </div>
                        </div>`;
            },
            item: function(item, escape) {
                return `<div class="font-medium text-gray-800">${escape(item.text)}</div>`;
            }
        }
    });

    // 2. Logic Modal
    function openEditModal(itemId, price, qty) {
        var form = document.getElementById('editForm');
        form.action = '/admin/flash-sales/{{ $flashSale->id }}/items/' + itemId; 
        document.getElementById('modal_price').value = price;
        document.getElementById('modal_quantity').value = qty;
        document.getElementById('editModal').classList.remove('hidden');
    }

    function closeEditModal() {
        document.getElementById('editModal').classList.add('hidden');
    }
</script>
@endsection