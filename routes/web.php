<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Client\ProductController;
use App\Http\Controllers\ProfileController;

/*
|--------------------------------------------------------------------------
| HOME
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return redirect()->route('client.products.index');
})->name('home');

/*
|--------------------------------------------------------------------------
| PRODUCTS - PUBLIC
|--------------------------------------------------------------------------
*/
Route::get('/products', [ProductController::class, 'index'])
    ->name('client.products.index');

Route::get('/products/{slug}', [ProductController::class, 'show'])
    ->name('client.products.show');

Route::get('/products/search', [ProductController::class, 'search'])
    ->name('client.products.search');

/*
|--------------------------------------------------------------------------
| AUTH REQUIRED
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    // CART

    Route::get('/cart', [ProductController::class, 'cart'])
        ->name('cart.index');

    Route::post('/cart/update/{id}', [ProductController::class, 'updateCart'])
        ->name('cart.update');

    Route::post('/cart/remove/{id}', [ProductController::class, 'removeFromCart'])
        ->name('cart.remove');

    // ACCOUNT
    Route::get('/account', function () {
        return view('client.account.index');
    })->name('account.index');

    // PROFILE (Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
});

Route::post('/cart/add/{id}', [ProductController::class, 'addToCart'])
        ->name('cart.add');

require __DIR__.'/auth.php';
