<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\AttributeController;
use App\Http\Controllers\Admin\ProductController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Trang chủ chuyển hướng vào Dashboard
Route::get('/', function () {
    return redirect()->route('admin.dashboard');
});

// NHÓM ROUTE ADMIN
Route::prefix('admin')->name('admin.')->group(function () {

    // 1. Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // 2. Brands (Thương hiệu)
    Route::resource('brands', BrandController::class);

    // 3. Categories (Danh mục)
    Route::resource('categories', CategoryController::class);

    // 4. Attributes (Thuộc tính)
    Route::resource('attributes', AttributeController::class)->except(['create', 'edit', 'update']);
    Route::post('attributes/{id}/values', [AttributeController::class, 'storeValue'])->name('attributes.values.store');
    Route::delete('attributes/values/{id}', [AttributeController::class, 'destroyValue'])->name('attributes.values.destroy');

    // 5. PRODUCTS (SẢN PHẨM) - QUAN TRỌNG: Thứ tự khai báo
    
    // --- Nhóm A: Các route Custom (Phải đặt TRƯỚC route resource) ---
    
    // Quản lý Thùng rác (Trash)
    Route::get('products/trash', [ProductController::class, 'trash'])->name('products.trash');
    Route::post('products/{id}/restore', [ProductController::class, 'restore'])->name('products.restore');
    Route::delete('products/{id}/force-delete', [ProductController::class, 'forceDelete'])->name('products.force_delete');

    // Quản lý Biến thể (Variants)
    Route::post('products/{id}/variants', [ProductController::class, 'storeVariant'])->name('products.variants.store');
    Route::put('products/variants/{variant_id}', [ProductController::class, 'updateVariant'])->name('products.variants.update'); // <--- MỚI: Route Update
    Route::delete('products/variants/{variant_id}', [ProductController::class, 'destroyVariant'])->name('products.variants.destroy');

    // --- Nhóm B: Route Resource chuẩn (Index, Create, Store, Edit, Update, Destroy) ---
    // Route này sẽ "bắt" tất cả các URL dạng /products/{id}, nên phải để cuối cùng
    Route::resource('products', ProductController::class);

});