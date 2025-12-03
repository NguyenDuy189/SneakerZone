@extends('admin.layouts.app')

@section('title', 'Thùng rác sản phẩm')
@section('header', 'Sản phẩm đã xóa')

@section('content')
<div class="container px-6 mx-auto pb-10">
    <!-- Header -->
    <div class="flex justify-between items-center my-6">
        <h2 class="text-2xl font-bold text-slate-800">Thùng rác</h2>
        <a href="{{ route('admin.products.index') }}" class="flex items-center px-4 py-2 text-sm font-medium text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-all shadow-sm">
            <i class="fa-solid fa-arrow-left mr-2"></i> Quay lại danh sách
        </a>
    </div>

    @if(session('success'))
        <div class="p-4 mb-4 text-sm text-emerald-700 bg-emerald-50 rounded-lg border border-emerald-200 shadow-sm animate-fade-in-down">
            <i class="fa-solid fa-check-circle mr-1"></i> {{ session('success') }}
        </div>
    @endif

    <div class="w-full overflow-hidden rounded-xl shadow-md border border-slate-200 bg-white">
        <div class="w-full overflow-x-auto">
            <table class="w-full whitespace-no-wrap text-left">
                <thead>
                    <tr class="text-xs font-bold tracking-wider text-slate-500 uppercase border-b bg-slate-50">
                        <th class="px-4 py-4">Ảnh</th>
                        <th class="px-4 py-4">Tên sản phẩm</th>
                        <th class="px-4 py-4">SKU</th>
                        <th class="px-4 py-4">Giá bán</th>
                        <th class="px-4 py-4">Ngày xóa</th>
                        <th class="px-4 py-4 text-center">Hành động</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($products as $product)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-4 py-3">
                            <div class="relative w-12 h-12 rounded-lg border border-slate-200 bg-white overflow-hidden">
                                <img class="object-cover w-full h-full" 
                                     src="{{ $product->thumbnail ? asset('storage/' . $product->thumbnail) : 'https://placehold.co/100x100' }}">
                            </div>
                        </td>
                        <td class="px-4 py-3 font-semibold text-slate-800">{{ $product->name }}</td>
                        <td class="px-4 py-3 font-mono text-xs text-slate-500">{{ $product->sku_code }}</td>
                        <td class="px-4 py-3 text-sm font-bold text-slate-700">{{ number_format($product->price_min) }} ₫</td>
                        <td class="px-4 py-3 text-sm text-slate-500">{{ $product->deleted_at->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center space-x-2">
                                <!-- Restore -->
                                <form action="{{ route('admin.products.restore', $product->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="px-3 py-1.5 text-xs font-bold text-white bg-emerald-500 border border-transparent rounded-md hover:bg-emerald-600 transition-colors shadow-sm" title="Khôi phục">
                                        <i class="fa-solid fa-rotate-left mr-1"></i> Khôi phục
                                    </button>
                                </form>

                                <!-- Force Delete -->
                                <form action="{{ route('admin.products.force_delete', $product->id) }}" method="POST" onsubmit="return confirm('CẢNH BÁO: Hành động này sẽ xóa vĩnh viễn sản phẩm và toàn bộ hình ảnh liên quan khỏi server. Bạn có chắc chắn không?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="px-3 py-1.5 text-xs font-bold text-white bg-rose-600 border border-transparent rounded-md hover:bg-rose-700 transition-colors shadow-sm" title="Xóa vĩnh viễn">
                                        <i class="fa-solid fa-ban mr-1"></i> Xóa vĩnh viễn
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-12 text-center text-slate-400">
                            <i class="fa-solid fa-trash-can text-3xl mb-2 text-slate-300"></i>
                            <p>Thùng rác trống.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t bg-slate-50">
            {{ $products->links() }}
        </div>
    </div>
</div>
@endsection