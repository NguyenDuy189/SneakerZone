<?php

use Illuminate\Support\Facades\Route;

// 1. IMPORT MIDDLEWARE (QUAN TRỌNG ĐỂ FIX LỖI)
use App\Http\Middleware\CheckRole;

// 2. IMPORT CONTROLLERS
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\AttributeController;
use App\Http\Controllers\Admin\DiscountController;
use App\Http\Controllers\Admin\ReviewController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\PurchaseOrderController;
use App\Http\Controllers\Admin\InventoryLogController;
use App\Http\Controllers\Admin\CustomerUserController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\ShippingController;
use App\Http\Controllers\Admin\FlashSaleController;

/*
|--------------------------------------------------------------------------
| HOME REDIRECT
|--------------------------------------------------------------------------
*/
Route::get('/', fn() => redirect()->route('admin.dashboard'))->name('home');

/*
|--------------------------------------------------------------------------
| ADMIN AUTHENTICATION (GUEST)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->name('login.submit');
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
});

/*
|--------------------------------------------------------------------------
| ADMIN PANEL (PROTECTED)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')
    ->name('admin.')
    // Middleware: Đăng nhập + Check Role (Admin hoặc Staff)
    ->middleware(['auth', CheckRole::class . ':admin,staff']) 
    ->group(function () {

        // =================================================================
        // DASHBOARD
        // =================================================================
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // =================================================================
        // QUẢN LÝ ĐƠN HÀNG (ORDERS)
        // =================================================================
        Route::prefix('orders')->name('orders.')->controller(OrderController::class)->group(function () {
            Route::get('{id}/print', 'print')->name('print');
            Route::put('{id}/status', 'updateStatus')->name('update_status');
        });
        Route::resource('orders', OrderController::class)->only(['index', 'show']);

        // =================================================================
        // QUẢN LÝ SẢN PHẨM (PRODUCTS)
        // =================================================================
        Route::prefix('products')->name('products.')->controller(ProductController::class)->group(function () {
            // Thùng rác & Khôi phục
            Route::get('trash', 'trash')->name('trash');
            Route::post('{id}/restore', 'restore')->name('restore');
            Route::delete('{id}/force-delete', 'forceDelete')->name('force_delete');

            // Biến thể (Variants)
            Route::post('{id}/variants', 'storeVariant')->name('variants.store');
            Route::put('variants/{variant_id}', 'updateVariant')->name('variants.update');
            Route::delete('variants/{variant_id}', 'destroyVariant')->name('variants.destroy');
        });
        Route::resource('products', ProductController::class);

        // =================================================================
        // DANH MỤC & THƯƠNG HIỆU
        // =================================================================
        Route::resource('categories', CategoryController::class);
        Route::resource('brands', BrandController::class);

        // =================================================================
        // THUỘC TÍNH (ATTRIBUTES)
        // =================================================================
        Route::prefix('attributes')->name('attributes.')->controller(AttributeController::class)->group(function () {
            Route::post('{id}/values', 'storeValue')->name('values.store');
            Route::delete('values/{id}', 'destroyValue')->name('values.destroy');
        });
        Route::resource('attributes', AttributeController::class)->except(['create', 'edit', 'update']);

        // =================================================================
        // ĐÁNH GIÁ (REVIEWS)
        // =================================================================
        Route::prefix('reviews')->name('reviews.')->controller(ReviewController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::put('{id}/approve', 'approve')->name('approve');
            Route::delete('{id}', 'destroy')->name('destroy');
        });

        // =================================================================
        // MÃ GIẢM GIÁ (DISCOUNTS)
        // =================================================================
        Route::prefix('discounts')->name('discounts.')->controller(DiscountController::class)->group(function () {
            // Các route custom nếu có
        });
        Route::resource('discounts', DiscountController::class)->except(['show']);

        // =================================================================
        // QUẢN LÝ KHÁCH HÀNG (CUSTOMERS)
        // =================================================================
        Route::prefix('customers')->name('customers.')->controller(CustomerUserController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('{id}', 'show')->name('show');
            Route::get('{id}/edit', 'edit')->name('edit');
            Route::put('{id}', 'update')->name('update');
            Route::put('{id}/status', 'updateStatus')->name('update_status');
            Route::delete('{id}', 'destroy')->name('destroy');
        });

        // =================================================================
        // QUẢN TRỊ VIÊN (ADMIN USERS)
        // =================================================================
        Route::resource('users', AdminUserController::class)->names('users');

        // =================================================================
        // KHO & NHẬP HÀNG (INVENTORY)
        // =================================================================
        Route::resource('suppliers', SupplierController::class);
        
        Route::prefix('purchase-orders')->name('purchase_orders.')->controller(PurchaseOrderController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('{id}', 'show')->name('show');
            Route::put('{id}/status', 'updateStatus')->name('update_status');
        });

        Route::prefix('inventory/logs')->name('inventory.logs.')->controller(InventoryLogController::class)->group(function () {
            Route::get('/', 'index')->name('index');
        });

        // =================================================================
        // VẬN CHUYỂN (SHIPPING)
        // =================================================================
        Route::prefix('shipping')->name('shipping.')->controller(ShippingController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('create', 'create')->name('create');
            Route::post('store', 'store')->name('store');
            Route::get('{shipping}/show', 'show')->name('show');
            
            // Thùng rác & Khôi phục
            Route::get('trash', 'trash')->name('trash');
            Route::post('{shipping}/restore', 'restore')->name('restore');
            Route::delete('{shipping}/destroy', 'destroy')->name('destroy'); // Xóa vĩnh viễn
            
            // Giao vận đơn & Trạng thái
            Route::get('{shipping}/assign', 'assignForm')->name('assign');
            Route::post('{shipping}/assign', 'assign')->name('assign.submit');
            Route::put('{shipping}/status', 'updateStatus')->name('update_status');
        });

        // =================================================================
        // QUẢN LÝ FLASH SALE (FULL)
        // =================================================================
        
        // 1. Route Tìm kiếm Ajax (QUAN TRỌNG: Phải đặt trên cùng)
        // Gọi bằng: route('admin.flash_sales.product_search')
        Route::get('flash-sales/search-products', [FlashSaleController::class, 'searchProductVariants'])
            ->name('flash_sales.product_search');

        // 2. Các chức năng con (Sản phẩm, Thống kê)
        Route::prefix('flash-sales/{flash_sale}')->group(function() {
            Route::get('items', [FlashSaleController::class, 'items'])->name('flash_sales.items');
            Route::post('items', [FlashSaleController::class, 'addItem'])->name('flash_sales.items.store');
            Route::put('items/{item}', [FlashSaleController::class, 'updateItem'])->name('flash_sales.items.update');
            Route::delete('items/{item}', [FlashSaleController::class, 'removeItem'])->name('flash_sales.items.destroy');
            Route::get('statistics', [FlashSaleController::class, 'statistics'])->name('flash_sales.statistics');
        });

        // 3. CRUD Chính cho Chiến dịch
        Route::resource('flash-sales', FlashSaleController::class)
            ->names([
                'index'   => 'flash_sales.index',
                'create'  => 'flash_sales.create',
                'store'   => 'flash_sales.store',
                'edit'    => 'flash_sales.edit',
                'update'  => 'flash_sales.update',
                'destroy' => 'flash_sales.destroy',
            ])
            ->parameters(['flash-sales' => 'flash_sale'])
            ->except(['show']);

        // 4. Redirect UX (Show -> Items)
        Route::get('flash-sales/{flash_sale}', function ($flash_sale) {
            return redirect()->route('admin.flash_sales.items', $flash_sale);
        })->name('flash_sales.show');

    }); // Kết thúc Group Admin Panel