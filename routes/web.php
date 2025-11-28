<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// 1. Route Trang chủ: Tự động chuyển hướng vào Dashboard
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// 2. Route Dashboard
// Tôi đặt name là 'dashboard' để sau này gọi route('dashboard') trong view cho tiện
Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard.index');

// 3. (Gợi ý mở rộng) Các route quản lý đơn hàng sau này
// Bạn có thể bỏ comment khi đã viết xong method show trong controller
/*
Route::prefix('orders')->name('orders.')->group(function () {
    // Ví dụ: Xem chi tiết đơn hàng (GET /orders/DH001)
    Route::get('/{code}', [DashboardController::class, 'showOrder'])->name('show');
});
*/