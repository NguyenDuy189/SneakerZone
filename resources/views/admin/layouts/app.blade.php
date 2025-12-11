<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Dashboard') - Sneaker Zone</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Icons (FontAwesome) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Alpine.js -->
<<<<<<< HEAD
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
=======
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
>>>>>>> main

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: {
                        primary: '#4F46E5', // Indigo 600
                        secondary: '#1E293B', // Slate 800
                    }
                }
            }
        }
    </script>

    <style>
        [x-cloak] { display: none !important; }
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: #1E293B; }
        ::-webkit-scrollbar-thumb { background: #475569; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #64748b; }
    </style>
</head>
<body class="font-sans antialiased bg-gray-50 text-slate-800" x-data="{ sidebarOpen: true }">

    <div class="flex h-screen overflow-hidden">

        <!-- SIDEBAR -->
        <aside class="flex flex-col fixed z-30 inset-y-0 left-0 bg-secondary text-white transition-all duration-300 ease-in-out transform shadow-xl lg:static lg:translate-x-0"
               :class="sidebarOpen ? 'w-64 translate-x-0' : 'w-0 -translate-x-full lg:w-20 lg:translate-x-0'">
            
            <!-- Logo -->
            <div class="flex items-center justify-center h-16 bg-slate-900 shadow-md flex-shrink-0">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 font-bold text-xl tracking-wider uppercase text-white overflow-hidden whitespace-nowrap">
                    <i class="fa-solid fa-shoe-prints text-indigo-400 text-2xl"></i>
                    <span x-show="sidebarOpen" class="transition-opacity duration-300">Sneaker<span class="text-indigo-400">Zone</span></span>
                </a>
            </div>

            <!-- Menu Items -->
            <div class="flex-1 flex flex-col overflow-y-auto overflow-x-hidden py-4">
                <nav class="space-y-1 px-2">
                    
                    <!-- 1. DASHBOARD -->
                    <a href="{{ route('admin.dashboard') }}" 
                       class="group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors relative
                       {{ request()->routeIs('admin.dashboard') ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:bg-slate-700 hover:text-white' }}">
                        <i class="fa-solid fa-chart-line w-6 h-6 flex items-center justify-center"></i>
                        <span x-show="sidebarOpen" class="ml-3 truncate">Tổng quan</span>
                        <div x-show="!sidebarOpen" class="absolute left-14 bg-gray-900 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 z-50 whitespace-nowrap">Dashboard</div>
                    </a>

                    <!-- GROUP: QUẢN LÝ SẢN PHẨM (CATALOG) -->
                    <!-- Tables: products, categories, brands, attributes, reviews -->
                    <div x-show="sidebarOpen" class="mt-6 mb-2 px-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">
                        Sản phẩm
                    </div>

                    <div x-data="{ open: {{ request()->routeIs('admin.products.*', 'admin.categories.*', 'admin.brands.*', 'admin.attributes.*', 'admin.reviews.*') ? 'true' : 'false' }} }">
                        <button @click="open = !open" 
                                class="w-full group flex items-center justify-between px-2 py-2 text-sm font-medium rounded-md transition-colors
                                {{ request()->routeIs('admin.products.*', 'admin.categories.*', 'admin.brands.*', 'admin.attributes.*', 'admin.reviews.*') ? 'bg-slate-700 text-white' : 'text-slate-300 hover:bg-slate-700 hover:text-white' }}">
                            <div class="flex items-center">
                                <i class="fa-solid fa-box-open w-6 h-6 flex items-center justify-center {{ request()->routeIs('admin.products.*', 'admin.categories.*', 'admin.brands.*') ? 'text-indigo-400' : '' }}"></i>
                                <span x-show="sidebarOpen" class="ml-3 truncate">Catalog</span>
                            </div>
                            <i x-show="sidebarOpen" class="fa-solid fa-chevron-down text-xs transition-transform duration-200" :class="open ? 'rotate-180' : ''"></i>
                        </button>
                        
                        <div x-show="open && sidebarOpen" x-collapse class="space-y-1 mt-1 pl-10">
                            <!-- Sản phẩm -->
                            <a href="{{ route('admin.products.index')}}" class="block px-2 py-1.5 text-sm rounded-md transition-colors {{ request()->routeIs('admin.products.*') ? 'text-white bg-indigo-600' : 'text-slate-400 hover:text-white hover:bg-slate-700' }}">
                                Tất cả sản phẩm
                            </a>
                            <!-- Danh mục -->
                            <a href="{{ route('admin.categories.index') }}" class="block px-2 py-1.5 text-sm rounded-md transition-colors {{ request()->routeIs('admin.categories.*') ? 'text-white bg-indigo-600' : 'text-slate-400 hover:text-white hover:bg-slate-700' }}">
                                Danh mục
                            </a>
                            <!-- Thương hiệu -->
                            <a href="{{ route('admin.brands.index') }}" class="block px-2 py-1.5 text-sm rounded-md transition-colors {{ request()->routeIs('admin.brands.*') ? 'text-white bg-indigo-600' : 'text-slate-400 hover:text-white hover:bg-slate-700' }}">
                                Thương hiệu
                            </a>
                            <!-- Thuộc tính (Size/Color) -->
                            <a href="{{ route('admin.attributes.index')}}" class="block px-2 py-1.5 text-sm rounded-md transition-colors {{ request()->routeIs('admin.attributes.*') ? 'text-white bg-indigo-600' : 'text-slate-400 hover:text-white hover:bg-slate-700' }}">
                                Thuộc tính (Size/Màu)
                            </a>
                             <!-- Đánh giá -->
                             <a href="{{ route('admin.reviews.index') }}" class="block px-2 py-1.5 text-sm rounded-md transition-colors {{ request()->routeIs('admin.reviews.*') ? 'text-white bg-indigo-600' : 'text-slate-400 hover:text-white hover:bg-slate-700' }}">
                                Đánh giá & Review
                            </a>
                        </div>
                    </div>

                    <!-- GROUP: BÁN HÀNG (SALES) -->
                    <!-- Tables: orders, transactions -->
                    <div x-show="sidebarOpen" class="mt-6 mb-2 px-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">
                        Bán hàng
                    </div>
                    
                    <a href="{{ route('admin.orders.index') }}" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md text-slate-300 hover:bg-slate-700 hover:text-white transition-colors relative">
                        <i class="fa-solid fa-cart-shopping w-6 h-6 flex items-center justify-center"></i>
                        <span x-show="sidebarOpen" class="ml-3 truncate">Đơn hàng</span>
                        <span x-show="sidebarOpen" class="ml-auto bg-red-500 text-white py-0.5 px-2 rounded-full text-xs">5</span>
                    </a>

                    <a href="#" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md text-slate-300 hover:bg-slate-700 hover:text-white transition-colors relative">
                        <i class="fa-solid fa-money-bill-transfer w-6 h-6 flex items-center justify-center"></i>
                        <span x-show="sidebarOpen" class="ml-3 truncate">Giao dịch</span>
                    </a>

                    <!-- GROUP: KHO HÀNG (INVENTORY) -->
                    <!-- Tables: suppliers, purchase_orders, inventory_logs -->
                    <div x-show="sidebarOpen" class="mt-6 mb-2 px-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">
                        Kho & Nhập hàng
                    </div>

                    <div x-data="{ open: {{ request()->routeIs('admin.suppliers.*', 'admin.purchase_orders.*', 'admin.inventory.*') ? 'true' : 'false' }} }">
                        <button @click="open = !open" 
                                class="w-full group flex items-center justify-between px-2 py-2 text-sm font-medium rounded-md transition-colors
                                {{ request()->routeIs('admin.suppliers.*', 'admin.purchase_orders.*', 'admin.inventory.*') ? 'bg-slate-700 text-white' : 'text-slate-300 hover:bg-slate-700 hover:text-white' }}">
                            <div class="flex items-center">
                                <i class="fa-solid fa-warehouse w-6 h-6 flex items-center justify-center {{ request()->routeIs('admin.suppliers.*', 'admin.purchase_orders.*') ? 'text-indigo-400' : '' }}"></i>
                                <span x-show="sidebarOpen" class="ml-3 truncate">Kho hàng</span>
                            </div>
                            <i x-show="sidebarOpen" class="fa-solid fa-chevron-down text-xs transition-transform duration-200" :class="open ? 'rotate-180' : ''"></i>
                        </button>
                        
                        <div x-show="open && sidebarOpen" x-collapse class="space-y-1 mt-1 pl-10">
                            <a href="#" class="block px-2 py-1.5 text-sm rounded-md transition-colors {{ request()->routeIs('admin.inventory.*') ? 'text-white bg-indigo-600' : 'text-slate-400 hover:text-white hover:bg-slate-700' }}">
                                Lịch sử tồn kho
                            </a>
                            <a href="#" class="block px-2 py-1.5 text-sm rounded-md transition-colors {{ request()->routeIs('admin.purchase_orders.*') ? 'text-white bg-indigo-600' : 'text-slate-400 hover:text-white hover:bg-slate-700' }}">
                                Phiếu nhập hàng (PO)
                            </a>
                            <a href="#" class="block px-2 py-1.5 text-sm rounded-md transition-colors {{ request()->routeIs('admin.suppliers.*') ? 'text-white bg-indigo-600' : 'text-slate-400 hover:text-white hover:bg-slate-700' }}">
                                Nhà cung cấp
                            </a>
                        </div>
                    </div>

                    <!-- GROUP: MARKETING -->
                    <!-- Tables: discounts, flash_sales, banners -->
                    <div x-show="sidebarOpen" class="mt-6 mb-2 px-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">
                        Marketing
                    </div>

                    <a href="#" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md text-slate-300 hover:bg-slate-700 hover:text-white transition-colors relative">
                        <i class="fa-solid fa-ticket w-6 h-6 flex items-center justify-center"></i>
                        <span x-show="sidebarOpen" class="ml-3 truncate">Mã giảm giá</span>
                    </a>
                    
                    <a href="#" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md text-slate-300 hover:bg-slate-700 hover:text-white transition-colors relative">
                        <i class="fa-solid fa-bolt w-6 h-6 flex items-center justify-center text-yellow-400"></i>
                        <span x-show="sidebarOpen" class="ml-3 truncate">Flash Sale</span>
                    </a>

                    <a href="#" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md text-slate-300 hover:bg-slate-700 hover:text-white transition-colors relative">
                        <i class="fa-regular fa-image w-6 h-6 flex items-center justify-center"></i>
                        <span x-show="sidebarOpen" class="ml-3 truncate">Banner & Slide</span>
                    </a>

                    <!-- GROUP: KHÁCH HÀNG & HỆ THỐNG -->
                    <!-- Tables: users, settings, notifications -->
                    <div x-show="sidebarOpen" class="mt-6 mb-2 px-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">
                        Hệ thống
                    </div>

                    <a href="#" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md text-slate-300 hover:bg-slate-700 hover:text-white transition-colors relative">
                        <i class="fa-solid fa-users w-6 h-6 flex items-center justify-center"></i>
                        <span x-show="sidebarOpen" class="ml-3 truncate">Khách hàng / User</span>
                    </a>

                    <a href="#" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md text-slate-300 hover:bg-slate-700 hover:text-white transition-colors relative">
                        <i class="fa-solid fa-gear w-6 h-6 flex items-center justify-center"></i>
                        <span x-show="sidebarOpen" class="ml-3 truncate">Cấu hình chung</span>
                    </a>

<<<<<<< HEAD
                    {{-- MENU: QUẢN LÝ TÀI KHOẢN (DROPDOWN MỚI) --}}
                    <div x-data="{ open: {{ request()->routeIs('admin.customers.*', 'admin.users.*') ? 'true' : 'false' }} }">
                        <button @click="open = !open" 
                                class="w-full group flex items-center justify-between px-3 py-2.5 text-sm font-medium rounded-xl transition-colors
                                {{ request()->routeIs('admin.customers.*', 'admin.users.*') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                            <div class="flex items-center">
                                <i class="fa-solid fa-users-gear w-6 h-6 flex items-center justify-center {{ request()->routeIs('admin.customers.*', 'admin.users.*') ? 'text-indigo-400' : '' }}"></i>
                                <span x-show="sidebarOpen" class="ml-3 truncate">Quản lý Tài khoản</span>
                            </div>
                            <i x-show="sidebarOpen" class="fa-solid fa-chevron-right text-[10px] text-slate-500 transition-transform duration-200" :class="open ? 'rotate-90' : ''"></i>
                        </button>
                        
                        <div x-show="open && sidebarOpen" x-collapse class="space-y-1 mt-1 pl-11 pr-2 relative">
                            <div class="absolute left-[22px] top-0 bottom-0 w-[1px] bg-slate-700"></div>

                            {{-- Khách hàng --}}
                            <a href="{{ route('admin.customers.index') }}" class="block px-2 py-1.5 text-sm rounded-lg transition-colors {{ request()->routeIs('admin.customers.*') ? 'text-white bg-indigo-600/20 border-l-2 border-indigo-500 pl-3' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                                Khách hàng
                            </a>

                            {{-- Admin & Staff --}}
                            <a href="{{ route('admin.users.index') }}" class="block px-2 py-1.5 text-sm rounded-lg transition-colors {{ request()->routeIs('admin.users.*') ? 'text-white bg-indigo-600/20 border-l-2 border-indigo-500 pl-3' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                                Admin & Staff
                            </a>
                        </div>
                    </div>

                    {{-- Banner --}}
                    <a href="{{ route('admin.banners.index') }}" class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-xl transition-colors relative
                        {{ request()->routeIs('admin.settings.*') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                        <i class="fa-solid fa-gears w-6 h-6 flex items-center justify-center {{ request()->routeIs('admin.settings.*') ? 'text-indigo-400' : '' }}"></i>
                        <span x-show="sidebarOpen" class="ml-3 truncate">Banner</span>
                    </a>

                    {{-- Cấu hình --}}
                    <a href="{{ route('admin.settings.index') }}" class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-xl transition-colors relative
                        {{ request()->routeIs('admin.settings.*') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                        <i class="fa-solid fa-gears w-6 h-6 flex items-center justify-center {{ request()->routeIs('admin.settings.*') ? 'text-indigo-400' : '' }}"></i>
                        <span x-show="sidebarOpen" class="ml-3 truncate">Cấu hình hệ thống</span>
=======
                    <a href="#" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md text-slate-300 hover:bg-slate-700 hover:text-white transition-colors relative">
                        <i class="fa-solid fa-bell w-6 h-6 flex items-center justify-center"></i>
                        <span x-show="sidebarOpen" class="ml-3 truncate">Gửi thông báo</span>
>>>>>>> main
                    </a>

                </nav>
            </div>

            <!-- Sidebar Footer -->
            <div class="bg-slate-900 p-4 border-t border-slate-700">
                <div class="flex items-center gap-3">
                    <img src="https://ui-avatars.com/api/?name=Admin+User&background=4F46E5&color=fff" alt="Admin" class="h-9 w-9 rounded-full border-2 border-slate-600">
                    <div x-show="sidebarOpen">
                        <p class="text-sm font-medium text-white">Admin User</p>
                        <p class="text-xs text-slate-400">Super Admin</p>
                    </div>
                </div>
            </div>
        </aside>

        <!-- MAIN CONTENT WRAPPER -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            
            <!-- TOP HEADER -->
            <header class="bg-white shadow-sm h-16 flex items-center justify-between px-6 z-20">
                <!-- Left: Toggle Sidebar -->
                <div class="flex items-center gap-4">
                    <button @click="sidebarOpen = !sidebarOpen" class="text-slate-500 hover:text-slate-700 focus:outline-none transition-transform active:scale-95">
                        <i class="fa-solid fa-bars text-xl"></i>
                    </button>
                    <!-- Search Bar -->
                    <div class="hidden md:flex relative">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 transform -translate-y-1/2 text-slate-400"></i>
                        <input type="text" placeholder="Tìm kiếm đơn hàng, sản phẩm..." class="pl-10 pr-4 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent w-64 transition-all focus:w-80">
                    </div>
                </div>

                <!-- Right: Notifications & Profile -->
                <div class="flex items-center gap-4">
                    <!-- Notification Bell -->
                    <button class="relative p-2 text-slate-400 hover:text-indigo-600 transition-colors">
                        <i class="fa-regular fa-bell text-xl"></i>
                        <span class="absolute top-1 right-1 h-2.5 w-2.5 bg-red-500 rounded-full border-2 border-white animate-pulse"></span>
                    </button>

                    <!-- Profile Dropdown -->
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" class="flex items-center gap-2 focus:outline-none">
                            <span class="text-sm font-medium text-slate-700 hidden md:block">Xin chào, Admin</span>
                            <i class="fa-solid fa-chevron-down text-xs text-slate-400 transition-transform" :class="open ? 'rotate-180' : ''"></i>
                        </button>

                        <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95"
                             class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-slate-100 py-1 z-50">
                            <a href="#" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 hover:text-indigo-600">
                                <i class="fa-regular fa-user mr-2"></i> Hồ sơ
                            </a>
                            <a href="#" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 hover:text-indigo-600">
                                <i class="fa-solid fa-gear mr-2"></i> Cài đặt
                            </a>
                            <div class="border-t border-slate-100 my-1"></div>
                            <form method="POST" action="#">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                    <i class="fa-solid fa-arrow-right-from-bracket mr-2"></i> Đăng xuất
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <!-- CONTENT BODY -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6">
                <!-- Breadcrumbs -->
                <div class="mb-6 flex items-center justify-between">
                    <h1 class="text-2xl font-bold text-slate-800">@yield('header', 'Tổng quan')</h1>
                    <nav class="flex text-sm text-slate-500">
                        <a href="{{ route('admin.dashboard') }}" class="hover:text-indigo-600 transition-colors">Admin</a>
                        <span class="mx-2">/</span>
                        <span class="text-slate-800 font-medium">@yield('header', 'Dashboard')</span>
                    </nav>
                </div>

                <!-- Alert Messages -->
                @if(session('success'))
                    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)" 
                         class="mb-4 bg-green-50 border-l-4 border-green-500 text-green-700 p-4 rounded shadow-sm flex justify-between items-center" role="alert">
                        <div class="flex items-center">
                            <i class="fa-solid fa-circle-check mr-2"></i>
                            <div>
                                <p class="font-bold">Thành công</p>
                                <p>{{ session('success') }}</p>
                            </div>
                        </div>
                        <button @click="show = false" class="text-green-700 hover:text-green-900"><i class="fa-solid fa-xmark"></i></button>
                    </div>
                @endif
                
                @if(session('error'))
                    <div x-data="{ show: true }" x-show="show" 
                         class="mb-4 bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded shadow-sm flex justify-between items-center" role="alert">
                         <div class="flex items-center">
                            <i class="fa-solid fa-circle-exclamation mr-2"></i>
                            <div>
                                <p class="font-bold">Lỗi</p>
                                <p>{{ session('error') }}</p>
                            </div>
                        </div>
                        <button @click="show = false" class="text-red-700 hover:text-red-900"><i class="fa-solid fa-xmark"></i></button>
                    </div>
                @endif

                <!-- Main Content Injection -->
                @yield('content')
            </main>

            <!-- FOOTER -->
            <footer class="bg-white border-t border-slate-200 p-4 text-center text-sm text-slate-500">
                &copy; {{ date('Y') }} Sneaker Zone Admin Panel.
            </footer>
        </div>
    </div>
</body>
</html>