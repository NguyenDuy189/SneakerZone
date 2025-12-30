@extends('admin.layouts.app')

@section('title', 'Quản lý Kho hàng')

@section('content')
<div class="container-fluid px-6 py-6 mx-auto">
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex items-center">
            <div class="p-3 bg-blue-100 text-blue-600 rounded-full mr-4">
                <i class="fa-solid fa-box text-xl"></i>
            </div>
            <div>
                <p class="text-sm text-gray-500">Tổng sản phẩm</p>
                <p class="text-2xl font-bold text-gray-800">{{ $variants->total() }}</p>
            </div>
        </div>
        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex items-center">
            <div class="p-3 bg-red-100 text-red-600 rounded-full mr-4">
                <i class="fa-solid fa-triangle-exclamation text-xl"></i>
            </div>
            <div>
                <p class="text-sm text-gray-500">Cảnh báo hết hàng</p>
                <p class="text-2xl font-bold text-gray-800">{{ \App\Models\ProductVariant::where('stock_quantity', '<=', 10)->count() }}</p>
            </div>
        </div>
        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex items-center">
            <div class="p-3 bg-indigo-100 text-indigo-600 rounded-full mr-4">
                <i class="fa-solid fa-truck-fast text-xl"></i>
            </div>
            <div>
                <p class="text-sm text-gray-500">Đơn nhập chờ duyệt</p>
                <p class="text-2xl font-bold text-gray-800">
                    {{ \App\Models\PurchaseOrder::where('status', 'pending')->count() }}
                </p>
                <a href="{{ route('admin.purchase_orders.index') }}" class="text-xs text-indigo-600 hover:underline">Xem ngay &rarr;</a>
            </div>
        </div>
    </div>

    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
        <h2 class="text-2xl font-bold text-gray-800">Trạng thái tồn kho</h2>
        
        <form method="GET" action="{{ route('admin.inventory.index') }}" class="flex flex-col md:flex-row gap-3 w-full md:w-auto bg-white p-2 rounded-lg shadow-sm border border-gray-100">
            <div class="relative">
                <input type="text" name="keyword" value="{{ request('keyword') }}" placeholder="Tìm SKU, Tên SP..." 
                       class="pl-9 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500 w-full md:w-64">
                <i class="fa-solid fa-search absolute left-3 top-2.5 text-gray-400"></i>
            </div>

            <select name="status" onchange="this.form.submit()" class="border border-gray-300 text-gray-700 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 p-2 cursor-pointer">
                <option value="">Tất cả trạng thái</option>
                <option value="out_of_stock" {{ request('status') == 'out_of_stock' ? 'selected' : '' }}>Hết hàng (0)</option>
                <option value="low_stock" {{ request('status') == 'low_stock' ? 'selected' : '' }}>Sắp hết (<=10)</option>
                <option value="in_stock" {{ request('status') == 'in_stock' ? 'selected' : '' }}>Sẵn hàng (>10)</option>
            </select>

            <a href="{{ route('admin.inventory.index') }}" class="px-3 py-2 text-gray-500 bg-gray-100 hover:bg-gray-200 rounded-lg transition" title="Làm mới">
                <i class="fa-solid fa-rotate"></i>
            </a>
        </form>
    </div>

    <div class="w-full overflow-hidden rounded-xl shadow-xs bg-white border border-gray-100">
        <div class="w-full overflow-x-auto">
            <table class="w-full whitespace-no-wrap">
                <thead>
                    <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b bg-gray-50">
                        <th class="px-4 py-3">Sản phẩm</th>
                        <th class="px-4 py-3">Phân loại</th>
                        <th class="px-4 py-3">SKU</th>
                        <th class="px-4 py-3 text-center">Tồn kho</th>
                        <th class="px-4 py-3 text-center">Trạng thái</th>
                        <th class="px-4 py-3 text-right">Cập nhật nhanh</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($variants as $variant)
                    <tr class="text-gray-700 hover:bg-gray-50 transition group">
                        
                        <td class="px-4 py-3">
                            <div class="flex items-center text-sm">
                                <div class="relative w-10 h-10 mr-3 rounded-lg overflow-hidden border border-gray-200">
                                    <img class="object-cover w-full h-full" 
                                         src="{{ $variant->image_url ? asset('storage/'.$variant->image_url) : ($variant->product->thumbnail ? asset('storage/'.$variant->product->thumbnail) : asset('images/default.png')) }}" 
                                         alt="" loading="lazy" />
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-800 line-clamp-1 w-48" title="{{ $variant->product->name ?? '' }}">
                                        {{ $variant->product->name ?? 'Sản phẩm lỗi' }}
                                    </p>
                                    <p class="text-[10px] text-gray-500">ID: #{{ $variant->id }}</p>
                                </div>
                            </div>
                        </td>

                        <td class="px-4 py-3 text-sm">
                            <div class="flex gap-1">
                                <span class="px-2 py-0.5 text-xs bg-gray-100 border border-gray-200 rounded text-gray-600 font-medium">
                                    {{ $variant->color }}
                                </span>
                                <span class="px-2 py-0.5 text-xs bg-gray-100 border border-gray-200 rounded text-gray-600 font-medium">
                                    {{ $variant->size }}
                                </span>
                            </div>
                        </td>

                        <td class="px-4 py-3 text-sm font-mono text-gray-500">
                            {{ $variant->sku }}
                        </td>

                        <td class="px-4 py-3" style="min-width: 150px;">
                            <div class="flex items-center gap-3">
                                <div class="flex-1 h-2 bg-gray-100 rounded-full overflow-hidden">
                                    @php
                                        $qty = $variant->stock_quantity;
                                        $percent = min(($qty / 50) * 100, 100); 
                                        $color = $qty == 0 ? 'bg-red-500' : ($qty <= 10 ? 'bg-amber-500' : 'bg-emerald-500');
                                    @endphp
                                    <div class="{{ $color }} h-full rounded-full" style="width: {{ $percent }}%"></div>
                                </div>
                                <span class="text-sm font-bold w-8 text-right">{{ $qty }}</span>
                            </div>
                        </td>

                        <td class="px-4 py-3 text-center">
                            @if($qty == 0)
                                <span class="px-2 py-1 font-bold text-red-700 bg-red-100 rounded-full text-xs">Hết hàng</span>
                            @elseif($qty <= 10)
                                <span class="px-2 py-1 font-bold text-amber-700 bg-amber-100 rounded-full text-xs">Sắp hết</span>
                            @else
                                <span class="px-2 py-1 font-bold text-emerald-700 bg-emerald-100 rounded-full text-xs">Sẵn hàng</span>
                            @endif
                        </td>

                        <td class="px-4 py-3 text-right">
                            <button onclick="openUpdateModal({{ $variant->id }}, '{{ $variant->sku }}', {{ $variant->stock_quantity }}, {{ $variant->original_price }})"
                                    class="text-indigo-600 hover:text-indigo-900 border border-indigo-200 hover:bg-indigo-50 px-3 py-1 rounded text-xs font-bold transition">
                                <i class="fa-solid fa-pen-to-square mr-1"></i> Điều chỉnh
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-400">
                            Không tìm thấy dữ liệu.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-gray-100 bg-gray-50">
            {{ $variants->links() }}
        </div>
    </div>
</div>

<div id="stockModal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-gray-900 bg-opacity-50 transition-opacity backdrop-blur-sm" onclick="closeModal()"></div>

    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-xl shadow-xl transform transition-all sm:max-w-lg w-full overflow-hidden">
            <form id="quickUpdateForm" onsubmit="submitQuickUpdate(event)">
                @csrf
                @method('PUT')
                
                <div class="bg-gray-50 px-5 py-4 border-b border-gray-100 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-gray-800">Điều chỉnh kho hàng</h3>
                    <button type="button" onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fa-solid fa-xmark text-xl"></i>
                    </button>
                </div>

                <div class="p-6">
                    <div class="mb-4 bg-blue-50 text-blue-800 text-sm p-3 rounded-lg border border-blue-100">
                        Đang điều chỉnh cho SKU: <span id="modalSku" class="font-mono font-bold"></span>
                    </div>

                    <input type="hidden" id="modalVariantId">

                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Tồn kho thực tế</label>
                            <input type="number" id="modalStock" name="stock_quantity" class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" required min="0">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Giá nhập (VNĐ)</label>
                            <input type="number" id="modalPrice" name="original_price" class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" min="0">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Lý do điều chỉnh</label>
                        <textarea name="note" rows="2" class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" placeholder="VD: Kiểm kê kho thấy lệch..."></textarea>
                    </div>
                </div>

                <div class="bg-gray-50 px-5 py-3 flex flex-row-reverse gap-2 border-t border-gray-100">
                    <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-indigo-700 transition shadow-sm">
                        Lưu thay đổi
                    </button>
                    <button type="button" onclick="closeModal()" class="bg-white text-gray-700 px-4 py-2 rounded-lg font-medium border border-gray-300 hover:bg-gray-50 transition">
                        Hủy bỏ
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function openUpdateModal(id, sku, stock, price) {
        document.getElementById('modalVariantId').value = id;
        document.getElementById('modalSku').innerText = sku;
        document.getElementById('modalStock').value = stock;
        document.getElementById('modalPrice').value = price;
        document.querySelector('textarea[name="note"]').value = '';
        document.getElementById('stockModal').classList.remove('hidden');
    }

    function closeModal() {
        document.getElementById('stockModal').classList.add('hidden');
    }

    function submitQuickUpdate(e) {
        e.preventDefault();
        const id = document.getElementById('modalVariantId').value;
        const stock = document.getElementById('modalStock').value;
        const price = document.getElementById('modalPrice').value;
        const note = document.querySelector('textarea[name="note"]').value;

        fetch(`/admin/inventory/${id}/quick-update`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ stock_quantity: stock, original_price: price, note: note })
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                alert('Cập nhật thành công!');
                location.reload();
            } else {
                alert('Lỗi: ' + data.message);
            }
        })
        .catch(err => alert('Lỗi hệ thống'));
    }
</script>
@endpush