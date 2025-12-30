<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Dashboard') - Sneaker Zone</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: { primary: '#4F46E5', secondary: '#1E293B' }
                }
            }
        }
    </script>
    <style>
        [x-cloak] { display: none !important; }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
</head>
<body class="font-sans antialiased bg-gray-50 text-slate-800" x-data="{ sidebarOpen: true }">

    <div class="flex h-screen overflow-hidden">
        <aside class="flex flex-col fixed z-30 inset-y-0 left-0 bg-secondary text-white transition-all duration-300 ease-in-out transform shadow-xl lg:static lg:translate-x-0"
               :class="sidebarOpen ? 'w-64 translate-x-0' : 'w-0 -translate-x-full lg:w-20 lg:translate-x-0'">
            
            <div class="flex items-center justify-center h-16 bg-slate-900 shadow-md flex-shrink-0 border-b border-slate-700">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 font-bold text-xl tracking-wider uppercase text-white overflow-hidden whitespace-nowrap group">
                    <i class="fa-solid fa-shoe-prints text-indigo-400 text-2xl group-hover:text-indigo-300 transition-colors"></i>
                    <span x-show="sidebarOpen" class="transition-opacity duration-300 group-hover:text-slate-200">Sneaker<span class="text-indigo-400">Zone</span></span>
                </a>
            </div>

            <div class="flex-1 flex flex-col overflow-y-auto overflow-x-hidden py-4 custom-scrollbar">
                <nav class="space-y-1 px-3">
                    
                    <a href="{{ route('admin.dashboard') }}" 
                       class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-xl transition-all relative mb-4
                       {{ request()->routeIs('admin.dashboard') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/30' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                        <i class="fa-solid fa-chart-pie w-6 h-6 flex items-center justify-center text-lg"></i>
                        <span x-show="sidebarOpen" class="ml-3 truncate">Tổng quan</span>
                    </a>

                    <div x-show="sidebarOpen" class="mt-6 mb-2 px-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider flex items-center gap-2">
                        <span class="w-8 h-[1px] bg-slate-600"></span> QUẢN LÝ SẢN PHẨM
                    </div>

                    <div x-data="{ open: {{ request()->routeIs('admin.products.*', 'admin.categories.*', 'admin.brands.*', 'admin.attributes.*', 'admin.reviews.*') ? 'true' : 'false' }} }">
                        <button @click="open = !open" 
                                class="w-full group flex items-center justify-between px-3 py-2.5 text-sm font-medium rounded-xl transition-colors mb-1
                                {{ request()->routeIs('admin.products.*', 'admin.categories.*', 'admin.brands.*', 'admin.attributes.*', 'admin.reviews.*') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                            <div class="flex items-center">
                                <i class="fa-solid fa-box-open w-6 h-6 flex items-center justify-center text-lg {{ request()->routeIs('admin.products.*') ? 'text-indigo-400' : '' }}"></i>
                                <span x-show="sidebarOpen" class="ml-3 truncate">Danh mục & SP</span>
                            </div>
                            <i x-show="sidebarOpen" class="fa-solid fa-chevron-right text-[10px] text-slate-500 transition-transform duration-300" :class="open ? 'rotate-90' : ''"></i>
                        </button>
                        
                        <div x-show="open && sidebarOpen" x-collapse class="space-y-1 mt-1 pl-11 pr-2 relative">
                            <div class="absolute left-[23px] top-0 bottom-0 w-[1px] bg-slate-700"></div>
                            <a href="{{ route('admin.products.index') }}" class="block px-3 py-2 text-sm rounded-lg transition-all {{ request()->routeIs('admin.products.*') ? 'text-white bg-indigo-600/20 border-l-2 border-indigo-500 pl-4' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">Tất cả sản phẩm</a>
                            <a href="{{ route('admin.categories.index') }}" class="block px-3 py-2 text-sm rounded-lg transition-all {{ request()->routeIs('admin.categories.*') ? 'text-white bg-indigo-600/20 border-l-2 border-indigo-500 pl-4' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">Danh mục</a>
                            <a href="{{ route('admin.brands.index') }}" class="block px-3 py-2 text-sm rounded-lg transition-all {{ request()->routeIs('admin.brands.*') ? 'text-white bg-indigo-600/20 border-l-2 border-indigo-500 pl-4' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">Thương hiệu</a>
                            <a href="{{ route('admin.attributes.index') }}" class="block px-3 py-2 text-sm rounded-lg transition-all {{ request()->routeIs('admin.attributes.*') ? 'text-white bg-indigo-600/20 border-l-2 border-indigo-500 pl-4' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">Thuộc tính</a>
                            <a href="{{ route('admin.reviews.index') }}" class="block px-3 py-2 text-sm rounded-lg transition-all {{ request()->routeIs('admin.reviews.*') ? 'text-white bg-indigo-600/20 border-l-2 border-indigo-500 pl-4' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">Đánh giá</a>
                        </div>
                    </div>

                    <div x-show="sidebarOpen" class="mt-6 mb-2 px-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider flex items-center gap-2">
                        <span class="w-8 h-[1px] bg-slate-600"></span> KHO & NHẬP HÀNG
                    </div>

                    <div x-data="{ open: {{ request()->routeIs('admin.inventory.*', 'admin.purchase_orders.*', 'admin.suppliers.*') ? 'true' : 'false' }} }">
                        <button @click="open = !open" 
                                class="w-full group flex items-center justify-between px-3 py-2.5 text-sm font-medium rounded-xl transition-colors mb-1
                                {{ request()->routeIs('admin.inventory.*', 'admin.purchase_orders.*', 'admin.suppliers.*') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                            <div class="flex items-center">
                                <i class="fa-solid fa-warehouse w-6 h-6 flex items-center justify-center text-lg {{ request()->routeIs('admin.inventory.*') ? 'text-indigo-400' : '' }}"></i>
                                <span x-show="sidebarOpen" class="ml-3 truncate">Quản lý kho</span>
                            </div>
                            <i x-show="sidebarOpen" class="fa-solid fa-chevron-right text-[10px] text-slate-500 transition-transform duration-300" :class="open ? 'rotate-90' : ''"></i>
                        </button>
                        
                        <div x-show="open && sidebarOpen" x-collapse class="space-y-1 mt-1 pl-11 pr-2 relative">
                            <div class="absolute left-[23px] top-0 bottom-0 w-[1px] bg-slate-700"></div>
                            
                            {{-- Tồn kho hiện tại --}}
                            <a href="{{ route('admin.inventory.index') }}" class="block px-3 py-2 text-sm rounded-lg transition-all {{ request()->routeIs('admin.inventory.index') ? 'text-white bg-indigo-600/20 border-l-2 border-indigo-500 pl-4' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                                Trạng thái kho
                            </a>

                            {{-- Lịch sử kho --}}
                            <a href="{{ route('admin.inventory.logs.index') }}" class="block px-3 py-2 text-sm rounded-lg transition-all {{ request()->routeIs('admin.inventory.logs.*') ? 'text-white bg-indigo-600/20 border-l-2 border-indigo-500 pl-4' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                                Lịch sử nhập/xuất
                            </a>

                            {{-- Phiếu nhập hàng (PO) --}}
                            <a href="{{ route('admin.purchase_orders.index') }}" class="block px-3 py-2 text-sm rounded-lg transition-all {{ request()->routeIs('admin.purchase_orders.*') ? 'text-white bg-indigo-600/20 border-l-2 border-indigo-500 pl-4' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                                Phiếu nhập hàng
                            </a>

                            {{-- Nhà cung cấp --}}
                            <a href="{{ route('admin.suppliers.index') }}" class="block px-3 py-2 text-sm rounded-lg transition-all {{ request()->routeIs('admin.suppliers.*') ? 'text-white bg-indigo-600/20 border-l-2 border-indigo-500 pl-4' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                                Nhà cung cấp
                            </a>
                        </div>
                    </div>

                    <div x-show="sidebarOpen" class="mt-6 mb-2 px-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider flex items-center gap-2">
                        <span class="w-8 h-[1px] bg-slate-600"></span> KINH DOANH
                    </div>

                    <a href="{{ route('admin.orders.index') }}" class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-xl transition-all relative
                        {{ request()->routeIs('admin.orders.*') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                        <i class="fa-solid fa-cart-shopping w-6 h-6 flex items-center justify-center text-lg {{ request()->routeIs('admin.orders.*') ? 'text-indigo-400' : '' }}"></i>
                        <span x-show="sidebarOpen" class="ml-3 truncate flex-1">Đơn hàng</span>
                    </a>

                    <div x-data="{ open: {{ request()->routeIs('admin.flash_sales.*', 'admin.banners.*', 'admin.discounts.*') ? 'true' : 'false' }} }">
                        <button @click="open = !open" 
                                class="w-full group flex items-center justify-between px-3 py-2.5 text-sm font-medium rounded-xl transition-colors mb-1
                                {{ request()->routeIs('admin.flash_sales.*', 'admin.banners.*', 'admin.discounts.*') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                            <div class="flex items-center">
                                <i class="fa-solid fa-bullhorn w-6 h-6 flex items-center justify-center text-lg {{ request()->routeIs('admin.flash_sales.*') ? 'text-indigo-400' : '' }}"></i>
                                <span x-show="sidebarOpen" class="ml-3 truncate">Marketing</span>
                            </div>
                            <i x-show="sidebarOpen" class="fa-solid fa-chevron-right text-[10px] text-slate-500 transition-transform duration-300" :class="open ? 'rotate-90' : ''"></i>
                        </button>
                        
                        <div x-show="open && sidebarOpen" x-collapse class="space-y-1 mt-1 pl-11 pr-2 relative">
                            <div class="absolute left-[23px] top-0 bottom-0 w-[1px] bg-slate-700"></div>
                            <a href="{{ route('admin.flash_sales.index') }}" class="block px-3 py-2 text-sm rounded-lg transition-all {{ request()->routeIs('admin.flash_sales.*') ? 'text-white bg-indigo-600/20 border-l-2 border-indigo-500 pl-4' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">Flash Sale</a>
                            <a href="{{ route('admin.banners.index') }}" class="block px-3 py-2 text-sm rounded-lg transition-all {{ request()->routeIs('admin.banners.*') ? 'text-white bg-indigo-600/20 border-l-2 border-indigo-500 pl-4' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">Banner</a>
                            <a href="{{ route('admin.discounts.index') }}" class="block px-3 py-2 text-sm rounded-lg transition-all {{ request()->routeIs('admin.discounts.*') ? 'text-white bg-indigo-600/20 border-l-2 border-indigo-500 pl-4' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">Mã giảm giá</a>
                        </div>
                    </div>

                    <div x-show="sidebarOpen" class="mt-6 mb-2 px-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider flex items-center gap-2">
                        <span class="w-8 h-[1px] bg-slate-600"></span> HỆ THỐNG
                    </div>

                    <div x-data="{ open: {{ request()->routeIs('admin.customers.*', 'admin.users.*') ? 'true' : 'false' }} }">
                        <button @click="open = !open" 
                                class="w-full group flex items-center justify-between px-3 py-2.5 text-sm font-medium rounded-xl transition-colors mb-1
                                {{ request()->routeIs('admin.customers.*', 'admin.users.*') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                            <div class="flex items-center">
                                <i class="fa-solid fa-users-gear w-6 h-6 flex items-center justify-center text-lg {{ request()->routeIs('admin.customers.*', 'admin.users.*') ? 'text-indigo-400' : '' }}"></i>
                                <span x-show="sidebarOpen" class="ml-3 truncate">Tài khoản</span>
                            </div>
                            <i x-show="sidebarOpen" class="fa-solid fa-chevron-right text-[10px] text-slate-500 transition-transform duration-300" :class="open ? 'rotate-90' : ''"></i>
                        </button>
                        
                        <div x-show="open && sidebarOpen" x-collapse class="space-y-1 mt-1 pl-11 pr-2 relative">
                            <div class="absolute left-[23px] top-0 bottom-0 w-[1px] bg-slate-700"></div>
                            <a href="{{ route('admin.customers.index') }}" class="block px-3 py-2 text-sm rounded-lg transition-all {{ request()->routeIs('admin.customers.*') ? 'text-white bg-indigo-600/20 border-l-2 border-indigo-500 pl-4' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">Khách hàng</a>
                            <a href="{{ route('admin.users.index') }}" class="block px-3 py-2 text-sm rounded-lg transition-all {{ request()->routeIs('admin.users.*') ? 'text-white bg-indigo-600/20 border-l-2 border-indigo-500 pl-4' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">Nhân viên</a>
                        </div>
                    </div>

                    <a href="{{ route('admin.settings.index') }}" class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-xl transition-all relative
                        {{ request()->routeIs('admin.settings.*') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                        <i class="fa-solid fa-sliders w-6 h-6 flex items-center justify-center text-lg {{ request()->routeIs('admin.settings.*') ? 'text-indigo-400' : '' }}"></i>
                        <span x-show="sidebarOpen" class="ml-3 truncate">Cấu hình chung</span>
                    </a>

                </nav>
            </div>

            <div class="bg-slate-900/50 p-4 border-t border-slate-700">
                <div class="flex items-center gap-3">
                    <div class="relative">
                        <img src="https://ui-avatars.com/api/?name=Admin&background=6366f1&color=fff" alt="Admin" class="h-10 w-10 rounded-full border-2 border-slate-600 shadow-sm">
                        <span class="absolute bottom-0 right-0 w-2.5 h-2.5 bg-green-500 border-2 border-slate-900 rounded-full"></span>
                    </div>
                    <div x-show="sidebarOpen" class="overflow-hidden">
                        <p class="text-sm font-bold text-white truncate">{{ Auth::user()->name ?? 'Administrator' }}</p>
                        <p class="text-[10px] text-slate-400 truncate">Online</p>
                    </div>
                    <form method="POST" action="{{ route('admin.logout') }}" class="ml-auto">
                        @csrf
                        <button type="submit" x-show="sidebarOpen" class="text-slate-400 hover:text-white transition-colors" title="Đăng xuất">
                            <i class="fa-solid fa-right-from-bracket"></i>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <header class="bg-white shadow-sm h-16 flex items-center justify-between px-6 z-20 sticky top-0">
                <div class="flex items-center gap-4">
                    <button @click="sidebarOpen = !sidebarOpen" class="text-slate-500 hover:text-slate-700 focus:outline-none transition-transform active:scale-95">
                        <i class="fa-solid fa-bars text-xl"></i>
                    </button>
                </div>
                <div class="flex items-center gap-4">
                    <div class="text-sm font-medium text-slate-500">{{ now()->format('l, d/m/Y') }}</div>
                </div>
            </header>

            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6">
                @if(session('success'))
                    <div class="mb-4 p-4 rounded-lg bg-green-50 border-l-4 border-green-500 text-green-700 fade-in">
                        <i class="fa-solid fa-check-circle mr-2"></i> {{ session('success') }}
                    </div>
                @endif
                @if(session('error'))
                    <div class="mb-4 p-4 rounded-lg bg-red-50 border-l-4 border-red-500 text-red-700 fade-in">
                        <i class="fa-solid fa-triangle-exclamation mr-2"></i> {{ session('error') }}
                    </div>
                @endif
                
                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>