<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product; // Import Model Product

class ProductController extends Controller
{
    /**
     * Hiển thị danh sách sản phẩm (Product Listing)
     */
    public function index()
{
    // --- 1. Tất cả sản phẩm (Lấy cả 'published' và 1) ---
    // Dùng whereIn để chấp nhận cả 2 kiểu status
    $products = Product::whereIn('status', [1, 'published'])->latest()->get();

    // --- 2. Sản phẩm nổi bật / sale ---
    $featuredProducts = Product::whereIn('status', [1, 'published'])
                            ->where('is_featured', 1)
                            ->take(8)
                            ->get();

    // --- 3. Sản phẩm mới ---
    $newProducts = Product::whereIn('status', [1, 'published'])
                        ->orderBy('created_at', 'desc')
                        ->take(8)
                        ->get();

    // --- 4. SỬA LỖI QUAN TRỌNG: Sản phẩm chạy bộ ---
    $runningProducts = Product::whereIn('status', [1, 'published']) // Sửa status
                            ->whereHas('categories', function ($q) {
                                // Dùng 'like' để tìm tất cả danh mục có chứa chữ "chay-bo"
                                // (Bao gồm cả 'chay-bo-giay-nam' và 'chay-bo-giay-nu')
                                $q->where('slug', 'like', '%chay-bo%'); 
                            })
                            ->take(8)
                            ->get();

    // --- 5. Sản phẩm bán chạy ---
    $bestSellerProducts = Product::whereIn('status', [1, 'published'])
                            ->inRandomOrder()
                            ->take(8)
                            ->get();

    return view('client.product.index', compact(
        'products', 
        'featuredProducts',
        'newProducts',
        'runningProducts',
        'bestSellerProducts'
    ));
}

    /**
     * Hiển thị chi tiết một sản phẩm (Product Detail)
     */
    public function show($slug)
    {
        // 1. Tìm sản phẩm theo 'slug'
        // 'slug' là trường giúp tạo URL thân thiện. Nếu bạn dùng 'id' thì thay $slug bằng $id
        $product = Product::where('slug', $slug)
                          ->where('status', 1)
                          ->firstOrFail(); // firstOrFail sẽ tự động trả về 404 nếu không tìm thấy

        // 2. Lấy thêm các sản phẩm liên quan (ví dụ: cùng danh mục)
        $relatedProducts = Product::where('id', '!=', $product->id)
                          ->inRandomOrder() // Lấy ngẫu nhiên
                          ->limit(4)
                          ->get();

        // 3. Trả về View cùng với dữ liệu chi tiết
        return view('client.product.detail', compact('product', 'relatedProducts'));
    }
}