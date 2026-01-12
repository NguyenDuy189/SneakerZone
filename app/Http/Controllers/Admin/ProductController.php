<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Attribute;
use App\Models\InventoryLog;
use App\Models\ProductImage;
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
    // 1. DANH SÁCH & TẠO MỚI
    // =========================================================================

    public function index(Request $request)
    {
        $query = Product::with(['brand', 'categories']); // Eager loading tối ưu

        // --- FILTER ---
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

        // --- SORTING ---
        $sortColumn = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $validSorts = ['name', 'price_min', 'created_at', 'sku_code', 'stock_quantity'];
        
        if (in_array($sortColumn, $validSorts)) {
            $query->orderBy($sortColumn, $sortOrder);
        } else {
            $query->latest();
        }

        $products = $query->paginate(10)->withQueryString();
        $brands = Brand::orderBy('name')->get();
        // Lấy danh mục cha để hiển thị filter
        $categories = Category::whereNull('parent_id')->orderBy('name')->get();

        return view('admin.products.index', compact('products', 'brands', 'categories'));
    }

    public function create()
    {
        $brands = Brand::orderBy('name')->get();
        // Lấy danh mục đa cấp (parent -> children)
        $categories = Category::whereNull('parent_id')->with('children')->get();
        $attributes = Attribute::with('values')->get(); 
        
        return view('admin.products.create', compact('brands', 'categories', 'attributes'));
    }

    public function store(Request $request)
    {
        // 1. Validate chặt chẽ
        $validated = $this->validateProductData($request);

        DB::beginTransaction();
        $pathsToDeleteOnError = []; // Mảng chứa các file cần xóa nếu lỗi

        try {
            // 2. Xử lý dữ liệu cơ bản
            $data = $request->except(['thumbnail', 'gallery', 'category_ids']);
            
            // Auto generate SLUG nếu trống
            $data['slug'] = $this->generateUniqueSlug($validated['name']);
            
            // Auto generate SKU nếu trống
            if (empty($data['sku_code'])) {
                $data['sku_code'] = $this->generateUniqueSku();
            }

            // Xử lý danh mục chính (primary category)
            $categoryIds = $request->input('category_ids', []);
            $data['category_id'] = $categoryIds[0] ?? null; // Lấy phần tử đầu tiên

            // 3. Upload Thumbnail (Bắt buộc)
            if ($request->hasFile('thumbnail')) {
                $thumbPath = $this->uploadFile($request->file('thumbnail'), 'products/thumbnails');
                $data['thumbnail'] = $thumbPath;
                $pathsToDeleteOnError[] = $thumbPath;
            }

            // 4. Tạo Product
            $product = Product::create($data);

            // 5. Sync Categories (Many-to-Many)
            if (!empty($categoryIds)) {
                $product->categories()->sync($categoryIds);
            }

            // 6. Upload Gallery (Multiple)
            if ($request->hasFile('gallery')) {
                foreach ($request->file('gallery') as $idx => $file) {
                    $galleryPath = $this->uploadFile($file, 'products/gallery');
                    $pathsToDeleteOnError[] = $galleryPath;

                    $product->gallery_images()->create([
                        'image_path' => $galleryPath,
                        'sort_order' => $idx + 1
                    ]);
                }
            }

            DB::commit(); // Lưu thành công
            
            return redirect()->route('admin.products.edit', $product->id)
                            ->with('success', 'Thêm mới sản phẩm thành công!');

        } catch (\Exception $e) {
            DB::rollBack(); // Hoàn tác DB
            Log::error("Product Store Error: " . $e->getMessage());
            
            // Xóa file rác vừa upload để không tốn dung lượng
            foreach ($pathsToDeleteOnError as $path) {
                $this->deleteFile($path);
            }

            return back()->withInput()->with('error', 'Lỗi hệ thống: ' . $e->getMessage());
        }
    }

    // =========================================================================
    // 2. CẬP NHẬT (EDIT & UPDATE)
    // =========================================================================

    public function edit($id)
    {
        $product = Product::with(['variants.attributeValues.attribute', 'categories', 'gallery_images'])
                    ->findOrFail($id);
        
        $brands = Brand::orderBy('name')->get();
        $categories = Category::whereNull('parent_id')->with('children')->get();
        $attributes = Attribute::with('values')->get();
        
        // Array các ID category đã chọn -> để check vào checkbox
        $selectedCategories = $product->categories->pluck('id')->toArray();

        return view('admin.products.edit', compact('product', 'brands', 'categories', 'selectedCategories', 'attributes'));
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        // 1. Validate
        $this->validateProductData($request, $id);

        DB::beginTransaction();
        $pathsToDeleteOnError = [];
        $oldThumbnail = $product->thumbnail; // Lưu lại đường dẫn cũ

        try {
            $data = $request->except(['thumbnail', 'gallery', 'category_ids']);
            
            // Cập nhật Slug nếu tên thay đổi (tùy chọn, tốt cho SEO)
            if ($product->name !== $request->name) {
                $data['slug'] = $this->generateUniqueSlug($request->name, $id);
            }

            $data['is_featured'] = $request->has('is_featured') ? 1 : 0;
            
            // Cập nhật danh mục chính
            $categoryIds = $request->input('category_ids', []);
            if (!empty($categoryIds)) {
                $data['category_id'] = $categoryIds[0];
            }

            // 2. Xử lý Ảnh đại diện mới (nếu có)
            if ($request->hasFile('thumbnail')) {
                $newThumbPath = $this->uploadFile($request->file('thumbnail'), 'products/thumbnails');
                $data['thumbnail'] = $newThumbPath;
                $pathsToDeleteOnError[] = $newThumbPath; // Đánh dấu để xóa nếu DB lỗi
            }

            // 3. Update Product
            $product->update($data);

            // 4. Sync Categories
            $product->categories()->sync($categoryIds);

            // 5. Upload thêm Gallery (nếu có)
            if ($request->hasFile('gallery')) {
                $currentMaxSort = $product->gallery_images()->max('sort_order') ?? 0;
                foreach ($request->file('gallery') as $idx => $file) {
                    $galleryPath = $this->uploadFile($file, 'products/gallery');
                    $pathsToDeleteOnError[] = $galleryPath;

                    $product->gallery_images()->create([
                        'image_path' => $galleryPath,
                        'sort_order' => $currentMaxSort + $idx + 1
                    ]);
                }
            }

            DB::commit();

            // === CLEANUP CHỈ KHI THÀNH CÔNG ===
            // Nếu upload ảnh mới thành công và commit xong, mới xóa ảnh cũ
            if ($request->hasFile('thumbnail') && $oldThumbnail) {
                $this->deleteFile($oldThumbnail);
            }

            return back()->with('success', 'Cập nhật sản phẩm thành công!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Product Update Error: " . $e->getMessage());

            // Xóa ảnh mới (rác) vì update thất bại
            foreach ($pathsToDeleteOnError as $path) {
                $this->deleteFile($path);
            }

            return back()->withInput()->with('error', 'Lỗi cập nhật: ' . $e->getMessage());
        }
    }

    // =========================================================================
    // 3. QUẢN LÝ ẢNH GALLERY (AJAX)
    // =========================================================================

    public function deleteGalleryImage($id)
    {
        try {
            $img = ProductImage::findOrFail($id);
            
            // 1. Xóa file vật lý trong storage
            if ($img->image_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($img->image_path)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($img->image_path);
            }
            
            // 2. Xóa record trong database
            $img->delete();
            
            return response()->json([
                'status' => 'success', 
                'message' => 'Đã xóa ảnh thành công!'
            ]);
        } catch (\Exception $e) {
            // Log lỗi ra file laravel.log để debug
            \Illuminate\Support\Facades\Log::error("Lỗi xóa ảnh: " . $e->getMessage());
            
            return response()->json([
                'status' => 'error', 
                'message' => 'Lỗi Server: ' . $e->getMessage()
            ], 500);
        }
    }

    // =========================================================================
    // 4. QUẢN LÝ BIẾN THỂ (VARIANTS)
    // =========================================================================

    public function storeVariant(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        // Validate riêng cho Variant
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'sku'              => ['required', 'string', 'max:100', 'unique:product_variants,sku'],
            'sale_price'       => 'required|numeric|min:0',
            'stock_quantity'   => 'required|integer|min:0',
            'attribute_values' => 'required|array|min:1',
            'image'            => 'nullable|image|max:2048' // 2MB
        ], [
            'sku.unique' => 'Mã SKU biến thể đã tồn tại.',
            'attribute_values.required' => 'Chưa chọn thuộc tính (Màu/Size).'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Kiểm tra trùng lặp thuộc tính (Logic quan trọng)
        $newAttrIds = array_map('intval', $request->attribute_values);
        sort($newAttrIds);
        
        if ($this->isVariantDuplicate($product, $newAttrIds)) {
             return response()->json([
                 'message' => 'Phiên bản này đã tồn tại!',
                 'errors' => ['attribute_values' => ['Bộ thuộc tính này đã tồn tại.']]
             ], 422);
        }

        DB::beginTransaction();
        try {
            $data = $request->only(['sku', 'stock_quantity', 'original_price', 'sale_price']);
            
            // Upload ảnh biến thể
            if ($request->hasFile('image')) {
                $data['image_url'] = $this->uploadFile($request->file('image'), 'variants');
            }

            $data['name'] = $product->name; // Đồng bộ tên
            $variant = $product->variants()->create($data);

            // Gắn thuộc tính
            $variant->attributeValues()->sync($newAttrIds);

            // Ghi Log Kho
            $this->logInventory($variant->id, $variant->stock_quantity, 0, 'initial', 'Khởi tạo biến thể');

            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Thêm biến thể thành công!']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function updateVariant(Request $request, $variantId)
    {
        $variant = ProductVariant::findOrFail($variantId);

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'sku'            => ['required', Rule::unique('product_variants')->ignore($variantId)],
            'sale_price'     => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 422);

        // Check trùng lặp (trừ chính nó)
        if ($request->has('attribute_values')) {
            $newAttrIds = array_map('intval', $request->attribute_values);
            sort($newAttrIds);
            if ($this->isVariantDuplicate($variant->product, $newAttrIds, $variantId)) {
                return response()->json(['message' => 'Thuộc tính trùng lặp!'], 422);
            }
        }

        DB::beginTransaction();
        try {
            $oldStock = $variant->stock_quantity;
            $newStock = (int) $request->stock_quantity;

            $data = $request->only(['sku', 'sale_price', 'original_price']);
            $data['stock_quantity'] = $newStock;

            if ($request->hasFile('image')) {
                // Xóa ảnh cũ nếu muốn tiết kiệm
                // $this->deleteFile($variant->image_url);
                $data['image_url'] = $this->uploadFile($request->file('image'), 'variants');
            }

            $variant->update($data);

            if ($request->has('attribute_values')) {
                 $variant->attributeValues()->sync($request->attribute_values);
            }

            // Ghi Log Kho nếu thay đổi
            if ($oldStock !== $newStock) {
                $type = $newStock > $oldStock ? 'import' : 'export';
                $change = abs($newStock - $oldStock);
                $this->logInventory($variant->id, $change, $oldStock, 'manual_update', 'Cập nhật thủ công', $type);
            }

            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Cập nhật thành công!']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function destroyVariant($id)
    {
        $variant = ProductVariant::findOrFail($id);
        $this->deleteFile($variant->image_url);
        $variant->delete();
        return back()->with('success', 'Đã xóa phiên bản.');
    }

    // =========================================================================
    // 5. HELPER METHODS (PRIVATE) - PHẦN NÂNG CẤP LOGIC
    // =========================================================================

    /**
     * Validate dữ liệu chung cho Create và Update
     */
    private function validateProductData(Request $request, $id = null)
    {
        $rules = [
            'name'          => ['required', 'string', 'max:255', Rule::unique('products')->ignore($id)],
            'sku_code'      => ['nullable', 'string', 'max:50', 'alpha_dash', Rule::unique('products')->ignore($id)], // alpha_dash: chỉ cho phép chữ, số, gạch ngang
            'category_ids'  => ['required', 'array', 'min:1'], // Bắt buộc là mảng
            'category_ids.*'=> ['integer', 'exists:categories,id'], // Từng ID phải tồn tại
            'brand_id'      => ['nullable', 'integer', 'exists:brands,id'],
            'price_min'     => ['required', 'numeric', 'min:0'],
            'status'        => ['required', 'in:draft,published,archived'],
            'gallery.*'     => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'], // Max 5MB
        ];

        // Nếu tạo mới thì bắt buộc có thumbnail, update thì nullable
        if (!$id) {
            $rules['thumbnail'] = ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:3072'];
        } else {
            $rules['thumbnail'] = ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:3072'];
        }

        $messages = [
            'name.required' => 'Tên sản phẩm không được để trống.',
            'name.unique'   => 'Tên sản phẩm này đã tồn tại.',
            'sku_code.unique' => 'Mã SKU đã tồn tại.',
            'sku_code.alpha_dash' => 'SKU chỉ được chứa chữ cái, số và dấu gạch ngang.',
            'category_ids.required' => 'Vui lòng chọn ít nhất 1 danh mục.',
            'category_ids.*.exists' => 'Danh mục đã chọn không hợp lệ.',
            'thumbnail.required' => 'Ảnh đại diện là bắt buộc khi tạo mới.',
            'thumbnail.max' => 'Ảnh đại diện không được quá 3MB.',
        ];

        return $request->validate($rules, $messages);
    }

    private function generateUniqueSlug($name, $ignoreId = null)
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $count = 1;

        // Vòng lặp check xem slug có trùng không, nếu trùng thì thêm số -1, -2...
        while (Product::where('slug', $slug)->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $originalSlug . '-' . $count++;
        }
        return $slug;
    }

    private function generateUniqueSku()
    {
        do {
            $sku = 'SP-' . strtoupper(Str::random(8));
        } while (Product::where('sku_code', $sku)->exists());
        
        return $sku;
    }

    private function isVariantDuplicate($product, $attrIds, $ignoreVariantId = null)
    {
        $variants = $product->variants()
            ->when($ignoreVariantId, fn($q) => $q->where('id', '!=', $ignoreVariantId))
            ->with('attributeValues')
            ->get();

        foreach ($variants as $v) {
            $currentIds = $v->attributeValues->pluck('id')->map(fn($i)=>(int)$i)->sort()->values()->all();
            if ($attrIds === $currentIds) return true;
        }
        return false;
    }

    private function logInventory($variantId, $amount, $oldQty, $refType, $note, $type = 'import')
    {
        InventoryLog::create([
            'product_variant_id' => $variantId,
            'user_id'            => Auth::id() ?? 1,
            'type'               => $type, // import/export
            'old_quantity'       => $oldQty,
            'change_amount'      => $amount,
            'new_quantity'       => $type === 'import' ? ($oldQty + $amount) : ($oldQty - $amount),
            'note'               => $note,
            'reference_type'     => $refType
        ]);
    }

    private function uploadFile($file, $folder)
    {
        // Tạo tên file ngẫu nhiên an toàn + extension gốc
        $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
        return $file->storeAs($folder, $filename, 'public');
    }

    private function deleteFile($path)
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    // =========================================================================
    // 6. TRASH (SOFT DELETE)
    // =========================================================================

    public function destroy($id)
    {
        // Soft delete
        $product = Product::findOrFail($id);
        $product->delete();
        return back()->with('success', 'Sản phẩm đã được chuyển vào thùng rác.');
    }

    public function trash(Request $request)
    {
        $products = Product::onlyTrashed()->with('brand')->latest()->paginate(10);
        return view('admin.products.trash', compact('products'));
    }

    public function restore($id)
    {
        Product::onlyTrashed()->findOrFail($id)->restore();
        return redirect()->route('admin.products.trash')->with('success', 'Khôi phục sản phẩm thành công.');
    }

    public function forceDelete($id)
    {
        try {
            $product = Product::onlyTrashed()->with(['gallery_images', 'variants'])->findOrFail($id);

            // Xóa toàn bộ ảnh liên quan trước khi xóa DB
            $this->deleteFile($product->thumbnail);
            
            foreach ($product->gallery_images as $img) {
                $this->deleteFile($img->image_path);
            }
            
            foreach ($product->variants as $var) {
                $this->deleteFile($var->image_url);
            }

            // Xóa cứng
            $product->forceDelete();

            return redirect()->route('admin.products.trash')->with('success', 'Đã xóa vĩnh viễn dữ liệu.');
        } catch (\Exception $e) {
            return back()->with('error', 'Lỗi: ' . $e->getMessage());
        }
    }
}