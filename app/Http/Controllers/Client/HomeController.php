<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Trang chủ client
     */
    public function index()
    {
        // Lấy 12 sản phẩm mới nhất, phân trang để có firstItem()
        $products = Product::with('category')->latest()->paginate(12);

        // Lấy tất cả category để filter hoặc hiển thị
        $categories = Category::all();

        return view('client.products.index', compact('products', 'categories'));
    }

    /**
     * Danh sách sản phẩm
     */
    public function products(Request $request)
    {
        // Build query với eager load
        $query = Product::with('category');

        // Lọc theo category nếu có
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Lọc theo giá min/max nếu cần
        if ($request->filled('price_min')) {
            $query->where('price_min', '>=', $request->price_min);
        }
        if ($request->filled('price_max')) {
            $query->where('price_min', '<=', $request->price_max);
        }

        // Phân trang 12 sản phẩm/trang
        $products = $query->latest()->paginate(12)->withQueryString();

        // Lấy tất cả category
        $categories = Category::all();

        return view('client.products.index', compact('products', 'categories'));
    }

    /**
     * Chi tiết sản phẩm
     */
    public function show($slug)
    {
        $product = Product::with(['category', 'variants'])->where('slug', $slug)->firstOrFail();

        return view('client.products.show', compact('product'));
    }
}
