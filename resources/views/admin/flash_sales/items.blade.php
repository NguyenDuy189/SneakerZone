@extends('admin.layouts.app')
@section('title', 'Cấu hình Flash Sale')

@section('content')
<div class="container px-6 mx-auto grid pb-12">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center my-6 gap-4">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <a href="{{ route('admin.flash_sales.index') }}" class="group w-12 h-12 flex items-center justify-center bg-white border border-gray-200 rounded-xl shadow-sm hover:shadow-md hover:bg-gray-50 hover:border-indigo-300 transition-all duration-200">
                    <i class="fa-solid fa-arrow-left text-xl text-gray-500 group-hover:text-indigo-600"></i>
                </a>
                <span class="text-gray-300">|</span>
                <span class="text-sm text-gray-500 uppercase tracking-wide">Quản lý khuyến mãi</span>
            </div>
            <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-3">
                {{ $flashSale->name }}
                @php
                    $now = now();
                    $statusClass = '';
                    $statusText = '';
                    if ($now < $flashSale->start_time) {
                        $statusClass = 'bg-yellow-100 text-yellow-800 border-yellow-200';
                        $statusText = 'Sắp diễn ra';
                    } elseif ($now >= $flashSale->start_time && $now <= $flashSale->end_time) {
                        $statusClass = 'bg-green-100 text-green-800 border-green-200';
                        $statusText = 'Đang diễn ra';
                    } else {
                        $statusClass = 'bg-gray-100 text-gray-800 border-gray-200';
                        $statusText = 'Đã kết thúc';
                    }
                @endphp
                <span class="px-2.5 py-0.5 rounded-full text-xs font-medium border {{ $statusClass }}">
                    {{ $statusText }}
                </span>
            </h2>
            <div class="mt-2 flex items-center text-sm text-gray-600 bg-white border border-gray-200 rounded-lg px-3 py-1.5 w-fit shadow-sm">
                <i class="fa-regular fa-clock text-indigo-500 mr-2"></i>
                <span class="font-medium">{{ $flashSale->start_time->format('H:i d/m') }}</span>
                <span class="mx-2 text-gray-400">⟶</span>
                <span class="font-medium">{{ $flashSale->end_time->format('H:i d/m/Y') }}</span>
            </div>
        </div>
        
        <div class="w-full md:w-auto">
             @if($errors->any())
             <div class="p-4 mb-4 text-sm text-red-700 bg-red-50 rounded-lg border border-red-200 shadow-sm" role="alert">
                 <div class="font-medium mb-1">Vui lòng kiểm tra lại:</div>
                 <ul class="list-disc list-inside">
                     @foreach($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                 </ul>
             </div>
             @endif
             @if(session('success'))
             <div class="p-4 mb-4 text-sm text-green-700 bg-green-50 rounded-lg border border-green-200 shadow-sm flex items-center" role="alert">
                 <i class="fa-solid fa-circle-check mr-2 text-lg"></i> {{ session('success') }}
             </div>
             @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        <div class="lg:col-span-4">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 sticky top-6 overflow-hidden">
                <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800"><i class="fa-solid fa-plus text-indigo-600 mr-1.5"></i> Thêm sản phẩm</h3>
                </div>
                
                <div class="p-6">
                    <form action="{{ route('admin.flash_sales.items.store', $flashSale->id) }}" method="POST">
                        @csrf
                        
                        <div class="mb-5">
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Chọn sản phẩm</label>
                            <div class="relative">
                                <select id="select-product" name="product_variant_id" placeholder="Nhập tên hoặc mã SKU..." autocomplete="off" required></select>
                            </div>
                            <p class="text-xs text-gray-500 mt-1.5"><i class="fa-solid fa-circle-info mr-1"></i>Hệ thống ưu tiên gợi ý sản phẩm tồn kho cao.</p>
                        </div>

                        <div id="product-info" class="hidden mb-5">
                            <div class="bg-indigo-50 border border-indigo-100 rounded-lg p-4 relative overflow-hidden">
                                <div class="absolute top-0 right-0 -mt-2 -mr-2 w-16 h-16 bg-indigo-100 rounded-full blur-xl opacity-50"></div>
                                <div class="relative z-10">
                                    <h4 class="text-xs font-bold text-indigo-800 uppercase tracking-wide mb-2">Thông tin cơ bản</h4>
                                    <div class="flex justify-between items-center mb-2 pb-2 border-b border-indigo-100 border-dashed">
                                        <span class="text-sm text-gray-600">Giá niêm yết:</span>
                                        <span class="font-bold text-gray-900 text-base"><span id="info-price">0</span> ₫</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600">Kho hiện tại:</span>
                                        <span id="info-stock" class="font-bold text-indigo-700 text-base">0</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4 mb-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Giá Flash Sale</label>
                                <div class="relative rounded-md shadow-sm">
                                    <input type="number" name="price" class="block w-full pr-10 border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm h-10" placeholder="0" required min="0">
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 sm:text-xs">VND</span>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Số lượng bán</label>
                                <input type="number" name="quantity" class="block w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm h-10" placeholder="SL" required min="1">
                            </div>
                        </div>

                        <button type="submit" class="w-full flex justify-center items-center py-2.5 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all transform hover:-translate-y-0.5">
                            Thêm vào danh sách
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="lg:col-span-8">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-800">Danh sách sản phẩm tham gia ({{ $items->total() }})</h3>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Sản phẩm</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Cấu hình Sale</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($items as $item)
                            <tr class="hover:bg-gray-50 transition-colors group">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-12 w-12 bg-gray-100 rounded-lg border border-gray-200 flex items-center justify-center text-gray-400 overflow-hidden relative">
                                            @if($item->productVariant->product->image)
                                                <img src="{{ $item->productVariant->product->image }}" class="w-full h-full object-cover">
                                            @else
                                                <i class="fa-solid fa-image text-xl"></i>
                                            @endif
                                            
                                            @php
                                                $original = $item->productVariant->original_price ?? 0;
                                                $sale = $item->price;
                                                $percent = $original > 0 ? round((($original - $sale) / $original) * 100) : 0;
                                            @endphp
                                            @if($percent > 0)
                                                <div class="absolute top-0 right-0 bg-red-500 text-white text-[10px] font-bold px-1 rounded-bl shadow-sm">-{{ $percent }}%</div>
                                            @endif
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900 line-clamp-1 max-w-[200px]" title="{{ $item->productVariant->product->name ?? '' }}">
                                                {{ $item->productVariant->product->name ?? 'Sản phẩm đã xóa' }}
                                            </div>
                                            <div class="text-xs text-gray-500 flex items-center mt-0.5">
                                                <span class="bg-gray-100 text-gray-600 px-1.5 py-0.5 rounded border border-gray-200 font-mono mr-2">{{ $item->productVariant->sku ?? 'N/A' }}</span>
                                            </div>
                                            <div class="text-xs text-gray-400 mt-1">
                                                Gốc: <span class="line-through">{{ number_format($item->productVariant->original_price ?? 0) }}đ</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    <div class="text-sm font-bold text-red-600 bg-red-50 border border-red-100 px-3 py-1 rounded-full inline-block">
                                        {{ number_format($item->price) }}đ
                                    </div>
                                    <div class="text-xs text-gray-500 mt-2">
                                        Giới hạn: <span class="font-bold text-gray-700">{{ $item->quantity }}</span> suất
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button onclick="openEditModal({{ $item->id }}, {{ $item->price }}, {{ $item->quantity }})" class="p-2 text-indigo-600 hover:bg-indigo-50 rounded-full transition-colors tooltip" title="Chỉnh sửa">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>
                                    
                                    <form action="{{ route('admin.flash_sales.items.destroy', [$flashSale->id, $item->id]) }}" method="POST" class="inline-block" onsubmit="return confirm('Bạn chắc chắn muốn gỡ sản phẩm này khỏi Flash Sale?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="p-2 text-red-600 hover:bg-red-50 rounded-full transition-colors tooltip" title="Xóa">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="px-6 py-12 text-center text-gray-500">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-3">
                                            <i class="fa-solid fa-basket-shopping text-3xl text-gray-300"></i>
                                        </div>
                                        <p class="text-base font-medium text-gray-600">Chưa có sản phẩm nào</p>
                                        <p class="text-sm text-gray-400 mt-1">Vui lòng chọn sản phẩm ở khung bên trái để thêm vào chương trình.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($items->hasPages())
                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                    {{ $items->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div id="editModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-900 bg-opacity-50 transition-opacity backdrop-blur-sm" aria-hidden="true" onclick="closeEditModal()"></div>
        
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        
        <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-100">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-indigo-100 sm:mx-0 sm:h-10 sm:w-10">
                        <i class="fa-solid fa-pen-to-square text-indigo-600"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-semibold text-gray-900" id="modal-title">Cập nhật sản phẩm</h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500 mb-4">Điều chỉnh giá bán và số lượng suất chạy cho sản phẩm này.</p>
                            
                            <form id="editForm" method="POST">
                                @csrf @method('PUT')
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Giá Flash Sale</label>
                                        <div class="relative rounded-md shadow-sm">
                                            <input type="number" name="price" id="modal_price" class="focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" required>
                                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                <span class="text-gray-500 sm:text-xs">VND</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Số lượng chạy</label>
                                        <input type="number" name="quantity" id="modal_quantity" class="focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" required>
                                    </div>
                                </div>
                                
                                <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none sm:col-start-2 sm:text-sm">
                                        Lưu thay đổi
                                    </button>
                                    <button type="button" onclick="closeEditModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:col-start-1 sm:text-sm">
                                        Hủy bỏ
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>

<style>
    .ts-control {
        border-radius: 0.5rem; /* rounded-lg */
        padding: 0.5rem 0.75rem;
        border-color: #d1d5db; /* gray-300 */
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    }
    .ts-control.focus {
        border-color: #6366f1; /* indigo-500 */
        box-shadow: 0 0 0 1px #6366f1;
    }
    .ts-dropdown {
        border-radius: 0.5rem;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        border: 1px solid #e5e7eb;
        overflow: hidden;
    }
    .ts-dropdown .option {
        padding: 8px 12px;
    }
    .ts-dropdown .active {
        background-color: #f3f4f6; /* gray-100 */
        color: #111827;
    }
</style>

<script>
    /**
     * ==========================================
     * UTILITIES: CÁC HÀM TIỆN ÍCH DÙNG CHUNG
     * ==========================================
     */
    const Utils = {
        /**
         * Format tiền tệ thông minh (Hỗ trợ tự động nhân triệu & sửa lỗi input)
         * @param {string|number} value - Giá trị đầu vào
         * @returns {string} - Chuỗi đã format (VD: 3.200.000)
         */
        formatCurrency: function(value) {
            if (value === null || value === undefined || value === '') return '0';

            // 1. Làm sạch dữ liệu: Chuyển về chuỗi và chỉ giữ lại số + dấu chấm thập phân
            let str = String(value).replace(/[^0-9.]/g, ''); 
            let number = parseFloat(str);

            if (isNaN(number)) return '0';

            // 2. Logic nghiệp vụ: Tự động phát hiện đơn vị "Triệu" (số < 10.000)
            // Ngưỡng 10.000 là an toàn vì không có sản phẩm nào giá < 10k
            if (number > 0 && number < 10000) {
                number = number * 1000000;
            }

            // 3. Làm tròn và format chuẩn Việt Nam
            return new Intl.NumberFormat('vi-VN').format(Math.round(number));
        },

        /**
         * Xác định trạng thái tồn kho để hiển thị class màu sắc
         * @param {number} stock 
         */
        getStockStatus: function(stock) {
            return stock > 0 
                ? { class: 'text-emerald-600 bg-emerald-50 border-emerald-100', label: 'Sẵn hàng' }
                : { class: 'text-rose-600 bg-rose-50 border-rose-100', label: 'Hết hàng' };
        }
    };

    /**
     * ==========================================
     * COMPONENT: TOMSELECT (TÌM KIẾM SẢN PHẨM)
     * ==========================================
     */
    new TomSelect("#select-product", {
        valueField: 'id',
        labelField: 'text',
        searchField: 'text',
        preload: 'focus', // Tải dữ liệu ngay khi click vào ô input
        maxOptions: 50,   // Giới hạn số lượng gợi ý để tối ưu render
        
        // Gọi API tìm kiếm
        load: function(query, callback) {
            const url = '{{ route("admin.flash_sales.product_search") }}?q=' + encodeURIComponent(query);
            fetch(url)
                .then(response => response.json())
                .then(json => callback(json))
                .catch(() => callback());
        },
        
        placeholder: 'Nhập tên sản phẩm hoặc mã SKU...',
        
        // Sự kiện: Khi chọn sản phẩm
        onChange: function(value) {
            const infoBox = document.getElementById('product-info');
            
            if (value) {
                const data = this.options[value];
                
                // Cập nhật UI
                document.getElementById('info-price').innerText = Utils.formatCurrency(data.original_price);
                document.getElementById('info-stock').innerText = data.stock;
                
                // Hiển thị Box thông tin với hiệu ứng
                infoBox.classList.remove('hidden');
                infoBox.classList.add('animate-fade-in-down');
            } else {
                // Ẩn Box thông tin nếu xóa chọn
                infoBox.classList.add('hidden');
            }
        },

        // Tùy chỉnh giao diện hiển thị (Custom Render)
        render: {
            // 1. Giao diện từng dòng trong danh sách gợi ý
            option: function(item, escape) {
                const priceDisplay = Utils.formatCurrency(item.original_price);
                const stockStatus = Utils.getStockStatus(item.stock);
                
                return `
                    <div class="py-3 px-4 border-b border-gray-100 hover:bg-indigo-50 cursor-pointer group transition-colors">
                        <div class="flex justify-between items-start mb-1">
                            <span class="font-semibold text-gray-800 text-sm truncate pr-2 group-hover:text-indigo-700 transition-colors">
                                ${escape(item.text)}
                            </span>
                        </div>
                        <div class="flex justify-between items-center mt-1.5 text-xs">
                            <span class="text-gray-600 flex items-center">
                                <i class="fa-solid fa-tag mr-1.5 text-indigo-400"></i>
                                <span class="font-bold text-gray-900">${priceDisplay} ₫</span>
                            </span>
                            <span class="px-2.5 py-0.5 rounded-full font-medium border ${stockStatus.class}">
                                Kho: ${item.stock}
                            </span>
                        </div>
                    </div>
                `;
            },
            
            // 2. Giao diện khi đã chọn (hiển thị trong ô input)
            item: function(item, escape) {
                return `
                    <div class="font-medium text-gray-800 flex items-center py-1">
                        <span class="w-6 h-6 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center mr-2 text-xs">
                            <i class="fa-solid fa-check"></i>
                        </span>
                        ${escape(item.text)}
                    </div>
                `;
            },
            
            // 3. Khi không tìm thấy kết quả
            no_results: function(data, escape) {
                return `
                    <div class="no-results p-6 text-center text-sm text-gray-500">
                        <i class="fa-solid fa-magnifying-glass text-2xl mb-2 text-gray-300 block"></i>
                        Không tìm thấy sản phẩm phù hợp
                    </div>
                `;
            },
            
            // 4. Khi đang tải dữ liệu
            loading: function(data, escape) {
                return `
                    <div class="spinner p-4 text-sm text-gray-500 text-center flex items-center justify-center">
                        <i class="fa-solid fa-circle-notch fa-spin mr-2 text-indigo-500 text-lg"></i>
                        Đang đồng bộ dữ liệu...
                    </div>
                `;
            }
        }
    });

    /**
     * ==========================================
     * COMPONENT: EDIT MODAL (CHỈNH SỬA)
     * ==========================================
     */
    const EditModal = {
        element: document.getElementById('editModal'),
        form: document.getElementById('editForm'),
        inputs: {
            price: document.getElementById('modal_price'),
            quantity: document.getElementById('modal_quantity')
        },

        open: function(itemId, price, qty) {
            // Cập nhật action form động
            this.form.action = '{{ url("admin/flash-sales/" . $flashSale->id . "/items") }}/' + itemId;
            
            // Fill dữ liệu hiện tại
            this.inputs.price.value = price;
            this.inputs.quantity.value = qty;
            
            // Hiển thị modal & khóa scroll body
            this.element.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            
            // Auto focus vào ô giá để sửa nhanh
            setTimeout(() => this.inputs.price.focus(), 100);
        },

        close: function() {
            this.element.classList.add('hidden');
            document.body.style.overflow = 'auto';
        }
    };

    // Binding sự kiện toàn cục cho Modal
    
    // 1. Hàm wrapper để gọi từ HTML onclick=""
    function openEditModal(itemId, price, qty) {
        EditModal.open(itemId, price, qty);
    }

    function closeEditModal() {
        EditModal.close();
    }
    
    // 2. Đóng modal khi nhấn phím ESC
    document.addEventListener('keydown', function(event) {
        if (event.key === "Escape" && !EditModal.element.classList.contains('hidden')) {
            EditModal.close();
        }
    });
</script>
@endsection