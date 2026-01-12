@extends('client.layouts.app')

@section('title', 'Về Chúng Tôi - Sneaker Zone')

@section('content')
<div class="bg-slate-50 py-10">
    <div class="container mx-auto px-4">
        {{-- Breadcrumb --}}
        <div class="text-sm text-slate-500 mb-6">
            <a href="/" class="hover:text-indigo-600">Trang chủ</a>
            <span class="mx-2">/</span>
            <span class="text-slate-800">Về chúng tôi</span>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8 md:p-12 max-w-4xl mx-auto">
            <h1 class="text-3xl md:text-4xl font-bold text-slate-900 mb-6 text-center">Câu chuyện Sneaker Zone</h1>

            {{-- Nội dung bài viết --}}
            <div class="prose prose-lg prose-indigo mx-auto text-slate-600 space-y-4">
                <p class="lead text-xl font-medium text-slate-800">
                    Chào mừng bạn đến với Sneaker Zone – Điểm đến hàng đầu cho những tín đồ đam mê giày sneaker chính hãng.
                </p>

                <p>
                    Được thành lập vào năm 2024, Sneaker Zone ra đời với sứ mệnh mang đến những đôi giày chất lượng nhất,
                    thời thượng nhất từ các thương hiệu hàng đầu thế giới như Nike, Adidas, Puma, New Balance... đến tận tay người tiêu dùng Việt Nam.
                </p>

                <h3 class="text-xl font-bold text-slate-900 mt-6">Cam kết của chúng tôi</h3>
                <ul class="list-disc pl-5 space-y-2">
                    <li><strong>100% Chính hãng:</strong> Đền bù gấp 10 lần nếu phát hiện hàng giả.</li>
                    <li><strong>Đa dạng mẫu mã:</strong> Luôn cập nhật những mẫu giày hot trend mới nhất.</li>
                    <li><strong>Giá cả cạnh tranh:</strong> Mang lại mức giá tốt nhất thị trường.</li>
                    <li><strong>Dịch vụ tận tâm:</strong> Hỗ trợ đổi trả trong 30 ngày, bảo hành keo chỉ trọn đời.</li>
                </ul>

                <div class="my-8 rounded-xl overflow-hidden">
                    {{-- Đã thay thế ảnh placeholder bằng ảnh cửa hàng giày thực tế --}}
                    <img src="https://images.unsplash.com/photo-1556906781-9a412961c28c?q=80&w=1000&auto=format&fit=crop" alt="Không gian cửa hàng Sneaker Zone hiện đại" class="w-full h-64 md:h-[400px] object-cover">
                </div>

                <p>
                    Hãy đến và trải nghiệm không gian mua sắm đẳng cấp tại Sneaker Zone. Chúng tôi không chỉ bán giày,
                    chúng tôi bán phong cách sống!
                </p>
            </div>
        </div>
    </div>
</div>
@endsection