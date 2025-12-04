<?php

use Illuminate\Support\Facades\Route;

// =========================================================================
// 1. IMPORT CONTROLLERS
// =========================================================================
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

/*
|--------------------------------------------------------------------------
| CLIENT SIDE (Tạm redirect về Admin)
|--------------------------------------------------------------------------
*/
Route::get('/', fn() => redirect()->route('admin.dashboard'))->name('home');


/*
|--------------------------------------------------------------------------
| ADMIN AUTH (Login/Logout)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->name('login.submit');
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
});


/*
|--------------------------------------------------------------------------
| ADMIN PANEL (Protected Routes)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'checkRole:admin,staff'])
    ->group(function () {

        // --- 1. DASHBOARD ---
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');


        // --- 2. ORDERS (Đơn hàng) ---
        Route::controller(OrderController::class)->prefix('orders')->name('orders.')->group(function () {
            // Các route custom phải đặt TRƯỚC route {id}
            Route::get('{id}/print', 'print')->name('print');
            Route::put('{id}/status', 'updateStatus')->name('update_status');
        });
        // Route Resource đặt sau cùng
        Route::resource('orders', OrderController::class)->only(['index', 'show']);


        // --- 3. PRODUCTS (Sản phẩm) - KHẮC PHỤC LỖI 404 TẠI ĐÂY ---
        Route::controller(ProductController::class)->prefix('products')->name('products.')->group(function () {
            // A. Thùng rác (Trash) - Phải đặt trên cùng
            Route::get('trash', 'trash')->name('trash');
            Route::post('{id}/restore', 'restore')->name('restore');
            Route::delete('{id}/force-delete', 'forceDelete')->name('force_delete');

            // B. Biến thể (Variants)
            Route::post('{id}/variants', 'storeVariant')->name('variants.store');
            Route::put('variants/{variant_id}', 'updateVariant')->name('variants.update');
            Route::delete('variants/{variant_id}', 'destroyVariant')->name('variants.destroy');
        });
        // C. Route Resource chuẩn (ĐẶT CUỐI CÙNG để không nuốt route 'trash')
        Route::resource('products', ProductController::class);


        // --- 4. CATALOG (Danh mục & Thương hiệu) ---
        Route::resource('categories', CategoryController::class);
        Route::resource('brands', BrandController::class);


        // --- 5. ATTRIBUTES (Thuộc tính) ---
        Route::controller(AttributeController::class)->prefix('attributes')->name('attributes.')->group(function () {
            Route::post('{id}/values', 'storeValue')->name('values.store');
            Route::delete('values/{id}', 'destroyValue')->name('values.destroy');
        });
        Route::resource('attributes', AttributeController::class)->except(['create', 'edit', 'update']);


        // --- 6. REVIEWS (Đánh giá) ---
        Route::controller(ReviewController::class)->prefix('reviews')->name('reviews.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::put('{id}/approve', 'approve')->name('approve');
            Route::delete('{id}', 'destroy')->name('destroy');
        });


        // --- 7. DISCOUNTS (Mã giảm giá) ---
        Route::resource('discounts', DiscountController::class)->except(['show']);


        // --- 8. USERS (Quản lý người dùng) ---
        
        // A. Khách hàng (Customers)
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

        // B. Nhân sự (Admins & Staffs)
        Route::resource('users', AdminUserController::class)->names('users');


        // --- 9. INVENTORY (Kho hàng) ---

        // A. Nhà cung cấp
        Route::resource('suppliers', SupplierController::class);

        // B. Phiếu nhập hàng
        Route::controller(PurchaseOrderController::class)->prefix('purchase-orders')->name('purchase_orders.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('{id}', 'show')->name('show');
            Route::put('{id}/status', 'updateStatus')->name('update_status');
        });

        // C. Lịch sử kho (Chỉ xem, không sửa xóa)
        // Đặt prefix là 'inventory/logs' để đẹp URL
        Route::controller(InventoryLogController::class)->prefix('inventory/logs')->name('inventory.logs.')->group(function () {
            Route::get('/', 'index')->name('index');
        });

        Route::prefix('shipping')->middleware(['auth'])->name('shipping.')->group(function () {

            // Danh sách đơn giao hàng
            Route::get('/', [ShippingController::class, 'index'])->name('index');
            // Hiển thị chi tiết đơn hàng + realtime
            Route::get('/{id}', [ShippingController::class, 'show'])->name('show');
            // Form gán shipper
            Route::get('/assign/{orderId}', [ShippingController::class, 'assignForm'])->name('assignForm');
            // Xử lý gán shipper
            Route::post('/assign/{orderId}', [ShippingController::class, 'assign'])->name('assign');
            // Cập nhật trạng thái đơn hàng (có thể dùng AJAX)
            Route::post('/update-status/{id}', [ShippingController::class, 'updateStatus'])->name('updateStatus');
        });

    });