<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| IMPORT CONTROLLERS (Sử dụng Alias để tránh trùng tên)
|--------------------------------------------------------------------------
*/

// 1. Client Controllers
use App\Http\Controllers\Client\HomeController;
use App\Http\Controllers\Client\ProductController as ClientProductController;
use App\Http\Controllers\Client\CartController;
use App\Http\Controllers\Client\ReviewController as ClientReviewController; // <--- Đã đặt biệt danh cho Client

// 2. Admin Controllers
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\AttributeController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ReviewController as AdminReviewController;   // <--- Đã đặt biệt danh cho Admin
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
use App\Http\Controllers\Client\CheckoutController;
// Middleware
use App\Http\Middleware\CheckRole;

/*
|--------------------------------------------------------------------------
| A. CLIENT ROUTES (Giao diện khách hàng)
|--------------------------------------------------------------------------
*/
Route::name('client.')->group(function () {
    
    // 1. Trang chủ
    Route::get('/', [HomeController::class, 'index'])->name('home');

    // 2. Sản phẩm (Shop)
    Route::prefix('shop')->name('products.')->group(function() {
        Route::get('/', [ClientProductController::class, 'index'])->name('index');
        Route::get('/{slug}', [ClientProductController::class, 'show'])->name('show');
    });
    
    // 3. Giỏ hàng
    Route::controller(CartController::class)->prefix('cart')->name('cart.')->group(function () {
        Route::get('/', 'index')->name('index');           // Xem giỏ hàng
        Route::post('add', 'add')->name('add');             // Thêm vào giỏ
        Route::post('update', 'update')->name('update');    // Cập nhật số lượng
        Route::get('remove/{id}', 'remove')->name('remove');// Xóa sản phẩm
    });

    // 4. Đánh giá (Review) - Bắt buộc đăng nhập
    Route::middleware('auth')->group(function() {
        // Lưu ý: Đang dùng ClientReviewController (Alias đã khai báo ở trên)
        Route::post('reviews/store', [ClientReviewController::class, 'store'])->name('reviews.store');

        Route::group(['prefix' => 'cart', 'as' => 'carts.'], function () { 
            Route::get('/', [CartController::class, 'index'])->name('index');
            Route::post('/add', [CartController::class, 'add'])->name('add');
            Route::post('/update', [CartController::class, 'update'])->name('update');
            Route::get('/remove/{id}', [CartController::class, 'remove'])->name('remove');

            Route::post('/apply-discount', [CartController::class, 'applyDiscount'])->name('apply_discount');
            Route::post('/remove-discount', [CartController::class, 'removeDiscount'])->name('remove_discount');
        });
    });

    // Sửa thành: Chỉ để 'checkouts.' (Nhớ có dấu chấm cuối)
    Route::group(['prefix' => 'checkout', 'as' => 'checkouts.'], function() { 
        Route::get('/', [CheckoutController::class, 'index'])->name('index');
        Route::post('/process', [CheckoutController::class, 'process'])->name('process');
    });

});

/*
|--------------------------------------------------------------------------
| B. ADMIN AUTH ROUTES (Đăng nhập quản trị)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('login', [AdminAuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AdminAuthController::class, 'login'])->name('login.submit');
    Route::post('logout', [AdminAuthController::class, 'logout'])->name('logout');
});

/*
|--------------------------------------------------------------------------
| C. ADMIN PROTECTED ROUTES (Phải đăng nhập + Check Role)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', CheckRole::class . ':admin,staff']) // Chỉ Admin & Staff
    ->group(function () {

        // 1. Dashboard
        Route::get('/', fn() => redirect()->route('admin.dashboard'));
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // 2. Đơn hàng (Order)
        Route::controller(OrderController::class)->prefix('orders')->name('orders.')->group(function () {
            Route::get('{id}/print', 'print')->name('print');
            Route::put('{id}/status', 'updateStatus')->name('update_status');
        });
        Route::resource('orders', OrderController::class)->only(['index', 'show']);

        // 3. Sản phẩm & Kho hàng
        Route::controller(AdminProductController::class)->prefix('products')->name('products.')->group(function () {
            Route::get('trash', 'trash')->name('trash');
            Route::post('{id}/restore', 'restore')->name('restore');
            Route::delete('{id}/force-delete', 'forceDelete')->name('force_delete');
            
            // Biến thể (Variants)
            Route::post('{id}/variants', 'storeVariant')->name('variants.store');
            Route::put('variants/{variant_id}', 'updateVariant')->name('variants.update');
            Route::delete('variants/{variant_id}', 'destroyVariant')->name('variants.destroy');
        });
        Route::resource('products', AdminProductController::class);

        Route::resource('categories', CategoryController::class);
        Route::resource('brands', BrandController::class);

        // Thuộc tính (Attributes)
        Route::controller(AttributeController::class)->prefix('attributes')->name('attributes.')->group(function () {
            Route::post('{id}/values', 'storeValue')->name('values.store');
            Route::delete('values/{id}', 'destroyValue')->name('values.destroy');
        });
        Route::resource('attributes', AttributeController::class)->except(['create', 'edit', 'update']);

        // 4. Quản lý Đánh giá (Admin Review)
        // Lưu ý: Đang dùng AdminReviewController (Alias đã khai báo ở trên)
        Route::controller(AdminReviewController::class)->prefix('reviews')->name('reviews.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('{id}', 'show')->name('show');
            Route::put('{id}/approve', 'approve')->name('approve'); // Duyệt đánh giá
            Route::delete('{id}', 'destroy')->name('destroy');
        });

        // 5. Marketing (Flash Sale, Banner, Discount)
        Route::resource('discounts', DiscountController::class);
        Route::resource('banners', BannerController::class);

        // Flash Sales Logic
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

        Route::get('flash-sales/{flash_sale}', fn($id) => redirect()->route('admin.flash_sales.items', $id))
            ->whereNumber('flash_sale')->name('flash_sales.show');

        Route::resource('flash-sales', FlashSaleController::class)->names('flash_sales')->except(['show']);

        // 6. Khách hàng (Customers)
        Route::controller(CustomerUserController::class)->prefix('customers')->name('customers.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('{id}', 'show')->name('show');
            Route::get('{id}/edit', 'edit')->name('edit');
            Route::put('{id}', 'update')->name('update');
            Route::put('{id}/status', 'updateStatus')->name('update_status');
            Route::delete('{id}', 'destroy')->name('destroy');

            // Địa chỉ khách hàng
            Route::get('{id}/addresses/create', 'createAddress')->name('addresses.create');
            Route::post('{id}/addresses', 'storeAddress')->name('addresses.store');
            Route::get('{id}/addresses/{address_id}/edit', 'editAddress')->name('addresses.edit');
            Route::put('{id}/addresses/{address_id}', 'updateAddress')->name('addresses.update');
            Route::delete('{id}/addresses/{address_id}', 'deleteAddress')->name('addresses.destroy');
        });

        // 7. Nhập kho & Nhà cung cấp
        Route::resource('suppliers', SupplierController::class);
        Route::controller(PurchaseOrderController::class)->prefix('purchase-orders')->name('purchase_orders.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('{id}', 'show')->name('show');
            Route::put('{id}/status', 'updateStatus')->name('update_status');
        });
        Route::get('inventory/logs', [InventoryLogController::class, 'index'])->name('inventory.logs.index');

        // 8. Vận chuyển (Shipping)
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

        // 9. Cài đặt hệ thống & Tài khoản Admin
        Route::resource('users', AdminUserController::class)->names('users');
        
        Route::controller(SettingController::class)->prefix('settings')->name('settings.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('edit', 'edit')->name('edit');
            Route::post('update', 'update')->name('update');
        });

    });