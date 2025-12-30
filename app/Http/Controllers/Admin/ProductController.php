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
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    // =========================================================================
    // 1. QUẢN LÝ SẢN PHẨM CHA (PARENT PRODUCTS)
    // =========================================================================

    public function index(Request $request)
    {
        $query = Product::with(['brand', 'categories', 'variants']); // Eager load variants để đếm

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
        
        // Chỉ cho phép sắp xếp các cột an toàn
        if (in_array($sortColumn, ['name', 'price_min', 'stock_quantity', 'created_at', 'sku_code'])) {
            $query->orderBy($sortColumn, $sortOrder);
        } else {
            $query->latest();
        }

        $products = $query->paginate(10)->withQueryString();
        $brands = Brand::orderBy('name')->get();
        $categories = Category::whereNull('parent_id')->with('children')->orderBy('name')->get(); // Lấy danh mục đa cấp

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

    public function store(Request $request)
    {
        $this->validateProduct($request);

        DB::beginTransaction();
        try {
            $data = $this->parseProductData($request);
            
            // Tự động sinh SKU nếu trống
            if (empty($data['sku_code'])) {
                $data['sku_code'] = $this->generateSku();
            }

            // Xử lý Ảnh đại diện
            if ($request->hasFile('thumbnail')) {
                $data['image'] = $this->uploadFile($request->file('thumbnail'), 'products/thumbnails'); // Sửa key thành 'image' khớp Model
            }

            // Xử lý Gallery (Lưu mảng JSON hoặc dùng bảng riêng ProductImage - Ở đây giả sử dùng bảng riêng ở bước sau)
            // Trong code mẫu Model bạn gửi dùng ProductImage hasMany, nên ở đây ta lưu Product trước.
            
            // Lưu Product vào DB
            $product = Product::create($data);
            $product->categories()->sync($request->category_ids);

            // Xử lý Gallery (Lưu vào bảng product_images)
            if ($request->hasFile('gallery')) {
                foreach ($request->file('gallery') as $idx => $file) {
                    $path = $this->uploadFile($file, 'products/gallery');
                    $product->gallery_images()->create([
                        'image_path' => $path,
                        'sort_order' => $idx
                    ]);
                }
            }

            // *** TÍNH NĂNG CAO CẤP: TẠO LUÔN BIẾN THỂ NẾU CÓ ***
            // Nếu form create có gửi kèm variants (JS dynamic rows)
            if ($request->has('variants') && is_array($request->variants)) {
                foreach ($request->variants as $vData) {
                    $this->createSingleVariant($product, $vData);
                }
                // Cập nhật lại tổng tồn kho cho cha
                $this->syncProductStock($product->id);
            }

            DB::commit();
            
            // Chuyển hướng thông minh: Nếu chưa có biến thể -> Edit, Có rồi -> Index
            if ($product->variants()->count() == 0) {
                return redirect()->route('admin.products.edit', $product->id)
                    ->with('warning', 'Sản phẩm đã tạo. Hãy thêm Biến thể (Size/Màu) để bắt đầu bán.');
            }

            return redirect()->route('admin.products.index')->with('success', 'Thêm sản phẩm thành công!');

        } catch (\Exception $e) {
            DB::rollBack();
            // Xóa ảnh rác nếu lỗi
            if (isset($data['image'])) $this->deleteFile($data['image']);
            
            Log::error("Lỗi thêm sản phẩm: " . $e->getMessage());
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
        $this->validateProduct($request, $id);

        DB::beginTransaction();
        try {
            $data = $this->parseProductData($request);

            // Cập nhật slug nếu đổi tên
            if ($product->name !== $request->name) {
                $data['slug'] = Str::slug($request->name) . '-' . $product->id;
            }

            // Xử lý Thumbnail
            if ($request->hasFile('thumbnail')) {
                $this->deleteFile($product->image);
                $data['image'] = $this->uploadFile($request->file('thumbnail'), 'products/thumbnails');
            }

            $product->update($data);
            $product->categories()->sync($request->category_ids);

            // Xử lý Gallery (Thêm mới)
            if ($request->hasFile('gallery')) {
                $currentMaxOrder = $product->gallery_images()->max('sort_order') ?? 0;
                foreach ($request->file('gallery') as $idx => $file) {
                    $path = $this->uploadFile($file, 'products/gallery');
                    $product->gallery_images()->create([
                        'image_path' => $path,
                        'sort_order' => $currentMaxOrder + $idx + 1
                    ]);
                }
            }
            
            // Lưu ý: Việc xóa ảnh gallery nên làm qua API/Route riêng (deleteImage) để UX tốt hơn

            DB::commit();
            return redirect()->route('admin.products.index')->with('success', 'Cập nhật sản phẩm thành công!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Lỗi cập nhật SP #$id: " . $e->getMessage());
            return back()->withInput()->with('error', 'Lỗi cập nhật: ' . $e->getMessage());
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

    public function storeVariant(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'sku'            => 'required|string|max:100|unique:product_variants,sku',
            'sale_price'     => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'original_price' => 'nullable|numeric|min:0|gte:sale_price',
            'attribute_values' => 'required|array|min:1', // Mảng ID của Size, Color
        ]);

        // Check trùng
        $newAttrIds = collect($request->attribute_values)->sort()->values()->all();
        if ($this->checkDuplicateVariant($product, $newAttrIds)) {
            return back()->with('error', 'Phiên bản này đã tồn tại!');
        }

        DB::beginTransaction();
        try {
            // 1. Tạo Variant
            $variant = $this->createSingleVariant($product, $request->all());
            
            // 2. Sync Attribute Values
            $variant->attributeValues()->sync($newAttrIds);

            // 3. Cập nhật tổng tồn kho cha
            $this->syncProductStock($product->id);

            DB::commit();
            return back()->with('success', 'Thêm phiên bản thành công!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Lỗi: ' . $e->getMessage());
        }
    }

    public function updateVariant(Request $request, $variantId)
    {
        $variant = ProductVariant::findOrFail($variantId);

        $request->validate([
            'sku'            => ['required', Rule::unique('product_variants')->ignore($variant->id)],
            'stock_quantity' => 'required|integer|min:0',
            'sale_price'     => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $oldStock = $variant->stock_quantity;
            $newStock = $request->stock_quantity;

            // 1. Cập nhật
            $variant->update([
                'sku'            => $request->sku,
                'stock_quantity' => $newStock,
                'original_price' => $request->original_price ?? 0,
                'sale_price'     => $request->sale_price,
            ]);

            // 2. Ghi Log Kho (Quan trọng cho E-commerce)
            if ($oldStock != $newStock) {
                $diff = $newStock - $oldStock;
                InventoryLog::create([
                    'product_variant_id' => $variant->id,
                    'user_id'            => Auth::id(),
                    'type'               => $diff > 0 ? 'import' : 'export',
                    'quantity'           => abs($diff),
                    'current_stock'      => $newStock,
                    'note'               => 'Cập nhật thủ công từ trang sửa sản phẩm',
                    'reference_type'     => 'manual_update',
                ]);
            }

            // 3. Cập nhật tổng tồn kho cha
            $this->syncProductStock($variant->product_id);

            DB::commit();
            return back()->with('success', 'Cập nhật phiên bản thành công.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Lỗi: ' . $e->getMessage());
        }
    }

    public function destroyVariant($id)
    {
        try {
            $variant = ProductVariant::findOrFail($id);
            $productId = $variant->product_id;
            
            // Xóa ảnh variant
            $this->deleteFile($variant->image_url);
            
            $variant->delete();
            
            // Cập nhật lại tổng tồn kho cha
            $this->syncProductStock($productId);

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
     * Tạo 1 variant (Dùng chung cho store và storeVariant)
     */
    private function createSingleVariant($product, $data)
    {
        $variant = $product->variants()->create([
            'sku'            => $data['sku'],
            'name'           => $product->name, // Hoặc ghép tên thuộc tính vào nếu muốn
            'original_price' => $data['original_price'] ?? $data['price'] ?? 0, // Fallback key
            'sale_price'     => $data['sale_price'] ?? $data['price'] ?? 0,
            'stock_quantity' => $data['stock_quantity'] ?? $data['stock'] ?? 0,
        ]);

        // Ghi log nhập kho lần đầu
        InventoryLog::create([
            'product_variant_id' => $variant->id,
            'user_id'            => Auth::id(),
            'type'               => 'import',
            'quantity'           => $variant->stock_quantity,
            'current_stock'      => $variant->stock_quantity,
            'note'               => 'Khởi tạo sản phẩm mới',
            'reference_type'     => 'initial_stock',
        ]);

        return $variant;
    }

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