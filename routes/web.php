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
| 1. GLOBAL AUTH ROUTES (Bên ngoài group client. để tránh prefix login)
|--------------------------------------------------------------------------
*/
/*
|--------------------------------------------------------------------------
| 1. GLOBAL AUTH ROUTES (Bên ngoài group client)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    // ... Login & Register cũ ...
    Route::get('login', [ClientAuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [ClientAuthController::class, 'login'])->name('login.submit');
    Route::get('register', [ClientAuthController::class, 'showRegisterForm'])->name('register');
    Route::post('register', [ClientAuthController::class, 'register'])->name('register.submit');

    // --- THÊM MỚI: QUÊN MẬT KHẨU (CLIENT) ---
    // 1. Form nhập email quên mật khẩu
    Route::get('forgot-password', [ClientAuthController::class, 'showForgotPasswordForm'])
        ->name('password.request');

    // 2. Xử lý gửi email reset link
    Route::post('forgot-password', [ClientAuthController::class, 'sendResetLinkEmail'])
        ->name('password.email');

    // 3. Form nhập mật khẩu mới (khi bấm link từ email)
    Route::get('reset-password/{token}', [ClientAuthController::class, 'showResetPasswordForm'])
        ->name('password.reset');

    // 4. Xử lý cập nhật mật khẩu mới
    Route::post('reset-password', [ClientAuthController::class, 'resetPassword'])
        ->name('password.update');
});

Route::post('logout', [ClientAuthController::class, 'logout'])->name('logout')->middleware('auth');


/*
|--------------------------------------------------------------------------
| 2. CLIENT ROUTES (Với prefix name 'client.')
|--------------------------------------------------------------------------
*/
// NHÓM CLIENT (Prefix name: client.)
Route::name('client.')->group(function () {

    // 1. TRANG CHỦ & CÔNG KHAI
    Route::get('/', [HomeController::class, 'index'])->name('home');

    // 2. SẢN PHẨM (SHOP)
    Route::prefix('shop')->name('products.')->group(function () {
        Route::get('/', [ClientProductController::class, 'index'])->name('index');
        Route::get('/{slug}', [ClientProductController::class, 'show'])->name('show');
    });

    // 3. KHU VỰC CẦN ĐĂNG NHẬP (Middleware: auth)
    Route::middleware(['auth'])->group(function () {

        // --- ACCOUNT & PROFILE ---
        // Name prefix: client.account.
        Route::controller(AccountController::class)
            ->prefix('account')
            ->name('account.')
            ->group(function () {
                // Profile
                Route::get('/profile', 'index')->name('profile');
                Route::get('/profile/edit', 'edit')->name('edit');
                Route::post('/profile/update', 'updateProfile')->name('update');
                
                // Orders (QUAN TRỌNG: Route này tạo ra tên 'client.account.orders')
                Route::get('/orders', 'orders')->name('orders'); 
                Route::get('/orders/{id}', 'orderDetail')->name('order_details');
                
                // Order Actions
                Route::patch('/orders/{id}/cancel', 'cancelOrder')->name('orders.cancel');
                Route::patch('/orders/{id}/payment-method', 'changePaymentMethod')->name('orders.change_payment');
            });

        // --- CART ---
        // Name prefix: client.carts.
        Route::controller(CartController::class)
            ->prefix('cart')
            ->name('carts.')
            ->group(function () {
                Route::get('/', 'index')->name('index'); 
                Route::post('/add', 'addToCart')->name('add'); 
                Route::post('/update-quantity', 'updateQuantity')->name('update'); 
                Route::post('/remove', 'removeItem')->name('remove');
                Route::post('/select-item', 'selectItem')->name('select'); 
                Route::post('/apply-discount', 'applyDiscount')->name('apply_discount');
            });

        // --- REVIEWS ---
        // Name prefix: client.reviews.
        Route::post('/reviews/store', [ClientReviewController::class, 'store'])->name('reviews.store');

        // --- CHECKOUT & PAYMENT ---
        // Name prefix: client.checkouts.
        Route::controller(CheckoutController::class)
            ->prefix('checkouts')
            ->name('checkouts.')
            ->group(function () {
                Route::get('/', 'index')->name('index');
                Route::post('/process', 'process')->name('process');
                Route::get('/success', 'success')->name('success');
                Route::get('/failed', 'paymentFailed')->name('failed');
                
                // Payment Gateways Callbacks & Fakes
                Route::get('fake-momo', 'fakeMomoScreen')->name('fake_momo');
                Route::get('fake-momo-process', 'fakeMomoProcess')->name('fake_momo_process');
                Route::get('vnpay-return', 'vnpayCallback')->name('vnpay_return');
                Route::get('momo-return', 'momo_return')->name('momo_return');
                Route::post('momo-callback', 'momoCallback')->name('momo_callback');
                Route::get('/zalopay-return', 'zalopayCallback')->name('zalopay_return');
            });
    });

    Route::get('/vouchers', [VoucherController::class, 'index'])->name('vouchers.index');

    Route::get('/sale', [ProductSaleController::class, 'index'])->name('products.sale');

    
});
// Pages Footer
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
/*
|--------------------------------------------------------------------------
| 3. ADMIN PANEL (FULL)
|--------------------------------------------------------------------------
*/
// Admin Auth
/*
|--------------------------------------------------------------------------
| 3. ADMIN PANEL (FULL)
|--------------------------------------------------------------------------
*/
// Admin Auth
Route::prefix('admin')->name('admin.')->group(function () {
    // ... Login cũ ...
    Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login'])->name('login.submit');
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

    // --- THÊM MỚI: QUÊN MẬT KHẨU (ADMIN) ---
    // URL thực tế: /admin/forgot-password
    
    // 1. Form nhập email
    Route::get('forgot-password', [AdminAuthController::class, 'showLinkRequestForm'])
        ->name('password.request'); // Tên đầy đủ: admin.password.request

    // 2. Gửi mail
    Route::post('forgot-password', [AdminAuthController::class, 'sendResetLinkEmail'])
        ->name('password.email');

    // 3. Form reset (có token)
    Route::get('reset-password/{token}', [AdminAuthController::class, 'showResetForm'])
        ->name('password.reset');

    // 4. Update mật khẩu
    Route::post('reset-password', [AdminAuthController::class, 'reset'])
        ->name('password.update');
});

// Admin Dashboard & Management
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', CheckRole::class . ':admin,staff'])
    ->group(function () {

    Route::get('/', fn () => redirect()->route('admin.dashboard'));
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/export', [DashboardController::class, 'export'])->name('dashboard.export');

    // ORDERS
    Route::controller(OrderController::class)->prefix('orders')->name('orders.')->group(function () {
        Route::get('{id}/print', 'print')->name('print');
        Route::put('{id}/status', 'updateStatus')->name('update_status');
    });
    Route::resource('orders', OrderController::class)->only(['index', 'show']);

    // PRODUCTS
    // Giả sử bạn đã có prefix 'admin' ở bên ngoài group này
    Route::controller(AdminProductController::class)
        ->prefix('products')        // URL sẽ là: admin/products/...
        ->name('products.')         // Tên route sẽ ghép thành: admin.products....
        ->group(function () {

            // --- 1. CRUD SẢN PHẨM CHA ---
            Route::get('/', 'index')->name('index');                // admin.products.index
            Route::get('/create', 'create')->name('create');        // admin.products.create
            Route::post('/', 'store')->name('store');               // admin.products.store
            Route::get('/{id}/edit', 'edit')->name('edit');         // admin.products.edit
            Route::put('/{id}', 'update')->name('update');          // admin.products.update
            Route::delete('/{id}', 'destroy')->name('destroy');     // admin.products.destroy

            // --- 2. THÙNG RÁC (Giữ nguyên nếu controller có hàm này) ---
            Route::get('/trash', 'trash')->name('trash');
            Route::post('/{id}/restore', 'restore')->name('restore');
            Route::delete('/{id}/force-delete', 'forceDelete')->name('force_delete');

            // --- 3. BIẾN THỂ (VARIANTS) - ĐÃ SỬA ---
            
            // Tạo biến thể mới cho sản phẩm có {id}
            // URL: admin/products/{id}/variants
            // Gọi hàm: storeVariant trong ProductController
            Route::post('/{id}/variants', 'storeVariant')->name('variants.store');

            // Cập nhật biến thể (dùng variant_id để tìm biến thể)
            // URL: admin/products/variants/{variant_id}
            // Gọi hàm: updateVariant trong ProductController
            Route::put('/variants/{variant_id}', 'updateVariant')->name('variants.update');

            // Xóa biến thể
            // URL: admin/products/variants/{variant_id}
            // Gọi hàm: destroyVariant trong ProductController
            Route::delete('/variants/{variant_id}', 'destroyVariant')->name('variants.destroy');

            // --- 4. XÓA ẢNH AJAX ---
            Route::delete('/images/{id}', 'deleteImage')->name('images.delete');
        });
    Route::resource('products', AdminProductController::class);
    Route::resource('categories', CategoryController::class);
    Route::resource('brands', BrandController::class);
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::put('/inventory/{id}/quick-update', [InventoryController::class, 'quickUpdate'])->name('inventory.quick_update');

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
        Route::get('{id}/addresses/create', 'createAddress')->name('addresses.create');
        Route::post('{id}/addresses', 'storeAddress')->name('addresses.store');
        Route::get('{id}/addresses/{address_id}/edit', 'editAddress')->name('addresses.edit');
        Route::put('{id}/addresses/{address_id}', 'updateAddress')->name('addresses.update');
        Route::delete('{id}/addresses/{address_id}', 'deleteAddress')->name('addresses.destroy');
    });

    // LOGISTICS & SETTINGS
    Route::resource('suppliers', SupplierController::class);
    Route::controller(PurchaseOrderController::class)->prefix('purchase-orders')->name('purchase_orders.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('{id}', 'show')->name('show');
        Route::put('{id}/status', 'updateStatus')->name('update_status');
    });
    Route::get('inventory/logs', [InventoryLogController::class, 'index'])->name('inventory.logs.index');
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
    Route::resource('users', AdminUserController::class)->names('users');
    Route::controller(SettingController::class)->prefix('settings')->name('settings.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('edit', 'edit')->name('edit');
        Route::post('update', 'update')->name('update');
    });

    
});