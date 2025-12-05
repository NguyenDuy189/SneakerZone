<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        
        // 1. ĐĂNG KÝ ALIAS (QUAN TRỌNG: Để fix lỗi Target class does not exist)
        $middleware->alias([
            'checkRole' => \App\Http\Middleware\CheckRole::class,
        ]);

        // 2. Cấu hình Redirect khi chưa login (Code cũ của bạn)
        $middleware->redirectGuestsTo(function (Request $request) {
            // Nếu cố truy cập trang admin mà chưa login -> Về trang admin login
            if ($request->is('admin/*')) {
                return route('admin.login');
            }
            // Mặc định (nếu sau này làm trang khách)
            return route('admin.login'); 
        });

    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();