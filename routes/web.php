<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// --- 1. Import Controllers (Admin) ---
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\AttributeController;
use App\Http\Controllers\Admin\ReviewController;
use App\Http\Controllers\Admin\DiscountController;
use App\Http\Controllers\Admin\CustomerUserController; // Quản lý Khách hàng
use App\Http\Controllers\Admin\AdminUserController;    // Quản lý Nhân sự
use App\Http\Controllers\Admin\AuthController;

/*
|--------------------------------------------------------------------------
| WEB ROUTES
|--------------------------------------------------------------------------
*/

// ... (Phần Client Side giữ nguyên)

// =========================================================================
// AUTHENTICATION (ĐĂNG NHẬP / ĐĂNG XUẤT)
// =========================================================================

// Route Login phải đặt tên là 'login' để Laravel tự redirect khi chưa auth
Route::get('admin/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('admin/login', [AuthController::class, 'login'])->name('admin.login.submit');
Route::post('admin/logout', [AuthController::class, 'logout'])->name('logout');


// =========================================================================
// PHẦN 2: ADMIN PANEL (BẢO VỆ BỞI AUTH)
// =========================================================================

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'checkRole:admin,staff']) // Uncomment khi đã có Auth
    ->group(function () {

        // --- 1. Dashboard ---
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');


        // --- 2. Quản lý Đơn hàng (Orders) ---
        Route::controller(OrderController::class)->prefix('orders')->name('orders.')->group(function () {
            // Custom Routes (Đặt trước Resource)
            Route::get('{id}/print', 'print')->name('print');
            Route::put('{id}/status', 'updateStatus')->name('update_status');
        });
        Route::resource('orders', OrderController::class)->only(['index', 'show']);


        // --- 3. Quản lý Sản phẩm & Kho (Products Core) ---
        
        // A. Sản phẩm (Products)
        Route::controller(ProductController::class)->prefix('products')->name('products.')->group(function () {
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

        // B. Danh mục & Thương hiệu
        Route::resource('categories', CategoryController::class);
        Route::resource('brands', BrandController::class);

        // C. Thuộc tính (Attributes)
        Route::controller(AttributeController::class)->prefix('attributes')->name('attributes.')->group(function () {
            Route::post('{id}/values', 'storeValue')->name('values.store');
            Route::delete('values/{id}', 'destroyValue')->name('values.destroy');
        });
        Route::resource('attributes', AttributeController::class)->except(['create', 'edit', 'update']);


        // --- 4. Marketing & Tương tác ---

        // A. Mã giảm giá (Discounts)
        Route::resource('discounts', DiscountController::class)->except(['show']);

        // B. Đánh giá (Reviews)
        Route::controller(ReviewController::class)->prefix('reviews')->name('reviews.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::put('{id}/approve', 'approve')->name('approve'); // Đổi thành PUT chuẩn RESTful
            Route::delete('{id}', 'destroy')->name('destroy');
        });


        // --- 5. Quản lý Con người (Users) ---

        // A. Khách hàng (Customers)
        Route::controller(CustomerUserController::class)->prefix('customers')->name('customers.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('{id}', 'show')->name('show'); // Xem chi tiết hồ sơ
            Route::get('{id}/edit', 'edit')->name('edit');
            Route::put('{id}', 'update')->name('update');
            Route::put('{id}/status', 'updateStatus')->name('update_status'); // Khóa/Mở khóa
            Route::delete('{id}', 'destroy')->name('destroy');
        });

        // B. Nhân sự (Admins & Staffs)
        Route::resource('users', AdminUserController::class)->names('users');


        Route::controller(\App\Http\Controllers\Admin\SupplierController::class)->prefix('suppliers')->name('suppliers.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/{id}/edit', 'edit')->name('edit');
            Route::put('/{id}', 'update')->name('update');
            Route::delete('/{id}', 'destroy')->name('destroy');
        });
        // Hoặc dùng ngắn gọn:
        // Route::resource('suppliers', \App\Http\Controllers\Admin\SupplierController::class);
    });