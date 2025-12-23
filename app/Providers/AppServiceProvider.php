<?php

namespace App\Providers;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Support\Facades\Schema::defaultStringLength(191);

    // Chia sẻ số lượng giỏ hàng cho tất cả các view
    view()->composer('*', function ($view) {
        $cartQuantity = 0;
        if (auth()->check()) {
            // Lấy giỏ hàng của user và đếm tổng số lượng (quantity) của các item
            $cart = \App\Models\Cart::where('user_id', auth()->id())->with('items')->first();
            if ($cart) {
                $cartQuantity = $cart->items->sum('quantity'); 
                // Hoặc $cart->items->count() nếu bạn muốn đếm số dòng sản phẩm
            }
        }
        $view->with('cartQuantity', $cartQuantity);
    });
    }
}
