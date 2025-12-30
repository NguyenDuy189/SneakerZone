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
use App\Http\Controllers\Client\AuthController as ClientAuthController; // Khôi phục từ HEAD
use App\Http\Controllers\Client\CheckoutController;
use App\Http\Controllers\Client\AccountController; // Sử dụng Controller mới

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
| A. CLIENT ROUTES
|--------------------------------------------------------------------------
*/
Route::name('client.')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | HOME
    |--------------------------------------------------------------------------
    */
    Route::get('/', [HomeController::class, 'index'])->name('home');

    /*
    |--------------------------------------------------------------------------
    | PRODUCTS / SHOP
    |--------------------------------------------------------------------------
    */
    Route::prefix('shop')->name('products.')->group(function () {
        Route::get('/', [ClientProductController::class, 'index'])->name('index');
        Route::get('/{slug}', [ClientProductController::class, 'show'])->name('show');
    });

    /*
    |--------------------------------------------------------------------------
    | AUTH REQUIRED AREA (Khu vực cần đăng nhập)
    |--------------------------------------------------------------------------
    */
    Route::middleware('auth')->group(function () {

        /*
        |--------------------------------------------------------------------------
        | ACCOUNT & PROFILE & ORDERS (Feature Branch Logic)
        |--------------------------------------------------------------------------
        */
        Route::controller(AccountController::class)
            ->prefix('account')
            ->name('account.')
            ->group(function () {
                // 1. Xem hồ sơ
                Route::get('/profile', 'index')->name('profile');
                
                // 2. Form sửa hồ sơ
                Route::get('/profile/edit', 'edit')->name('edit');
                
                // 3. Hành động cập nhật hồ sơ
                Route::post('/profile/update', 'updateProfile')->name('update');

                // 4. Danh sách đơn hàng
                Route::get('/orders', 'orders')->name('orders');
                
                // 5. Chi tiết đơn hàng
                Route::get('/orders/{id}', 'orderDetail')->name('order_details');

                // 6. Hủy đơn hàng
                Route::patch('/orders/{id}/cancel', 'cancelOrder')->name('orders.cancel');

                // 7. Đổi phương thức thanh toán
                Route::patch('/orders/{id}/payment-method', 'changePaymentMethod')->name('orders.change_payment');
            });

        /*
        |--------------------------------------------------------------------------
        | CART
        |--------------------------------------------------------------------------
        */
        Route::controller(CartController::class)
            ->prefix('cart')
            ->name('carts.')
            ->group(function () {
                Route::get('/', 'index')->name('index');
                Route::post('/add', 'add')->name('add');
                Route::post('/update', 'update')->name('update');
                Route::get('/remove/{id}', 'remove')->name('remove');
                Route::post('/apply-discount', 'applyDiscount')->name('apply_discount');
                Route::post('/remove-discount', 'removeDiscount')->name('remove_discount');
            });

        /*
        |--------------------------------------------------------------------------
        | REVIEWS (Khôi phục từ HEAD)
        |--------------------------------------------------------------------------
        */
        Route::post('/reviews/store', [ClientReviewController::class, 'store'])
            ->name('reviews.store');

        /*
        |--------------------------------------------------------------------------
        | CHECKOUT & PAYMENT
        |--------------------------------------------------------------------------
        */
        Route::prefix('checkouts')
            ->name('checkouts.')
            ->group(function () {
                Route::get('/', [CheckoutController::class, 'index'])->name('index');
                Route::post('/process', [CheckoutController::class, 'process'])->name('process');
                Route::get('/success', [CheckoutController::class, 'success'])->name('success');
                Route::get('/failed', [CheckoutController::class, 'paymentFailed'])->name('failed');

                // --- FAKE MOMO (TESTING) ---
                Route::get('fake-momo', [CheckoutController::class, 'fakeMomoScreen'])->name('fake_momo');
                Route::get('fake-momo-process', [CheckoutController::class, 'fakeMomoProcess'])->name('fake_momo_process');

                // --- CALLBACKS / RETURN ---
                Route::get('vnpay-return', [CheckoutController::class, 'vnpayCallback'])->name('vnpay_return');
                
                Route::get('momo-return', [CheckoutController::class, 'momo_return'])->name('momo_return');
                Route::post('momo-callback', [CheckoutController::class, 'momoCallback'])->name('momo_callback');
                
                // --- ZALO PAY ---
                Route::get('/zalopay-return', [CheckoutController::class, 'zalopayCallback'])->name('zalopay_return');                            
            });
            
        /*
        |--------------------------------------------------------------------------
        | LOGOUT (Khôi phục từ HEAD)
        |--------------------------------------------------------------------------
        */
        Route::post('logout', [ClientAuthController::class, 'logout'])->name('logout');
    });

    /*
    |--------------------------------------------------------------------------
    | GUEST ROUTES (Login/Register - Khôi phục từ HEAD)
    |--------------------------------------------------------------------------
    */
    Route::middleware('guest')->group(function () {
        Route::get('login', [ClientAuthController::class, 'showLoginForm'])->name('login');
        Route::post('login', [ClientAuthController::class, 'login'])->name('login.submit');
        Route::get('register', [ClientAuthController::class, 'showRegisterForm'])->name('register');
        Route::post('register', [ClientAuthController::class, 'register'])->name('register.submit');
    });
});

/*
|--------------------------------------------------------------------------
| B. ADMIN AUTH
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login'])->name('login.submit');
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');
});

/*
|--------------------------------------------------------------------------
| C. ADMIN PANEL (AUTH + ROLE)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', CheckRole::class . ':admin,staff'])
    ->group(function () {

    Route::get('/', fn () => redirect()->route('admin.dashboard'));
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/export', [DashboardController::class, 'export'])->name('dashboard.export');

    /*
    |--------------------------------------------------------------------------
    | ORDERS
    |--------------------------------------------------------------------------
    */
    Route::controller(OrderController::class)->prefix('orders')->name('orders.')->group(function () {
        Route::get('{id}/print', 'print')->name('print');
        Route::put('{id}/status', 'updateStatus')->name('update_status');
    });
    Route::resource('orders', OrderController::class)->only(['index', 'show']);

    /*
    |--------------------------------------------------------------------------
    | PRODUCTS & INVENTORY
    |--------------------------------------------------------------------------
    */
    Route::controller(AdminProductController::class)->prefix('products')->name('products.')->group(function () {
        Route::get('trash', 'trash')->name('trash');
        Route::post('{id}/restore', 'restore')->name('restore');
        Route::delete('{id}/force-delete', 'forceDelete')->name('force_delete');
        Route::post('{id}/variants', 'storeVariant')->name('variants.store');
        Route::put('variants/{variant_id}', 'updateVariant')->name('variants.update');
        Route::delete('variants/{variant_id}', 'destroyVariant')->name('variants.destroy');
    });
    Route::resource('products', AdminProductController::class);

    Route::resource('categories', CategoryController::class);
    Route::resource('brands', BrandController::class);

    // Route Quản lý kho
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    
    // Route Cập nhật nhanh (AJAX) - MỚI
    Route::put('/inventory/{id}/quick-update', [InventoryController::class, 'quickUpdate'])->name('inventory.quick_update');

    /*
    |--------------------------------------------------------------------------
    | ATTRIBUTES
    |--------------------------------------------------------------------------
    */
    Route::controller(AttributeController::class)->prefix('attributes')->name('attributes.')->group(function () {
        Route::post('{id}/values', 'storeValue')->name('values.store');
        Route::delete('values/{id}', 'destroyValue')->name('values.destroy');
    });
    Route::resource('attributes', AttributeController::class)->except(['create', 'edit', 'update']);

    /*
    |--------------------------------------------------------------------------
    | REVIEWS
    |--------------------------------------------------------------------------
    */
    Route::controller(AdminReviewController::class)->prefix('reviews')->name('reviews.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('{id}', 'show')->name('show');
        Route::put('{id}/approve', 'approve')->name('approve');
        Route::delete('{id}', 'destroy')->name('destroy');
    });

    /*
    |--------------------------------------------------------------------------
    | MARKETING
    |--------------------------------------------------------------------------
    */
    Route::resource('discounts', DiscountController::class);
    Route::resource('banners', BannerController::class);

    /*
    |--------------------------------------------------------------------------
    | FLASH SALES
    |--------------------------------------------------------------------------
    */
    Route::get('flash-sales/search-products', [FlashSaleController::class, 'searchProductVariants'])
        ->name('flash_sales.product_search');

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

    Route::get('flash-sales/{flash_sale}', fn ($id) =>
        redirect()->route('admin.flash_sales.items', $id)
    )->whereNumber('flash_sale')->name('flash_sales.show');

    Route::resource('flash-sales', FlashSaleController::class)
        ->names('flash_sales')
        ->except(['show']);

    /*
    |--------------------------------------------------------------------------
    | CUSTOMERS
    |--------------------------------------------------------------------------
    */
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

    /*
    |--------------------------------------------------------------------------
    | SUPPLIERS / SHIPPING / SETTINGS
    |--------------------------------------------------------------------------
    */
    Route::resource('suppliers', SupplierController::class);

    Route::controller(PurchaseOrderController::class)
        ->prefix('purchase-orders')
        ->name('purchase_orders.')
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('{id}', 'show')->name('show');
            Route::put('{id}/status', 'updateStatus')->name('update_status');
        });

    Route::get('inventory/logs', [InventoryLogController::class, 'index'])
        ->name('inventory.logs.index');

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