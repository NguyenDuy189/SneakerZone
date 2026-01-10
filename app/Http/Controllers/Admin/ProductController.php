<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Attribute;
use App\Models\InventoryLog; // BẮT BUỘC: Model ghi lịch sử kho
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    // =========================================================================
    // 1. QUẢN LÝ SẢN PHẨM CHA (PARENT PRODUCTS)
    // =========================================================================

    public function index(Request $request)
    {
        $query = Product::with(['brand', 'categories', 'variants']);

        // --- BỘ LỌC TÌM KIẾM ---
        if ($request->filled('keyword')) {
            $keyword = trim($request->keyword);
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                  ->orWhere('sku_code', 'like', "%{$keyword}%");
            });
        }
        if ($request->filled('category_id')) {
            $query->whereHas('categories', fn($q) => $q->where('categories.id', $request->category_id));
        }
        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // --- SẮP XẾP ---
        $sortColumn = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        
        // ĐÃ SỬA: Bỏ 'stock_quantity' ra khỏi mảng cho phép sắp xếp
        if (in_array($sortColumn, ['name', 'price_min', 'created_at', 'sku_code'])) {
            $query->orderBy($sortColumn, $sortOrder);
        } else {
            $query->latest();
        }

        $products = $query->paginate(10)->withQueryString();
        $brands = Brand::orderBy('name')->get();
        $categories = Category::whereNull('parent_id')->with('children')->orderBy('name')->get();

        return view('admin.products.index', compact('products', 'brands', 'categories'));
    }

    public function create()
    {
        $brands = Brand::orderBy('name')->get();
        $categories = Category::whereNull('parent_id')->with('children')->get();
        // Lấy thuộc tính để (nếu muốn) render form biến thể ngay trang create
        $attributes = Attribute::with('values')->get(); 
        
        return view('admin.products.create', compact('brands', 'categories', 'attributes'));
    }

    // Thay thế hoàn toàn hàm store cũ của bạn
    // Thay thế hoàn toàn hàm store cũ của bạn
    public function store(Request $request)
    {
        // 1. Validate dữ liệu Sản phẩm cha
        $this->validateProduct($request);

        DB::beginTransaction();
        try {
            // 2. Xử lý dữ liệu
            $data = $this->parseProductData($request);
            
            // Tạo SKU code nếu chưa có
            if (empty($data['sku_code'])) {
                $data['sku_code'] = $this->generateSku();
            }

            // Upload Thumbnail (Bắt buộc khi tạo mới)
            if ($request->hasFile('thumbnail')) {
                $data['image'] = $this->uploadFile($request->file('thumbnail'), 'products/thumbnails');
            }

            // 3. Tạo Product
            $product = Product::create($data);

            // 4. Gắn danh mục
            if ($request->has('category_ids')) {
                $product->categories()->sync($request->category_ids);
            }

            // 5. Xử lý Gallery (Nếu cho upload ngay lúc tạo)
            if ($request->hasFile('gallery')) {
                foreach ($request->file('gallery') as $idx => $file) {
                    $path = $this->uploadFile($file, 'products/gallery');
                    $product->gallery_images()->create([
                        'image_path' => $path,
                        'sort_order' => $idx + 1
                    ]);
                }
            }

            DB::commit();

            // Chuyển hướng sang trang Edit để thêm biến thể
            return redirect()->route('admin.products.edit', $product->id)
                             ->with('success', 'Tạo sản phẩm thành công! Hãy thêm các phiên bản (biến thể).');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Lỗi tạo sản phẩm: " . $e->getMessage());
            // Xóa ảnh nếu upload thành công mà lưu DB thất bại (tránh rác server)
            if (isset($data['image'])) $this->deleteFile($data['image']);
            
            return back()->withInput()->with('error', 'Lỗi hệ thống: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        // Load relationships sâu để hiển thị đầy đủ
        $product = Product::with(['variants.attributeValues.attribute', 'categories', 'gallery_images'])->findOrFail($id);
        
        $brands = Brand::orderBy('name')->get();
        $categories = Category::whereNull('parent_id')->with('children')->get();
        $attributes = Attribute::with('values')->get();
        $selectedCategories = $product->categories->pluck('id')->toArray();

        return view('admin.products.edit', compact('product', 'brands', 'categories', 'selectedCategories', 'attributes'));
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'name'      => ['required', 'string', 'max:255', Rule::unique('products')->ignore($id)],
            'brand_id'  => 'required|exists:brands,id',
            'price_min' => 'required|numeric|min:0',
            'thumbnail' => 'nullable|image|max:3072',
        ]);

        DB::beginTransaction();
        try {
            $data = [
                'name'              => $request->name,
                'slug'              => Str::slug($request->name) . '-' . $id,
                'sku_code'          => $request->sku_code,
                'brand_id'          => $request->brand_id,
                'description'       => $request->description,
                'price_min'         => $request->price_min,
                'status'            => $request->status,
                'is_featured'       => $request->has('is_featured') ? 1 : 0,
            ];

            if ($request->hasFile('thumbnail')) {
                // Xóa ảnh cũ (nhớ import Storage)
                if ($product->image) \Illuminate\Support\Facades\Storage::disk('public')->delete($product->image);
                $data['image'] = $this->uploadFile($request->file('thumbnail'), 'products/thumbnails');
            }

            $product->update($data);
            
            if ($request->has('category_ids')) {
                $product->categories()->sync($request->category_ids);
            }

            // Xử lý Gallery thêm mới (như cũ)
            // ...

            DB::commit();
            return back()->with('success', 'Cập nhật thông tin sản phẩm thành công!'); // Ở lại trang Edit
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Lỗi cập nhật: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $product = Product::findOrFail($id);
            $product->delete(); // Soft delete
            return back()->with('success', 'Đã chuyển sản phẩm vào thùng rác.');
        } catch (\Exception $e) {
            return back()->with('error', 'Lỗi khi xóa sản phẩm.');
        }
    }

    // =========================================================================
    // 2. QUẢN LÝ BIẾN THỂ (PRODUCT VARIANTS) - CORE LOGIC
    // =========================================================================

    // Trong ProductController.php

    // 1. Hàm thêm biến thể (Giữ nguyên logic image_url, chỉ sửa gọi hàm con)
    // ==========================================
    // 1. HÀM THÊM MỚI BIẾN THỂ (STORE)
    // ==========================================
    public function storeVariant(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        // 1. Validate dữ liệu đầu vào (Tiếng Việt)
        $rules = [
            'sku'                => 'required|string|max:100|unique:product_variants,sku',
            'sale_price'         => 'required|numeric|min:0',
            'stock_quantity'     => 'required|integer|min:0',
            'attribute_values'   => 'required|array|min:1',
            'attribute_values.*' => 'integer|exists:attribute_values,id',
            'image'              => 'nullable|image|max:2048' // Input form là 'image'
        ];

        $messages = [
            'sku.required'            => 'Vui lòng nhập mã SKU.',
            'sku.unique'              => 'Mã SKU này đã tồn tại, vui lòng chọn mã khác.',
            'sku.max'                 => 'Mã SKU quá dài (tối đa 100 ký tự).',
            'sale_price.required'     => 'Vui lòng nhập giá bán.',
            'sale_price.numeric'      => 'Giá bán phải là số.',
            'sale_price.min'          => 'Giá bán không được nhỏ hơn 0.',
            'stock_quantity.required' => 'Vui lòng nhập số lượng tồn kho.',
            'stock_quantity.integer'  => 'Số lượng tồn kho phải là số nguyên.',
            'stock_quantity.min'      => 'Số lượng không được nhỏ hơn 0.',
            'attribute_values.required' => 'Bạn chưa chọn thuộc tính (Màu/Size).',
            'attribute_values.min'      => 'Vui lòng chọn ít nhất 1 thuộc tính.',
            'image.image'             => 'File tải lên phải là hình ảnh.',
            'image.max'               => 'Dung lượng ảnh tối đa là 2MB.',
        ];

        $request->validate($rules, $messages);

        // 2. Check trùng biến thể (Logic riêng)
        $newAttrIds = collect($request->attribute_values)->sort()->values()->all();
        if ($this->checkDuplicateVariant($product, $newAttrIds)) {
             return response()->json([
                'message' => 'Phiên bản (Màu/Size) này đã tồn tại!',
                'errors' => ['attribute_values' => ['Phiên bản này đã tồn tại trong hệ thống.']]
            ], 422);
        }

        DB::beginTransaction();
        try {
            // 3. Chuẩn bị dữ liệu
            $data = $request->all();
            
            // Xử lý upload ảnh: Map từ input 'image' -> column 'image_url'
            if ($request->hasFile('image')) {
                $path = $this->uploadFile($request->file('image'), 'variants');
                $data['image_url'] = $path; 
            }
            
            // Xóa key 'image' để tránh lỗi SQL "Unknown column"
            unset($data['image']); 

            // 4. Gọi hàm tạo biến thể (đã sửa ở dưới)
            $variant = $this->createSingleVariant($product, $data); 
            
            // 5. Lưu thuộc tính (Màu/Size)
            if (!empty($newAttrIds)) {
                $variant->attributeValues()->sync($newAttrIds);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Thêm phiên bản thành công!'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            // Trả về lỗi chi tiết để hiển thị lên màn hình (Debug)
            return response()->json([
                'status'  => 'error',
                'message' => 'LỖI HỆ THỐNG: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ==========================================
    // 2. HÀM CẬP NHẬT BIẾN THỂ (UPDATE)
    // ==========================================
    public function updateVariant(Request $request, $variantId)
    {
        $variant = ProductVariant::findOrFail($variantId);

        // 1. Validate
        $rules = [
            'sku'            => ['required', Rule::unique('product_variants')->ignore($variantId)],
            'sale_price'     => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'attribute_values'   => 'nullable|array',
            'attribute_values.*' => 'integer|exists:attribute_values,id',
            'image'              => 'nullable|image|max:2048'
        ];
        
        // (Bạn có thể copy mảng $messages từ hàm store xuống đây nếu muốn full tiếng Việt)
        $request->validate($rules);

        DB::beginTransaction();
        try {
            $oldStock = $variant->stock_quantity;
            $newStock = (int) $request->stock_quantity;

            // 2. Chuẩn bị dữ liệu update
            $updateData = [
                'sku'            => $request->sku,
                'sale_price'     => $request->sale_price,
                'original_price' => $request->original_price ?? $variant->original_price,
                'stock_quantity' => $newStock,
            ];

            // Xử lý ảnh update
            if ($request->hasFile('image')) {
                // Xóa ảnh cũ nếu cần (tùy logic)
                // $this->deleteFile($variant->image_url); 
                
                $path = $this->uploadFile($request->file('image'), 'variants');
                $updateData['image_url'] = $path; // Map sang 'image_url'
            }

            // 3. Thực hiện Update vào bảng product_variants
            $variant->update($updateData);

            // 4. Cập nhật thuộc tính (nếu có chọn lại)
            if ($request->has('attribute_values')) {
                 $newAttrIds = collect($request->attribute_values)->sort()->values()->all();
                 $variant->attributeValues()->sync($newAttrIds);
            }

            // 5. GHI LOG KHO (Fix lỗi quan trọng tại đây)
            if ($oldStock !== $newStock) {
                $diff = $newStock - $oldStock;
                
                InventoryLog::create([
                    'product_variant_id' => $variant->id,
                    'user_id'            => Auth::id() ?? 1,
                    'type'               => $diff > 0 ? 'import' : 'export',
                    
                    // CÁC CỘT QUAN TRỌNG KHỚP VỚI DB CỦA BẠN:
                    'old_quantity'       => $oldStock,     // Tồn cũ
                    'change_amount'      => abs($diff),    // Số lượng thay đổi (dương)
                    'new_quantity'       => $newStock,     // Tồn mới
                    
                    'note'               => 'Cập nhật thủ công (Admin)',
                    'reference_type'     => 'manual_update'
                ]);
            }

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Đã cập nhật biến thể thành công!'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Lỗi cập nhật: ' . $e->getMessage()
            ], 500);
        }
    }

    // ==========================================
    // 3. HÀM TẠO 1 BIẾN THỂ (PRIVATE)
    // ==========================================
    private function createSingleVariant($product, $data)
    {
        // Tạo variant mới
        $variant = $product->variants()->create([
            'sku'            => $data['sku'],
            'name'           => $product->name, 
            'original_price' => $data['original_price'] ?? 0,
            'sale_price'     => $data['sale_price'] ?? 0,
            'stock_quantity' => $data['stock_quantity'] ?? 0,
            
            // Map đúng tên cột trong DB
            'image_url'      => $data['image_url'] ?? null, 
        ]);

        // Ghi log kho lần đầu (Fix lỗi quan trọng tại đây)
        InventoryLog::create([
            'product_variant_id' => $variant->id,
            'user_id'            => Auth::id() ?? 1,
            'type'               => 'import',
            
            // CÁC CỘT QUAN TRỌNG KHỚP VỚI DB CỦA BẠN:
            'old_quantity'       => 0,                          // Ban đầu là 0
            'change_amount'      => $variant->stock_quantity,   // Thay đổi bằng chính số lượng nhập
            'new_quantity'       => $variant->stock_quantity,   // Tồn mới bằng số lượng nhập
            
            'note'               => 'Khởi tạo sản phẩm mới',
            'reference_type'     => 'initial_stock',
        ]);

        return $variant;
    }
    
    public function destroyVariant($id)
    {
        try {
            $variant = ProductVariant::findOrFail($id);
            // $productId = $variant->product_id; // Không cần lấy ID này nữa vì không dùng đến

            // Xóa ảnh variant
            $this->deleteFile($variant->image_url);
            
            $variant->delete();
            
            // ĐÃ SỬA: Xóa dòng $this->syncProductStock($productId); tại đây

            return back()->with('success', 'Đã xóa phiên bản.');
        } catch (\Exception $e) {
            return back()->with('error', 'Lỗi khi xóa phiên bản.');
        }
    }

    public function deleteImage($id)
    {
        try {
            // Giả sử Model ProductImage
            $img = \App\Models\ProductImage::findOrFail($id);
            $this->deleteFile($img->image_path);
            $img->delete();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false], 500);
        }
    }

    // =========================================================================
    // 3. CÁC HÀM XỬ LÝ LOGIC (PRIVATE)
    // =========================================================================
    
    /**
     * Đồng bộ tổng tồn kho từ con lên cha
     */
    private function syncProductStock($productId)
    {
        $totalStock = ProductVariant::where('product_id', $productId)->sum('stock_quantity');
        Product::where('id', $productId)->update(['stock_quantity' => $totalStock]);
    }

    private function validateProduct(Request $request, $id = null)
    {
        $rules = [
            'name'          => ['required', 'string', 'max:255', Rule::unique('products')->ignore($id)],
            'sku_code'      => ['nullable', 'string', 'max:50', Rule::unique('products')->ignore($id)],
            'brand_id'      => 'required|exists:brands,id',
            'category_ids'  => 'required|array|min:1',
            'price_min'     => 'required|numeric|min:0',
            'status'        => 'required|in:draft,published',
        ];

        // Ảnh: Bắt buộc khi tạo mới
        if (!$id) {
            $rules['thumbnail'] = 'required|image|max:3072';
        } else {
            $rules['thumbnail'] = 'nullable|image|max:3072';
        }

        $request->validate($rules, [
            'name.required' => 'Tên sản phẩm không được trống.',
            'category_ids.required' => 'Chọn ít nhất 1 danh mục.',
            'thumbnail.required' => 'Ảnh đại diện là bắt buộc.',
        ]);
    }

    private function parseProductData(Request $request)
    {
        return [
            'name'              => strip_tags(trim($request->name)),
            'slug'              => Str::slug($request->name),
            'sku_code'          => $request->sku_code,
            'brand_id'          => $request->brand_id,
            'description'       => $request->description, // Cho phép HTML
            'short_description' => strip_tags($request->short_description),
            'price_min'         => $request->price_min,
            'status'            => $request->status,
            'is_featured'       => $request->has('is_featured') ? 1 : 0,
            // Thêm các trường SEO nếu DB có cột
            // 'meta_title' => $request->meta_title,
            // 'meta_description' => $request->meta_description,
        ];
    }

    private function checkDuplicateVariant($product, $newAttrIds, $ignoreId = null)
    {
        $existing = $product->variants()
            ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
            ->with('attributeValues')
            ->get();

        foreach ($existing as $v) {
            $currentIds = $v->attributeValues->pluck('id')->sort()->values()->all();
            if ($newAttrIds == $currentIds) return true;
        }
        return false;
    }

    private function generateSku()
    {
        do {
            $sku = 'SP-' . strtoupper(Str::random(8));
        } while (Product::where('sku_code', $sku)->exists());
        return $sku;
    }

    private function uploadFile($file, $folder)
    {
        $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
        return $file->storeAs($folder, $filename, 'public');
    }

    private function deleteFile($path)
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}