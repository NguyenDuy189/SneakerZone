@extends('admin.layouts.app')

@section('title', 'Thêm Banner Mới')

@section('content')
<div class="container px-6 mx-auto pb-20 max-w-5xl">

    {{-- HEADER --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8 pt-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Thêm Banner</h1>
            <p class="text-slate-500 text-sm mt-1">Tạo banner quảng cáo mới cho website.</p>
        </div>
        <a href="{{ route('admin.banners.index') }}" 
           class="group inline-flex items-center px-5 py-2.5 bg-white border border-slate-200 rounded-xl text-slate-600 hover:border-indigo-300 hover:text-indigo-600 font-medium transition-all shadow-sm">
            <i class="fa-solid fa-arrow-left mr-2 group-hover:-translate-x-1 transition-transform"></i> Quay lại
        </a>
    </div>

    {{-- ERROR LIST --}}
    @if ($errors->any())
        <div class="p-4 mb-6 rounded-xl bg-rose-50 border border-rose-100 flex items-start gap-3 animate-fade-in-down">
            <div class="flex-shrink-0 w-8 h-8 rounded-full bg-rose-100 flex items-center justify-center text-rose-600 mt-0.5">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <div>
                <h3 class="text-rose-800 font-bold text-sm">Vui lòng kiểm tra lại dữ liệu</h3>
                <ul class="list-disc list-inside text-sm text-rose-600 mt-1 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    {{-- FORM --}}
    <form action="{{ route('admin.banners.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-12 gap-8">
            
            {{-- LEFT COLUMN: IMAGE UPLOAD (4/12) --}}
            <div class="md:col-span-4 space-y-6">
                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
                    <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-4">Hình ảnh Banner <span class="text-rose-500">*</span></h3>
                    
                    <div class="relative w-full aspect-video bg-slate-50 border-2 border-dashed border-slate-300 rounded-xl overflow-hidden hover:border-indigo-400 transition-colors group">
                        <img id="preview-img" src="https://placehold.co/600x400/f1f5f9/94a3b8?text=Upload+Image" class="w-full h-full object-cover">
                        
                        <label for="image-upload" class="absolute inset-0 flex flex-col items-center justify-center bg-slate-900/0 group-hover:bg-slate-900/40 transition-all cursor-pointer">
                            <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center shadow-lg opacity-0 group-hover:opacity-100 transform translate-y-4 group-hover:translate-y-0 transition-all duration-300">
                                <i class="fa-solid fa-cloud-arrow-up text-indigo-600"></i>
                            </div>
                            <span class="text-white text-xs font-bold mt-2 opacity-0 group-hover:opacity-100 transition-all duration-300 delay-75">Click để tải ảnh</span>
                        </label>
                        <input type="file" name="image" id="image-upload" class="hidden" accept="image/*" onchange="previewImage(this)">
                    </div>
                    <p class="text-xs text-slate-400 mt-3 text-center">Định dạng: JPG, PNG, WEBP. Max: 5MB.</p>
                </div>

                {{-- Status Card --}}
                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-bold text-slate-800">Trạng thái</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Hiển thị banner ngay sau khi lưu?</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" class="sr-only peer" checked>
                            <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                        </label>
                    </div>
                </div>
            </div>

            {{-- RIGHT COLUMN: INFO (8/12) --}}
            <div class="md:col-span-8 space-y-6">
                <div class="bg-white p-8 rounded-2xl border border-slate-200 shadow-sm">
                    <h3 class="text-lg font-bold text-slate-800 mb-6 flex items-center gap-2">
                        <i class="fa-regular fa-pen-to-square text-indigo-500"></i> Thông tin chi tiết
                    </h3>

                    <div class="space-y-6">
                        {{-- Title --}}
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Tiêu đề Banner <span class="text-rose-500">*</span></label>
                            <input type="text" name="title" value="{{ old('title') }}" placeholder="Nhập tiêu đề banner..." 
                                   class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition-all placeholder:text-slate-400">
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- Link --}}
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Đường dẫn (Link)</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                        <i class="fa-solid fa-link"></i>
                                    </div>
                                    <input type="url" name="link" value="{{ old('link') }}" placeholder="https://..." 
                                           class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition-all placeholder:text-slate-400">
                                </div>
                            </div>

                            {{-- Priority --}}
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Thứ tự ưu tiên</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                        <i class="fa-solid fa-arrow-down-1-9"></i>
                                    </div>
                                    <input type="number" name="priority" value="{{ old('priority', 0) }}" min="0" 
                                           class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition-all">
                                </div>
                                <p class="text-[10px] text-slate-400 mt-1">Số càng lớn càng hiển thị trước.</p>
                            </div>
                        </div>

                        {{-- Position --}}
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Vị trí hiển thị <span class="text-rose-500">*</span></label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <label class="relative flex items-center p-4 border border-slate-200 rounded-xl cursor-pointer hover:bg-slate-50 hover:border-indigo-300 transition-all has-[:checked]:border-indigo-500 has-[:checked]:bg-indigo-50 has-[:checked]:ring-1 has-[:checked]:ring-indigo-500">
                                    <input type="radio" name="position" value="home_slider" class="sr-only" {{ old('position') == 'home_slider' ? 'checked' : '' }}>
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center">
                                            <i class="fa-solid fa-images"></i>
                                        </div>
                                        <div>
                                            <span class="block text-sm font-bold text-slate-800">Home Slider</span>
                                            <span class="block text-xs text-slate-500">Slide chính trang chủ</span>
                                        </div>
                                    </div>
                                </label>

                                <label class="relative flex items-center p-4 border border-slate-200 rounded-xl cursor-pointer hover:bg-slate-50 hover:border-indigo-300 transition-all has-[:checked]:border-indigo-500 has-[:checked]:bg-indigo-50 has-[:checked]:ring-1 has-[:checked]:ring-indigo-500">
                                    <input type="radio" name="position" value="home_mid" class="sr-only" {{ old('position') == 'home_mid' ? 'checked' : '' }}>
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-lg bg-orange-100 text-orange-600 flex items-center justify-center">
                                            <i class="fa-solid fa-rectangle-ad"></i>
                                        </div>
                                        <div>
                                            <span class="block text-sm font-bold text-slate-800">Home Middle</span>
                                            <span class="block text-xs text-slate-500">Banner quảng cáo giữa trang</span>
                                        </div>
                                    </div>
                                </label>

                                <label class="relative flex items-center p-4 border border-slate-200 rounded-xl cursor-pointer hover:bg-slate-50 hover:border-indigo-300 transition-all has-[:checked]:border-indigo-500 has-[:checked]:bg-indigo-50 has-[:checked]:ring-1 has-[:checked]:ring-indigo-500">
                                    <input type="radio" name="position" value="sidebar" class="sr-only" {{ old('position') == 'sidebar' ? 'checked' : '' }}>
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center">
                                            <i class="fa-solid fa-table-columns"></i>
                                        </div>
                                        <div>
                                            <span class="block text-sm font-bold text-slate-800">Sidebar</span>
                                            <span class="block text-xs text-slate-500">Cột bên trang sản phẩm</span>
                                        </div>
                                    </div>
                                </label>

                                <label class="relative flex items-center p-4 border border-slate-200 rounded-xl cursor-pointer hover:bg-slate-50 hover:border-indigo-300 transition-all has-[:checked]:border-indigo-500 has-[:checked]:bg-indigo-50 has-[:checked]:ring-1 has-[:checked]:ring-indigo-500">
                                    <input type="radio" name="position" value="footer" class="sr-only" {{ old('position') == 'footer' ? 'checked' : '' }}>
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center">
                                            <i class="fa-solid fa-shoe-prints"></i>
                                        </div>
                                        <div>
                                            <span class="block text-sm font-bold text-slate-800">Footer</span>
                                            <span class="block text-xs text-slate-500">Cuối trang web</span>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        {{-- Description --}}
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Mô tả (Tùy chọn)</label>
                            <textarea name="content" rows="3" placeholder="Nhập ghi chú hoặc mô tả ngắn..."
                                      class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition-all placeholder:text-slate-400 resize-none">{{ old('content') }}</textarea>
                        </div>
                    </div>

                    {{-- Footer Action --}}
                    <div class="mt-8 pt-6 border-t border-slate-100 flex justify-end gap-4">
                        <a href="{{ route('admin.banners.index') }}" class="px-6 py-2.5 bg-white border border-slate-300 text-slate-700 font-bold rounded-xl hover:bg-slate-50 transition-colors">
                            Hủy bỏ
                        </a>
                        <button type="submit" class="px-8 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-bold rounded-xl shadow-lg shadow-indigo-500/30 transition-all transform hover:scale-[1.02] active:scale-[0.98]">
                            <i class="fa-solid fa-floppy-disk mr-2"></i> Lưu Banner
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    function previewImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('preview-img').src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endsection