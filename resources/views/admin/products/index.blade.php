@extends('admin.layouts.app')

@section('title', 'Quản lý sản phẩm')
@section('header', 'Danh sách sản phẩm')

@section('content')
<div class="container px-6 mx-auto pb-10">
    <!-- Header Tools -->
    <div class="flex flex-col md:flex-row justify-between items-center my-6 gap-4">
        <h2 class="text-2xl font-bold text-slate-800">Tất cả sản phẩm</h2>
        <div class="flex gap-3">
            <a href="{{ route('admin.products.trash') }}" class="flex items-center px-4 py-2.5 text-sm font-medium text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-all shadow-sm">
                <i class="fa-solid fa-trash-can mr-2"></i> Thùng rác
            </a>
            <a href="{{ route('admin.products.create') }}" class="flex items-center px-5 py-2.5 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 shadow-md transition-all transform active:scale-95">
                <i class="fa-solid fa-plus mr-2"></i> Thêm sản phẩm
            </a>
        </div>
    </div>

    <!-- Alert Success -->
    @if(session('success'))
        <div class="p-4 mb-6 text-sm text-emerald-700 bg-emerald-50 rounded-lg border border-emerald-200 shadow-sm flex items-center animate-fade-in-down">
            <i class="fa-solid fa-circle-check mr-2 text-lg"></i> {{ session('success') }}
        </div>
    @endif

    <!-- FILTER BAR -->
    <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-200 mb-6">
        <form action="{{ route('admin.products.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-12 gap-4">
            <!-- Tìm kiếm -->
            <div class="md:col-span-4 relative">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 transform -translate-y-1/2 text-slate-400"></i>
                <input type="text" name="keyword" value="{{ request('keyword') }}" 
                    placeholder="Tìm theo tên sản phẩm, mã SKU..." 
                    class="pl-10 pr-4 py-2.5 w-full border border-slate-300 rounded-lg focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 text-sm shadow-sm">
            </div>

            <!-- Lọc Danh mục -->
            <div class="md:col-span-2">
                <select name="category_id" class="w-full border border-slate-300 rounded-lg py-2.5 px-3 text-sm focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 bg-white cursor-pointer shadow-sm">
                    <option value="">Tất cả danh mục</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                            {{ str_repeat('— ', $cat->level) }} {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Lọc Thương hiệu -->
            <div class="md:col-span-2">
                <select name="brand_id" class="w-full border border-slate-300 rounded-lg py-2.5 px-3 text-sm focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 bg-white cursor-pointer shadow-sm">
                    <option value="">Tất cả thương hiệu</option>
                    @foreach($brands as $brand)
                        <option value="{{ $brand->id }}" {{ request('brand_id') == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Lọc Trạng thái -->
            <div class="md:col-span-2">
                <select name="status" class="w-full border border-slate-300 rounded-lg py-2.5 px-3 text-sm focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 bg-white cursor-pointer shadow-sm">
                    <option value="">Trạng thái</option>
                    <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Đang bán</option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Bản nháp</option>
                    <option value="archived" {{ request('status') == 'archived' ? 'selected' : '' }}>Lưu trữ</option>
                </select>
            </div>

            <!-- Buttons -->
            <div class="md:col-span-2 flex gap-2">
                <button type="submit" class="flex-1 px-4 py-2.5 text-sm font-bold text-white bg-slate-800 rounded-lg hover:bg-slate-900 transition-colors shadow-sm">
                    <i class="fa-solid fa-filter mr-1"></i> Lọc
                </button>
                @if(request()->hasAny(['keyword', 'category_id', 'brand_id', 'status']))
                    <a href="{{ route('admin.products.index') }}" class="px-3 py-2.5 text-slate-500 bg-slate-100 border border-slate-300 rounded-lg hover:bg-slate-200 transition-colors" title="Xóa bộ lọc">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- DATA TABLE -->
    <div class="w-full overflow-hidden rounded-xl shadow-md border border-slate-200 bg-white">
        <div class="w-full overflow-x-auto">
            <table class="w-full whitespace-no-wrap text-left">
                <thead>
                    <tr class="text-xs font-bold tracking-wider text-slate-500 uppercase border-b border-slate-200 bg-slate-50">
                        <th class="px-4 py-4 w-20">Hình ảnh</th>
                        <th class="px-4 py-4 cursor-pointer hover:bg-slate-100 transition-colors">
                            <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'name', 'sort_order' => request('sort_order') == 'asc' ? 'desc' : 'asc']) }}" class="flex items-center group">
                                Sản phẩm 
                                <span class="ml-1 text-slate-300 group-hover:text-indigo-500 transition-colors">
                                    <i class="fa-solid fa-sort{{ request('sort_by') == 'name' ? (request('sort_order') == 'asc' ? '-up' : '-down') : '' }}"></i>
                                </span>
                            </a>
                        </th>
                        <th class="px-4 py-4 cursor-pointer hover:bg-slate-100 transition-colors">
                            <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'price_min', 'sort_order' => request('sort_order') == 'asc' ? 'desc' : 'asc']) }}" class="flex items-center group">
                                Giá bán 
                                <span class="ml-1 text-slate-300 group-hover:text-indigo-500 transition-colors">
                                    <i class="fa-solid fa-sort{{ request('sort_by') == 'price_min' ? (request('sort_order') == 'asc' ? '-up' : '-down') : '' }}"></i>
                                </span>
                            </a>
                        </th>
                        <th class="px-4 py-4">Phân loại</th>
                        <th class="px-4 py-4 text-center">Trạng thái</th>
                        <th class="px-4 py-4 text-center w-32">Hành động</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($products as $product)
                    <tr class="hover:bg-slate-50 transition-colors group">
                        <!-- Thumbnail -->
                        <td class="px-4 py-3">
                            <div class="relative w-14 h-14 rounded-lg border border-slate-200 overflow-hidden bg-white shadow-sm group-hover:border-indigo-300 transition-colors">
                                <img class="object-cover w-full h-full transform transition-transform duration-500 group-hover:scale-110" 
                                     src="{{ $product->thumbnail ? asset('storage/' . $product->thumbnail) : 'https://placehold.co/100x100?text=No+Img' }}" 
                                     alt="{{ $product->name }}" loading="lazy">
                            </div>
                        </td>
                        
                        <!-- Info -->
                        <td class="px-4 py-3">
                            <a href="{{ route('admin.products.edit', $product->id) }}" class="font-bold text-slate-800 hover:text-indigo-600 transition-colors line-clamp-1 text-base mb-1" title="{{ $product->name }}">
                                {{ $product->name }}
                            </a>
                            <div class="flex items-center gap-2 text-xs">
                                <span class="bg-slate-100 text-slate-600 px-2 py-0.5 rounded border border-slate-200 font-mono tracking-wide">{{ $product->sku_code }}</span>
                                @if($product->brand)
                                    <span class="text-slate-400">•</span>
                                    <span class="text-indigo-600 font-semibold">{{ $product->brand->name }}</span>
                                @endif
                                @if($product->is_featured)
                                    <span class="text-amber-500 ml-1" title="Sản phẩm nổi bật"><i class="fa-solid fa-star"></i></span>
                                @endif
                            </div>
                        </td>

                        <!-- Price -->
                        <td class="px-4 py-3">
                            <div class="text-sm font-bold text-slate-700">
                                {{ number_format($product->price_min, 0, ',', '.') }} <span class="text-xs font-normal text-slate-500">vnđ</span>
                            </div>
                        </td>

                        <!-- Taxonomy -->
                        <td class="px-4 py-3">
                            <div class="flex flex-wrap gap-1 max-w-[200px]">
                                @foreach($product->categories->take(2) as $cat)
                                    <span class="px-2 py-1 text-[10px] font-medium bg-slate-100 text-slate-600 rounded-md border border-slate-200">
                                        {{ $cat->name }}
                                    </span>
                                @endforeach
                                @if($product->categories->count() > 2)
                                    <span class="px-2 py-1 text-[10px] bg-indigo-50 text-indigo-600 rounded-md border border-indigo-100 font-bold cursor-help" title="{{ $product->categories->pluck('name')->implode(', ') }}">
                                        +{{ $product->categories->count() - 2 }}
                                    </span>
                                @endif
                            </div>
                        </td>

                        <!-- Status -->
                        <td class="px-4 py-3 text-center">
                            @php
                                $statusStyles = [
                                    'published' => 'bg-emerald-100 text-emerald-700 border-emerald-200 ring-emerald-500/30',
                                    'draft' => 'bg-slate-100 text-slate-600 border-slate-200 ring-slate-500/30',
                                    'archived' => 'bg-rose-100 text-rose-700 border-rose-200 ring-rose-500/30',
                                ];
                                $statusLabel = [
                                    'published' => 'Đang bán',
                                    'draft' => 'Bản nháp',
                                    'archived' => 'Lưu trữ',
                                ];
                            @endphp
                            <span class="inline-flex items-center justify-center px-2.5 py-1 text-xs font-bold rounded-full border ring-1 ring-inset {{ $statusStyles[$product->status] ?? '' }}">
                                <span class="w-1.5 h-1.5 rounded-full bg-current mr-1.5 opacity-70"></span>
                                {{ $statusLabel[$product->status] ?? $product->status }}
                            </span>
                        </td>

                        <!-- Actions -->
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('admin.products.edit', $product->id) }}" class="p-2 text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition-colors border border-indigo-100 shadow-sm" title="Chỉnh sửa">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn chuyển sản phẩm này vào thùng rác?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-2 text-rose-600 bg-rose-50 hover:bg-rose-100 rounded-lg transition-colors border border-rose-100 shadow-sm" title="Xóa tạm">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-12 text-center text-slate-400">
                            <div class="flex flex-col items-center justify-center">
                                <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mb-3">
                                    <i class="fa-solid fa-box-open text-3xl text-slate-300"></i>
                                </div>
                                <p class="font-medium text-slate-500">Không tìm thấy sản phẩm nào.</p>
                                <p class="text-sm mt-1">Thử thay đổi bộ lọc hoặc thêm sản phẩm mới.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-slate-200 bg-slate-50">
            {{ $products->links() }}
        </div>
    </div>
</div>
@endsection