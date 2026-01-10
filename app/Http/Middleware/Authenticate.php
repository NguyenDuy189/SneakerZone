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
    // File: App\Http\Middleware\Authenticate.php

    protected function redirectTo($request)
    {
        if (! $request->expectsJson()) {
            
            // 1. Kiểm tra nếu là đường dẫn Admin -> Về trang login Admin
            if ($request->is('admin') || $request->is('admin/*')) {
                return route('admin.login');
            }

            // 2. Mặc định: Về trang login Client
            // Chỉ flash session lỗi giỏ hàng nếu cần thiết (tùy logic của bạn)
            // session()->flash('error', 'Bạn cần đăng nhập để tiếp tục.'); 
            
            return route('login'); 
        }
        
        // Trả về null để Laravel tự xử lý trả về JSON 401 nếu là API
        return null;
    }
}
