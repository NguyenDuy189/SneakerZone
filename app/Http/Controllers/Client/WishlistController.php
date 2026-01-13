<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Wishlist;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    /**
     * Hiển thị danh sách yêu thích
     */
    public function index()
    {
        // Kiểm tra đăng nhập (Dù middleware đã chặn, thêm check này cho an toàn code)
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // Lấy danh sách wishlist
        // Eager Load: 'product' (để lấy tin sp), 'product.category' (lấy tên danh mục), 'product.variants' (để tính tồn kho)
        $wishlist = Wishlist::with(['product', 'product.category', 'product.variants'])
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate(10); // Phân trang 10 sản phẩm

        return view('client.wishlist.index', compact('wishlist'));
    }

    /**
     * Thêm/Xóa sản phẩm (AJAX)
     */
    public function toggle(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false, 
                'message' => 'Vui lòng đăng nhập để thực hiện chức năng này!',
                'code' => 401
            ], 401);
        }

        $productId = $request->input('product_id');
        $userId = Auth::id();

        // Kiểm tra tồn tại
        $wishlistItem = Wishlist::where('user_id', $userId)
            ->where('product_id', $productId)
            ->first();

        if ($wishlistItem) {
            // Có rồi -> Xóa
            $wishlistItem->delete();
            $action = 'removed';
            $message = 'Đã xóa khỏi danh sách yêu thích';
        } else {
            // Chưa có -> Thêm
            Wishlist::create([
                'user_id' => $userId,
                'product_id' => $productId
            ]);
            $action = 'added';
            $message = 'Đã thêm vào danh sách yêu thích';
        }

        // Đếm lại số lượng để cập nhật badge trên header (nếu cần)
        $count = Wishlist::where('user_id', $userId)->count();

        return response()->json([
            'success' => true,
            'action' => $action, // 'added' hoặc 'removed'
            'message' => $message,
            'count' => $count
        ]);
    }
}