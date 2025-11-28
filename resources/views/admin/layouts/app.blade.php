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

    <!-- Tailwind CSS (Dùng CDN cho nhanh, thực tế nên dùng npm run build) -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Alpine.js (Xử lý JS tương tác nhẹ) -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Cấu hình Tailwind cơ bản -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
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
        /* Scrollbar tùy chỉnh */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
</head>
<body class="font-sans antialiased bg-gray-50 text-slate-800" x-data="{ sidebarOpen: true }">

    <!-- Wrapper -->
    <div class="flex h-screen overflow-hidden">

        <!-- SIDEBAR -->
        <aside class="flex flex-col fixed z-30 inset-y-0 left-0 bg-secondary text-white transition-all duration-300 ease-in-out transform shadow-xl lg:static lg:translate-x-0"
               :class="sidebarOpen ? 'w-64 translate-x-0' : 'w-0 -translate-x-full lg:w-20 lg:translate-x-0'"
               class="w-64">
            
            <!-- Logo -->
            <div class="flex items-center justify-center h-16 bg-slate-900 shadow-md">
                <a href="#" class="flex items-center gap-2 font-bold text-xl tracking-wider uppercase text-white">
                    <i class="fa-solid fa-shoe-prints text-indigo-400"></i>
                    <span x-show="sidebarOpen" class="transition-opacity duration-300">Sneaker<span class="text-indigo-400">Zone</span></span>
                </a>
            </div>

            <!-- Menu Items -->
            <div class="flex-1 flex flex-col overflow-y-auto overflow-x-hidden py-4">
                <nav class="space-y-1 px-2">
                    
                    <!-- Dashboard -->
                    <a href="#" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md bg-slate-700 text-white relative">
                        <i class="fa-solid fa-chart-line w-6 h-6 flex items-center justify-center text-indigo-400 transition-colors group-hover:text-white"></i>
                        <span x-show="sidebarOpen" class="ml-3 truncate">Dashboard</span>
                        <div x-show="!sidebarOpen" class="absolute left-14 bg-gray-900 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity z-50 whitespace-nowrap">Dashboard</div>
                    </a>

                    <!-- Section Title -->
                    <div x-show="sidebarOpen" class="mt-6 mb-2 px-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">
                        Quản lý bán hàng
                    </div>

                    <!-- Đơn hàng -->
                    <a href="#" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md text-slate-300 hover:bg-slate-700 hover:text-white transition-colors relative">
                        <i class="fa-solid fa-cart-shopping w-6 h-6 flex items-center justify-center group-hover:text-indigo-400"></i>
                        <span x-show="sidebarOpen" class="ml-3 truncate">Đơn hàng</span>
                        <span x-show="sidebarOpen" class="ml-auto bg-red-500 text-white py-0.5 px-2 rounded-full text-xs">3</span>
                    </a>

                    <!-- Sản phẩm -->
                    <div x-data="{ open: false }">
                        <button @click="open = !open" class="w-full group flex items-center justify-between px-2 py-2 text-sm font-medium rounded-md text-slate-300 hover:bg-slate-700 hover:text-white transition-colors">
                            <div class="flex items-center">
                                <i class="fa-solid fa-box-open w-6 h-6 flex items-center justify-center group-hover:text-indigo-400"></i>
                                <span x-show="sidebarOpen" class="ml-3 truncate">Sản phẩm</span>
                            </div>
                            <i x-show="sidebarOpen" class="fa-solid fa-chevron-down text-xs transition-transform duration-200" :class="open ? 'rotate-180' : ''"></i>
                        </button>
                        <!-- Submenu -->
                        <div x-show="open && sidebarOpen" x-collapse class="space-y-1 mt-1 pl-10">
                            <a href="#" class="block px-2 py-1.5 text-sm text-slate-400 hover:text-white hover:bg-slate-700 rounded-md">Tất cả sản phẩm</a>
                            <a href="#" class="block px-2 py-1.5 text-sm text-slate-400 hover:text-white hover:bg-slate-700 rounded-md">Thêm mới</a>
                            <a href="#" class="block px-2 py-1.5 text-sm text-slate-400 hover:text-white hover:bg-slate-700 rounded-md">Danh mục</a>
                            <a href="#" class="block px-2 py-1.5 text-sm text-slate-400 hover:text-white hover:bg-slate-700 rounded-md">Thuộc tính</a>
                        </div>
                    </div>

                    <!-- Khách hàng -->
                    <a href="#" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md text-slate-300 hover:bg-slate-700 hover:text-white transition-colors relative">
                        <i class="fa-solid fa-users w-6 h-6 flex items-center justify-center group-hover:text-indigo-400"></i>
                        <span x-show="sidebarOpen" class="ml-3 truncate">Khách hàng</span>
                    </a>

                    <!-- Section Title -->
                    <div x-show="sidebarOpen" class="mt-6 mb-2 px-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">
                        Marketing
                    </div>

                    <!-- Khuyến mãi -->
                    <a href="#" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md text-slate-300 hover:bg-slate-700 hover:text-white transition-colors relative">
                        <i class="fa-solid fa-tags w-6 h-6 flex items-center justify-center group-hover:text-indigo-400"></i>
                        <span x-show="sidebarOpen" class="ml-3 truncate">Mã giảm giá</span>
                    </a>
                    
                    <!-- Flash Sale -->
                    <a href="#" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md text-slate-300 hover:bg-slate-700 hover:text-white transition-colors relative">
                        <i class="fa-solid fa-bolt w-6 h-6 flex items-center justify-center text-yellow-400 group-hover:text-yellow-300"></i>
                        <span x-show="sidebarOpen" class="ml-3 truncate">Flash Sale</span>
                    </a>

                    <!-- Section Title -->
                    <div x-show="sidebarOpen" class="mt-6 mb-2 px-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">
                        Hệ thống
                    </div>

                    <!-- Cài đặt -->
                    <a href="#" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md text-slate-300 hover:bg-slate-700 hover:text-white transition-colors relative">
                        <i class="fa-solid fa-gear w-6 h-6 flex items-center justify-center group-hover:text-indigo-400"></i>
                        <span x-show="sidebarOpen" class="ml-3 truncate">Cài đặt chung</span>
                    </a>

                </nav>
            </div>

            <!-- Sidebar Footer -->
            <div class="bg-slate-900 p-4 border-t border-slate-700">
                <div class="flex items-center gap-3">
                    <img src="https://ui-avatars.com/api/?name=Admin+User&background=4F46E5&color=fff" alt="Admin" class="h-9 w-9 rounded-full border-2 border-slate-600">
                    <div x-show="sidebarOpen">
                        <p class="text-sm font-medium text-white">Admin User</p>
                        <p class="text-xs text-slate-400">Quản trị viên</p>
                    </div>
                </div>
            </div>
        </aside>

        <!-- MAIN CONTENT -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            
            <!-- TOP HEADER -->
            <header class="bg-white shadow-sm h-16 flex items-center justify-between px-6 z-20">
                <!-- Left: Toggle Sidebar -->
                <div class="flex items-center gap-4">
                    <button @click="sidebarOpen = !sidebarOpen" class="text-slate-500 hover:text-slate-700 focus:outline-none">
                        <i class="fa-solid fa-bars text-xl"></i>
                    </button>
                    <!-- Search Bar -->
                    <div class="hidden md:flex relative">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 transform -translate-y-1/2 text-slate-400"></i>
                        <input type="text" placeholder="Tìm kiếm đơn hàng, sản phẩm..." class="pl-10 pr-4 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent w-64">
                    </div>
                </div>

                <!-- Right: Notifications & Profile -->
                <div class="flex items-center gap-4">
                    <!-- Notification Bell -->
                    <button class="relative p-2 text-slate-400 hover:text-indigo-600 transition-colors">
                        <i class="fa-regular fa-bell text-xl"></i>
                        <span class="absolute top-1 right-1 h-2.5 w-2.5 bg-red-500 rounded-full border-2 border-white"></span>
                    </button>

                    <!-- Profile Dropdown -->
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" class="flex items-center gap-2 focus:outline-none">
                            <span class="text-sm font-medium text-slate-700 hidden md:block">Xin chào, Admin</span>
                            <i class="fa-solid fa-chevron-down text-xs text-slate-400"></i>
                        </button>

                        <!-- Dropdown Menu -->
                        <div x-show="open" @click.away="open = false" x-transition 
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
                    <h1 class="text-2xl font-bold text-slate-800">@yield('header', 'Dashboard')</h1>
                    <nav class="flex text-sm text-slate-500">
                        <a href="#" class="hover:text-indigo-600">Admin</a>
                        <span class="mx-2">/</span>
                        <span class="text-slate-800 font-medium">@yield('header', 'Dashboard')</span>
                    </nav>
                </div>

                <!-- Alert Messages -->
                @if(session('success'))
                    <div class="mb-4 bg-green-50 border-l-4 border-green-500 text-green-700 p-4 rounded shadow-sm" role="alert">
                        <p class="font-bold">Thành công</p>
                        <p>{{ session('success') }}</p>
                    </div>
                @endif

                <!-- Main Content Injection -->
                @yield('content')
            </main>

            <!-- FOOTER -->
            <footer class="bg-white border-t border-slate-200 p-4 text-center text-sm text-slate-500">
                &copy; {{ date('Y') }} Sneaker Zone Admin Panel. All rights reserved.
            </footer>
        </div>
    </div>
</body>
</html>