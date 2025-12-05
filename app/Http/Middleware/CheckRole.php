<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CheckRole
{
    /**
     * Xử lý middleware kiểm tra role.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  mixed ...$roles  // Danh sách role được phép
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = Auth::user();

        // Nếu chưa đăng nhập
        if (!$user) {
            return redirect()->route('admin.login')->withErrors('Bạn cần đăng nhập để tiếp tục.');
        }

        // Nếu không có role hợp lệ
        if (!in_array($user->role, $roles)) {
            // Ghi log truy cập bị từ chối
            Log::warning("Truy cập bị từ chối: User ID {$user->id}, Role {$user->role}, URL: {$request->fullUrl()}");

            // Có thể redirect về trang dashboard hoặc 403
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Bạn không có quyền truy cập.'], 403);
            }
            
            return redirect()->route('admin.dashboard')->withErrors('Bạn không có quyền truy cập trang này.');
        }

        return $next($request);
    }
}
