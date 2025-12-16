<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| IMPORT CONTROLLERS
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\AttributeController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ReviewController;
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
use App\Http\Controllers\Client\CartController;
use App\Http\Controllers\Client\HomeController;
use App\Http\Controllers\Client\ProductController;
use App\Http\Middleware\CheckRole;

/*
|--------------------------------------------------------------------------
| 1. CLIENT ROUTES (PUBLIC)
|--------------------------------------------------------------------------
*/
// ... Các import giữ nguyên



// Nhóm Route cho Client
Route::name('client.')->group(function () {
    
    // 1. TRANG CHỦ
    // Tên route: client.home
    Route::get('/', [HomeController::class, 'index'])->name('home');

    // 2. NHÓM SẢN PHẨM (Cửa hàng)
    // Prefix URL: /shop
    // Prefix Name: client.products.
    Route::prefix('shop')->name('products.')->group(function() {
        
        // Danh sách sản phẩm
        // Tên đầy đủ: client.products.index
        Route::get('/', [ProductController::class, 'index'])->name('index');
        
        // Chi tiết sản phẩm
        // Tên đầy đủ: client.products.show
        Route::get('/{slug}', [ProductController::class, 'show'])->name('show');
    });
    
    Route::controller(CartController::class)
    ->prefix('cart')
    ->name('cart.') // Tên route sẽ là: client.cart.index, client.cart.add...
    ->group(function () {
        Route::get('/', 'index')->name('index');           // Xem giỏ hàng
        Route::post('add', 'add')->name('add');             // Thêm vào giỏ
        Route::post('update', 'update')->name('update');    // Cập nhật số lượng
        Route::get('remove/{id}', 'remove')->name('remove');// Xóa sản phẩm
    });

    // ... các route khác ...

    // ROUTE REVIEW (Thêm đoạn này)
    Route::post('reviews', [ReviewController::class, 'store'])
        ->name('reviews.store')
        ->middleware('auth'); // Bắt buộc đăng nhập mới được review

});

/*
|--------------------------------------------------------------------------
| 2. ADMIN AUTH ROUTES (GUEST)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('login', [AdminAuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AdminAuthController::class, 'login'])->name('login.submit');
    Route::post('logout', [AdminAuthController::class, 'logout'])->name('logout');
});

/*
|--------------------------------------------------------------------------
| 3. ADMIN PROTECTED ROUTES (AUTH + ROLE CHECK)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', CheckRole::class . ':admin,staff']) // Bảo vệ Admin & Staff
    ->group(function () {

        // =================================================================
        // DASHBOARD
        // =================================================================
        Route::get('/', fn() => redirect()->route('admin.dashboard')); // Redirect root admin
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // =================================================================
        // ORDER MANAGEMENT
        // =================================================================
        Route::controller(OrderController::class)->prefix('orders')->name('orders.')->group(function () {
            Route::get('{id}/print', 'print')->name('print');
            Route::put('{id}/status', 'updateStatus')->name('update_status');
        });
        Route::resource('orders', OrderController::class)->only(['index', 'show']);

        // =================================================================
        // PRODUCT CATALOG
        // =================================================================
        Route::controller(AdminProductController::class)->prefix('products')->name('products.')->group(function () {
            // Trash
            Route::get('trash', 'trash')->name('trash');
            Route::post('{id}/restore', 'restore')->name('restore');
            Route::delete('{id}/force-delete', 'forceDelete')->name('force_delete');
            
            // Variants
            Route::post('{id}/variants', 'storeVariant')->name('variants.store');
            Route::put('variants/{variant_id}', 'updateVariant')->name('variants.update');
            Route::delete('variants/{variant_id}', 'destroyVariant')->name('variants.destroy');
        });
        Route::resource('products', AdminProductController::class);

        Route::resource('categories', CategoryController::class);
        Route::resource('brands', BrandController::class);

        Route::controller(AttributeController::class)->prefix('attributes')->name('attributes.')->group(function () {
            Route::post('{id}/values', 'storeValue')->name('values.store');
            Route::delete('values/{id}', 'destroyValue')->name('values.destroy');
        });
        Route::resource('attributes', AttributeController::class)->except(['create', 'edit', 'update']);

        Route::controller(ReviewController::class)->prefix('reviews')->name('reviews.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('{id}', 'show')->name('show');
            Route::put('{id}/approve', 'approve')->name('approve');
            Route::delete('{id}', 'destroy')->name('destroy');
        });

        // =================================================================
        // MARKETING TOOLS
        // =================================================================
        Route::resource('discounts', DiscountController::class);
        Route::resource('banners', BannerController::class);

        // Flash Sales
        // 1. Route Tìm kiếm sản phẩm (THÊM DÒNG NÀY)
        // Đặt nó lên đầu để tránh bị nhầm lẫn với các tham số {id}
        Route::get('flash-sales/search-products', [FlashSaleController::class, 'searchProductVariants'])
            ->name('flash_sales.product_search');

        // 2. Các Route quản lý Items (Nested)
        Route::controller(FlashSaleController::class)
            ->prefix('flash-sales/{flash_sale}')
            ->name('flash_sales.')
            ->whereNumber('flash_sale') // Chỉ nhận ID là số
            ->group(function () {
                Route::get('items', 'items')->name('items');
                Route::post('items', 'addItem')->name('items.store');
                Route::put('items/{item}', 'updateItem')->name('items.update');
                Route::delete('items/{item}', 'removeItem')->name('items.destroy');
                Route::get('statistics', 'statistics')->name('statistics');
            });

        // 3. Redirect UX (Click tên -> vào items)
        Route::get('flash-sales/{flash_sale}', fn($id) => redirect()->route('admin.flash_sales.items', $id))
            ->whereNumber('flash_sale')
            ->name('flash_sales.show');

        // 4. Resource chính (CRUD Flash Sale)
        Route::resource('flash-sales', FlashSaleController::class)
            ->names('flash_sales')
            ->except(['show']);

        // =================================================================
        // CUSTOMERS
        // =================================================================
        Route::controller(CustomerUserController::class)->prefix('customers')->name('customers.')->group(function () {
            // CRUD Customer
            Route::get('/', 'index')->name('index');
            Route::get('create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('{id}', 'show')->name('show');
            Route::get('{id}/edit', 'edit')->name('edit');
            Route::put('{id}', 'update')->name('update');
            Route::put('{id}/status', 'updateStatus')->name('update_status');
            Route::delete('{id}', 'destroy')->name('destroy');

            // Customer Addresses
            Route::get('{id}/addresses/create', 'createAddress')->name('addresses.create');
            Route::post('{id}/addresses', 'storeAddress')->name('addresses.store');
            Route::get('{id}/addresses/{address_id}/edit', 'editAddress')->name('addresses.edit');
            Route::put('{id}/addresses/{address_id}', 'updateAddress')->name('addresses.update');
            Route::delete('{id}/addresses/{address_id}', 'deleteAddress')->name('addresses.destroy');
        });

        // =================================================================
        // INVENTORY & SUPPLIERS
        // =================================================================
        Route::resource('suppliers', SupplierController::class);

        Route::controller(PurchaseOrderController::class)->prefix('purchase-orders')->name('purchase_orders.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('{id}', 'show')->name('show');
            Route::put('{id}/status', 'updateStatus')->name('update_status');
        });

        Route::get('inventory/logs', [InventoryLogController::class, 'index'])->name('inventory.logs.index');

        // =================================================================
        // SHIPPING
        // =================================================================
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

        // =================================================================
        // SYSTEM SETTINGS
        // =================================================================
        Route::resource('users', AdminUserController::class)->names('users');
        
        Route::controller(SettingController::class)->prefix('settings')->name('settings.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('edit', 'edit')->name('edit');
            Route::post('update', 'update')->name('update');
        });

    }); // End Admin Group