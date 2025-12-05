@extends('admin.layouts.app')
@section('title', 'Chỉnh sửa Flash Sale')

@section('content')
<div class="container px-6 mx-auto grid">
    <div class="flex flex-col md:flex-row items-center my-6 gap-4">
        <a href="{{ route('admin.flash_sales.index') }}" class="group w-12 h-12 flex items-center justify-center bg-white border border-gray-200 rounded-xl shadow-sm hover:shadow-md hover:bg-gray-50 hover:border-indigo-300 transition-all duration-200">
            <i class="fa-solid fa-arrow-left text-xl text-gray-500 group-hover:text-indigo-600"></i>
        </a>
        <span class="text-gray-300">|</span>
                <span class="text-sm text-gray-500 uppercase tracking-wide">Quản lý khuyến mãi</span>
    </div>
    <h3 class="text-2xl font-semibold text-gray-700">Chỉnh sửa chiến dịch</h3>

    @if($flashSale->is_running)
    <div class="p-4 mb-4 text-sm text-amber-700 bg-amber-50 rounded-lg border border-amber-200 flex items-center">
        <i class="fa-solid fa-triangle-exclamation mr-2 text-lg"></i>
        <span>Chiến dịch này <strong>đang diễn ra</strong>. Hãy cẩn thận khi thay đổi thời gian để tránh ảnh hưởng khách hàng.</span>
    </div>
    @endif

    <div class="px-4 py-3 mb-8 bg-white rounded-lg shadow-md">
        <form action="{{ route('admin.flash_sales.update', $flashSale->id) }}" method="POST">
            @csrf
            @method('PUT') <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="col-span-2">
                    <label class="block text-sm">
                        <span class="text-gray-700">Tên chiến dịch</span>
                        <input name="name" 
                               value="{{ old('name', $flashSale->name) }}" 
                               class="block w-full mt-1 text-sm border-gray-300 rounded-md focus:border-indigo-400 focus:ring focus:ring-indigo-300" 
                               placeholder="VD: Flash Sale 12/12" required />
                        @error('name') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </label>
                </div>

                <div>
                    <label class="block text-sm">
                        <span class="text-gray-700">Thời gian bắt đầu</span>
                        <input name="start_time" 
                               id="start_time" 
                               value="{{ old('start_time', $flashSale->start_time->format('Y-m-d H:i')) }}" 
                               class="block w-full mt-1 text-sm border-gray-300 rounded-md" 
                               placeholder="Chọn ngày giờ..." required />
                        @error('start_time') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </label>
                </div>

                <div>
                    <label class="block text-sm">
                        <span class="text-gray-700">Thời gian kết thúc</span>
                        <input name="end_time" 
                               id="end_time" 
                               value="{{ old('end_time', $flashSale->end_time->format('Y-m-d H:i')) }}" 
                               class="block w-full mt-1 text-sm border-gray-300 rounded-md" 
                               placeholder="Chọn ngày giờ..." required />
                        @error('end_time') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </label>
                </div>

                <div class="col-span-2">
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" 
                               class="form-checkbox text-indigo-600 h-5 w-5" 
                               {{ old('is_active', $flashSale->is_active) ? 'checked' : '' }}>
                        <span class="text-gray-700 font-medium">Kích hoạt hoạt động</span>
                    </label>
                    <p class="text-xs text-gray-500 mt-1 ml-7">Tắt mục này để tạm dừng chiến dịch khẩn cấp.</p>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <button type="reset" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300">Khôi phục</button>
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">Cập nhật</button>
            </div>
        </form>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    // Cấu hình Flatpickr, tự nhận value từ thẻ input để set defaultDate
    flatpickr("#start_time", { enableTime: true, dateFormat: "Y-m-d H:i", time_24hr: true });
    flatpickr("#end_time", { enableTime: true, dateFormat: "Y-m-d H:i", time_24hr: true });
</script>
@endsection