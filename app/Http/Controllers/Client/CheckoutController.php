<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cart; 
use Illuminate\Support\Facades\Auth;

class CheckoutController extends Controller
{
    public function index()
    {
        // 1. Lấy giỏ hàng của user hiện tại
        // (Nếu bạn có logic cho khách vãng lai dùng session_id, hãy sửa lại đoạn này giống CartController)
        $userId = Auth::id();
        
        $cart = Cart::with(['items.variant.product', 'items.variant.attributeValues.attribute'])
            ->where('user_id', $userId)
            ->first();

        // 2. Nếu giỏ hàng trống hoặc không tồn tại thì đá về trang sản phẩm
        if (!$cart || $cart->items->count() == 0) {
            return redirect()->route('client.products.index')->with('error', 'Giỏ hàng của bạn đang trống!');
        }

        // 3. Tính toán lại tổng tiền để hiển thị
        $subtotal = $cart->items->sum(function($item) {
            return $item->quantity * ($item->variant->price ?: $item->variant->product->price_min);
        });
        
        $discount = $cart->discount_amount ?? 0;
        $total = $subtotal - $discount;
        
        // Đảm bảo không âm tiền
        if($total < 0) $total = 0;

        // 4. Trả về view checkout
        return view('client.checkouts.index', compact('cart', 'subtotal', 'discount', 'total'));
    }

    public function process(Request $request)
    {
        // Xử lý đặt hàng (Lưu vào DB Order) - Sẽ làm sau
        dd($request->all());
    }
}