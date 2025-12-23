@extends('admin.layouts.app')
@section('title', 'Tạo Flash Sale Mới')

@section('content')
<div class="container px-6 mx-auto grid">
    <h2 class="my-6 text-2xl font-semibold text-gray-700">Tạo Chiến Dịch Mới</h2>

    <div class="px-4 py-3 mb-8 bg-white rounded-lg shadow-md">
        <form action="{{ route('admin.flash_sales.store') }}" method="POST">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="col-span-2">
                    <label class="block text-sm">
                        <span class="text-gray-700">Tên chiến dịch</span>
                        <input name="name" value="{{ old('name') }}" class="block w-full mt-1 text-sm border-gray-300 rounded-md focus:border-indigo-400 focus:ring focus:ring-indigo-300" placeholder="VD: Flash Sale 12/12" required />
                        @error('name') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </label>
                </div>

                <div>
                    <label class="block text-sm">
                        <span class="text-gray-700">Thời gian bắt đầu</span>
                        <input name="start_time" id="start_time" value="{{ old('start_time') }}" class="block w-full mt-1 text-sm border-gray-300 rounded-md" placeholder="Chọn ngày giờ..." required />
                        @error('start_time') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </label>
                </div>

                <div>
                    <label class="block text-sm">
                        <span class="text-gray-700">Thời gian kết thúc</span>
                        <input name="end_time" id="end_time" value="{{ old('end_time') }}" class="block w-full mt-1 text-sm border-gray-300 rounded-md" placeholder="Chọn ngày giờ..." required />
                        @error('end_time') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </label>
                </div>

                <div class="col-span-2">
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" class="form-checkbox text-indigo-600 h-5 w-5" checked>
                        <span class="text-gray-700 font-medium">Kích hoạt ngay</span>
                    </label>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <a href="{{ route('admin.flash_sales.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300">Hủy</a>
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">Lưu Chiến Dịch</button>
            </div>
        </form>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    flatpickr("#start_time", { enableTime: true, dateFormat: "Y-m-d H:i", time_24hr: true });
    flatpickr("#end_time", { enableTime: true, dateFormat: "Y-m-d H:i", time_24hr: true });
</script>
@endsection