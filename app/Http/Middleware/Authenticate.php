<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Closure;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Xác định đường dẫn redirect khi chưa đăng nhập.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        if (! $request->expectsJson()) {

            // Nếu URL bắt đầu bằng /admin → redirect về admin login
            if ($request->is('admin') || $request->is('admin/*')) {
                return route('admin.login');
            }

            // Ngược lại, redirect về login client
            return route('login'); // route login client, nếu có
        }

        // Nếu request AJAX hoặc expects JSON → trả về 401 JSON
        return null;
    }
}
