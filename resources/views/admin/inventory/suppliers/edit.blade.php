@extends('admin.layouts.app')
@section('title', 'Sửa thông tin: ' . $supplier->name)

@section('content')
<div class="container px-6 mx-auto mb-20 fade-in">
    
    <div class="flex items-center gap-4 my-6">
        <a href="{{ route('admin.suppliers.index') }}" class="w-10 h-10 flex items-center justify-center rounded-xl bg-white border border-slate-200 text-slate-500 hover:bg-slate-50 hover:text-indigo-600 transition-all shadow-sm">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Cập nhật thông tin</h2>
            <p class="text-sm text-slate-500 mt-0.5">ID: #{{ $supplier->id }} — {{ $supplier->name }}</p>
        </div>
    </div>

    <div class="max-w-3xl mx-auto">
        <form action="{{ route('admin.suppliers.update', $supplier->id) }}" method="POST" class="bg-white p-8 rounded-2xl shadow-sm border border-slate-200">
            @csrf
            @method('PUT')
            
            <h3 class="text-lg font-bold text-slate-800 mb-6 flex items-center gap-2">
                <i class="fa-solid fa-building text-indigo-500"></i> Thông tin doanh nghiệp
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1.5">Mã nhà cung cấp <span class="text-rose-500">*</span></label>
                    <input type="text" name="code" value="{{ old('code', $supplier->code) }}" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm font-mono uppercase focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 shadow-sm">
                    @error('code') <p class="text-xs text-rose-500 mt-1 font-medium">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1.5">Tên nhà cung cấp <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $supplier->name) }}" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 shadow-sm">
                    @error('name') <p class="text-xs text-rose-500 mt-1 font-medium">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="border-t border-slate-100 my-6"></div>

            <h3 class="text-lg font-bold text-slate-800 mb-6 flex items-center gap-2">
                <i class="fa-solid fa-address-book text-emerald-500"></i> Thông tin liên hệ
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="md:col-span-2">
                    <label class="block text-sm font-bold text-slate-700 mb-1.5">Người đại diện / Liên hệ</label>
                    <input type="text" name="contact_name" value="{{ old('contact_name', $supplier->contact_name) }}" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 shadow-sm">
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1.5">Email</label>
                    <input type="email" name="email" value="{{ old('email', $supplier->email) }}" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 shadow-sm">
                    @error('email') <p class="text-xs text-rose-500 mt-1 font-medium">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1.5">Số điện thoại</label>
                    <input type="text" name="phone" value="{{ old('phone', $supplier->phone) }}" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 shadow-sm">
                    @error('phone') <p class="text-xs text-rose-500 mt-1 font-medium">{{ $message }}</p> @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-bold text-slate-700 mb-1.5">Địa chỉ kho / Văn phòng</label>
                    <textarea name="address" rows="3" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 shadow-sm">{{ old('address', $supplier->address) }}</textarea>
                </div>
            </div>

            <div class="pt-4 flex gap-4">
                <button type="submit" class="flex-1 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-lg shadow-indigo-600/20 transition-all active:scale-95 flex items-center justify-center gap-2">
                    <i class="fa-solid fa-floppy-disk"></i> Lưu thay đổi
                </button>
                <a href="{{ route('admin.suppliers.index') }}" class="px-6 py-3 bg-white border border-slate-200 text-slate-600 font-bold rounded-xl hover:bg-slate-50 transition-colors">
                    Hủy
                </a>
            </div>
        </form>
    </div>
</div>
@endsection