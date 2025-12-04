<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// ADMIN CONTROLLERS
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\AttributeController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ReviewController;
use App\Http\Controllers\HomeController;

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

Route::get('/category/{slug}', [HomeController::class, 'category'])->name('category.products');

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

    });
