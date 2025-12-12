<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Trang Cửa hàng / Danh sách sản phẩm (Shop Page)
     * Hỗ trợ: Tìm kiếm, Lọc theo Danh mục, Giá, Sắp xếp
     */
    public function index(Request $request)
    {
        // 1. Khởi tạo Query (Chỉ lấy sản phẩm Published)
        $query = Product::where('status', 'published');

        // 2. Xử lý Tìm kiếm (Keyword)
        if ($request->filled('keyword')) {
            $keyword = trim($request->keyword);
            $query->where(function($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                  ->orWhere('sku', 'like', "%{$keyword}%");
            });
        }

        // 3. Lọc theo Danh mục (Category)
        if ($request->filled('category')) {
            $slug = $request->category;
            $query->whereHas('category', function($q) use ($slug) {
                $q->where('slug', $slug);
            });
        }

        // 4. Lọc theo Khoảng giá (Price Range)
        if ($request->filled('min_price')) {
            $query->where('price_min', '>=', (int)$request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('price_min', '<=', (int)$request->max_price);
        }

        // 5. Sắp xếp (Sorting)
        // validate chặt chẽ giá trị sort để tránh lỗi SQL
        $sort = $request->get('sort', 'latest');
        switch ($sort) {
            case 'price_asc':
                $query->orderBy('price_min', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price_min', 'desc');
                break;
            case 'name_asc':
                $query->orderBy('name', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('name', 'desc');
                break;
            default: // latest
                $query->latest();
                break;
        }

        // 6. Phân trang (Pagination)
        // Giữ lại các tham số filter trên URL khi chuyển trang (appends)
        $products = $query->with('category')->paginate(12)->withQueryString();

        // Lấy danh mục để hiển thị sidebar lọc (nếu cần)
        $categories = Category::where('status', 'active')->get();

        return view('client.product.index', compact('products', 'categories'));
    }

    /**
     * Chi tiết sản phẩm (Product Detail)
     */
    public function show($slug)
    {
        // 1. Tìm sản phẩm (Eager Loading quan hệ để tối ưu)
        $product = Product::where('slug', $slug)
            ->where('status', 'published')
            // Load các quan hệ: Ảnh gallery, Danh mục, Biến thể (Size/Màu), Đánh giá
            ->with(['gallery_images', 'category', 'variants', 'reviews.user'])
            ->firstOrFail(); // Trả về 404 đẹp mắt nếu không tìm thấy

        // 2. Logic Sản phẩm liên quan (Related Products)
        // Tìm sản phẩm cùng danh mục, trừ sản phẩm đang xem
        $relatedProducts = Product::where('status', 'published')
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->inRandomOrder()
            ->limit(4)
            ->get();

        // 3. Logic "Đã xem gần đây" (Optional - Dùng Session)
        // Có thể bổ sung logic lưu ID sản phẩm vào session để hiển thị "Recently Viewed"

        return view('client.product.detail', compact('product', 'relatedProducts'));
    }
}