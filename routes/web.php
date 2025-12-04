<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// --- Import Controllers (Admin) ---
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\AttributeController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\OrderController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// -------------------------------------------------------------------------
// PHẦN 1: CLIENT SIDE (Khách hàng)
// -------------------------------------------------------------------------

Route::get('/', function () {
    return redirect()->route('admin.dashboard'); // Tạm thời chuyển hướng về Admin
})->name('home');

// Authentication (Login, Register, Logout)
// Auth::routes(); 

// -------------------------------------------------------------------------
// PHẦN 2: ADMIN SIDE (Quản trị viên)
// -------------------------------------------------------------------------

Route::prefix('admin')->name('admin.')->group(function () {

    // 1. Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // 2. Quản lý Đơn hàng (Orders)
    // Các route custom phải đặt TRƯỚC route resource để tránh bị ghi đè
    Route::get('orders/{id}/print', [OrderController::class, 'print'])->name('orders.print');
    Route::put('orders/{id}/status', [OrderController::class, 'updateStatus'])->name('orders.update_status');
    // Route resource chuẩn (chỉ lấy index và show)
    Route::resource('orders', OrderController::class)->only(['index', 'show']);

    // 3. Brands (Thương hiệu)
    Route::resource('brands', BrandController::class);

    // 4. Categories (Danh mục)
    Route::resource('categories', CategoryController::class);

    // 5. Attributes (Thuộc tính & Giá trị)
    // Route resource cho thuộc tính cha (Size, Color)
    Route::resource('attributes', AttributeController::class)->except(['create', 'edit', 'update']);
    // Route custom cho giá trị con (Values)
    Route::post('attributes/{id}/values', [AttributeController::class, 'storeValue'])->name('attributes.values.store');
    Route::delete('attributes/values/{id}', [AttributeController::class, 'destroyValue'])->name('attributes.values.destroy');

    // 6. QUẢN LÝ SẢN PHẨM (PRODUCTS)
    // QUAN TRỌNG: Các route custom phải đặt TRƯỚC route resource
    
    // --- Nhóm A: Quản lý Thùng rác (Trash) ---
    Route::get('products/trash', [ProductController::class, 'trash'])->name('products.trash');
    Route::post('products/{id}/restore', [ProductController::class, 'restore'])->name('products.restore');
    Route::delete('products/{id}/force-delete', [ProductController::class, 'forceDelete'])->name('products.force_delete');

    // --- Nhóm B: Quản lý Biến thể (Variants) ---
    Route::post('products/{id}/variants', [ProductController::class, 'storeVariant'])->name('products.variants.store');
    Route::put('products/variants/{variant_id}', [ProductController::class, 'updateVariant'])->name('products.variants.update');
    Route::delete('products/variants/{variant_id}', [ProductController::class, 'destroyVariant'])->name('products.variants.destroy');

    // --- Nhóm C: Route chuẩn (CRUD) ---
    // Route này sẽ "bắt" mọi URL dạng /products/{id}, nên phải để cuối cùng trong nhóm Product
    Route::resource('products', ProductController::class);

});