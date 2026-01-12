<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sneaker Zone - Đẳng cấp giày chính hãng')</title>

    {{-- 1. FONTS (Inter & Archivo) --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo:wght@400;700;900&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">

    {{-- 2. ICONS (FontAwesome) --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    {{-- 3. TAILWIND CSS --}}
    <script src="https://cdn.tailwindcss.com"></script>
    
    {{-- 4. ALPINE.JS --}}
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    {{-- 5. ANIMATE ON SCROLL (AOS) --}}
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    {{-- Config Tailwind --}}
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { 
                        sans: ['Inter', 'sans-serif'],
                        display: ['Archivo', 'sans-serif'],
                    },
                    colors: {
                        primary: '#0f172a', // Slate 900
                        accent: '#4f46e5',  // Indigo 600
                    }
                }
            }
        }
    </script>

    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Inter', sans-serif; }
        h1, h2, h3, h4, h5, .font-display { font-family: 'Archivo', sans-serif; }
        
        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
</head>
<body class="bg-white text-slate-900 antialiased selection:bg-indigo-500 selection:text-white flex flex-col min-h-screen" 
      x-data="{ mobileMenuOpen: false, searchOpen: false, cartOpen: false }">

    {{-- =========================================================== --}}
    {{-- 1. TOP BAR (Thông báo) --}}
    {{-- =========================================================== --}}
    <div class="bg-slate-900 text-white text-[11px] font-bold tracking-widest uppercase py-2.5 text-center relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-r from-indigo-600 via-purple-600 to-indigo-600 opacity-50 animate-pulse"></div>
        <span class="relative z-10 flex justify-center items-center gap-2">
            <i class="fa-solid fa-bolt text-yellow-400"></i> 
            Miễn phí vận chuyển cho đơn hàng từ 2.000.000đ
        </span>
    </div>

    {{-- =========================================================== --}}
    {{-- 2. HEADER (Sticky & Glassmorphism) --}}
    {{-- =========================================================== --}}
    <header 
        x-data="{ isScrolled: false }"
        x-init="isScrolled = (window.scrollY > 10)"
        @scroll.window="isScrolled = (window.scrollY > 10)"
        class="sticky top-0 z-50 w-full transition-all duration-300 border-b"
        :class="isScrolled ? 'bg-white/90 backdrop-blur-md border-slate-100 shadow-sm py-2' : 'bg-white border-transparent py-4'"
    >
        
        <div class="container mx-auto px-4 md:px-6">
            <div class="flex items-center justify-between">
                
                {{-- LOGO --}}
                <div class="flex-shrink-0">
                    <a href="{{ route('client.home') }}" class="flex items-center gap-2 group">
                        <i class="fa-solid fa-shoe-prints text-3xl text-indigo-600 group-hover:-rotate-12 transition-transform duration-300"></i>
                        <span class="font-display font-black text-2xl tracking-tighter text-slate-900">
                            SNEAKER<span class="text-indigo-600">ZONE</span>.
                        </span>
                    </a>
                </div>

                {{-- DESKTOP MENU --}}
                <nav class="hidden lg:flex items-center gap-8">
                    <a href="{{ route('client.home') }}" class="text-sm font-bold text-slate-600 hover:text-indigo-600 uppercase tracking-wide transition-colors {{ request()->routeIs('client.home') ? 'text-indigo-600' : '' }}">Trang chủ</a>
                    
                    <a href="{{ route('client.products.index') }}" class="text-sm font-bold text-slate-600 hover:text-indigo-600 uppercase tracking-wide transition-colors {{ request()->routeIs('client.products.*') ? 'text-indigo-600' : '' }}">Sản phẩm</a>
                    
                    <a href="{{ route('client.products.sale') }}" class="text-sm font-bold text-rose-500 hover:text-rose-600 uppercase tracking-wide transition-colors relative group">
                        Sale
                        <span class="absolute -top-3 -right-3 text-[9px] bg-rose-600 text-white px-1 rounded animate-bounce">Hot</span>
                    </a>
                    <a href="{{ route('client.vouchers.index')}}" class="text-sm font-bold text-slate-600 hover:text-indigo-600 uppercase tracking-wide transition-colors">Mã giảm giá</a>
                </nav>

                {{-- ICONS & ACTIONS --}}
                <div class="flex items-center gap-2 md:gap-4">
                    {{-- Search Icon --}}
                    <button @click="searchOpen = !searchOpen" class="w-10 h-10 rounded-full flex items-center justify-center text-slate-500 hover:bg-slate-100 hover:text-indigo-600 transition-all">
                        <i class="fa-solid fa-magnifying-glass text-lg"></i>
                    </button>

                    {{-- User Dropdown --}}
                    <div class="relative" x-data="{ userOpen: false }">
                        <button @click="userOpen = !userOpen" @click.outside="userOpen = false" class="w-10 h-10 rounded-full flex items-center justify-center text-slate-500 hover:bg-slate-100 hover:text-indigo-600 transition-all">
                            <i class="fa-regular fa-user text-lg"></i>
                        </button>
                        
                        {{-- Dropdown Menu (MERGED & FIXED) --}}
                        <div x-show="userOpen" x-transition.origin.top.right x-cloak 
                             class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-xl border border-gray-100 py-2 z-50">
                            @auth
                                <div class="px-4 py-2 border-b border-gray-50">
                                    <p class="text-xs text-slate-400">Xin chào,</p>
                                    <p class="text-sm font-bold text-slate-800 truncate">{{ Auth::user()->full_name ?? Auth::user()->name }}</p>
                                </div>
                                
                                {{-- Link tới trang Đơn hàng (Dùng route mới của feature) --}}
                                <a href="{{ route('client.account.orders') }}" class="block px-4 py-2 text-sm text-slate-600 hover:bg-indigo-50 hover:text-indigo-600">
                                    Đơn hàng của tôi
                                </a>
                                
                                {{-- Link tới trang Profile (Dùng route mới của feature) --}}
                                <a href="{{ route('client.account.profile') }}" class="block px-4 py-2 text-sm text-slate-600 hover:bg-indigo-50 hover:text-indigo-600">
                                    Tài khoản
                                </a>
                                
                                @if(Auth::user()->role === 'admin' || Auth::user()->role === 'staff')
                                    <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 text-sm text-indigo-600 font-bold hover:bg-indigo-50">Vào trang quản trị</a>
                                @endif
                                
                                {{-- Nút đăng xuất (Sửa lại thành logout cho đúng luồng) --}}
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full text-left px-4 py-2 text-sm text-rose-600 hover:bg-rose-50">Đăng xuất</button>
                                </form>
                            @else
                                <a href="{{ route('login') }}" class="block px-4 py-2 text-sm text-slate-600 hover:bg-indigo-50 hover:text-indigo-600 font-bold">
                                    Đăng nhập
                                </a>

                                <a href="{{ route('register') }}" class="block px-4 py-2 text-sm text-slate-600 hover:bg-indigo-50 hover:text-indigo-600">
                                    Đăng ký
                                </a>
                            @endauth
                        </div>
                    </div>

                    {{-- Cart Icon --}}
                    <button class="relative w-10 h-10 rounded-full flex items-center justify-center text-slate-500 hover:bg-slate-100 hover:text-indigo-600 transition-all">
                        <a href="{{ route('client.carts.index') }}" class="relative">
                            <i class="fa fa-shopping-cart text-xl"></i>

                            @auth
                                <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full px-1 min-w-[16px] text-center">
                                    {{ Auth::user()->cart?->items->count() ?? 0 }}
                                </span>
                            @endauth
                        </a>
                    </button>

                    {{-- Mobile Menu Button --}}
                    <button @click="mobileMenuOpen = true" class="lg:hidden w-10 h-10 rounded-full flex items-center justify-center text-slate-900 hover:bg-slate-100">
                        <i class="fa-solid fa-bars-staggered text-xl"></i>
                    </button>
                </div>
            </div>

            {{-- Search Box Expandable --}}
            <div x-show="searchOpen" x-collapse x-cloak class="border-t border-gray-100 py-4 mt-2">
                <form action="{{ route('client.products.index') }}" method="GET" class="relative max-w-2xl mx-auto">
                    <input type="text" name="keyword" placeholder="Tìm kiếm sản phẩm..." class="w-full pl-5 pr-12 py-3 bg-gray-50 border-none rounded-full focus:ring-2 focus:ring-indigo-200 outline-none text-sm font-medium">
                    <button type="submit" class="absolute right-2 top-1/2 -translate-y-1/2 w-9 h-9 bg-indigo-600 rounded-full text-white flex items-center justify-center hover:bg-indigo-700 transition-colors">
                        <i class="fa-solid fa-magnifying-glass text-sm"></i>
                    </button>
                </form>
            </div>
        </div>
    </header>

    {{-- =========================================================== --}}
    {{-- 3. MOBILE MENU (OFF-CANVAS) --}}
    {{-- =========================================================== --}}
    <div x-show="mobileMenuOpen" class="relative z-[60]" aria-labelledby="slide-over-title" role="dialog" aria-modal="true" x-cloak>
        <div x-show="mobileMenuOpen" x-transition.opacity class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm" @click="mobileMenuOpen = false"></div>
        <div class="fixed inset-0 overflow-hidden">
            <div class="absolute inset-0 overflow-hidden">
                <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10">
                    <div x-show="mobileMenuOpen" 
                         x-transition:enter="transform transition ease-in-out duration-300"
                         x-transition:enter-start="translate-x-full"
                         x-transition:enter-end="translate-x-0"
                         x-transition:leave="transform transition ease-in-out duration-300"
                         x-transition:leave-start="translate-x-0"
                         x-transition:leave-end="translate-x-full"
                         class="pointer-events-auto w-screen max-w-xs bg-white shadow-xl flex flex-col">
                        
                        <div class="flex items-center justify-between px-6 py-6 border-b border-gray-100">
                            <span class="font-display font-black text-xl">MENU</span>
                            <button @click="mobileMenuOpen = false" class="text-slate-400 hover:text-slate-600">
                                <i class="fa-solid fa-xmark text-2xl"></i>
                            </button>
                        </div>

                        <div class="flex-1 overflow-y-auto py-6 px-6 space-y-6">
                            <a href="{{ route('client.home') }}" class="block text-lg font-bold text-slate-900 hover:text-indigo-600">Trang chủ</a>
                            <a href="{{ route('client.products.index') }}" class="block text-lg font-bold text-slate-900 hover:text-indigo-600">Sản phẩm</a>
                            <a href="#" class="block text-lg font-bold text-slate-900 hover:text-indigo-600">Nam</a>
                            <a href="#" class="block text-lg font-bold text-slate-900 hover:text-indigo-600">Nữ</a>
                            <a href="#" class="block text-lg font-bold text-rose-500">Khuyến mãi</a>
                        </div>

                        {{-- USER INFO SECTION --}}
                        <div class="border-t border-gray-100 px-6 py-6 bg-slate-50">
                            @auth
                                {{-- Link tới Profile --}}
                                <a href="{{ route('client.account.profile') }}" class="flex items-center gap-3 mb-4 group">
                                    <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold overflow-hidden border border-indigo-200">
                                        @if(Auth::user()->avatar)
                                            <img src="{{ Storage::url(Auth::user()->avatar) }}" class="w-full h-full object-cover">
                                        @else
                                            {{ substr(Auth::user()->full_name ?? Auth::user()->name, 0, 1) }}
                                        @endif
                                    </div>
                                    <div>
                                        <p class="font-bold text-slate-900 group-hover:text-indigo-600 transition-colors">{{ Auth::user()->full_name ?? Auth::user()->name }}</p>
                                        <p class="text-xs text-slate-500">Thành viên</p>
                                    </div>
                                </a>

                                {{-- Nút thao tác nhanh --}}
                                <div class="grid grid-cols-2 gap-2 mb-4">
                                     <a href="{{ route('client.account.orders') }}" class="text-sm font-medium text-slate-600 bg-white py-2 px-3 rounded border border-slate-200 text-center hover:bg-indigo-50 hover:text-indigo-600">Đơn hàng</a>
                                     
                                     {{-- Sửa route logout mobile --}}
                                     <form method="POST" action="{{ route('logout') }}" class="block">
                                        @csrf
                                        <button type="submit" class="w-full text-sm font-medium text-rose-600 bg-white py-2 px-3 rounded border border-slate-200 hover:bg-rose-50">Đăng xuất</button>
                                     </form>
                                </div>
                            @else
                                <a href="{{ route('login') }}"
                                   class="block w-full py-3 bg-slate-900 text-white text-center rounded-xl font-bold mb-3">
                                   Đăng nhập
                                </a>

                                <a href="{{ route('register') }}"
                                   class="block w-full py-3 border border-slate-300 text-slate-700 text-center rounded-xl font-bold">
                                   Đăng ký
                                </a>
                            @endauth
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- =========================================================== --}}
    {{-- 4. MAIN CONTENT --}}
    {{-- =========================================================== --}}
    <main class="flex-grow">
        @yield('content')
    </main>

    {{-- =========================================================== --}}
    {{-- 5. FOOTER --}}
    {{-- =========================================================== --}}
    <footer class="bg-slate-900 text-white pt-16 pb-8 border-t border-slate-800">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12 mb-12">
                {{-- Brand Info --}}
                <div class="space-y-4">
                    <a href="#" class="flex items-center gap-2 group">
                        <i class="fa-solid fa-shoe-prints text-2xl text-indigo-500"></i>
                        <span class="font-display font-black text-xl tracking-tighter text-white">
                            SNEAKER<span class="text-indigo-500">ZONE</span>.
                        </span>
                    </a>
                    <p class="text-slate-400 text-sm leading-relaxed">
                        Điểm đến hàng đầu cho các tín đồ sneaker. Chúng tôi cam kết mang đến những sản phẩm chính hãng, độc đáo và phong cách nhất.
                    </p>
                    <div class="flex gap-4 pt-2">
                        <a href="#" class="w-9 h-9 rounded-full bg-slate-800 flex items-center justify-center hover:bg-indigo-600 transition-colors"><i class="fa-brands fa-facebook-f"></i></a>
                        <a href="#" class="w-9 h-9 rounded-full bg-slate-800 flex items-center justify-center hover:bg-rose-500 transition-colors"><i class="fa-brands fa-instagram"></i></a>
                        <a href="#" class="w-9 h-9 rounded-full bg-slate-800 flex items-center justify-center hover:bg-sky-500 transition-colors"><i class="fa-brands fa-twitter"></i></a>
                    </div>
                </div>

                {{-- Links --}}
                {{-- Ví dụ đoạn code Footer --}}
                <div>
                    <h4 class="font-bold text-lg mb-6 uppercase tracking-wider">Cửa hàng</h4>
                    <ul class="space-y-3 text-sm text-slate-400">
                        <li><a href="{{ route('client.page.about') }}" class="hover:text-white transition-colors">Về chúng tôi</a></li>
                        <li><a href="{{ route('client.page.contact') }}" class="hover:text-white transition-colors">Liên hệ</a></li>
                        <li><a href="{{ route('client.page.stores') }}" class="hover:text-white transition-colors">Tìm cửa hàng</a></li>
                        <li><a href="{{ route('client.page.news') }}" class="hover:text-white transition-colors">Tin tức</a></li>
                    </ul>
                    </div>


                    <div>
                    <h4 class="font-bold text-lg mb-6 uppercase tracking-wider">Hỗ trợ</h4>
                    <ul class="space-y-3 text-sm text-slate-400">
                        <li><a href="{{ route('client.page.buying-guide') }}" class="hover:text-white transition-colors">Hướng dẫn mua hàng</a></li>
                        <li><a href="{{ route('client.page.return-policy') }}" class="hover:text-white transition-colors">Chính sách đổi trả</a></li>
                        <li><a href="{{ route('client.page.privacy-policy') }}" class="hover:text-white transition-colors">Chính sách bảo mật</a></li>
                        <li><a href="{{ route('client.page.tracking') }}" class="hover:text-white transition-colors">Tra cứu đơn hàng</a></li>
                    </ul>
                </div>

                {{-- Newsletter --}}
                <div>
                    <h4 class="font-bold text-lg mb-6 uppercase tracking-wider">Đăng ký nhận tin</h4>
                    <p class="text-slate-400 text-sm mb-4">Nhận thông tin về sản phẩm mới và khuyến mãi đặc biệt.</p>
                    <form class="flex flex-col gap-2">
                        <input type="email" placeholder="Email của bạn..." class="w-full px-4 py-2.5 rounded-lg bg-slate-800 border border-slate-700 text-white focus:outline-none focus:border-indigo-500 text-sm">
                        <button class="w-full px-4 py-2.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm transition-colors">Đăng ký ngay</button>
                    </form>
                </div>
            </div>

            <div class="border-t border-slate-800 pt-8 flex flex-col md:flex-row justify-between items-center gap-4">
                <p class="text-slate-500 text-xs">© 2024 Sneaker Zone. All rights reserved.</p>
                <div class="flex gap-4 text-2xl text-slate-500">
                    <i class="fa-brands fa-cc-visa hover:text-white transition-colors"></i>
                    <i class="fa-brands fa-cc-mastercard hover:text-white transition-colors"></i>
                    <i class="fa-brands fa-cc-paypal hover:text-white transition-colors"></i>
                </div>
            </div>
        </div>
    </footer>

    {{-- Script Stack --}}
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 800,
            once: true,
        });
    </script>
    @stack('scripts')

    {{-- Nhúng Component Toast vào đây --}}
    @include('client.products.toast-arlert')

    {{-- Khai báo hàm JS helper toàn cục (để gọi từ JS dễ hơn) --}}
    <script>
        window.showToast = function(message, type = 'success') {
            window.dispatchEvent(new CustomEvent('show-toast', { 
                detail: { message: message, type: type } 
            }));
        }
    </script>
</body>
</html>