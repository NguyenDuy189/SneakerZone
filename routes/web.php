<?php

use App\Http\Controllers\Admin\AttributeController;
use Illuminate\Support\Facades\Route;

// 1. IMPORT CÁC CONTROLLER (Lưu ý Namespace Admin)
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CategoryController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// 2. ROUTE TRANG CHỦ
// Khi vào trang chủ, tự động chuyển hướng vào Dashboard quản trị
Route::get('/', function () {
    return redirect()->route('admin.dashboard');
});


// 3. NHÓM ROUTE QUẢN TRỊ (ADMIN)
// - prefix('admin'): Tất cả URL sẽ bắt đầu bằng /admin (VD: /admin/brands)
// - name('admin.'): Tất cả tên route sẽ bắt đầu bằng admin. (VD: admin.brands.index)

Route::prefix('admin')->name('admin.')->group(function () {

    // --- DASHBOARD ---
    // URL: /admin/dashboard
    // Route Name: admin.dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');


    // --- QUẢN LÝ THƯƠNG HIỆU (BRANDS) ---
    // Route::resource tự động tạo 7 route chuẩn (index, create, store, edit, update, destroy...)
    // URL: /admin/brands
    // Route Name: admin.brands.index, admin.brands.create...
    Route::resource('brands', BrandController::class);


    // --- QUẢN LÝ DANH MỤC (CATEGORIES) ---
    // URL: /admin/categories
    // Route Name: admin.categories.index...
    Route::resource('categories', CategoryController::class);


    // Route cho Attributes
    Route::resource('attributes', AttributeController::class)->except(['create', 'edit', 'update']); // Ta làm form nhanh nên bỏ bớt create/edit riêng

    // Route riêng để thêm/xóa giá trị con
    Route::post('attributes/{id}/values', [AttributeController::class, 'storeValue'])->name('attributes.values.store');
    Route::delete('attributes/values/{id}', [AttributeController::class, 'destroyValue'])->name('attributes.values.destroy');

    // --- (DÀNH CHO TƯƠNG LAI) ---
    // Tại đây bạn sẽ thêm tiếp Products, Orders, Users...
    // Route::resource('products', ProductController::class);
    // Route::resource('orders', OrderController::class);

});