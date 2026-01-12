<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ProductVariant;
use App\Models\Discount; // Hoặc Coupon tùy tên model của bạn
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class CartController extends Controller
{
    /**
     * LẤY GIỎ HÀNG HIỆN TẠI (Helper function)
     * Tự động xử lý logic: Đã đăng nhập (User ID) hoặc Khách (Session ID)
     */
    private function getCart()
    {
        if (Auth::check()) {
            // Nếu đã đăng nhập -> Lấy theo User ID
            return Cart::firstOrCreate(['user_id' => Auth::id()]);
        } else {
            // Nếu là khách -> Lấy theo Session ID
            $sessionId = Session::getId();
            return Cart::firstOrCreate(['session_id' => $sessionId]);
        }
    }

    /**
     * TRANG GIỎ HÀNG
     */
    public function index()
    {
        $cart = $this->getCart();
        
        // CẬP NHẬT LẠI DÒNG NÀY
        // Load variants kèm theo các thuộc tính động (Màu, Size, Vật liệu...)
        // thay vì fix cứng color/size
        $cart->load([
            'items.variant.product', 
            'items.variant.attributeValues.attribute'
        ]);

        // Tính toán lại lần cuối
        $totals = $this->calculateCartTotals($cart);

        return view('client.carts.index', compact('cart', 'totals'));
    }

    /**
     * THÊM VÀO GIỎ HÀNG (AJAX)
     * Có kiểm tra tồn kho (Stock Check)
     */
    public function addToCart(Request $request)
    {
        // 1. Validate dữ liệu đầu vào
        $request->validate([
            'product_variant_id' => 'required|integer|exists:product_variants,id',
            'quantity' => 'required|integer|min:1'
        ]);

        DB::beginTransaction();
        try {
            // 2. Lấy giỏ hàng hiện tại (Hàm này của bạn đã có sẵn)
            $cart = $this->getCart();

            // 3. Tìm biến thể sản phẩm & KHÓA DÒNG (Lock for update)
            // Việc này ngăn chặn 2 người mua cùng lúc khi kho chỉ còn 1 cái
            $variant = ProductVariant::with('product') // Load thêm quan hệ product để lấy tên
                ->lockForUpdate() 
                ->find($request->product_variant_id);

            if (!$variant) {
                throw new \Exception('Sản phẩm không tồn tại hoặc đã bị xóa.');
            }

            $productName = $variant->product->name ?? 'Sản phẩm';
            $variantName = $variant->name ?? ''; // Ví dụ: Size L, Màu Đỏ
            $fullName = trim("$productName $variantName");

            // 4. Kiểm tra tồn kho thực tế
            $currentStock = $variant->stock_quantity ?? 0;
            
            // Lấy số lượng hiện có trong giỏ của khách này
            $existingItem = CartItem::where('cart_id', $cart->id)
                ->where('product_variant_id', $variant->id)
                ->first();

            $currentQtyInCart = $existingItem ? $existingItem->quantity : 0;
            $requestedQty = $request->quantity;
            $totalQty = $currentQtyInCart + $requestedQty;

            // Logic check tồn kho: Tổng mua + Trong giỏ > Tồn kho
            if ($totalQty > $currentStock) {
                $availableToAdd = $currentStock - $currentQtyInCart;
                $msg = $availableToAdd > 0 
                    ? "Bạn chỉ có thể thêm tối đa $availableToAdd sản phẩm nữa."
                    : "Sản phẩm này đã hết hàng hoặc bạn đã thêm hết số lượng vào giỏ.";
                
                throw new \Exception($msg);
            }

            // 5. Thêm hoặc Cập nhật giỏ hàng
            if ($existingItem) {
                $existingItem->quantity = $totalQty;
                $existingItem->save();
            } else {
                CartItem::create([
                    'cart_id' => $cart->id,
                    'product_variant_id' => $variant->id,
                    'quantity' => $requestedQty,
                    'is_selected' => 1 // Mặc định chọn để mua
                ]);
            }

            // 6. Tính toán lại tổng tiền (nếu cần cập nhật cache hoặc logic khác)
            // $this->calculateCartTotals($cart); 

            DB::commit();

            // 7. Chuẩn bị thông điệp và Link
            $message = "Đã thêm '$fullName' vào giỏ hàng!";
            $actionUrl = route('client.carts.index'); // Route xem giỏ hàng
            $actionText = "Xem giỏ hàng";

            // 8. TRẢ VỀ KẾT QUẢ (Dual Response Strategy)
            
            // Trường hợp A: Gọi bằng AJAX / Fetch / Axios (JS)
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'status' => 'success',
                    'message' => $message,
                    'cart_count' => $cart->items()->sum('quantity'), // Cập nhật số trên icon giỏ hàng
                    'toast' => [ // Dữ liệu để frontend hiện Toast
                        'type' => 'success',
                        'message' => $message,
                        'actionUrl' => $actionUrl,
                        'actionText' => $actionText
                    ]
                ]);
            }

            // Trường hợp B: Form Submit thông thường (Load lại trang)
            return redirect()->back()
                ->with('success', $message)
                ->with('action_url', $actionUrl)
                ->with('action_text', $actionText);

        } catch (\Exception $e) {
            DB::rollBack();

            // Xử lý lỗi cho 2 trường hợp
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $e->getMessage()
                ], 422);
            }

            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * CẬP NHẬT SỐ LƯỢNG (+/-)
     */
    public function updateQuantity(Request $request)
    {
        $itemId = $request->item_id;
        $qty = $request->quantity;

        $cart = $this->getCart();
        $item = CartItem::where('cart_id', $cart->id)->where('id', $itemId)->first();

        if (!$item) return response()->json(['status' => 'error', 'message' => 'Sản phẩm không tồn tại']);

        // --- SỬA TẠI ĐÂY: Đổi quantity -> stock_quantity ---
        $currentStock = $item->variant->stock_quantity ?? 0;

        // Check tồn kho real-time
        if ($qty > $currentStock) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kho chỉ còn ' . $currentStock . ' sản phẩm.'
            ]);
        }

        if ($qty <= 0) {
            $item->delete(); // Xóa nếu về 0
        } else {
            $item->quantity = $qty;
            $item->save();
        }

        $totals = $this->calculateCartTotals($cart);

        return response()->json([
            'status' => 'success',
            'data' => $totals
        ]);
    }

    /**
     * CHỌN / BỎ CHỌN SẢN PHẨM (Checkbox)
     */
    public function selectItem(Request $request)
    {
        $cart = $this->getCart();
        $isSelected = filter_var($request->selected, FILTER_VALIDATE_BOOLEAN) ? 1 : 0;

        if ($request->is_all) {
            // Chọn tất cả
            $cart->items()->update(['is_selected' => $isSelected]);
        } else {
            // Chọn lẻ
            CartItem::where('cart_id', $cart->id)
                ->where('id', $request->item_id)
                ->update(['is_selected' => $isSelected]);
        }

        // Tính lại tiền ngay lập tức
        $totals = $this->calculateCartTotals($cart);

        return response()->json(['status' => 'success', 'data' => $totals]);
    }

    /**
     * XÓA SẢN PHẨM
     */
    public function removeItem(Request $request)
    {
        $cart = $this->getCart();
        CartItem::where('cart_id', $cart->id)->where('id', $request->item_id)->delete();
        
        // Nếu giỏ hàng trống thì xóa luôn mã giảm giá
        if ($cart->items()->count() == 0) {
            $cart->update(['discount_code' => null, 'discount_amount' => 0]);
        }

        $totals = $this->calculateCartTotals($cart);

        return response()->json(['status' => 'success', 'data' => $totals]);
    }

    /**
     * ÁP DỤNG MÃ GIẢM GIÁ (Logic đã fix chuẩn)
     */
    public function applyDiscount(Request $request)
    {
        try {
            $cart = $this->getCart();
            
            // 1. Nếu giỏ hàng trống
            if (!$cart || $cart->items->isEmpty()) {
                return response()->json(['status' => 'error', 'message' => 'Giỏ hàng trống']);
            }

            $code = strtoupper(trim($request->code));

            // --- TRƯỜNG HỢP A: GỠ BỎ MÃ (Khi client gửi code rỗng) ---
            if (empty($code)) {
                $cart->discount_code = null;
                $cart->save();
                $cart->refresh();
                
                // Tính lại giá khi không có mã
                $totals = $this->calculateCartTotals($cart); 

                return response()->json([
                    'status' => 'success',
                    'message' => 'Đã gỡ mã giảm giá.',
                    'data' => $totals
                ]);
            }

            // --- TRƯỜNG HỢP B: ÁP DỤNG MÃ ---
            
            // 2. Tìm mã trong DB
            $discount = Discount::where('code', $code)
                ->where('start_date', '<=', now())
                ->where('end_date', '>=', now())
                // ->where('is_active', true) // Bật dòng này nếu bảng discounts có cột is_active
                ->first();

            // Validate cơ bản
            if (!$discount) {
                return response()->json(['status' => 'error', 'message' => 'Mã giảm giá không tồn tại hoặc hết hạn']);
            }
            if (isset($discount->quantity) && $discount->quantity <= 0) {
                return response()->json(['status' => 'error', 'message' => 'Mã giảm giá đã hết lượt sử dụng']);
            }

            // Validate số lượt dùng của User (Tuỳ chọn - nếu có logic này)
            // ... (Code check user usage limit) ...

            // 3. Tính toán thử (Dry Run) để xem đủ điều kiện không
            $totals = $this->calculateCartTotals($cart, $discount);

            // Kiểm tra Min Order Amount
            if ($discount->min_order_amount > 0 && $totals['subtotal'] < $discount->min_order_amount) {
                return response()->json([
                    'status' => 'error', 
                    'message' => 'Đơn hàng cần tối thiểu ' . number_format($discount->min_order_amount) . 'đ để dùng mã này.'
                ]);
            }

            // Kiểm tra xem mã có thực sự giảm được đồng nào không
            if ($totals['discount'] <= 0) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Mã này không áp dụng cho các sản phẩm trong giỏ của bạn.'
                ]);
            }

            // 4. Hợp lệ -> Lưu vào DB
            $cart->discount_code = $code;
            $cart->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Áp dụng mã thành công!',
                'data' => $totals
            ]);

        } catch (\Exception $e) {
            Log::error("APPLY_DISCOUNT_ERROR: " . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Có lỗi xảy ra, vui lòng thử lại']);
        }
    }

    /**
     * CORE ENGINE: TÍNH TOÁN TỔNG TIỀN (Private)
     * Đây là trái tim của Controller, mọi hàm khác đều gọi về đây.
     */
    private function calculateCartTotals($cart, $forcedDiscount = null)
    {
        // Load quan hệ để lấy giá và tồn kho
        $cart->load('items.variant.product');

        $subtotal = 0;
        $countSelected = 0;
        $cartItems = $cart->items;

        // 1. Tính Subtotal (Chỉ tính item được tick chọn)
        foreach ($cartItems as $item) {
            if ($item->is_selected) {
                // Ưu tiên giá Sale của Variant -> Giá thường Variant -> Giá Sale Product -> Giá thường Product
                $price = $item->variant->sale_price > 0 
                    ? $item->variant->sale_price 
                    : ($item->variant->price ?: ($item->variant->product->sale_price ?: $item->variant->product->price));
                
                $subtotal += $price * $item->quantity;
                $countSelected++;
            }
        }

        // 2. Tính Discount
        $discountAmount = 0;
        
        // Ưu tiên mã vừa nhập ($forcedDiscount), nếu không có thì lấy mã trong DB
        $discount = $forcedDiscount;
        if (!$discount && $cart->discount_code) {
             $discount = Discount::where('code', $cart->discount_code)
                ->where('start_date', '<=', now())
                ->where('end_date', '>=', now())
                ->first();
        }

        if ($discount && $subtotal > 0) {
            $minOrder = (float) $discount->min_order_amount;
            
            // Check điều kiện Min Order
            if ($minOrder == 0 || $subtotal >= $minOrder) {
                
                $type = trim(strtolower($discount->type)); 
                $value = (float) $discount->value;

                if ($type === 'percent' || $type === 'percentage') {
                    $discountAmount = ($subtotal * $value) / 100;
                    
                    // Check trần giảm giá (Max Discount)
                    if (isset($discount->max_discount_amount) && $discount->max_discount_amount > 0) {
                         $discountAmount = min($discountAmount, $discount->max_discount_amount);
                    }
                } else {
                    $discountAmount = $value; // Giảm tiền mặt
                }
            }
        }

        // 3. Chốt số liệu (Safety Check)
        if ($discountAmount > $subtotal) $discountAmount = $subtotal;
        
        $total = $subtotal - $discountAmount;

        // 4. Lưu vào Database (Để dùng cho bước Checkout/Thanh toán sau này)
        $cart->update([
            'subtotal' => $subtotal,
            'discount_amount' => $discountAmount,
            'total' => $total
        ]);

        // 5. Trả về mảng dữ liệu chuẩn cho Frontend
        return [
            'subtotal' => $subtotal,
            'discount' => $discountAmount,
            'total'    => $total,
            'subtotal_formatted' => number_format($subtotal) . 'đ',
            'discount_formatted' => number_format($discountAmount) . 'đ',
            'total_formatted'    => number_format($total) . 'đ',
            'count_selected'     => $countSelected
        ];
    }
}