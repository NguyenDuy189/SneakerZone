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
            // 1. Load sản phẩm
            $product = Product::with([
                'category', 
                'brand', 
                'gallery_images',
                'variants.attributeValues.attribute' 
            ])
            ->where('slug', $slug)
            ->where('status', 'published') // Lưu ý: Cột status này là của bảng Product (vẫn giữ nguyên)
            ->firstOrFail();

            // 2. Lấy Review (SỬA LẠI ĐOẠN NÀY)
            $reviews = $product->reviews()
                ->with('user')
                ->where('is_approved', true) // <--- SỬA: Dùng cột 'is_approved' thay vì 'status'
                ->latest()
                ->paginate(5);

            // 3. Tính điểm trung bình (CŨNG PHẢI SỬA)
            $avgRating = $product->reviews()
                ->where('is_approved', true) // <--- SỬA: Dùng cột 'is_approved'
                ->avg('rating') ?? 0;

            // 4. Xử lý thuộc tính (Logic cũ giữ nguyên)
            $groupedAttributes = collect();
            if ($product->variants) {
                foreach ($product->variants as $variant) {
                    if ($variant->stock_quantity > 0) { 
                        foreach ($variant->attributeValues as $attrValue) {
                            $attrName = $attrValue->attribute->name;
                            if (!$groupedAttributes->has($attrName)) {
                                $groupedAttributes->put($attrName, collect());
                            }
                            if (!$groupedAttributes[$attrName]->contains('id', $attrValue->id)) {
                                $groupedAttributes[$attrName]->push($attrValue);
                            }
                        }
                    }
                }
            }

            // 5. Map dữ liệu biến thể
            $variantMap = $product->variants->mapWithKeys(function ($variant) use ($product) {
                return [
                    $variant->id => [
                        'id' => $variant->id,
                        'attributes' => $variant->attributeValues->pluck('id')->sort()->values()->all(),
                        'stock' => $variant->stock_quantity,
                        'price' => $variant->price ?? $product->price_regular,
                        'sku' => $variant->sku
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
                'avgRating', 
                'groupedAttributes', 
                'variantMap', 
                'relatedProducts'
            ));

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Error: " . $e->getMessage());
            return redirect()->route('client.products.index')->with('error', 'Có lỗi xảy ra.');
        }
    }
}