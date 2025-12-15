<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;

class ProductController extends Controller
{
    /**
     * Hiển thị danh sách sản phẩm
     */
    public function index()
    {
        // 1. Danh sách tất cả sản phẩm hiển thị
        $products = Product::whereIn('status', [1, 'published'])
            ->latest()
            ->get();

        // 2. Sản phẩm nổi bật
        $featuredProducts = Product::whereIn('status', [1, 'published'])
            ->where('is_featured', 1)
            ->take(8)
            ->get();

        // 3. Sản phẩm mới
        $newProducts = Product::whereIn('status', [1, 'published'])
            ->orderBy('created_at', 'desc')
            ->take(8)
            ->get();

        // 4. Sản phẩm chạy bộ
        $runningProducts = Product::whereIn('status', [1, 'published'])
            ->whereHas('categories', function ($q) {
                $q->where('slug', 'like', '%chay-bo%');
            })
            ->take(8)
            ->get();

        // 5. Sản phẩm bán chạy
        $bestSellerProducts = Product::whereIn('status', [1, 'published'])
            ->inRandomOrder()
            ->take(8)
            ->get();

        // ⭐⭐ VIEW CHUẨN (đúng thư mục client/product/)
        return view('client.product.index', compact(
            'products',
            'featuredProducts',
            'newProducts',
            'runningProducts',
            'bestSellerProducts'
        ));
    }

    /**
     * Hiển thị chi tiết sản phẩm theo slug
     */
    public function show($slug)
    {
        // Lấy đúng sản phẩm
        $product = Product::where('slug', $slug)
            ->whereIn('status', [1, 'published'])
            ->firstOrFail();

        // Sản phẩm liên quan
        $relatedProducts = Product::where('id', '!=', $product->id)
            ->inRandomOrder()
            ->limit(4)
            ->get();

        // ⭐⭐ VIEW CHUẨN (đúng thư mục client/product/)
        return view('client.product.detail', compact('product', 'relatedProducts'));
    }
    //thanh tìm kiếm
    public function search(Request $request)
{
    $keyword = $request->get('q');

    $products = Product::whereIn('status', [1, 'published'])
        ->where('name', 'like', '%' . $keyword . '%')
        ->latest()
        ->get();

    return view('client.product.search', compact('products', 'keyword'));
}

//giỏ hàng
public function addToCart($id)
{
    $product = Product::findOrFail($id);

    $cart = session()->get('cart', []);

    if (isset($cart[$id])) {
        $cart[$id]['quantity']++;
    } else {
        $cart[$id] = [
            'id' => $product->id,
            'name' => $product->name,
            'price' => $product->price_min,
            'image' => $product->image,
            'quantity' => 1
        ];
    }

    session()->put('cart', $cart);

    return back()->with('success', 'Đã thêm vào giỏ hàng');
}

public function cart()
{
    $cart = session()->get('cart', []);
    return view('client.cart.index', compact('cart'));
}

//update cart
public function updateCart(Request $request, $id)
{
    $request->validate([
        'quantity' => 'required|integer|min:1',
    ]);

    $cart = session()->get('cart', []);

    if (!isset($cart[$id])) {
        return back()->with('error', 'Sản phẩm không tồn tại trong giỏ');
    }

    $cart[$id]['quantity'] = (int) $request->quantity;

    // 👇 GHI ĐÈ SESSION
    session()->put('cart', $cart);

    return back()->with('success', 'Cập nhật giỏ hàng thành công');
}

public function removeFromCart($id)
{
    $cart = session()->get('cart', []);

    if (isset($cart[$id])) {
        unset($cart[$id]);
        session()->put('cart', $cart);
    }

    return back();
}


}
