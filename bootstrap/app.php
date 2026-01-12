<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException; // <--- Import để xử lý lỗi 419

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        
        // --- 1. ĐĂNG KÝ ALIAS (BÍ DANH) ---
        $middleware->alias([
            'checkRole' => \App\Http\Middleware\CheckRole::class,
        ]);

        // --- 2. CẤU HÌNH CHUYỂN HƯỚNG KHI CHƯA ĐĂNG NHẬP ---
        // Hàm này chạy khi user chưa login mà cố truy cập trang cần bảo vệ
        $middleware->redirectGuestsTo(function (Request $request) {
            
            // Nếu đường dẫn bắt đầu bằng 'admin', chuyển về Admin Login
            if ($request->is('admin*')) {
                return route('admin.login');
            }

            // Mặc định chuyển về Client Login (Kiểm tra xem bạn đã đặt tên route này chưa)
            // Nếu route client của bạn tên khác, hãy sửa lại 'client.login' bên dưới
            return route('client.login'); 
        });
    })
    ->withExceptions(function (Exceptions $exceptions) {
        
        // --- 3. XỬ LÝ LỖI 419 (PAGE EXPIRED) ---
        $exceptions->render(function (TokenMismatchException $e, Request $request) {
            
            // Chỉ xử lý nếu không phải là request API/Json
            if (! $request->expectsJson()) {
                
                // Tạo thông báo lỗi
                $msg = 'Phiên làm việc đã hết hạn. Vui lòng đăng nhập lại.';

                // Logic chuyển hướng thông minh dựa trên đường dẫn hiện tại
                if ($request->is('admin*')) {
                    return redirect()->route('admin.login')->withErrors(['email' => $msg]);
                }

                return redirect()->route('client.login')->withErrors(['email' => $msg]);
            }
        });

    })->create();