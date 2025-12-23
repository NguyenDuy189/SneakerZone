@extends('admin.layouts.app')
@section('title', 'Thuộc tính sản phẩm')
@section('header', 'Thuộc tính sản phẩm')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 container px-6 mx-auto">
    
    <!-- CỘT PHẢI: Form thêm mới -->
    <div class="md:col-span-1">
        <div class="bg-white p-6 rounded-lg shadow-md border border-gray-200 sticky top-20">
            <h3 class="text-lg font-bold text-gray-700 mb-4 border-b pb-2">Thêm thuộc tính mới</h3>
            
            <form action="{{ route('admin.attributes.store') }}" method="POST">
                @csrf
                <!-- Tên thuộc tính -->
                <div class="mb-4">
                    <label class="block text-sm font-bold text-gray-700 mb-2">
                        Tên thuộc tính <span class="text-red-500">*</span>
                    </label>
                    <input name="name" type="text" 
                        class="w-full rounded-lg border-gray-300 bg-gray-50 p-2.5 text-sm focus:ring-indigo-500 focus:border-indigo-500 @error('name') border-red-500 bg-red-50 @enderror" 
                        placeholder="VD: Size, Màu sắc, Chất liệu..." 
                        value="{{ old('name') }}" required>
                    
                    @error('name')
                        <p class="mt-1 text-xs text-red-600 font-semibold"><i class="fa-solid fa-circle-exclamation mr-1"></i><i class="fa-solid fa-circle-exclamation mr-1"></i>{{ $message }}</p></p>
                    @enderror
                </div>
                
                <!-- Loại hiển thị -->
                <div class="mb-6">
                    <label class="block text-sm font-bold text-gray-700 mb-2">
                        Loại hiển thị <span class="text-red-500">*</span>
                    </label>
                    <select name="type" class="w-full rounded-lg border-gray-300 bg-gray-50 p-2.5 text-sm focus:ring-indigo-500 cursor-pointer">
                        <option value="text">Văn bản / Số (Size, Chất liệu)</option>
                        <option value="color">Màu sắc (Color Picker)</option>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Chọn "Màu sắc" để hiện bảng chọn màu cho khách.</p>
                </div>

                <button type="submit" class="w-full px-4 py-2.5 text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 font-medium shadow-sm transition-transform active:scale-95">
                    <i class="fa-solid fa-plus mr-2"></i> Lưu thuộc tính
                </button>
            </form>
        </div>
    </div>

    <!-- CỘT TRÁI: Danh sách -->
    <div class="md:col-span-2">
        <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
            <table class="w-full whitespace-no-wrap">
                <thead class="bg-gray-50 border-b">
                    <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase">
                        <th class="px-4 py-3">Tên</th>
                        <th class="px-4 py-3">Mã Code</th>
                        <th class="px-4 py-3">Loại</th>
                        <th class="px-4 py-3">Số giá trị</th>
                        <th class="px-4 py-3 text-right">Cấu hình</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($attributes as $attr)
                    <tr class="text-gray-700 hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3 font-bold text-gray-800">{{ $attr->name }}</td>
                        <td class="px-4 py-3 text-sm font-mono text-gray-500">{{ $attr->code }}</td>
                        <td class="px-4 py-3 text-sm">
                            @if($attr->type == 'color') 
                                <span class="inline-flex items-center px-2 py-1 bg-purple-100 text-purple-700 rounded text-xs font-bold border border-purple-200">
                                    <span class="w-2 h-2 rounded-full bg-purple-500 mr-1"></span> Màu sắc
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-1 bg-gray-100 text-gray-700 rounded text-xs font-bold border border-gray-200">
                                    <i class="fa-solid fa-font mr-1 text-gray-400"></i> Text/Số
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <span class="px-2 py-1 bg-blue-50 text-blue-600 rounded-full text-xs font-bold">
                                {{ $attr->values_count }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.attributes.show', $attr->id) }}" class="inline-flex items-center px-3 py-1.5 bg-indigo-50 text-indigo-600 rounded-md hover:bg-indigo-100 text-xs font-bold mr-2 transition-colors">
                                <i class="fa-solid fa-gear mr-1"></i> Cấu hình
                            </a>
                            <form action="{{ route('admin.attributes.destroy', $attr->id) }}" method="POST" class="inline" onsubmit="return confirm('CẢNH BÁO: Xóa thuộc tính này sẽ xóa tất cả các biến thể sản phẩm liên quan. Bạn có chắc chắn không?')">
                                @csrf @method('DELETE')
                                <button class="inline-flex items-center px-2 py-1.5 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-md transition-colors">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-gray-500 italic">
                            Chưa có thuộc tính nào. Hãy thêm "Size" hoặc "Màu sắc" bên phải.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4 px-2">{{ $attributes->links() }}</div>
    </div>
</div>
@endsection