<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Brand;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    /**
     * Danh sách sản phẩm
     */
    public function index()
    {
        $products = Product::with(['brand', 'categories'])
            ->latest()
            ->paginate(10);
            
        return view('admin.products.index', compact('products'));
    }

    /**
     * Form thêm mới
     */
    public function create()
    {
        $brands = Brand::orderBy('name', 'asc')->get();
        // Lấy danh mục theo thứ tự logic (Level)
        $categories = Category::orderBy('level', 'asc')->orderBy('name', 'asc')->get();
        
        return view('admin.products.create', compact('brands', 'categories'));
    }

    /**
     * Xử lý Thêm mới (Cao cấp)
     */
    public function store(Request $request)
    {
        $this->validateProduct($request);

        DB::beginTransaction();
        try {
            // 1. Chuẩn bị dữ liệu (Sanitize text, giữ HTML description)
            $data = $this->parseProductData($request);
            
            // 2. Tự động sinh SKU nếu rỗng
            if (empty($data['sku_code'])) {
                $data['sku_code'] = $this->generateSku();
            }

            // 3. Xử lý Ảnh đại diện (Thumbnail)
            if ($request->hasFile('thumbnail')) {
                $data['thumbnail'] = $this->uploadFile($request->file('thumbnail'), 'products/thumbnails');
            }

            // 4. Xử lý Gallery (Upload nhiều ảnh)
            $galleryPaths = [];
            if ($request->hasFile('gallery')) {
                foreach ($request->file('gallery') as $file) {
                    $galleryPaths[] = $this->uploadFile($file, 'products/gallery');
                }
            }
            $data['gallery'] = $galleryPaths;

            // 5. Tạo sản phẩm & Liên kết danh mục
            $product = Product::create($data);
            $product->categories()->sync($request->category_ids);

            DB::commit();
            return redirect()->route('admin.products.index')->with('success', 'Thêm sản phẩm thành công!');

        } catch (\Exception $e) {
            DB::rollBack();
            // Dọn dẹp file rác nếu lỗi
            $this->cleanupFailedUploads($data['thumbnail'] ?? null, $galleryPaths ?? []);
            
            Log::error("Lỗi thêm Product: " . $e->getMessage());
            return back()->withInput()->with('error', 'Lỗi hệ thống: ' . $e->getMessage());
        }
    }

    /**
     * Form chỉnh sửa
     */
    public function edit($id)
    {
        $product = Product::findOrFail($id);
        $brands = Brand::orderBy('name', 'asc')->get();
        $categories = Category::orderBy('level', 'asc')->orderBy('name', 'asc')->get();
        
        $selectedCategories = $product->categories->pluck('id')->toArray();

        return view('admin.products.edit', compact('product', 'brands', 'categories', 'selectedCategories'));
    }

    /**
     * Xử lý Cập nhật (Cao cấp - Có xử lý xóa ảnh Gallery)
     */
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $this->validateProduct($request, $id);

        DB::beginTransaction();
        try {
            $data = $this->parseProductData($request);

            // Cập nhật slug nếu tên đổi
            if ($product->name != $request->name) {
                $data['slug'] = Str::slug($request->name);
            }

            // 1. Xử lý Thumbnail mới (nếu có)
            if ($request->hasFile('thumbnail')) {
                $this->deleteFile($product->thumbnail); // Xóa cũ
                $data['thumbnail'] = $this->uploadFile($request->file('thumbnail'), 'products/thumbnails'); // Up mới
            }

            // 2. Xử lý Gallery (Phức tạp: Giữ cũ - Xóa chọn lọc + Thêm mới)
            $currentGallery = $product->gallery ?? [];

            // A. Xóa các ảnh được đánh dấu xóa (Từ input hidden hoặc checkbox ở View)
            // Gửi lên mảng: remove_gallery[] = "products/gallery/abc.jpg"
            if ($request->filled('remove_gallery')) {
                $removeList = $request->input('remove_gallery');
                foreach ($removeList as $pathToRemove) {
                    $this->deleteFile($pathToRemove); // Xóa file vật lý
                    // Xóa khỏi mảng dữ liệu
                    $key = array_search($pathToRemove, $currentGallery);
                    if ($key !== false) unset($currentGallery[$key]);
                }
            }

            // B. Thêm ảnh mới vào Gallery
            if ($request->hasFile('gallery')) {
                foreach ($request->file('gallery') as $file) {
                    $currentGallery[] = $this->uploadFile($file, 'products/gallery');
                }
            }
            
            // Đánh lại chỉ số mảng (Re-index) sau khi unset để tránh lỗi JSON object
            $data['gallery'] = array_values($currentGallery);

            // 3. Lưu dữ liệu
            $product->update($data);
            $product->categories()->sync($request->category_ids);

            DB::commit();
            return redirect()->route('admin.products.index')->with('success', 'Cập nhật sản phẩm thành công!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Lỗi sửa Product ID $id: " . $e->getMessage());
            return back()->withInput()->with('error', 'Lỗi hệ thống: ' . $e->getMessage());
        }
    }

    /**
     * Xóa mềm (Soft Delete)
     */
    public function destroy($id)
    {
        try {
            $product = Product::findOrFail($id);
            $product->delete(); // Soft delete
            return back()->with('success', 'Đã chuyển sản phẩm vào thùng rác.');
        } catch (\Exception $e) {
            return back()->with('error', 'Lỗi xóa sản phẩm.');
        }
    }

    // =========================================================================
    // PRIVATE HELPER METHODS (Giúp code gọn gàng, dễ bảo trì)
    // =========================================================================

    /**
     * Validate dữ liệu chung cho Store và Update
     */
    private function validateProduct(Request $request, $id = null)
    {
        $rules = [
            'name'           => ['required', 'string', 'max:255', Rule::unique('products')->ignore($id)],
            'sku_code'       => ['nullable', 'string', 'max:50', Rule::unique('products')->ignore($id)],
            'brand_id'       => 'required|exists:brands,id',
            'category_ids'   => 'required|array',
            'category_ids.*' => 'exists:categories,id',
            'price_min'      => 'required|numeric|min:0',
            'status'         => 'required|in:draft,published,archived',
        ];

        // Ảnh: Nếu là thêm mới thì bắt buộc, sửa thì không
        if (!$id) {
            $rules['thumbnail'] = 'required|image|mimes:jpeg,png,jpg,webp|max:3072';
        } else {
            $rules['thumbnail'] = 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072';
        }

        $rules['gallery.*'] = 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072';

        $request->validate($rules, [
            'brand_id.required' => 'Vui lòng chọn thương hiệu.',
            'category_ids.required' => 'Vui lòng chọn ít nhất một danh mục.',
            'price_min.min' => 'Giá bán không được âm.',
        ]);
    }

    /**
     * Xử lý dữ liệu đầu vào (Sanitize)
     */
    private function parseProductData(Request $request)
    {
        return [
            'name'              => strip_tags($request->name),
            'sku_code'          => strip_tags($request->sku_code),
            'description'       => $request->description, // Giữ nguyên HTML cho WYSIWYG Editor
            'short_description' => strip_tags($request->short_description),
            'brand_id'          => $request->brand_id,
            'price_min'         => $request->price_min,
            'status'            => $request->status,
            'is_featured'       => $request->has('is_featured'),
            'slug'              => Str::slug($request->name),
        ];
    }

    /**
     * Upload file lên Storage
     */
    private function uploadFile($file, $folder)
    {
        // Tạo tên file ngẫu nhiên + time để tránh trùng
        $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
        return $file->storeAs($folder, $filename, 'public');
    }

    /**
     * Xóa file khỏi Storage
     */
    private function deleteFile($path)
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    /**
     * Sinh mã SKU tự động
     */
    private function generateSku()
    {
        return 'SKU-' . strtoupper(Str::random(8));
    }

    /**
     * Dọn dẹp file khi transaction lỗi
     */
    private function cleanupFailedUploads($thumbnail, $gallery)
    {
        if ($thumbnail) $this->deleteFile($thumbnail);
        if (!empty($gallery)) {
            foreach ($gallery as $path) $this->deleteFile($path);
        }
    }
}