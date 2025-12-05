<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| IMPORT MIDDLEWARES
|--------------------------------------------------------------------------
*/
use App\Http\Middleware\CheckRole;

/*
|--------------------------------------------------------------------------
| IMPORT CONTROLLERS
|--------------------------------------------------------------------------
*/
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
use App\Http\Controllers\Admin\SettingController;

/*
|--------------------------------------------------------------------------
| GLOBAL REDIRECT
|--------------------------------------------------------------------------
*/
Route::get('/', fn() => redirect()->route('admin.dashboard'))->name('home');

/*
|--------------------------------------------------------------------------
| GUEST ROUTES (LOGIN/LOGOUT)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->name('login.submit');
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
});

/*
|--------------------------------------------------------------------------
| PROTECTED ADMIN ROUTES
|--------------------------------------------------------------------------
*/
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', CheckRole::class . ':admin,staff']) // Áp dụng middleware kiểm tra quyền
    ->group(function () {

        // =================================================================
        // 1. DASHBOARD
        // =================================================================
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // =================================================================
        // 2. ORDER MANAGEMENT
        // =================================================================
        Route::controller(OrderController::class)->prefix('orders')->name('orders.')->group(function () {
            Route::get('{id}/print', 'print')->name('print');
            Route::put('{id}/status', 'updateStatus')->name('update_status');
        });
        Route::resource('orders', OrderController::class)->only(['index', 'show']);

        // =================================================================
        // 3. PRODUCT MANAGEMENT
        // =================================================================
        Route::controller(ProductController::class)->prefix('products')->name('products.')->group(function () {
            // Trash & Restore
            Route::get('trash', 'trash')->name('trash');
            Route::post('{id}/restore', 'restore')->name('restore');
            Route::delete('{id}/force-delete', 'forceDelete')->name('force_delete');

            // Product Variants
            Route::post('{id}/variants', 'storeVariant')->name('variants.store');
            Route::put('variants/{variant_id}', 'updateVariant')->name('variants.update');
            Route::delete('variants/{variant_id}', 'destroyVariant')->name('variants.destroy');
        });
        Route::resource('products', ProductController::class);

        // =================================================================
        // 4. CATEGORIES & BRANDS & ATTRIBUTES
        // =================================================================
        Route::resource('categories', CategoryController::class);
        Route::resource('brands', BrandController::class);

        Route::controller(AttributeController::class)->prefix('attributes')->name('attributes.')->group(function () {
            Route::post('{id}/values', 'storeValue')->name('values.store');
            Route::delete('values/{id}', 'destroyValue')->name('values.destroy');
        });
        Route::resource('attributes', AttributeController::class)->except(['create', 'edit', 'update']);

        // =================================================================
        // 5. MARKETING (DISCOUNTS & FLASH SALES)
        // =================================================================
        
        // --- Discounts ---
        Route::resource('discounts', DiscountController::class)->except(['show']);

        // --- Flash Sales (Complex Logic) ---
        // A. AJAX Search (Must be BEFORE resource to avoid conflict with {id})
        Route::get('flash-sales/search-products', [FlashSaleController::class, 'searchProductVariants'])
            ->name('flash_sales.product_search');

        // B. Items & Statistics Management
        Route::controller(FlashSaleController::class)->prefix('flash-sales/{flash_sale}')->name('flash_sales.')->group(function() {
            Route::get('items', 'items')->name('items');
            Route::post('items', 'addItem')->name('items.store');
            Route::put('items/{item}', 'updateItem')->name('items.update');
            Route::delete('items/{item}', 'removeItem')->name('items.destroy');
            Route::get('statistics', 'statistics')->name('statistics');
        });

        // C. UX Redirect (View -> Items)
        Route::get('flash-sales/{flash_sale}', fn($id) => redirect()->route('admin.flash_sales.items', $id))
            ->name('flash_sales.show');

        // D. Main Resource
        Route::resource('flash-sales', FlashSaleController::class)
            ->names('flash_sales') // Force route names to use underscore (admin.flash_sales.index)
            ->except(['show']);

        // =================================================================
        // 6. CUSTOMER & REVIEWS
        // =================================================================
        Route::controller(ReviewController::class)->prefix('reviews')->name('reviews.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::put('{id}/approve', 'approve')->name('approve');
            Route::delete('{id}', 'destroy')->name('destroy');
        });

        Route::controller(CustomerUserController::class)->prefix('customers')->name('customers.')->group(function () {
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
        // 7. INVENTORY & SUPPLIERS
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
        // 8. SHIPPING MANAGEMENT
        // =================================================================
        Route::controller(ShippingController::class)->prefix('shipping')->name('shipping.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('create', 'create')->name('create');
            Route::post('store', 'store')->name('store');
            Route::get('trash', 'trash')->name('trash');
            
            // Routes with ID parameter
            Route::prefix('{shipping}')->group(function() {
                Route::get('show', 'show')->name('show');
                Route::post('restore', 'restore')->name('restore');
                Route::delete('destroy', 'destroy')->name('destroy');
                
                Route::get('assign', 'assignForm')->name('assign');
                Route::post('assign', 'assign')->name('assign.submit');
                Route::put('status', 'updateStatus')->name('update_status');
            });
        });

        // =================================================================
        // 9. SYSTEM ADMINISTRATION (Users & Settings)
        // =================================================================
        Route::resource('users', AdminUserController::class)->names('users');

        Route::controller(SettingController::class)->prefix('settings')->name('settings.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('edit', 'edit')->name('edit');
            Route::post('update', 'update')->name('update');
        });

    }); // End Admin Routes Group