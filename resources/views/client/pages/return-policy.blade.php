@extends('client.layouts.app')

@section('title', 'Chính sách đổi trả - Sneaker Zone')

@section('content')
    <div class="container mx-auto px-4 py-16 max-w-4xl">
        <h1 class="text-3xl font-display font-bold text-slate-900 mb-8 pb-4 border-b border-slate-200">
            Chính sách đổi trả & Hoàn tiền
        </h1>
        
        <div class="prose prose-slate max-w-none prose-headings:font-display prose-headings:text-slate-900 prose-a:text-indigo-600 prose-li:marker:text-indigo-600">
            <h3>1. Điều kiện đổi hàng</h3>
            <p>Quý khách có thể đổi hàng trong vòng <strong>07 ngày</strong> kể từ ngày nhận hàng nếu thỏa mãn các điều kiện sau:</p>
            <ul>
                <li>Sản phẩm còn nguyên tem mác, hộp giày (box) và phụ kiện đi kèm.</li>
                <li>Sản phẩm chưa qua sử dụng, không bị dơ bẩn, trầy xước.</li>
                <li>Có hóa đơn mua hàng hoặc thông tin đơn hàng trên hệ thống.</li>
                <li>Sản phẩm bị lỗi do nhà sản xuất hoặc do vận chuyển (cần có video quay lại quá trình mở hộp).</li>
            </ul>

            <h3>2. Các trường hợp không được hỗ trợ</h3>
            <ul>
                <li>Sản phẩm đã qua sử dụng hoặc bị hư hỏng do người dùng.</li>
                <li>Sản phẩm mua trong các chương trình Sale trên 50% (Flash Sale, Black Friday...).</li>
                <li>Quá thời hạn 7 ngày kể từ khi nhận hàng.</li>
            </ul>

            <h3>3. Quy trình đổi trả</h3>
            <p>Vui lòng liên hệ với chúng tôi qua Hotline hoặc Fanpage để được hướng dẫn chi tiết:</p>
            <ol>
                <li>Liên hệ bộ phận CSKH báo tình trạng sản phẩm.</li>
                <li>Đóng gói sản phẩm cẩn thận và gửi về địa chỉ cửa hàng.</li>
                <li>Chúng tôi sẽ kiểm tra và gửi lại sản phẩm mới cho quý khách trong vòng 3-5 ngày làm việc.</li>
            </ol>
        </div>
    </div>
@endsection