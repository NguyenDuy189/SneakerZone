<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Attribute;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    // =========================================================================
    // 1. QUẢN LÝ SẢN PHẨM CHA (PARENT PRODUCTS)
    // =========================================================================

    public function index(Request $request)
    {
        $query = Product::with(['brand', 'categories']);

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
        
        if (in_array($sortColumn, ['name', 'price_min', 'created_at', 'sku_code'])) {
            $query->orderBy($sortColumn, $sortOrder);
        } else {
            $query->latest();
        }

        $products = $query->paginate(10)->withQueryString();
        $brands = Brand::orderBy('name')->get();
        $categories = Category::orderBy('level')->get();

        return view('admin.products.index', compact('products', 'brands', 'categories'));
    }

    public function create()
    {
        $brands = Brand::orderBy('name')->get();
        $categories = Category::orderBy('level')->get();
        return view('admin.products.create', compact('brands', 'categories'));
    }

    public function store(Request $request)
    {
        $this->validateProduct($request); // Validate Tiếng Việt

        DB::beginTransaction();
        try {
            $data = $this->parseProductData($request);
            
            // Tự động sinh SKU nếu trống
            if (empty($data['sku_code'])) {
                $data['sku_code'] = $this->generateSku();
            }

            // Xử lý Ảnh đại diện
            if ($request->hasFile('thumbnail')) {
                $data['thumbnail'] = $this->uploadFile($request->file('thumbnail'), 'products/thumbnails');
            }

            // Xử lý Gallery
            $galleryPaths = [];
            if ($request->hasFile('gallery')) {
                foreach ($request->file('gallery') as $file) {
                    $galleryPaths[] = $this->uploadFile($file, 'products/gallery');
                }
            }
            $data['gallery'] = $galleryPaths;

            // Lưu vào DB
            $product = Product::create($data);
            $product->categories()->sync($request->category_ids);

            DB::commit();
            return redirect()->route('admin.products.index')->with('success', 'Thêm sản phẩm thành công! Bạn có thể thêm biến thể ngay bây giờ.');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->cleanupFailedUploads($data['thumbnail'] ?? null, $galleryPaths ?? []);
            Log::error("Lỗi thêm sản phẩm: " . $e->getMessage());
            return back()->withInput()->with('error', 'Đã xảy ra lỗi hệ thống. Vui lòng thử lại sau.');
        }
    }

    public function edit($id)
    {
        $product = Product::with(['variants.attributeValues.attribute', 'categories'])->findOrFail($id);
        $brands = Brand::orderBy('name')->get();
        $categories = Category::orderBy('level')->get();
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

            if ($product->name !== $request->name) {
                $data['slug'] = Str::slug($request->name);
            }

            // Xử lý Thumbnail
            if ($request->hasFile('thumbnail')) {
                $this->deleteFile($product->thumbnail);
                $data['thumbnail'] = $this->uploadFile($request->file('thumbnail'), 'products/thumbnails');
            }

            // Xử lý Gallery
            $currentGallery = $product->gallery ?? [];
            
            // Xóa ảnh được chọn
            if ($request->filled('remove_gallery')) {
                foreach ($request->input('remove_gallery') as $pathToRemove) {
                    $this->deleteFile($pathToRemove);
                    $key = array_search($pathToRemove, $currentGallery);
                    if ($key !== false) unset($currentGallery[$key]);
                }
            }

            // Thêm ảnh mới
            if ($request->hasFile('gallery')) {
                foreach ($request->file('gallery') as $file) {
                    $currentGallery[] = $this->uploadFile($file, 'products/gallery');
                }
            }
            $data['gallery'] = array_values($currentGallery);

            $product->update($data);
            $product->categories()->sync($request->category_ids);

            DB::commit();
            return redirect()->route('admin.products.index')->with('success', 'Cập nhật sản phẩm thành công!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Lỗi cập nhật sản phẩm ID $id: " . $e->getMessage());
            return back()->withInput()->with('error', 'Không thể cập nhật dữ liệu. Vui lòng thử lại.');
        }
    }

    public function destroy($id)
    {
        try {
            $product = Product::findOrFail($id);
            $product->delete();
            return back()->with('success', 'Sản phẩm đã được chuyển vào thùng rác.');
        } catch (\Exception $e) {
            return back()->with('error', 'Lỗi khi xóa sản phẩm.');
        }
    }

    // =========================================================================
    // 2. QUẢN LÝ BIẾN THỂ (PRODUCT VARIANTS)
    // =========================================================================

    public function storeVariant(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        // --- VALIDATE BIẾN THỂ (TIẾNG VIỆT) ---
        $request->validate([
            'sku'              => 'required|string|max:100|unique:product_variants,sku',
            'sale_price'       => 'required|numeric|min:0|max:9999999999',
            'stock_quantity'   => 'required|integer|min:0|max:1000000',
            'original_price'   => 'nullable|numeric|min:0|gte:sale_price',
            'attribute_values' => 'required|array|min:1',
            'attribute_values.*' => 'required|integer|exists:attribute_values,id|distinct',
        ], [
            'sku.required' => 'Vui lòng nhập mã SKU phiên bản.',
            'sku.unique' => 'Mã SKU phiên bản này đã tồn tại.',
            'sale_price.required' => 'Vui lòng nhập giá bán.',
            'sale_price.min' => 'Giá bán không được nhỏ hơn 0.',
            'stock_quantity.required' => 'Vui lòng nhập số lượng tồn kho.',
            'original_price.gte' => 'Giá gốc phải lớn hơn hoặc bằng giá bán.',
            'attribute_values.required' => 'Vui lòng chọn ít nhất 1 thuộc tính (Size/Màu).',
            'attribute_values.*.required' => 'Vui lòng chọn giá trị cho thuộc tính.',
            'attribute_values.*.distinct' => 'Không được chọn trùng loại thuộc tính (Ví dụ: 2 lần Size).',
        ]);

        // Kiểm tra logic trùng lặp
        $newAttrIds = collect($request->attribute_values)->sort()->values()->all();
        if ($this->checkDuplicateVariant($product, $newAttrIds)) {
            return back()->with('error', 'Phiên bản với các thuộc tính này đã tồn tại!');
        }

        DB::beginTransaction();
        try {
            $variant = $product->variants()->create([
                'sku'            => $request->sku,
                'name'           => $product->name,
                'original_price' => $request->original_price ?? 0,
                'sale_price'     => $request->sale_price,
                'stock_quantity' => $request->stock_quantity,
            ]);

            if ($request->hasFile('variant_image')) {
                $path = $this->uploadFile($request->file('variant_image'), 'products/variants');
                $variant->update(['image_url' => $path]);
            }

            $variant->attributeValues()->sync($newAttrIds);

            DB::commit();
            return back()->with('success', 'Thêm phiên bản mới thành công!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Lỗi hệ thống: ' . $e->getMessage());
        }
    }

    public function updateVariant(Request $request, $variantId)
    {
        $variant = ProductVariant::findOrFail($variantId);

        $request->validate([
            'sku'              => ['required', 'string', 'max:100', Rule::unique('product_variants')->ignore($variant->id)],
            'sale_price'       => 'required|numeric|min:0',
            'stock_quantity'   => 'required|integer|min:0',
            'original_price'   => 'nullable|numeric|min:0|gte:sale_price',
            'attribute_values' => 'required|array|min:1',
            'attribute_values.*' => 'required|integer|exists:attribute_values,id|distinct',
        ], [
            'sku.required' => 'Vui lòng nhập mã SKU.',
            'sku.unique' => 'Mã SKU này đã được sử dụng.',
            'original_price.gte' => 'Giá gốc phải lớn hơn hoặc bằng giá bán.',
            'attribute_values.*.required' => 'Có thuộc tính chưa được chọn giá trị.',
            'attribute_values.*.distinct' => 'Thuộc tính bị trùng lặp.',
        ]);

        $newAttrIds = collect($request->attribute_values)->sort()->values()->all();
        if ($this->checkDuplicateVariant($variant->product, $newAttrIds, $variant->id)) {
            return back()->with('error', 'Bộ thuộc tính này đã trùng với một phiên bản khác!');
        }

        DB::beginTransaction();
        try {
            $variant->update([
                'sku'            => $request->sku,
                'original_price' => $request->original_price ?? 0,
                'sale_price'     => $request->sale_price,
                'stock_quantity' => $request->stock_quantity,
            ]);

            if ($request->hasFile('variant_image')) {
                $this->deleteFile($variant->image_url);
                $path = $this->uploadFile($request->file('variant_image'), 'products/variants');
                $variant->update(['image_url' => $path]);
            }

            $variant->attributeValues()->sync($newAttrIds);

            DB::commit();
            return back()->with('success', 'Cập nhật phiên bản thành công.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Lỗi cập nhật: ' . $e->getMessage());
        }
    }

    public function destroyVariant($id)
    {
        try {
            $variant = ProductVariant::findOrFail($id);
            $this->deleteFile($variant->image_url);
            $variant->delete();
            return back()->with('success', 'Đã xóa phiên bản thành công.');
        } catch (\Exception $e) {
            return back()->with('error', 'Lỗi khi xóa phiên bản.');
        }
    }

    // =========================================================================
    // 3. THÙNG RÁC (TRASH)
    // =========================================================================

    public function trash()
    {
        $products = Product::onlyTrashed()->with(['brand', 'categories'])->latest()->paginate(10);
        return view('admin.products.trash', compact('products'));
    }

    public function restore($id)
    {
        Product::onlyTrashed()->findOrFail($id)->restore();
        return back()->with('success', 'Đã khôi phục sản phẩm thành công.');
    }

    public function forceDelete($id)
    {
        try {
            $product = Product::onlyTrashed()->findOrFail($id);
            
            $this->deleteFile($product->thumbnail);
            if (!empty($product->gallery)) {
                foreach ($product->gallery as $path) $this->deleteFile($path);
            }
            
            foreach($product->variants as $variant) {
                $this->deleteFile($variant->image_url);
                $variant->delete();
            }

            $product->categories()->detach();
            $product->forceDelete();

            return back()->with('success', 'Đã xóa vĩnh viễn sản phẩm và toàn bộ dữ liệu liên quan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Lỗi xóa vĩnh viễn: ' . $e->getMessage());
        }
    }

    // =========================================================================
    // 4. PRIVATE HELPERS (HÀM PHỤ TRỢ)
    // =========================================================================

    private function validateProduct(Request $request, $id = null)
    {
        $rules = [
            'name'           => ['required', 'string', 'max:255', Rule::unique('products')->ignore($id)],
            'sku_code'       => ['nullable', 'string', 'max:50', Rule::unique('products')->ignore($id)],
            'brand_id'       => 'required|integer|exists:brands,id',
            'category_ids'   => 'required|array|min:1',
            'category_ids.*' => 'integer|exists:categories,id',
            'price_min'      => 'required|numeric|min:0|max:9999999999',
            'status'         => 'required|in:draft,published,archived',
        ];

        // Ảnh: Bắt buộc khi tạo mới
        $imgRule = 'image|mimes:jpeg,png,jpg,webp|max:3072'; // 3MB
        if (!$id) {
            $rules['thumbnail'] = "required|$imgRule";
        } else {
            $rules['thumbnail'] = "nullable|$imgRule";
        }
        $rules['gallery.*'] = "nullable|$imgRule";

        // Thông báo lỗi Tiếng Việt
        $messages = [
            'name.required' => 'Vui lòng nhập tên sản phẩm.',
            'name.unique' => 'Tên sản phẩm này đã tồn tại, vui lòng chọn tên khác.',
            'sku_code.unique' => 'Mã SKU này đã tồn tại.',
            'brand_id.required' => 'Vui lòng chọn thương hiệu.',
            'category_ids.required' => 'Vui lòng chọn ít nhất một danh mục.',
            'price_min.required' => 'Vui lòng nhập giá bán.',
            'price_min.min' => 'Giá bán không được nhỏ hơn 0đ.',
            'price_min.max' => 'Giá bán quá lớn.',
            'thumbnail.required' => 'Vui lòng tải lên ảnh đại diện.',
            'thumbnail.image' => 'File tải lên phải là hình ảnh.',
            'thumbnail.max' => 'Dung lượng ảnh tối đa 3MB.',
            'gallery.*.image' => 'File trong thư viện ảnh phải là hình ảnh.',
            'gallery.*.max' => 'Ảnh trong thư viện quá lớn (Tối đa 3MB/ảnh).',
        ];

        $request->validate($rules, $messages);
    }

    private function parseProductData(Request $request)
    {
        return [
            'name'              => strip_tags(trim($request->name)),
            'sku_code'          => strip_tags(trim($request->sku_code)),
            'description'       => $request->description, // Giữ HTML
            'short_description' => strip_tags(trim($request->short_description)),
            'brand_id'          => $request->brand_id,
            'price_min'         => $request->price_min,
            'status'            => $request->status,
            'is_featured'       => $request->has('is_featured'),
            'slug'              => Str::slug($request->name),
        ];
    }

    private function checkDuplicateVariant($product, $newAttrIds, $ignoreVariantId = null)
    {
        $existingVariants = $product->variants()
            ->when($ignoreVariantId, fn($q) => $q->where('id', '!=', $ignoreVariantId))
            ->with('attributeValues')
            ->get();

        foreach ($existingVariants as $v) {
            $existingAttrIds = $v->attributeValues->pluck('id')->sort()->values()->all();
            if ($newAttrIds == $existingAttrIds) {
                return true;
            }
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

    private function cleanupFailedUploads($thumbnail, $gallery)
    {
        if ($thumbnail) $this->deleteFile($thumbnail);
        if (!empty($gallery)) {
            foreach ($gallery as $path) $this->deleteFile($path);
        }
    }
}