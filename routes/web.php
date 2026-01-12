<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| CLIENT CONTROLLERS
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\Client\HomeController;
use App\Http\Controllers\Client\ProductController as ClientProductController;
use App\Http\Controllers\Client\CartController;
use App\Http\Controllers\Client\ReviewController as ClientReviewController;
use App\Http\Controllers\Client\AuthController as ClientAuthController;
use App\Http\Controllers\Client\CheckoutController;
use App\Http\Controllers\Client\AccountController;
use App\Http\Controllers\Client\VoucherController;
use App\Http\Controllers\Client\PageController;
use App\Http\Controllers\Client\ProductSaleController;

/*
|--------------------------------------------------------------------------
| ADMIN CONTROLLERS
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\AttributeController;
use App\Http\Controllers\Admin\OrderController; 
use App\Http\Controllers\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\PurchaseOrderController;
use App\Http\Controllers\Admin\InventoryLogController;
use App\Http\Controllers\Admin\CustomerUserController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\ShippingController;
use App\Http\Controllers\Admin\FlashSaleController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\DiscountController;
use App\Http\Controllers\Admin\InventoryController;

/*
|--------------------------------------------------------------------------
| MIDDLEWARE
|--------------------------------------------------------------------------
*/
use App\Http\Middleware\CheckRole;

/*
|--------------------------------------------------------------------------
| 1. GLOBAL AUTH ROUTES (GUEST)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    // --- CLIENT LOGIN & REGISTER ---
    Route::get('login', [ClientAuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [ClientAuthController::class, 'login'])->name('login.submit');
    Route::get('register', [ClientAuthController::class, 'showRegisterForm'])->name('register');
    Route::post('register', [ClientAuthController::class, 'register'])->name('register.submit');

    // --- CLIENT FORGOT PASSWORD ---
    Route::get('forgot-password', [ClientAuthController::class, 'showForgotPasswordForm'])->name('password.request');
    Route::post('forgot-password', [ClientAuthController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('reset-password/{token}', [ClientAuthController::class, 'showResetPasswordForm'])->name('password.reset');
    Route::post('reset-password', [ClientAuthController::class, 'resetPassword'])->name('password.update');
});

// Logout chung cho Client
Route::post('logout', [ClientAuthController::class, 'logout'])->name('logout')->middleware('auth');

/*
|--------------------------------------------------------------------------
| 2. CLIENT ROUTES (Prefix name: client.)
|--------------------------------------------------------------------------
*/
Route::name('client.')->group(function () {

    // 1. TRANG CHỦ & CÔNG KHAI
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('/vouchers', [VoucherController::class, 'index'])->name('vouchers.index');
    Route::get('/sale', [ProductSaleController::class, 'index'])->name('products.sale');

    // 2. SẢN PHẨM (SHOP)
    Route::prefix('shop')->name('products.')->group(function () {
        Route::get('/', [ClientProductController::class, 'index'])->name('index');
        Route::get('/{slug}', [ClientProductController::class, 'show'])->name('show');
    });

    // 3. KHU VỰC CẦN ĐĂNG NHẬP (Auth)
    Route::middleware(['auth'])->group(function () {

        // --- ACCOUNT ---
        Route::controller(AccountController::class)->prefix('account')->name('account.')->group(function () {
            Route::get('/profile', 'index')->name('profile');
            Route::get('/profile/edit', 'edit')->name('edit');
            Route::post('/profile/update', 'updateProfile')->name('update');
            
            // Orders
            Route::get('/orders', 'orders')->name('orders'); 
            Route::get('/orders/{id}', 'orderDetail')->name('order_details');
            Route::patch('/orders/{id}/cancel', 'cancelOrder')->name('orders.cancel');
            Route::patch('/orders/{id}/payment-method', 'changePaymentMethod')->name('orders.change_payment');
        });

        // --- CART ---
        Route::controller(CartController::class)->prefix('cart')->name('carts.')->group(function () {
            Route::get('/', 'index')->name('index'); 
            Route::post('/add', 'addToCart')->name('add'); 
            Route::post('/update-quantity', 'updateQuantity')->name('update'); 
            Route::post('/remove', 'removeItem')->name('remove');
            Route::post('/select-item', 'selectItem')->name('select'); 
            Route::post('/apply-discount', 'applyDiscount')->name('apply_discount');
        });

        // --- REVIEWS ---
        Route::post('/reviews/store', [ClientReviewController::class, 'store'])->name('reviews.store');

        // --- CHECKOUT & PAYMENT ---
        Route::controller(CheckoutController::class)->prefix('checkouts')->name('checkouts.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/process', 'process')->name('process');
            Route::get('/success', 'success')->name('success');
            Route::get('/failed', 'paymentFailed')->name('failed');
            
            // Callbacks & Fakes
            Route::get('fake-momo', 'fakeMomoScreen')->name('fake_momo');
            Route::get('fake-momo-process', 'fakeMomoProcess')->name('fake_momo_process');
            Route::get('vnpay-return', 'vnpayCallback')->name('vnpay_return');
            Route::get('momo-return', 'momo_return')->name('momo_return');
            Route::post('momo-callback', 'momoCallback')->name('momo_callback');
            Route::get('/zalopay-return', 'zalopayCallback')->name('zalopay_return');
        });
    });

    // 4. PAGES FOOTER
    Route::prefix('page')->name('page.')->group(function() {
        Route::get('/ve-chung-toi', [PageController::class, 'about'])->name('about');
        Route::get('/lien-he', [PageController::class, 'contact'])->name('contact');
        Route::get('/chinh-sach-doi-tra', [PageController::class, 'returnPolicy'])->name('return-policy');
        Route::get('/chinh-sach-bao-mat', [PageController::class, 'privacyPolicy'])->name('privacy-policy');
        Route::get('/huong-dan-mua-hang', [PageController::class, 'buyingGuide'])->name('buying-guide');
        Route::get('/tra-cuu-don-hang', [PageController::class, 'tracking'])->name('tracking');
        Route::get('/tim-cua-hang', [PageController::class, 'stores'])->name('stores');
        Route::get('/tin-tuc', [PageController::class, 'news'])->name('news');
    });
});

/*
|--------------------------------------------------------------------------
| 3. ADMIN PANEL (FULL)
|--------------------------------------------------------------------------
*/

// ADMIN AUTH (GUEST)
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login'])->name('login.submit');
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

    // Admin Forgot Password
    Route::get('forgot-password', [AdminAuthController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('forgot-password', [AdminAuthController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('reset-password/{token}', [AdminAuthController::class, 'showResetForm'])->name('password.reset');
    Route::post('reset-password', [AdminAuthController::class, 'reset'])->name('password.update');
});

// ADMIN DASHBOARD & MANAGEMENT (AUTH + ROLE)
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', CheckRole::class . ':admin,staff'])
    ->group(function () {

    // DASHBOARD
    Route::get('/', fn () => redirect()->route('admin.dashboard'));
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/export', [DashboardController::class, 'export'])->name('dashboard.export');

    // ORDERS
    Route::controller(OrderController::class)->prefix('orders')->name('orders.')->group(function () {
        Route::get('{id}/print', 'print')->name('print');
        Route::put('{id}/status', 'updateStatus')->name('update_status');
    });
    Route::resource('orders', OrderController::class)->only(['index', 'show']);

    // =========================================================================
    // SẢN PHẨM (PRODUCTS) - ĐÃ FIX XUNG ĐỘT RESOURCE
    // =========================================================================
    Route::controller(AdminProductController::class)
        ->prefix('products')
        ->name('products.')
        ->group(function () {
            // 1. Các route chức năng đặc biệt (Phải đặt TRƯỚC route {id})
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/trash', 'trash')->name('trash');
            
            // Route xóa ảnh Gallery (AJAX) - Quan trọng
            Route::delete('/images/{id}', 'deleteGalleryImage')->name('images.delete'); 

            // 2. Các route thao tác với Biến thể (Variants)
            Route::get('/{id}/variants', 'variants')->name('variants');
            Route::post('/{id}/variants', 'storeVariant')->name('variants.store');
            Route::put('/variants/{variant_id}', 'updateVariant')->name('variants.update');
            Route::delete('/variants/{variant_id}', 'destroyVariant')->name('variants.destroy');

            // 3. Các route CRUD chuẩn dựa trên {id}
            Route::get('/{id}/edit', 'edit')->name('edit');
            Route::put('/{id}', 'update')->name('update');
            Route::delete('/{id}', 'destroy')->name('destroy');
            Route::post('/{id}/restore', 'restore')->name('restore');
            Route::delete('/{id}/force-delete', 'forceDelete')->name('force_delete');
            
            // Route Index (Đặt cuối cùng trong nhóm get)
            Route::get('/', 'index')->name('index'); 
        });
    // LƯU Ý: Đã xóa Route::resource('products') để tránh lỗi ghi đè.

    // =========================================================================

    // CÁC RESOURCE KHÁC
    Route::resource('categories', CategoryController::class);
    Route::resource('brands', BrandController::class);
    
    // INVENTORY (KHO)
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::put('/inventory/{id}/quick-update', [InventoryController::class, 'quickUpdate'])->name('inventory.quick_update');
    Route::get('inventory/logs', [InventoryLogController::class, 'index'])->name('inventory.logs.index');

    // ATTRIBUTES
    Route::controller(AttributeController::class)->prefix('attributes')->name('attributes.')->group(function () {
        Route::post('{id}/values', 'storeValue')->name('values.store');
        Route::delete('values/{id}', 'destroyValue')->name('values.destroy');
    });
    Route::resource('attributes', AttributeController::class)->except(['create', 'edit', 'update']);

    // REVIEWS
    Route::controller(AdminReviewController::class)->prefix('reviews')->name('reviews.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('{id}', 'show')->name('show');
        Route::put('{id}/approve', 'approve')->name('approve');
        Route::delete('{id}', 'destroy')->name('destroy');
    });

    // MARKETING
    Route::resource('discounts', DiscountController::class);
    Route::resource('banners', BannerController::class);

    // FLASH SALES
    Route::get('flash-sales/search-products', [FlashSaleController::class, 'searchProductVariants'])->name('flash_sales.product_search');
    Route::controller(FlashSaleController::class)
        ->prefix('flash-sales/{flash_sale}')
        ->name('flash_sales.')
        ->whereNumber('flash_sale')
        ->group(function () {
            Route::get('items', 'items')->name('items');
            Route::post('items', 'addItem')->name('items.store');
            Route::put('items/{item}', 'updateItem')->name('items.update');
            Route::delete('items/{item}', 'removeItem')->name('items.destroy');
            Route::get('statistics', 'statistics')->name('statistics');
        });
    Route::get('flash-sales/{flash_sale}', fn ($id) => redirect()->route('admin.flash_sales.items', $id))->whereNumber('flash_sale')->name('flash_sales.show');
    Route::resource('flash-sales', FlashSaleController::class)->names('flash_sales')->except(['show']);

    // CUSTOMERS
    Route::controller(CustomerUserController::class)->prefix('customers')->name('customers.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('{id}', 'show')->name('show');
        Route::get('{id}/edit', 'edit')->name('edit');
        Route::put('{id}', 'update')->name('update');
        Route::put('{id}/status', 'updateStatus')->name('update_status');
        Route::delete('{id}', 'destroy')->name('destroy');
        
        // Addresses
        Route::get('{id}/addresses/create', 'createAddress')->name('addresses.create');
        Route::post('{id}/addresses', 'storeAddress')->name('addresses.store');
        Route::get('{id}/addresses/{address_id}/edit', 'editAddress')->name('addresses.edit');
        Route::put('{id}/addresses/{address_id}', 'updateAddress')->name('addresses.update');
        Route::delete('{id}/addresses/{address_id}', 'deleteAddress')->name('addresses.destroy');
    });

    // LOGISTICS
    Route::resource('suppliers', SupplierController::class);
    Route::controller(PurchaseOrderController::class)->prefix('purchase-orders')->name('purchase_orders.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('{id}', 'show')->name('show');
        Route::put('{id}/status', 'updateStatus')->name('update_status');
    });
    
    Route::controller(ShippingController::class)->prefix('shipping')->name('shipping.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::post('store', 'store')->name('store');
        Route::get('trash', 'trash')->name('trash');
        Route::prefix('{shipping}')->group(function () {
            Route::get('show', 'show')->name('show');
            Route::post('restore', 'restore')->name('restore');
            Route::delete('destroy', 'destroy')->name('destroy');
            Route::get('assign', 'assignForm')->name('assign');
            Route::post('assign', 'assign')->name('assign.submit');
            Route::put('status', 'updateStatus')->name('update_status');
        });
    });

    // SYSTEM
    Route::resource('users', AdminUserController::class)->names('users');
    
    Route::controller(SettingController::class)->prefix('settings')->name('settings.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('edit', 'edit')->name('edit');
        Route::post('update', 'update')->name('update');
    });
});