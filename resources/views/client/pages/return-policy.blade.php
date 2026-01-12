@extends('client.layouts.app')

@section('title', 'Chính Sách Đổi Trả - Sneaker Zone')

@section('content')
<div class="bg-slate-50 py-10">
    <div class="container mx-auto px-4">
        {{-- Breadcrumb --}}
        <div class="text-sm text-slate-500 mb-6">
            <a href="/" class="hover:text-indigo-600">Trang chủ</a> <span class="mx-2">/</span> <span>Chính sách đổi trả</span>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8 md:p-12 max-w-4xl mx-auto prose prose-indigo text-slate-600">
            <h1 class="text-3xl font-bold text-slate-900 mb-6 text-center">Chính Sách Đổi Trả & Bảo Hành</h1>
            
            <p>Tại Sneaker Zone, chúng tôi cam kết mang đến sự hài lòng tuyệt đối cho khách hàng. Dưới đây là quy định đổi trả hàng hóa:</p>

            <h3>1. Điều kiện đổi trả</h3>
            <ul>
                <li>Sản phẩm được đổi trong vòng <strong>30 ngày</strong> kể từ ngày nhận hàng.</li>
                <li>Sản phẩm phải còn nguyên tem, mác, hộp và chưa qua sử dụng (đế giày chưa bị bẩn, mòn).</li>
                <li>Có đầy đủ hóa đơn mua hàng hoặc thông tin đơn hàng trên hệ thống.</li>
                <li>Sản phẩm không nằm trong danh sách "Sale off" hoặc "Xả kho" (trừ khi có lỗi từ nhà sản xuất).</li>
            </ul>

            <h3>2. Quy trình đổi hàng</h3>
            <p>Khách hàng có thể mang trực tiếp đến cửa hàng hoặc gửi chuyển phát nhanh:</p>
            <ol>
                <li>Liên hệ hotline hoặc fanpage để thông báo tình trạng đổi trả.</li>
                <li>Đóng gói sản phẩm cẩn thận (nên dùng hộp carton cứng để bảo vệ hộp giày).</li>
                <li>Gửi về địa chỉ kho: 123 Đường ABC, Quận 1, TP.HCM.</li>
                <li>Sau khi nhận và kiểm tra, chúng tôi sẽ gửi sản phẩm mới cho bạn trong vòng 3-5 ngày.</li>
            </ol>

            <h3>3. Chính sách hoàn tiền</h3>
            <p>Áp dụng khi sản phẩm lỗi do nhà sản xuất mà hết size để đổi:</p>
            <ul>
                <li>Hoàn tiền 100% qua chuyển khoản ngân hàng trong vòng 48h làm việc.</li>
                <li>Phí vận chuyển 2 chiều sẽ do Sneaker Zone chi trả.</li>
            </ul>
        </div>
    </div>
</div>
@endsection