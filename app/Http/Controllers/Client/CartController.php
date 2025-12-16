<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ProductVariant;

class CartController extends Controller
{
    // Thêm sản phẩm vào giỏ
    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'variant_id' => 'required|exists:product_variants,id',
            'quantity'   => 'required|integer|min:1'
        ]);

        try {
            $variant = ProductVariant::findOrFail($request->variant_id);

            // 1. Kiểm tra tồn kho
            if ($variant->stock_quantity < $request->quantity) {
                return back()->with('error', 'Sản phẩm không đủ số lượng tồn kho!');
            }

            // 2. Lấy hoặc Tạo Giỏ hàng (Cart)
            $cart = $this->getOrCreateCart();

            // 3. Kiểm tra sản phẩm đã có trong giỏ chưa
            $cartItem = CartItem::where('cart_id', $cart->id)
                                ->where('product_variant_id', $variant->id)
                                ->first();

            if ($cartItem) {
                // Nếu có rồi -> Cộng dồn số lượng
                $newQty = $cartItem->quantity + $request->quantity;
                
                // Check tồn kho lần nữa
                if ($variant->stock_quantity < $newQty) {
                    return back()->with('error', 'Kho chỉ còn ' . $variant->stock_quantity . ' sản phẩm!');
                }

                $cartItem->quantity = $newQty;
                $cartItem->save();
            } else {
                // Nếu chưa có -> Tạo mới
                CartItem::create([
                    'cart_id' => $cart->id,
                    'product_variant_id' => $variant->id,
                    'quantity' => $request->quantity
                ]);
            }

            return back()->with('success', 'Đã thêm vào giỏ hàng thành công!');

        } catch (\Exception $e) {
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    // Hàm hỗ trợ: Lấy giỏ hàng hiện tại hoặc tạo mới
    private function getOrCreateCart()
    {
        if (Auth::check()) {
            // Nếu đã đăng nhập: Tìm theo user_id
            $cart = Cart::firstOrCreate(['user_id' => Auth::id()]);
            
            // (Optional) Logic gộp giỏ hàng session vào user nếu cần
        } else {
            // Nếu chưa đăng nhập: Tìm theo session_id
            $sessionId = Session::getId();
            $cart = Cart::firstOrCreate(['session_id' => $sessionId]);
        }
        return $cart;
    }

    // Xem giỏ hàng
    public function index()
    {
        $cart = $this->getOrCreateCart();
        // Load các item kèm thông tin sản phẩm
        $cartItems = CartItem::with(['variant.product', 'variant.attributeValues.attribute'])
                             ->where('cart_id', $cart->id)
                             ->get();

        return view('client.cart.index', compact('cartItems'));
    }

    // ... (Các hàm add, index, getOrCreateCart đã có ở phần trước) ...

    // Cập nhật số lượng (Dùng cho nút +/- trong giỏ hàng)
    public function update(Request $request)
    {
        // Validate dữ liệu đầu vào
        $request->validate([
            'id' => 'required|exists:cart_items,id',
            'quantity' => 'required|integer|min:1'
        ]);

        try {
            $cartItem = CartItem::findOrFail($request->id);
            $variant = $cartItem->variant;

            // Kiểm tra tồn kho
            if ($variant->stock_quantity < $request->quantity) {
                return back()->with('error', 'Kho chỉ còn ' . $variant->stock_quantity . ' sản phẩm!');
            }

            $cartItem->quantity = $request->quantity;
            $cartItem->save();

            return back()->with('success', 'Đã cập nhật số lượng!');
        } catch (\Exception $e) {
            return back()->with('error', 'Lỗi cập nhật: ' . $e->getMessage());
        }
    }

    // Xóa sản phẩm khỏi giỏ
    public function remove($id)
    {
        try {
            // Kiểm tra item có thuộc giỏ hàng của người dùng hiện tại không để bảo mật
            // (Ở đây làm đơn giản, bạn có thể thêm check quyền sở hữu)
            CartItem::destroy($id);
            return back()->with('success', 'Đã xóa sản phẩm khỏi giỏ hàng!');
        } catch (\Exception $e) {
            return back()->with('error', 'Lỗi xóa sản phẩm.');
        }
    }
}