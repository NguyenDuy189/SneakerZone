<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Builder;

// Import Models
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Wishlist; // <--- Cần thêm model này
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    /**
     * DANH SÁCH SẢN PHẨM (FILTER, SORT, SEARCH)
     * Xử lý tìm kiếm, lọc đa tầng (Danh mục, Thương hiệu, Giá) và sắp xếp.
     */
    public function index(Request $request)
    {
        try {
            // 1. Khởi tạo Query Builder
            // Eager loading 'category' và 'brand' để tối ưu hiệu năng (tránh N+1 query)
            $query = Product::with(['category', 'brand'])
                            ->where('status', 'published');

            // 2. TÌM KIẾM (Keyword)
            if ($request->filled('keyword')) {
                $keyword = trim($request->keyword);
                $query->where(function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%")
                      ->orWhere('sku_code', 'like', "%{$keyword}%")
                      ->orWhere('short_description', 'like', "%{$keyword}%");
                });
            }

            // 3. BỘ LỌC (Filters)
            
            // Lọc theo Danh mục (Slug)
            if ($request->filled('category')) {
                $query->whereHas('category', function ($q) use ($request) {
                    $q->where('slug', $request->category);
                });
            }

            // Lọc theo Thương hiệu (Checkbox/Slug)
            if ($request->filled('brand')) {
                $brands = (array) $request->brand; // Ép kiểu mảng để an toàn
                $query->whereHas('brand', function ($q) use ($brands) {
                    $q->whereIn('slug', $brands);
                });
            }

            // Lọc theo Giá (Xử lý chuỗi nhập vào ví dụ: 1.000.000 -> 1000000)
            if ($request->filled('min_price')) {
                $min = str_replace([',', '.'], '', $request->min_price);
                $query->where('price_min', '>=', (int)$min);
            }
            if ($request->filled('max_price')) {
                $max = str_replace([',', '.'], '', $request->max_price);
                $query->where('price_min', '<=', (int)$max);
            }

            // 4. XỬ LÝ SẮP XẾP (Sorting)
            $sort = $request->input('sort', 'newest');
            
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
                case 'oldest':     
                    $query->orderBy('created_at', 'asc'); 
                    break;
                default:           
                    // Mặc định: Sản phẩm nổi bật lên đầu, sau đó đến mới nhất
                    $query->orderBy('is_featured', 'desc')->latest(); 
                    break;
            }

            // 5. PHÂN TRANG (Pagination)
            // withQueryString() giữ lại các tham số lọc trên URL khi chuyển trang
            $products = $query->paginate(12)->withQueryString();

            // 6. LẤY DỮ LIỆU SIDEBAR
            // Chỉ hiển thị các danh mục và thương hiệu có sản phẩm đang hoạt động
            $categories = Category::where('is_visible', true)
                ->whereHas('products', fn($q) => $q->where('status', 'published'))
                ->withCount(['products' => fn($q) => $q->where('status', 'published')])
                ->get();

            $brands = Brand::where('is_visible', true)
                ->whereHas('products', fn($q) => $q->where('status', 'published'))
                ->get();

            return view('client.products.index', compact('products', 'categories', 'brands'));

        } catch (\Exception $e) {
            Log::error("Product Index Error: " . $e->getMessage());
            return redirect()->route('client.home')->with('error', '...');
            
            // --- THÊM DÒNG NÀY ĐỂ DEBUG ---
            // dd($e->getMessage(), $e->getTraceAsString()); 
        }
    }

    /**
     * CHI TIẾT SẢN PHẨM (DETAIL PAGE)
     * Hiển thị thông tin chi tiết, biến thể, ảnh và đánh giá.
     */
    public function show($slug)
    {
        try {
            // 1. Load sản phẩm + Tính toán số sao (Avg) + Đếm số đánh giá (Count)
            $product = Product::with([
                'category', 
                'brand', 
                'gallery_images',
                'variants.attributeValues.attribute' 
            ])
            // Tính trung bình sao và số lượng đánh giá đã duyệt
            ->withAvg(['reviews' => function($query) {
                $query->where('is_approved', true);
            }], 'rating')
            ->withCount(['reviews' => function($query) {
                $query->where('is_approved', true);
            }])
            ->where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

            // [MỚI] Tăng lượt xem sản phẩm (Nếu DB bạn có cột 'views')
            $product->increment('views');

            // 2. Lấy danh sách Review để hiển thị (Phân trang 5 review/trang)
            $reviews = $product->reviews()
                ->with('user')
                ->where('is_approved', true)
                ->latest()
                ->paginate(5);

            // 3. Kiểm tra User hiện tại
            $userReview = null;
            $isLiked = false; // [MỚI] Mặc định chưa like

            if (Auth::check()) {
                $userId = Auth::id();

                // 3a. Lấy review cũ của user (để hiện form edit hoặc ẩn form add)
                $userReview = $product->reviews()
                    ->where('user_id', $userId)
                    ->first();
                
                // 3b. [QUAN TRỌNG] Kiểm tra xem user đã like sản phẩm này chưa
                $isLiked = Wishlist::where('user_id', $userId)
                    ->where('product_id', $product->id)
                    ->exists();
            }

            // 4. Xử lý thuộc tính (Logic nhóm thuộc tính)
            $groupedAttributes = collect();
            if ($product->variants) {
                foreach ($product->variants as $variant) {
                    // Chỉ hiển thị thuộc tính của các biến thể còn hàng hoặc có giá
                    if ($variant->stock_quantity > 0 || $variant->price > 0) { 
                        foreach ($variant->attributeValues as $attrValue) {
                            $attrName = $attrValue->attribute->name;
                            if (!$groupedAttributes->has($attrName)) {
                                $groupedAttributes->put($attrName, collect());
                            }
                            // Tránh trùng lặp giá trị (ví dụ nhiều biến thể cùng Size M)
                            if (!$groupedAttributes[$attrName]->contains('id', $attrValue->id)) {
                                $groupedAttributes[$attrName]->push($attrValue);
                            }
                        }
                    }
                }
            }

            // 5. Map biến thể cho JS (Để script chọn size/màu hoạt động)
            $variantMap = $product->variants->mapWithKeys(function ($variant) use ($product) {
                return [
                    $variant->id => [
                        'id' => $variant->id,
                        'attributes' => $variant->attributeValues->pluck('id')->map(fn($id) => (int)$id)->sort()->values()->all(),
                        'stock' => $variant->stock_quantity,
                        // Nếu biến thể không có giá riêng, lấy giá sản phẩm gốc
                        'price' => $variant->price ?? $product->price_regular, 
                        'sku' => $variant->sku,
                        // Thêm ảnh biến thể để JS đổi ảnh khi chọn
                        'image' => $variant->image ? asset('storage/'.$variant->image) : null 
                    ]
                ];
            });

            // 6. Sản phẩm liên quan
            $relatedProducts = Product::where('category_id', $product->category_id)
                ->where('id', '!=', $product->id)
                ->where('status', 'published')
                ->inRandomOrder()
                ->take(4)
                ->get();

            return view('client.products.show', compact(
                'product', 
                'reviews', 
                'userReview', 
                'groupedAttributes', 
                'variantMap', 
                'relatedProducts',
                'isLiked' // <--- Nhớ truyền biến này sang View
            ));

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Xử lý riêng lỗi 404 để hiển thị trang 404 chuẩn của Laravel
            abort(404); 
        } catch (\Exception $e) {
            Log::error("Show Product Error: " . $e->getMessage());
            return redirect()->route('client.products.index')->with('error', 'Sản phẩm không khả dụng.');
        }
    }
}