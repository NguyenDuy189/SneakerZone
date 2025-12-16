<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

// Import Models
use App\Models\Product;
use App\Models\Banner;
use App\Models\Category;

class HomeController extends Controller
{
    /**
     * Hiển thị Trang chủ (Flagship Store Interface)
     * * Logic:
     * 1. Lấy Banner Slider & Promo
     * 2. Lấy sản phẩm theo các tiêu chí: Nổi bật, Mới nhất, Bán chạy
     * 3. Tối ưu query bằng Eager Loading (with)
     */
    public function index()
    {
        try {
            // --- 1. QUERY BUILDER CƠ BẢN (Dùng chung) ---
            // Chỉ lấy sản phẩm đang Published và load sẵn quan hệ Category để tránh lỗi N+1
            $baseQuery = Product::with('category')
                                ->where('status', 'published');

            // --- 2. LẤY BANNERS (MARKETING) ---
            // Slider chính (Toàn màn hình)
            $banners = Banner::where('position', 'home_slider')
                ->where('is_active', true)
                ->orderBy('priority', 'desc')
                ->latest()
                ->get();

            // Banner quảng cáo giữa trang (Parallax)
            $promoBanner = Banner::where('position', 'home_mid')
                ->where('is_active', true)
                ->orderBy('priority', 'desc')
                ->first();

            // --- 3. LẤY SẢN PHẨM TRƯNG BÀY (COLLECTIONS) ---
            
            // A. Sản phẩm Nổi bật (Trending Now)
            // Lấy 8 sản phẩm được đánh dấu là featured
            $featuredProducts = (clone $baseQuery)
                ->where('is_featured', true)
                ->latest()
                ->take(8)
                ->get();

            // B. Sản phẩm Mới về (New Arrivals)
            // Lấy 8 sản phẩm mới nhất dựa trên ngày tạo
            $newProducts = (clone $baseQuery)
                ->latest('created_at')
                ->take(8)
                ->get();

            // C. Sản phẩm Bán chạy (Best Sellers) - Optional
            // Logic: Nếu chưa có cột sold_count, lấy ngẫu nhiên để demo
            $bestSellerProducts = (clone $baseQuery)
                // ->orderBy('sold_count', 'desc') 
                ->inRandomOrder()
                ->take(4)
                ->get();

            // D. Danh mục nổi bật (Cho phần Bento Grid)
            // Lấy các danh mục cha (parent_id = null)
            $categories = Category::whereNull('parent_id')
                ->where('is_visible', true)
                ->take(4)
                ->get();

            // --- 4. TRẢ VỀ VIEW ---
            return view('client.home.index', compact(
                'banners',
                'promoBanner',
                'featuredProducts',
                'newProducts',
                'bestSellerProducts',
                'categories'
            ));

        } catch (\Exception $e) {
            // --- 5. XỬ LÝ LỖI CHUYÊN NGHIỆP ---
            
            // Ghi log lỗi cho Developer xem (storage/logs/laravel.log)
            Log::error("Homepage Error: " . $e->getMessage());

            // Thông báo cho người dùng (Sử dụng Flash Session)
            // Thay vì hiện trang lỗi 500 xấu xí, ta reload và báo lỗi nhẹ nhàng
            return redirect()->back()->with('error', 'Hệ thống đang bảo trì giây lát. Vui lòng quay lại sau!');
        }
    }

    /**
     * Trang tĩnh: Giới thiệu, Liên hệ... (Nếu cần)
     */
    public function contact()
    {
        return view('client.pages.contact');
    }
}