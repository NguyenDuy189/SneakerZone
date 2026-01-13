<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| CONTROLLER IMPORTS
|--------------------------------------------------------------------------
*/
// 1. Client Controllers
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
use App\Http\Controllers\Client\OrderController as ClientOrderController;
use App\Http\Controllers\Client\WishlistController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\SimpleNewsletter;



// 2. Admin Controllers
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
// 3. Middleware
use App\Http\Middleware\CheckRole;

/*
|--------------------------------------------------------------------------
| PART 1: CLIENT SIDE & GLOBAL AUTH
|--------------------------------------------------------------------------
*/

// --- A. GUEST ROUTES (Login, Register, Forgot Password) ---
Route::middleware('guest')->group(function () {
    // 1. Login & Register
    Route::get('login', [ClientAuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [ClientAuthController::class, 'login'])->name('login.submit');
    Route::get('register', [ClientAuthController::class, 'showRegisterForm'])->name('register');
    Route::post('register', [ClientAuthController::class, 'register'])->name('register.submit');

    // 2. Forgot Password
    Route::get('forgot-password', [ClientAuthController::class, 'showForgotPasswordForm'])->name('password.request');
    Route::post('forgot-password', [ClientAuthController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('reset-password/{token}', [ClientAuthController::class, 'showResetPasswordForm'])->name('password.reset');
    Route::post('reset-password', [ClientAuthController::class, 'resetPassword'])->name('password.update');
});

// Logout (Yêu cầu đăng nhập)
Route::post('logout', [ClientAuthController::class, 'logout'])->name('logout')->middleware('auth');

// --- B. PUBLIC & PROTECTED CLIENT ROUTES ---
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

    // 3. KHU VỰC CẦN ĐĂNG NHẬP (Middleware: auth)
    Route::middleware(['auth'])->group(function () {

        Route::prefix('account')->name('account.')->group(function () {

            // =========================================================
            // 1. NHÓM PROFILE (AccountController)
            // =========================================================
            Route::controller(AccountController::class)->group(function () {
                // URL: /account/profile
                // Name: client.account.profile
                Route::get('/profile', 'index')->name('profile');

                // URL: /account/profile/edit
                // Name: client.account.edit
                Route::get('/profile/edit', 'edit')->name('edit');

                // URL: /account/profile/update
                // Name: client.account.update
                Route::post('/profile/update', 'updateProfile')->name('update');
            });

            // =========================================================
            // 2. NHÓM ORDERS (OrderController)
            // =========================================================
            Route::controller(ClientOrderController::class)
                ->prefix('orders')      // URL nối tiếp: /account/orders...
                ->name('orders.')       // Name nối tiếp: client.account.orders.index...
                ->group(function () {

                    // URL: /account/orders
                    // Name: client.account.orders.index
                    // (Mặc định gọi 'client.account.orders.index' sẽ vào đây)
                    Route::get('/', 'index')->name('index');

                    // URL: /account/orders/{id}
                    // Name: client.account.orders.show (Thay cho order_details cho chuẩn RESTful)
                    Route::get('/{id}', 'show')->name('show');

                    // URL: /account/orders/cancel/{id}
                    // Name: client.account.orders.cancel
                    Route::patch('/cancel/{id}', 'cancel')->name('cancel');

                    // URL: /account/orders/payment-method/{id}
                    // Name: client.account.orders.index.payment_method
                    Route::post('/payment-method/{id}', 'changePaymentMethod')->name('payment_method');
                });
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

            // Payment Callbacks
            Route::get('fake-momo', 'fakeMomoScreen')->name('fake_momo');
            Route::get('fake-momo-process', 'fakeMomoProcess')->name('fake_momo_process');
            Route::get('vnpay-return', 'vnpayCallback')->name('vnpay_return');
            Route::get('momo-return', 'momo_return')->name('momo_return');
            Route::post('momo-callback', 'momoCallback')->name('momo_callback');
            Route::get('/zalopay-return', 'zalopayCallback')->name('zalopay_return');
        });

        // --- WISHLIST ---
        Route::controller(WishlistController::class)
            ->prefix('wishlist')     // URL sẽ là /wishlist và /wishlist/toggle
            ->name('wishlist.')      // Tên route sẽ ghép với client. -> wishlist
            ->group(function () {
                // Tên đầy đủ: wishlistindex
                Route::get('/', 'index')->name('index');

                // Tên đầy đủ: wishlisttoggle
                Route::post('/toggle', 'toggle')->name('toggle');
            });
    });
});

// --- C. STATIC PAGES (FOOTER) ---
Route::prefix('page')->name('client.page.')->controller(PageController::class)->group(function () {
    Route::get('/ve-chung-toi', 'about')->name('about');
    Route::get('/lien-he', 'contact')->name('contact');
    Route::get('/chinh-sach-doi-tra', 'returnPolicy')->name('return-policy');
    Route::get('/chinh-sach-bao-mat', 'privacyPolicy')->name('privacy-policy');
    Route::get('/huong-dan-mua-hang', 'buyingGuide')->name('buying-guide');
    Route::get('/tra-cuu-don-hang', 'tracking')->name('tracking');
    Route::get('/tim-cua-hang', 'stores')->name('stores');
    Route::get('/tin-tuc', 'news')->name('news');
});

// routes/web.php

Route::post('/newsletter/subscribe', function (Request $request) {
    // 1. Validate: Bắt buộc phải nhập email đúng định dạng
    $request->validate([
        'email' => 'required|email'
    ], [
        'email.required' => 'Vui lòng nhập địa chỉ email.',
        'email.email' => 'Địa chỉ email không hợp lệ.',
    ]);

    try {
        // 2. Gửi mail
        // Nếu chưa tạo Class Mail, xem Bước 2 bên dưới
        Mail::to($request->email)->send(new SimpleNewsletter());

        // 3. Trả về thông báo thành công
        return back()->with('success', 'Đăng ký thành công! Hãy kiểm tra email của bạn.');
        
    } catch (\Exception $e) {
        // Trả về lỗi nếu gửi mail thất bại (do mạng hoặc cấu hình .env sai)
        return back()->withErrors(['email' => 'Lỗi gửi mail: ' . $e->getMessage()])->withInput();
    }

})->name('client.newsletter.subscribe'); // <--- QUAN TRỌNG: Tên này phải khớp với action trong form


/*
|--------------------------------------------------------------------------
| PART 2: ADMIN PANEL
|--------------------------------------------------------------------------
*/

// --- A. ADMIN GUEST (Login, Forgot Password) ---
Route::prefix('admin')->name('admin.')->group(function () {
    // Login
    Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login'])->name('login.submit');

    // Forgot Password
    Route::get('forgot-password', [AdminAuthController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('forgot-password', [AdminAuthController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('reset-password/{token}', [AdminAuthController::class, 'showResetForm'])->name('password.reset');
    Route::post('reset-password', [AdminAuthController::class, 'reset'])->name('password.update');
});

// --- B. ADMIN PROTECTED ROUTES ---
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', CheckRole::class . ':admin,staff']) // Middleware check role
    ->group(function () {

        // Logout
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

        // Dashboard
        Route::get('/', fn() => redirect()->route('admin.dashboard'));
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard/export', [DashboardController::class, 'export'])->name('dashboard.export');

        // 1. ORDERS
        Route::controller(OrderController::class)->prefix('orders')->name('orders.')->group(function () {
            Route::get('{id}/print', 'print')->name('print');
            Route::put('{id}/status', 'updateStatus')->name('update_status');
        });
        // Bỏ 'only' để mở các route create, store, edit...
        Route::resource('orders', OrderController::class)->except(['destroy']);

        // 2. PRODUCTS (Phức tạp nhất - Đã gộp và sắp xếp để tránh lỗi)
        Route::controller(AdminProductController::class)->prefix('products')->name('products.')->group(function () {
            // --- Custom Routes (Trash, Restore, Force Delete) ---
            Route::get('/trash', 'trash')->name('trash');
            Route::post('/{id}/restore', 'restore')->name('restore');
            Route::delete('/{id}/force-delete', 'forceDelete')->name('force_delete');

            // --- Variants ---
            Route::get('/{id}/variants', 'variants')->name('variants');
            Route::post('/{id}/variants', 'storeVariant')->name('variants.store');
            Route::put('/variants/{variant_id}', 'updateVariant')->name('variants.update');
            Route::delete('/variants/{variant_id}', 'destroyVariant')->name('variants.destroy');

            // --- Images ---
            Route::delete('/images/{id}', 'deleteGalleryImage')->name('images.delete'); // Đã sửa tên hàm cho khớp controller

            // --- Standard CRUD (Manual Definition to ensure order) ---
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/{id}/edit', 'edit')->name('edit');
            Route::put('/{id}', 'update')->name('update');
            Route::delete('/{id}', 'destroy')->name('destroy');
        });

        // 3. BASIC RESOURCES (Categories, Brands, Attributes)
        Route::resource('categories', CategoryController::class);
        Route::resource('brands', BrandController::class);

        // Attributes (Custom + Resource)
        Route::controller(AttributeController::class)->prefix('attributes')->name('attributes.')->group(function () {
            Route::post('{id}/values', 'storeValue')->name('values.store');
            Route::delete('values/{id}', 'destroyValue')->name('values.destroy');
        });
        Route::resource('attributes', AttributeController::class)->except(['create', 'edit', 'update']);

        // 4. INVENTORY (Đã thêm route bị thiếu gây lỗi 500/404)
        Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
        Route::put('/inventory/{id}/quick-update', [InventoryController::class, 'quickUpdate'])->name('inventory.quick_update');
        Route::get('inventory/logs', [InventoryLogController::class, 'index'])
            ->name('inventory.logs.index');
        // 5. REVIEWS
        Route::controller(AdminReviewController::class)->prefix('reviews')->name('reviews.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('{id}', 'show')->name('show');
            Route::put('{id}/approve', 'approve')->name('approve');
            Route::delete('{id}', 'destroy')->name('destroy');
        });

        // 6. MARKETING (Discounts, Banners, Flash Sales)
        Route::resource('discounts', DiscountController::class);
        Route::resource('banners', BannerController::class);

        // Flash Sales (Logic phức tạp)
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
        Route::get('flash-sales/{flash_sale}', fn($id) => redirect()->route('admin.flash_sales.items', $id))->whereNumber('flash_sale')->name('flash_sales.show');
        Route::resource('flash-sales', FlashSaleController::class)->names('flash_sales')->except(['show']);

        // 7. CUSTOMERS
        Route::controller(CustomerUserController::class)->prefix('customers')->name('customers.')->group(function () {
            // Main CRUD
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

        // 8. LOGISTICS (Suppliers, Purchase Orders, Shipping)
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
            // Group with ID
            Route::prefix('{shipping}')->group(function () {
                Route::get('show', 'show')->name('show');
                Route::post('restore', 'restore')->name('restore');
                Route::delete('destroy', 'destroy')->name('destroy');
                Route::get('assign', 'assignForm')->name('assign');
                Route::post('assign', 'assign')->name('assign.submit');
                Route::put('status', 'updateStatus')->name('update_status');
            });
        });

        // 9. SYSTEM (Users, Settings)
        Route::resource('users', AdminUserController::class)->names('users');

        Route::controller(SettingController::class)->prefix('settings')->name('settings.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('edit', 'edit')->name('edit');
            Route::post('update', 'update')->name('update');
        });
    });
