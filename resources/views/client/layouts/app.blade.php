<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SneakerZone')</title>

    {{-- 1. IMPORT CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    {{-- CSS TÙY CHỈNH CHO GIAO DIỆN GIỐNG ẢNH 1 --}}
    <style>
        body {
            font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
            color: #111;
        }

        /* 1. TOP BAR (Thanh nhỏ xám bên trên) */
        .top-bar {
            background-color: #f5f5f5;
            font-size: 12px;
            padding: 8px 0;
            font-weight: 500;
        }
        .top-bar a {
            color: #111;
            text-decoration: none;
            margin-left: 15px;
        }
        .top-bar a:hover { text-decoration: underline; }

        /* 2. MAIN HEADER (Menu chính) */
        .main-header {
            background-color: #fff;
            padding: 0 20px; /* Padding giống ảnh */
            border-bottom: 1px solid #e5e5e5; /* Viền mờ ngăn cách */
        }
        
        .navbar-brand {
            font-weight: 900;
            font-size: 24px;
            letter-spacing: -1px;
        }

        /* Menu Link */
        .nav-link {
            color: #111 !important;
            font-weight: 600;
            margin: 0 10px;
            position: relative;
        }
        
        /* Hiệu ứng gạch chân khi hover menu */
        .nav-link::after {
            content: '';
            position: absolute;
            width: 100%;
            transform: scaleX(0);
            height: 2px;
            bottom: 0;
            left: 0;
            background-color: #111;
            transform-origin: bottom right;
            transition: transform 0.25s ease-out;
        }
        .nav-link:hover::after {
            transform: scaleX(1);
            transform-origin: bottom left;
        }

        /* Icons bên phải */
        .header-icons .btn-icon {
            color: #111;
            font-size: 20px;
            margin-left: 15px;
            text-decoration: none;
            transition: 0.2s;
        }
        .header-icons .btn-icon:hover {
            opacity: 0.6;
        }

        /* 3. FOOTER (Chân trang chuyên nghiệp hơn) */
        footer {
            background-color: #111;
            color: #7e7e7e;
            padding: 40px 0;
            font-size: 12px;
        }
        footer h5 {
            color: #fff;
            font-weight: 800;
            font-size: 14px;
            margin-bottom: 20px;
            text-transform: uppercase;
        }
        footer a {
            color: #7e7e7e;
            text-decoration: none;
            display: block;
            margin-bottom: 10px;
        }
        footer a:hover { color: #fff; }
    </style>
    
    @yield('css')
</head>
<body class="d-flex flex-column min-vh-100">

    {{-- ================================================================ --}}
    {{-- HEADER (PHẦN ĐẦU TRANG - ĐÃ SỬA GIỐNG ẢNH) --}}
    {{-- ================================================================ --}}
    
    <div class="header-icons d-flex align-items-center">

    @auth
        <a href="{{ route('account.index') }}" class="btn-icon">
            <i class="far fa-user"></i>
        </a>

        <a href="{{ route('cart.index') }}" class="btn-icon">
            <i class="fas fa-shopping-bag"></i>
        </a>
    @else
        <a href="{{ route('login') }}" class="btn-icon">
            <i class="far fa-user"></i>
        </a>
    @endauth

    </div>


    <nav class="navbar navbar-expand-lg main-header sticky-top">
        <div class="container-fluid px-4">
            
            <a class="navbar-brand" href="{{ route('home') }}">
                {{-- Bạn có thể thay bằng thẻ <img> nếu có logo ảnh --}}
                SNEAKERZONE
            </a>

            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse justify-content-center" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('client.products.index') }}">Trang Chủ</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('client.products.index') }}">Sản Phẩm Mới</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Nam</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Nữ</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Giảm Giá</a>
                    </li>
                </ul>
            </div>

            <div class="header-icons d-flex align-items-center">
                <div class="d-none d-lg-block me-3 position-relative">
                    <form action="{{ route('client.products.search') }}" method="GET" class="d-none d-lg-block me-3 position-relative">
    <input 
        type="text" 
        name="q"
        value="{{ request('q') }}"
        class="form-control rounded-pill bg-light border-0"
        placeholder="Tìm kiếm sản phẩm..."
        style="padding-left: 35px; height: 35px; width: 200px;"
    >
    <i class="fas fa-search position-absolute text-muted" style="top: 10px; left: 12px;"></i>
</form>

                    <i class="fas fa-search position-absolute text-muted" style="top: 10px; left: 12px;"></i>
                </div>
                
                <a href="#" class="btn-icon"><i class="far fa-heart"></i></a>
                @auth
    <a href="{{ route('cart.index') }}" class="btn-icon position-relative">
        <i class="fas fa-shopping-bag"></i>
    </a>
@else
    <a href="{{ route('login') }}" class="btn-icon position-relative">
        <i class="fas fa-shopping-bag"></i>
    </a>
@endauth

            </div>
        </div>
    </nav>

    {{-- ================================================================ --}}
    {{-- NỘI DUNG CHÍNH (CONTENT) --}}
    {{-- ================================================================ --}}
    <main class="flex-grow-1">
        @yield('content') 
    </main>
    
    {{-- ================================================================ --}}
    {{-- FOOTER (PHẦN CHÂN TRANG - STYLE NIKE) --}}
    {{-- ================================================================ --}}
    <footer>
        <div class="container">
            <div class="row">
                <div class="col-md-3 mb-4">
                    <h5>TÌM CỬA HÀNG</h5>
                    <a href="#">Trở thành thành viên</a>
                    <a href="#">Gửi phản hồi</a>
                    <a href="#">Khuyến mãi</a>
                </div>
                <div class="col-md-3 mb-4">
                    <h5>TRỢ GIÚP</h5>
                    <a href="#">Trạng thái đơn hàng</a>
                    <a href="#">Vận chuyển & Giao hàng</a>
                    <a href="#">Trả hàng</a>
                    <a href="#">Phương thức thanh toán</a>
                </div>
                <div class="col-md-3 mb-4">
                    <h5>VỀ SNEAKERZONE</h5>
                    <a href="#">Tin tức</a>
                    <a href="#">Nghề nghiệp</a>
                    <a href="#">Nhà đầu tư</a>
                    <a href="#">Bền vững</a>
                </div>
                <div class="col-md-3 mb-4 text-md-end text-start">
                    <a href="#" class="d-inline me-3 text-white fs-5"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="d-inline me-3 text-white fs-5"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="d-inline me-3 text-white fs-5"><i class="fab fa-youtube"></i></a>
                    <a href="#" class="d-inline text-white fs-5"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
            <div class="row mt-4 pt-3 border-top border-secondary">
                <div class="col-md-6 text-md-start text-center">
                    <span class="text-muted"><i class="fas fa-map-marker-alt me-2"></i> Việt Nam</span>
                </div>
                <div class="col-md-6 text-md-end text-center">
                    <span class="text-muted">&copy; {{ date('Y') }} SneakerZone. All Rights Reserved.</span>
                </div>
            </div>
        </div>
    </footer>

    {{-- 2. IMPORT JAVASCRIPT --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')

</body>
</html>