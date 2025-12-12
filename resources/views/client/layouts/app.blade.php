<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SneakerZone - Premium Footwear')</title>

    {{-- 1. FONTS & ICONS --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    {{-- 2. CUSTOM CSS --}}
    <style>
        :root {
            --primary-color: #111111;
            --secondary-color: #757575;
            --accent-color: #fa541c; /* Orange accent like in your image */
            --bg-light: #f5f5f5;
            --border-color: #e5e5e5;
        }

        body {
            font-family: 'Inter', sans-serif;
            color: var(--primary-color);
            background-color: #ffffff;
            -webkit-font-smoothing: antialiased;
        }

        a { text-decoration: none; color: inherit; transition: 0.2s; }
        a:hover { color: var(--accent-color); }

        /* --- HEADER --- */
        .top-bar {
            background-color: var(--bg-light);
            font-size: 11px;
            padding: 6px 0;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .main-header {
            background-color: #fff;
            padding: 15px 0;
            border-bottom: 1px solid var(--border-color);
            box-shadow: 0 2px 10px rgba(0,0,0,0.03);
        }

        .navbar-brand {
            font-weight: 800;
            font-size: 28px;
            letter-spacing: -1px;
            text-transform: uppercase;
        }

        .nav-link {
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            margin: 0 12px;
            letter-spacing: 0.5px;
        }

        .header-icons .btn-icon {
            font-size: 20px;
            margin-left: 20px;
            position: relative;
        }

        /* --- SEARCH BAR --- */
        .search-wrapper {
            position: relative;
            width: 250px;
        }
        .search-input {
            width: 100%;
            background: var(--bg-light);
            border: none;
            border-radius: 50px;
            padding: 8px 15px 8px 40px;
            font-size: 13px;
            font-weight: 500;
            transition: 0.3s;
        }
        .search-input:focus {
            background: #fff;
            box-shadow: 0 0 0 2px var(--border-color);
            outline: none;
        }
        .search-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--secondary-color);
            font-size: 14px;
        }

        /* --- FOOTER --- */
        footer {
            background-color: #111;
            color: #b0b0b0;
            padding-top: 60px;
            padding-bottom: 30px;
            font-size: 13px;
        }
        footer h5 {
            color: #fff;
            font-weight: 800;
            font-size: 14px;
            text-transform: uppercase;
            margin-bottom: 25px;
            letter-spacing: 1px;
        }
        footer .footer-link {
            display: block;
            margin-bottom: 12px;
            color: #b0b0b0;
            font-weight: 400;
        }
        footer .footer-link:hover { color: #fff; padding-left: 5px; }
        
        .social-btn {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: #333;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
            color: #fff;
            transition: 0.3s;
        }
        .social-btn:hover { background: var(--accent-color); color: #fff; }

    </style>
    @yield('css')
</head>
<body class="d-flex flex-column min-vh-100">

    {{-- TOP BAR --}}
    <div class="top-bar d-none d-md-block">
        <div class="container d-flex justify-content-between">
            <span class="text-muted">Miễn phí vận chuyển cho đơn hàng trên 2.000.000đ</span>
            <div>
                <a href="#" class="mx-2 text-dark">Trợ giúp</a>
                <span class="text-muted">|</span>
                <a href="#" class="mx-2 text-dark">Theo dõi đơn hàng</a>
                <span class="text-muted">|</span>
                <a href="{{ route('login')}}" class="mx-2 text-dark">Đăng nhập</a>
            </div>
        </div>
    </div>

    {{-- MAIN HEADER --}}
    <nav class="navbar navbar-expand-lg main-header sticky-top">
        <div class="container">
            <a class="navbar-brand" href="{{ route('home') }}">Sneaker<span style="color: var(--accent-color);">Up</span></a>
            
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarContent">
                <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link" href="{{ route('client.products.index') }}">New Arrivals</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Men</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Women</a></li>
                    <li class="nav-item"><a class="nav-link" href="#" style="color: var(--accent-color);">Sale</a></li>
                </ul>

                <div class="header-icons d-flex align-items-center">
                    <div class="search-wrapper d-none d-lg-block">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" class="search-input" placeholder="Tìm kiếm sản phẩm...">
                    </div>
                    <a href="#" class="btn-icon"><i class="far fa-heart"></i></a>
                    <a href="#" class="btn-icon">
                        <i class="fas fa-shopping-bag"></i>
                        {{-- <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 9px; padding: 3px 5px;">2</span> --}}
                    </a>
                </div>
            </div>
        </div>
    </nav>

    {{-- MAIN CONTENT --}}
    <main class="flex-grow-1">
        @yield('content')
    </main>

    {{-- FOOTER --}}
    <footer>
        <div class="container">
            <div class="row">
                <div class="col-lg-3 col-md-6 mb-4">
                    <h5>Sản Phẩm</h5>
                    <a href="#" class="footer-link">Giày Chạy Bộ</a>
                    <a href="#" class="footer-link">Giày Bóng Rổ</a>
                    <a href="#" class="footer-link">Giày Tập Luyện</a>
                    <a href="#" class="footer-link">Thời Trang & Lifestyle</a>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <h5>Hỗ Trợ</h5>
                    <a href="#" class="footer-link">Tình trạng đơn hàng</a>
                    <a href="#" class="footer-link">Vận chuyển & Giao hàng</a>
                    <a href="#" class="footer-link">Chính sách đổi trả</a>
                    <a href="#" class="footer-link">Phương thức thanh toán</a>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <h5>Về Chúng Tôi</h5>
                    <a href="#" class="footer-link">Câu chuyện thương hiệu</a>
                    <a href="#" class="footer-link">Tuyển dụng</a>
                    <a href="#" class="footer-link">Tin tức & Sự kiện</a>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <h5>Kết Nối</h5>
                    <div class="d-flex mb-3">
                        <a href="#" class="social-btn"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social-btn"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-btn"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-btn"><i class="fab fa-youtube"></i></a>
                    </div>
                    <p class="text-muted">Đăng ký nhận tin để không bỏ lỡ ưu đãi mới nhất.</p>
                </div>
            </div>
            <div class="row pt-4 mt-4 border-top border-secondary">
                <div class="col-md-6 text-center text-md-start mb-2">
                    <span class="text-muted">© 2024 SneakerUp. All Rights Reserved.</span>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <i class="fab fa-cc-visa fa-lg mx-1 text-muted"></i>
                    <i class="fab fa-cc-mastercard fa-lg mx-1 text-muted"></i>
                    <i class="fab fa-cc-paypal fa-lg mx-1 text-muted"></i>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>