<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Discount;
use App\Models\ProductVariant;

class CartController extends Controller
{

    /**
     * The `getCart` function retrieves the shopping cart either based on the user ID if logged in or
     * the session ID if not logged in in a PHP application.
     * 
     * @return The `getCart` function returns a Cart model instance with its related items, variant,
     * and product models loaded. If the user is authenticated (logged in), it retrieves the cart based
     * on the user_id. If the user is not authenticated (not logged in), it retrieves the cart based on
     * the session_id.
     */
    private function getCart()
    {
        if (Auth::check()) {
            // Nếu đã đăng nhập -> Lấy theo user_id
            return \App\Models\Cart::with('items.variant.product')
                ->where('user_id', Auth::id())
                ->first();
        } else {
            // Nếu chưa đăng nhập -> Lấy theo session_id
            return \App\Models\Cart::with('items.variant.product')
                ->where('session_id', session()->getId())
                ->first();
        }
    }

    // 1. Lấy hoặc tạo giỏ hàng (Private Helper)
    private function getOrCreateCart()
    {
        if (Auth::check()) {
            return Cart::firstOrCreate(['user_id' => Auth::id()]);
        } else {
            $sessionId = Session::getId();
            return Cart::firstOrCreate(['session_id' => $sessionId]);
        }
    }

    // 2. Hiển thị giỏ hàng
    public function index()
    {
        $cart = $this->getOrCreateCart();

        // Eager load các quan hệ để tối ưu query
        $cartItems = CartItem::with(['variant.product', 'variant.attributeValues.attribute'])
            ->where('cart_id', $cart->id)
            ->get();

        // Tính tổng tiền server-side
        $totalPrice = $cartItems->sum(function ($item) {
            return $item->quantity * ($item->variant->price ?: $item->variant->product->price_min);
            // Lưu ý: Logic giá này tuỳ thuộc vào DB của bạn
        });

        return view('client.carts.index', compact('cart', 'cartItems', 'totalPrice'));
    }

    // 3. Thêm vào giỏ (Logic giữ nguyên, chỉ format lại code)
    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'variant_id' => 'required|exists:product_variants,id',
            'quantity'   => 'required|integer|min:1'
        ]);

        try {
            $variant = ProductVariant::findOrFail($request->variant_id);

            if ($variant->stock_quantity < $request->quantity) {
                // Nếu request là AJAX (ví dụ từ nút Quick Add) thì trả về JSON
                if ($request->wantsJson()) {
                    return response()->json(['status' => 'error', 'message' => 'Hết hàng!'], 400);
                }
                return back()->with('error', 'Sản phẩm không đủ số lượng tồn kho!');
            }

            $cart = $this->getOrCreateCart();

            $cartItem = CartItem::where('cart_id', $cart->id)
                ->where('product_variant_id', $variant->id)
                ->first();

            if ($cartItem) {
                $newQty = $cartItem->quantity + $request->quantity;
                if ($variant->stock_quantity < $newQty) {
                    if ($request->wantsJson()) return response()->json(['status' => 'error', 'message' => 'Kho không đủ hàng!'], 400);
                    return back()->with('error', 'Kho chỉ còn ' . $variant->stock_quantity . ' sản phẩm!');
                }
                $cartItem->quantity = $newQty;
                $cartItem->save();
            } else {
                CartItem::create([
                    'cart_id' => $cart->id,
                    'product_variant_id' => $variant->id,
                    'quantity' => $request->quantity
                ]);
            }

            if ($request->wantsJson()) {
                // Đếm tổng số lượng item để cập nhật badge trên header
                $totalItems = CartItem::where('cart_id', $cart->id)->sum('quantity');
                return response()->json([
                    'status' => 'success',
                    'message' => 'Đã thêm vào giỏ!',
                    'total_items' => $totalItems
                ]);
            }

            return back()->with('success', 'Đã thêm vào giỏ hàng thành công!');
        } catch (\Exception $e) {
            if ($request->wantsJson()) return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    // 4. Cập nhật số lượng (Dùng cho AJAX ở trang Cart)
    // app/Http/Controllers/Client/CartController.php
    // 4. Cập nhật số lượng (Đã sửa lỗi $newSubtotal)
    public function update(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:cart_items,id',
            'quantity' => 'required|integer|min:1'
        ]);

        try {
            $cart = $this->getOrCreateCart();
            
            // SECURITY CHECK: Chỉ cho phép update item thuộc giỏ hàng hiện tại
            $cartItem = $cart->items()->where('id', $request->id)->first();

            if (!$cartItem) {
                return response()->json(['status' => 'error', 'message' => 'Không tìm thấy sản phẩm!'], 404);
            }

            $variant = $cartItem->variant;

            // Kiểm tra tồn kho
            if ($variant->stock_quantity < $request->quantity) {
                return response()->json([
                    'status' => 'error', 
                    'message' => 'Kho chỉ còn ' . $variant->stock_quantity . ' sp!',
                    'current_stock' => $variant->stock_quantity
                ], 400);
            }

            // Lưu số lượng mới
            $cartItem->quantity = $request->quantity;
            $cartItem->save();

            // --- TÍNH TOÁN CÁC BIẾN CẦN THIẾT (SỬA LỖI TẠI ĐÂY) ---

            // 1. Định nghĩa $newSubtotal (Thành tiền của dòng sản phẩm đó)
            $unitPrice = $variant->price ?: $variant->product->price_min;
            $newSubtotal = $cartItem->quantity * $unitPrice;

            // 2. Tính lại tổng tiền hàng (Subtotal) của cả giỏ
            // (Phải tính lại vì số lượng thay đổi -> tổng tiền thay đổi)
            $subtotalAll = $cart->items->sum(function($item) {
                 return $item->quantity * ($item->variant->price ?: $item->variant->product->price_min);
            });

            // 3. Tính lại tổng thanh toán cuối cùng (Sau khi trừ Voucher)
            $discountAmount = $cart->discount_amount ?? 0;
            
            // (Đảm bảo không giảm quá số tiền hàng)
            if ($discountAmount > $subtotalAll) {
                $discountAmount = $subtotalAll; 
                // Cập nhật lại DB nếu cần, ở đây ta chỉ update biến để trả về
            }

            $finalTotal = $subtotalAll - $discountAmount;

            return response()->json([
                'status' => 'success',
                'item_subtotal' => $newSubtotal,  // <-- Biến này giờ đã có giá trị
                'cart_subtotal' => $subtotalAll,  // Tổng tiền hàng chưa giảm
                'cart_total' => $finalTotal,      // Tổng thanh toán (đã trừ voucher)
                'discount_amount' => $discountAmount
            ]);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // 5. Xóa sản phẩm
    public function remove($id)
    {
        try {
            $cart = $this->getOrCreateCart();

            // SECURITY CHECK
            $cartItem = $cart->items()->where('id', $id)->first();

            if ($cartItem) {
                $cartItem->delete();
            }

            // Tính lại tổng Bill sau khi xóa
            $totalBill = $cart->items()->with('variant.product')->get()->sum(function ($item) {
                return $item->quantity * ($item->variant->price ?: $item->variant->product->price_min);
            });

            // Đếm lại số item
            $itemCount = $cart->items()->count();

            // Nếu request AJAX (Xóa mượt không load lại trang)
            if (request()->wantsJson()) {
                return response()->json([
                    'status' => 'success',
                    'cart_total' => $totalBill,
                    'item_count' => $itemCount
                ]);
            }

            return back()->with('success', 'Đã xóa sản phẩm khỏi giỏ hàng!');
        } catch (\Exception $e) {
            if (request()->wantsJson()) return response()->json(['status' => 'error', 'message' => 'Lỗi xóa sản phẩm'], 500);
            return back()->with('error', 'Lỗi xóa sản phẩm.');
        }
    }

    public function applyDiscount(Request $request)
    {
        $request->validate([
            'code' => 'required|string'
        ]);

        // 1. Lấy giỏ hàng hiện tại (Code cũ của bạn)
        $cart = $this->getCart();

        if (!$cart || $cart->items->count() == 0) {
            return response()->json(['status' => 'error', 'message' => 'Giỏ hàng trống!']);
        }

        $code = strtoupper($request->code);

        // 2. Tìm mã trong DB
        $discount = Discount::where('code', $code)->first();

        if (!$discount) {
            return response()->json(['status' => 'error', 'message' => 'Mã giảm giá không tồn tại!']);
        }

        // 3. Sử dụng hàm isValid() bạn đã viết trong Model
        if (!$discount->isValid()) {
            // Tùy chỉnh thông báo chi tiết hơn nếu muốn
            if ($discount->isExpired()) {
                return response()->json(['status' => 'error', 'message' => 'Mã đã hết hạn!']);
            }
            return response()->json(['status' => 'error', 'message' => 'Mã không khả dụng hoặc chưa bắt đầu!']);
        }

        // 4. Tính tổng tiền tạm tính (Subtotal)
        $subtotal = 0;
        foreach ($cart->items as $item) {
            $price = $item->variant->price ?: $item->variant->product->price_min;
            $subtotal += $price * $item->quantity;
        }

        // 5. Kiểm tra điều kiện giá trị đơn tối thiểu (min_order_amount)
        if ($subtotal < $discount->min_order_amount) {
            return response()->json([
                'status' => 'error',
                'message' => 'Đơn hàng cần tối thiểu ' . number_format($discount->min_order_amount) . 'đ để dùng mã này!'
            ]);
        }

        // 6. Tính toán số tiền giảm (Logic Percentage vs Fixed)
        $discountAmount = 0;

        if ($discount->type === 'percentage') {
            $discountAmount = ($subtotal * $discount->value) / 100;

            // Nếu bạn có cột max_discount_value trong DB (như trong $fillable của Model)
            // thì mở comment dòng dưới ra:
            // if ($discount->max_discount_value && $discountAmount > $discount->max_discount_value) {
            //    $discountAmount = $discount->max_discount_value;
            // }
        } else {
            // Loại 'fixed' (trừ tiền trực tiếp)
            $discountAmount = $discount->value;
        }

        // Không cho phép giảm giá lớn hơn tổng đơn hàng
        if ($discountAmount > $subtotal) {
            $discountAmount = $subtotal;
        }

        // 7. Lưu vào DB Cart
        $cart->discount_code = $code;
        $cart->discount_amount = $discountAmount;
        $cart->save();

        // 8. Trả về kết quả cho AlpineJS
        return response()->json([
            'status' => 'success',
            'message' => 'Áp dụng mã thành công!',
            'discount' => $discountAmount,
            'total' => $subtotal - $discountAmount,
            'discount_code' => $code
        ]);
    }

    public function removeDiscount()
    {
        $cart = $this->getCart();

        if ($cart) {
            $cart->discount_code = null;
            $cart->discount_amount = 0;
            $cart->save();
        }

        // Tính lại subtotal để trả về
        $subtotal = 0;
        foreach ($cart->items as $item) {
            $price = $item->variant->price ?: $item->variant->product->price_min;
            $subtotal += $price * $item->quantity;
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Đã gỡ mã giảm giá.',
            'discount' => 0,
            'total' => $subtotal,
            'discount_code' => null
        ]);
    }
}