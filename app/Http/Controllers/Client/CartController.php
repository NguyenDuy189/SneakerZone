<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ProductVariant;

class CartController extends Controller
{
    public function index()
    {
        $cart = Cart::firstOrCreate(['user_id' => Auth::id()]);

        $cartItems = $cart->items()->with('variant.product')->get();

        return view('client.cart.index', compact('cart', 'cartItems'));
    }

    public function add(Request $request)
{
    $request->validate([
        'variant_id' => 'required|exists:product_variants,id',
        'quantity' => 'nullable|integer|min:1',
    ]);

    $cart = Cart::firstOrCreate(['user_id' => Auth::id()]);

    // Lấy variant + product từ DB
    $variant = ProductVariant::with('product')
        ->findOrFail($request->variant_id);

    $item = CartItem::where('cart_id', $cart->id)
        ->where('variant_id', $variant->id)
        ->first();

    if ($item) {
        $item->increment('quantity', $request->quantity ?? 1);
    } else {
        CartItem::create([
            'cart_id'   => $cart->id,
            'variant_id'=> $variant->id,
            'price'     => $variant->price, // ✅ GIÁ LẤY TỪ DB
            'quantity'  => $request->quantity ?? 1,
        ]);
    }

    return redirect()->route('client.cart.index')
        ->with('success', 'Đã thêm vào giỏ hàng');
}


    public function update(Request $request)
    {
        CartItem::where('id', $request->id)
            ->update(['quantity' => $request->quantity]);

        return back();
    }

    public function remove($id)
    {
        CartItem::findOrFail($id)->delete();
        return back();
    }
}
