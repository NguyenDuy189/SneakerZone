@extends('client.layouts.app')

@section('title', 'Chính Sách Bảo Mật - Sneaker Zone')

@section('content')
<div class="bg-slate-50 py-10">
    <div class="container mx-auto px-4">
        <div class="text-sm text-slate-500 mb-6">
            <a href="/" class="hover:text-indigo-600">Trang chủ</a> <span class="mx-2">/</span> <span>Chính sách bảo mật</span>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8 md:p-12 max-w-4xl mx-auto prose prose-indigo text-slate-600">
            <h1 class="text-3xl font-bold text-slate-900 mb-6 text-center">Chính Sách Bảo Mật Thông Tin</h1>
            
            <h3>1. Mục đích thu thập thông tin</h3>
            <p>Sneaker Zone thu thập thông tin cá nhân của khách hàng (Họ tên, SĐT, Email, Địa chỉ) nhằm mục đích:</p>
            <ul>
                <li>Xử lý đơn hàng và giao hàng.</li>
                <li>Gửi thông báo về trạng thái đơn hàng.</li>
                <li>Gửi thông tin khuyến mãi (chỉ khi khách hàng đăng ký nhận tin).</li>
                <li>Hỗ trợ đổi trả và bảo hành.</li>
            </ul>

            <h3>2. Cam kết bảo mật</h3>
            <p>Chúng tôi cam kết <strong>không chia sẻ, bán hoặc cho thuê</strong> thông tin cá nhân của khách hàng cho bất kỳ bên thứ ba nào, ngoại trừ các đơn vị vận chuyển (để giao hàng).</p>

            <h3>3. Thời gian lưu trữ</h3>
            <p>Thông tin cá nhân của khách hàng sẽ được lưu trữ cho đến khi có yêu cầu hủy bỏ hoặc khách hàng tự đăng nhập và thực hiện hủy bỏ. Còn lại trong mọi trường hợp thông tin cá nhân thành viên sẽ được bảo mật trên máy chủ của Sneaker Zone.</p>
        </div>
    </div>
</div>
@endsection