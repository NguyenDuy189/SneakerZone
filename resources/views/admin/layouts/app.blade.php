<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Quản trị hệ thống') - Sneaker Zone Admin</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Icons (FontAwesome 6) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Tailwind CSS (CDN for Dev) -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Config Tailwind -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: {
                        primary: '#4F46E5', // Indigo 600
                        secondary: '#0f172a', // Slate 900
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.5s ease-out',
                    },
                    keyframes: {
                        fadeIn: {
                            '0%': { opacity: '0', transform: 'translateY(10px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        }
                    }
                }
            }
        }
    </script>

    <style>
        [x-cloak] { display: none !important; }
        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        /* Sidebar Scrollbar */
        aside ::-webkit-scrollbar-track { background: #0f172a; }
        aside ::-webkit-scrollbar-thumb { background: #334155; }
    </style>
</head>
<body class="font-sans antialiased bg-slate-50 text-slate-800" x-data="{ sidebarOpen: true }">

    <div class="flex h-screen overflow-hidden">

        <!-- ============================================================== -->
        <!-- SIDEBAR -->
        <!-- ============================================================== -->
        <aside class="flex flex-col fixed z-30 inset-y-0 left-0 bg-secondary text-white transition-all duration-300 ease-in-out transform shadow-2xl lg:static lg:translate-x-0 border-r border-slate-800"
               :class="sidebarOpen ? 'w-64 translate-x-0' : 'w-0 -translate-x-full lg:w-20 lg:translate-x-0'">
            
            <!-- Logo -->
            <div class="flex items-center justify-center h-16 bg-secondary shadow-sm flex-shrink-0 border-b border-slate-800">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 font-bold text-xl tracking-wider uppercase text-white overflow-hidden whitespace-nowrap">
                    <div class="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center text-white shadow-lg shadow-indigo-500/50">
                        <i class="fa-solid fa-shoe-prints text-sm"></i>
                    </div>
                    <span x-show="sidebarOpen" class="transition-opacity duration-300 font-extrabold tracking-tighter">
                        Sneaker<span class="text-indigo-500">Zone</span>
                    </span>
                </a>
            </div>

            <!-- Menu Items -->
            <div class="flex-1 flex flex-col overflow-y-auto overflow-x-hidden py-4 custom-scrollbar">
                <nav class="space-y-1 px-3">
                    
                    {{-- 1. DASHBOARD --}}
                    <a href="{{ route('admin.dashboard') }}" 
                       class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-xl transition-all duration-200 relative mb-4
                       {{ request()->routeIs('admin.dashboard') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-900/50' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                        <i class="fa-solid fa-chart-pie w-6 h-6 flex items-center justify-center transition-transform group-hover:scale-110"></i>
                        <span x-show="sidebarOpen" class="ml-3 truncate font-semibold">Tổng quan</span>
                        {{-- Tooltip khi đóng sidebar --}}
                        <div x-show="!sidebarOpen" class="absolute left-14 bg-slate-900 text-white text-xs px-2 py-1 rounded shadow-lg opacity-0 group-hover:opacity-100 z-50 whitespace-nowrap border border-slate-700">Dashboard</div>
                    </a>

                    {{-- GROUP LABEL: CATALOG --}}
                    <div x-show="sidebarOpen" class="mt-6 mb-2 px-3 text-[10px] font-bold text-slate-500 uppercase tracking-widest">
                        Sản phẩm & Kho
                    </div>

                    {{-- MENU: CATALOG --}}
                    <div x-data="{ open: {{ request()->routeIs('admin.products.*', 'admin.categories.*', 'admin.brands.*', 'admin.attributes.*', 'admin.reviews.*') ? 'true' : 'false' }} }">
                        <button @click="open = !open" 
                                class="w-full group flex items-center justify-between px-3 py-2.5 text-sm font-medium rounded-xl transition-colors
                                {{ request()->routeIs('admin.products.*', 'admin.categories.*', 'admin.brands.*', 'admin.attributes.*', 'admin.reviews.*') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                            <div class="flex items-center">
                                <i class="fa-solid fa-box w-6 h-6 flex items-center justify-center {{ request()->routeIs('admin.products.*', 'admin.categories.*', 'admin.brands.*') ? 'text-indigo-400' : '' }}"></i>
                                <span x-show="sidebarOpen" class="ml-3 truncate">Quản lý Sản phẩm</span>
                            </div>
                            <i x-show="sidebarOpen" class="fa-solid fa-chevron-right text-[10px] text-slate-500 transition-transform duration-200" :class="open ? 'rotate-90' : ''"></i>
                        </button>
                        
                        <div x-show="open && sidebarOpen" x-collapse class="space-y-1 mt-1 pl-11 pr-2 relative">
                            {{-- Vertical Line --}}
                            <div class="absolute left-[22px] top-0 bottom-0 w-[1px] bg-slate-700"></div>

                            <a href="{{ route('admin.products.index')}}" class="block px-2 py-1.5 text-sm rounded-lg transition-colors {{ request()->routeIs('admin.products.*') ? 'text-white bg-indigo-600/20 border-l-2 border-indigo-500 pl-3' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                                Tất cả sản phẩm
                            </a>
                            <a href="{{ route('admin.categories.index') }}" class="block px-2 py-1.5 text-sm rounded-lg transition-colors {{ request()->routeIs('admin.categories.*') ? 'text-white bg-indigo-600/20 border-l-2 border-indigo-500 pl-3' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                                Danh mục (Category)
                            </a>
                            <a href="{{ route('admin.brands.index') }}" class="block px-2 py-1.5 text-sm rounded-lg transition-colors {{ request()->routeIs('admin.brands.*') ? 'text-white bg-indigo-600/20 border-l-2 border-indigo-500 pl-3' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                                Thương hiệu (Brand)
                            </a>
                            <a href="{{ route('admin.attributes.index')}}" class="block px-2 py-1.5 text-sm rounded-lg transition-colors {{ request()->routeIs('admin.attributes.*') ? 'text-white bg-indigo-600/20 border-l-2 border-indigo-500 pl-3' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                                Thuộc tính (Size/Màu)
                            </a>
                             <a href="{{ route('admin.reviews.index')}}" class="block px-2 py-1.5 text-sm rounded-lg transition-colors {{ request()->routeIs('admin.reviews.*') ? 'text-white bg-indigo-600/20 border-l-2 border-indigo-500 pl-3' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                                Đánh giá (Reviews)
                            </a>
                        </div>
                    </div>

                    {{-- GROUP LABEL: SALES --}}
                    <div x-show="sidebarOpen" class="mt-6 mb-2 px-3 text-[10px] font-bold text-slate-500 uppercase tracking-widest">
                        Kinh doanh
                    </div>
                    
                    {{-- Đơn hàng --}}
                    <a href="{{ route('admin.orders.index') }}" class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-xl transition-colors relative
                        {{ request()->routeIs('admin.orders.*') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                        <i class="fa-solid fa-cart-shopping w-6 h-6 flex items-center justify-center {{ request()->routeIs('admin.orders.*') ? 'text-indigo-400' : '' }}"></i>
                        <span x-show="sidebarOpen" class="ml-3 truncate">Đơn hàng</span>
                        <span x-show="sidebarOpen" class="ml-auto bg-rose-600 text-white text-[10px] font-bold px-2 py-0.5 rounded-full shadow-lg shadow-rose-900/50">
                            {{ \App\Models\Order::where('status', 'pending')->count() }}
                        </span>
                    </a>

                    {{-- Mã giảm giá --}}
                    <a href="{{ route('admin.discounts.index') }}" class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-xl transition-colors relative
                        {{ request()->routeIs('admin.discounts.*') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                        <i class="fa-solid fa-ticket w-6 h-6 flex items-center justify-center {{ request()->routeIs('admin.discounts.*') ? 'text-indigo-400' : '' }}"></i>
                        <span x-show="sidebarOpen" class="ml-3 truncate">Mã giảm giá</span>
                    </a>

                    {{-- GROUP LABEL: PEOPLE --}}
                    <div x-show="sidebarOpen" class="mt-6 mb-2 px-3 text-[10px] font-bold text-slate-500 uppercase tracking-widest">
                        Người dùng
                    </div>

                    {{-- Khách hàng --}}
                    <a href="{{ route('admin.customers.index') }}" class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-xl transition-colors relative
                        {{ request()->routeIs('admin.customers.*') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                        <i class="fa-solid fa-users w-6 h-6 flex items-center justify-center {{ request()->routeIs('admin.customers.*') ? 'text-indigo-400' : '' }}"></i>
                        <span x-show="sidebarOpen" class="ml-3 truncate">Khách hàng</span>
                    </a>

                    {{-- Nhân sự --}}
                    <a href="{{ route('admin.users.index') }}" class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-xl transition-colors relative
                        {{ request()->routeIs('admin.users.*') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                        <i class="fa-solid fa-user-shield w-6 h-6 flex items-center justify-center {{ request()->routeIs('admin.users.*') ? 'text-indigo-400' : '' }}"></i>
                        <span x-show="sidebarOpen" class="ml-3 truncate">Nhân sự & Admin</span>
                    </a>

                </nav>
            </div>

            <!-- Sidebar Footer (Profile Mini) -->
            <div class="bg-slate-900/50 p-4 border-t border-slate-800">
                <div class="flex items-center gap-3">
                    <img src="{{ Auth::user()->avatar_url ?? 'https://ui-avatars.com/api/?name=Admin&background=4F46E5&color=fff' }}" 
                         alt="Admin" class="h-9 w-9 rounded-full border border-slate-600 shadow-sm">
                    <div x-show="sidebarOpen" class="overflow-hidden">
                        <p class="text-sm font-medium text-white truncate">{{ Auth::user()->full_name ?? 'Administrator' }}</p>
                        <p class="text-xs text-slate-400 truncate">{{ Auth::user()->role ?? 'Super Admin' }}</p>
                    </div>
                </div>
            </div>
        </aside>

        <!-- ============================================================== -->
        <!-- MAIN CONTENT WRAPPER -->
        <!-- ============================================================== -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden bg-gray-50">
            
            <!-- TOP BAR -->
            <header class="bg-white/80 backdrop-blur-md shadow-sm h-16 flex items-center justify-between px-6 z-20 border-b border-slate-100 sticky top-0">
                <!-- Left: Toggle Sidebar -->
                <div class="flex items-center gap-4">
                    <button @click="sidebarOpen = !sidebarOpen" class="text-slate-500 hover:text-indigo-600 focus:outline-none transition-transform active:scale-95">
                        <i class="fa-solid fa-bars-staggered text-xl"></i>
                    </button>
                    <!-- Search Bar (Dummy) -->
                    <div class="hidden md:flex relative group">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 transform -translate-y-1/2 text-slate-400 group-focus-within:text-indigo-500 transition-colors"></i>
                        <input type="text" placeholder="Tìm kiếm nhanh (Ctrl+K)..." class="pl-10 pr-4 py-2 bg-slate-100 border-none rounded-full text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 w-64 transition-all focus:w-80 focus:bg-white placeholder-slate-400">
                    </div>
                </div>

                <!-- Right: Notifications & Profile -->
                <div class="flex items-center gap-5">
                    <!-- Notification Bell -->
                    <button class="relative p-1.5 text-slate-400 hover:text-indigo-600 transition-colors">
                        <i class="fa-regular fa-bell text-xl"></i>
                        <span class="absolute top-1 right-1 h-2.5 w-2.5 bg-red-500 rounded-full border-2 border-white animate-pulse"></span>
                    </button>

                    <!-- Profile Dropdown -->
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" class="flex items-center gap-2 focus:outline-none hover:bg-slate-50 p-1.5 rounded-lg transition-colors">
                            <span class="text-sm font-semibold text-slate-700 hidden md:block">{{ Auth::user()->full_name ?? 'Admin' }}</span>
                            <i class="fa-solid fa-chevron-down text-[10px] text-slate-400 transition-transform" :class="open ? 'rotate-180' : ''"></i>
                        </button>

                        <div x-show="open" @click.away="open = false" x-transition.origin.top.right
                             class="absolute right-0 mt-3 w-56 bg-white rounded-xl shadow-xl border border-slate-100 py-2 z-50">
                            <div class="px-4 py-3 border-b border-slate-100">
                                <p class="text-sm font-bold text-slate-800">Tài khoản</p>
                                <p class="text-xs text-slate-500 truncate">{{ Auth::user()->email ?? 'admin@example.com' }}</p>
                            </div>
                            <a href="#" class="flex items-center px-4 py-2.5 text-sm text-slate-600 hover:bg-slate-50 hover:text-indigo-600 transition-colors">
                                <i class="fa-regular fa-user mr-3 w-4"></i> Hồ sơ cá nhân
                            </a>
                            <a href="#" class="flex items-center px-4 py-2.5 text-sm text-slate-600 hover:bg-slate-50 hover:text-indigo-600 transition-colors">
                                <i class="fa-solid fa-sliders mr-3 w-4"></i> Cài đặt hệ thống
                            </a>
                            <div class="border-t border-slate-100 my-1"></div>
                            
                            {{-- LOGOUT FORM --}}
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full text-left flex items-center px-4 py-2.5 text-sm text-rose-600 hover:bg-rose-50 transition-colors">
                                    <i class="fa-solid fa-arrow-right-from-bracket mr-3 w-4"></i> Đăng xuất
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <!-- MAIN CONTENT BODY -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50/50 p-6 scroll-smooth">
                <!-- Alert Messages (Toast) -->
                @if(session('success'))
                    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)" 
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 transform translate-x-4"
                         x-transition:enter-end="opacity-100 transform translate-x-0"
                         x-transition:leave="transition ease-in duration-300"
                         x-transition:leave-start="opacity-100 transform translate-x-0"
                         x-transition:leave-end="opacity-0 transform translate-x-4"
                         class="fixed top-20 right-6 z-50 bg-white border-l-4 border-emerald-500 text-slate-700 p-4 rounded-lg shadow-lg flex items-start gap-3 w-80">
                        <div class="text-emerald-500 mt-0.5"><i class="fa-solid fa-circle-check text-xl"></i></div>
                        <div class="flex-1">
                            <h4 class="font-bold text-sm text-emerald-600">Thành công!</h4>
                            <p class="text-sm text-slate-600 mt-1">{{ session('success') }}</p>
                        </div>
                        <button @click="show = false" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark"></i></button>
                    </div>
                @endif
                
                @if(session('error'))
                    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" 
                         class="fixed top-20 right-6 z-50 bg-white border-l-4 border-rose-500 text-slate-700 p-4 rounded-lg shadow-lg flex items-start gap-3 w-80">
                        <div class="text-rose-500 mt-0.5"><i class="fa-solid fa-circle-exclamation text-xl"></i></div>
                        <div class="flex-1">
                            <h4 class="font-bold text-sm text-rose-600">Đã xảy ra lỗi!</h4>
                            <p class="text-sm text-slate-600 mt-1">{{ session('error') }}</p>
                        </div>
                        <button @click="show = false" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark"></i></button>
                    </div>
                @endif

                <!-- Content Injection -->
                @yield('content')
            </main>

            <!-- FOOTER -->
            <footer class="bg-white border-t border-slate-200 py-4 text-center text-xs text-slate-400">
                &copy; {{ date('Y') }} <span class="font-bold text-indigo-600">Sneaker Zone</span>. All rights reserved. Designed for performance.
            </footer>
        </div>
    </div>
</body>
</html>