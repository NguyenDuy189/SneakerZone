<?php

use Illuminate\Support\Facades\Route;
// use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\Client\ProductController;
// ADMIN CONTROLLERS
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CategoryController;
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



/*
|--------------------------------------------------------------------------
| WEB ROUTES
|--------------------------------------------------------------------------
*/

// -------------------------------------------------------------------------
// CLIENT (Tạm thời redirect về admin)
// -------------------------------------------------------------------------
Route::get('/', function () {
    return redirect()->route('admin.dashboard');
})->name('home');
//client 
        // 1. Danh sách sản phẩm (VD: /products)
        Route::get('/client-products', [ProductController::class, 'index'])->name('client.products.index');

        // 2. Chi tiết sản phẩm (VD: /products/ten-san-pham-a)
        // Dùng {slug} để truyền tên sản phẩm thân thiện qua URL
        Route::get('/products/{slug}', [ProductController::class, 'show'])->name('client.products.show');

        // Lưu ý: Nếu trang chủ của bạn đã có hiển thị sản phẩm, bạn có thể thêm logic lấy sản phẩm vào HomeController.
// -------------------------------------------------------------------------
// ADMIN AREA
// -------------------------------------------------------------------------
Route::prefix('admin')
    ->name('admin.')
    //->middleware(['auth', 'is_admin']) // Nếu bạn có middleware phân quyền
    ->group(function () {

        // ================================================================
        // 1. DASHBOARD
        // ================================================================
        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->name('dashboard');


        // ================================================================
        // 2. ORDERS (Đơn hàng)
        // ================================================================

        // Custom trước
        Route::get('orders/{id}/print', [OrderController::class, 'print'])
            ->name('orders.print');

        Route::put('orders/{id}/status', [OrderController::class, 'updateStatus'])
            ->name('orders.update_status');

        // Chỉ index + show
        Route::resource('orders', OrderController::class)
            ->only(['index', 'show']);


        // ================================================================
        // 3. BRANDS (Thương hiệu)
        // ================================================================
        Route::resource('brands', BrandController::class);


        // ================================================================
        // 4. CATEGORIES (Danh mục)
        // ================================================================
        Route::resource('categories', CategoryController::class);


        // ================================================================
        // 5. ATTRIBUTES (Thuộc tính & Giá trị)
        // ================================================================

        // CRUD thuộc tính cha
        Route::resource('attributes', AttributeController::class)
            ->except(['create', 'edit', 'update']);

        // CRUD giá trị con
        Route::post('attributes/{id}/values', [AttributeController::class, 'storeValue'])
            ->name('attributes.values.store');

        Route::delete('attributes/values/{id}', [AttributeController::class, 'destroyValue'])
            ->name('attributes.values.destroy');


        // ================================================================
        // 6. PRODUCTS (Sản phẩm)
        // ================================================================

        // --- TRASH ---
        Route::get('products/trash', [ProductController::class, 'trash'])
            ->name('products.trash');

        Route::post('products/{id}/restore', [ProductController::class, 'restore'])
            ->name('products.restore');

        Route::delete('products/{id}/force-delete', [ProductController::class, 'forceDelete'])
            ->name('products.force_delete');

        // --- VARIANTS ---
        Route::post('products/{id}/variants', [ProductController::class, 'storeVariant'])
            ->name('products.variants.store');

        Route::put('products/variants/{variant_id}', [ProductController::class, 'updateVariant'])
            ->name('products.variants.update');

        Route::delete('products/variants/{variant_id}', [ProductController::class, 'destroyVariant'])
            ->name('products.variants.destroy');

        // --- PRODUCTS CRUD (để cuối cùng) ---
        Route::resource('products', ProductController::class);


        // ================================================================
        // 7. REVIEWS (Đánh giá)
        // ================================================================

        // Trang danh sách review (admin)
        Route::prefix('reviews')->name('reviews.')->group(function () {

        Route::get('/', [\App\Http\Controllers\Admin\ReviewController::class, 'index'])
            ->name('index');

        Route::get('/reviews/approve/{id}', [ReviewController::class, 'approve'])
        ->name('show');


        Route::post('/reject/{id}', [\App\Http\Controllers\Admin\ReviewController::class, 'reject'])
            ->name('reject');

        Route::delete('/{id}', [\App\Http\Controllers\Admin\ReviewController::class, 'destroy'])
            ->name('destroy');
        

        
    });

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

        // --- KHÁCH HÀNG & SỔ ĐỊA CHỈ (Đã sửa hoàn chỉnh) ---
        Route::controller(CustomerUserController::class)->prefix('customers')->name('customers.')->group(function () {
            // CRUD Khách hàng
            Route::get('/', 'index')->name('index');
            Route::get('create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('{id}', 'show')->name('show');
            Route::get('{id}/edit', 'edit')->name('edit');
            Route::put('{id}', 'update')->name('update');
            Route::put('{id}/status', 'updateStatus')->name('update_status');
            Route::delete('{id}', 'destroy')->name('destroy');

            // CRUD Địa chỉ (Addresses) - Nested Routes
            Route::get('{id}/addresses/create', 'createAddress')->name('addresses.create');     // Form thêm địa chỉ
            Route::post('{id}/addresses', 'storeAddress')->name('addresses.store');             // Lưu địa chỉ mới
            Route::get('{id}/addresses/{address_id}/edit', 'editAddress')->name('addresses.edit'); // Form sửa địa chỉ
            Route::put('{id}/addresses/{address_id}', 'updateAddress')->name('addresses.update');  // Lưu sửa địa chỉ
            Route::delete('{id}/addresses/{address_id}', 'deleteAddress')->name('addresses.destroy'); // Xóa địa chỉ
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


        Route::resource('banners', BannerController::class);

    }); // End Admin Routes Group
