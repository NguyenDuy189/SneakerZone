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
            // =============================================================
            // 1. LOAD SẢN PHẨM & QUAN HỆ (Đã sửa lỗi Review & Load Size/Màu)
            // =============================================================
            $product = Product::with([
                'category', 
                'brand', 
                'gallery_images',
                // Load sâu để lấy được tên thuộc tính (Ví dụ: Variant A -> AttributeValue (Đỏ) -> Attribute (Màu sắc))
                'variants.attributeValues.attribute', 
                // --- FIX LỖI Ở ĐÂY: Bỏ điều kiện where('status', 'approved') ---
                'reviews' => fn($q) => $q->latest()->take(5),
                'reviews.user'
            ])
            ->where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

            // Tăng lượt xem (Nếu bảng products chưa có cột 'views' thì hãy comment dòng này lại)
            // $product->increment('views'); 

            // =============================================================
            // 2. XỬ LÝ GOM NHÓM THUỘC TÍNH (Để hiển thị nút bấm Size/Màu)
            // =============================================================
            $groupedAttributes = collect();

            if ($product->variants) {
                foreach ($product->variants as $variant) {
                    // Chỉ lấy thuộc tính của các biến thể còn hàng
                    if ($variant->stock_quantity > 0) { 
                        foreach ($variant->attributeValues as $attrValue) {
                            $attrName = $attrValue->attribute->name; // VD: Size, Màu sắc
                            
                            // Nếu chưa có nhóm này thì tạo mới
                            if (!$groupedAttributes->has($attrName)) {
                                $groupedAttributes->put($attrName, collect());
                            }
                            
                            // Thêm giá trị vào nhóm (tránh trùng lặp)
                            if (!$groupedAttributes[$attrName]->contains('id', $attrValue->id)) {
                                $groupedAttributes[$attrName]->push($attrValue);
                            }
                        }
                    }
                }
            }

            // =============================================================
            // 3. TẠO MAP DỮ LIỆU CHO JAVASCRIPT (AlpineJS)
            // =============================================================
            // Giúp JS biết: Khi chọn (Màu Đỏ + Size 40) thì là Variant ID nào?
            $variantMap = $product->variants->mapWithKeys(function ($variant) use ($product) {
                return [
                    $variant->id => [
                        'id' => $variant->id,
                        // Lấy mảng ID các thuộc tính: VD [10, 25] (Màu Đỏ ID 10, Size 40 ID 25)
                        'attributes' => $variant->attributeValues->pluck('id')->sort()->values()->all(),
                        'stock' => $variant->stock_quantity,
                        'price' => $variant->price ?? $product->price_min, 
                        'sku' => $variant->sku
                    ]
                ];
            });

            // =============================================================
            // 4. LẤY SẢN PHẨM LIÊN QUAN
            // =============================================================
            $relatedProducts = Product::where('category_id', $product->category_id)
                ->where('id', '!=', $product->id)
                ->where('status', 'published')
                ->inRandomOrder()
                ->take(4)
                ->get();

            // =============================================================
            // 5. TRẢ VỀ VIEW
            // =============================================================
            return view('client.products.show', compact(
                'product', 
                'relatedProducts', 
                'groupedAttributes', // Biến này để vẽ nút chọn Size/Màu
                'variantMap'         // Biến này để JS xử lý logic chọn
            ));

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('client.products.index')->with('error', 'Sản phẩm không tồn tại.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Product Detail Error: " . $e->getMessage());
            
            // --- BẬT DÒNG NÀY ĐỂ XEM LỖI NẾU CẦN ---
            /* The line `// dd(->getMessage(), ->getTraceAsString());` is a debugging statement in
            PHP. */
            dd($e->getMessage(), $e->getTraceAsString());
            
            return redirect()->route('client.products.index')->with('error', 'Có lỗi xảy ra khi tải trang chi tiết.');
        }
    }
}