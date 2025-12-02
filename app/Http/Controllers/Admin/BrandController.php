<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB; // Thêm DB để dùng transaction
use Illuminate\Validation\Rule;

class BrandController extends Controller
{
    public function index()
    {
        $brands = Brand::latest()->paginate(10);
        return view('admin.brands.index', compact('brands'));
    }

    public function create()
    {
        return view('admin.brands.create');
    }

    // --- XỬ LÝ THÊM MỚI (CAO CẤP) ---
    public function store(Request $request)
    {
        // 1. Validate cực chặt
        $request->validate([
            'name' => [
                'required', 
                'string', 
                'max:255', 
                'unique:brands,name',
                'regex:/^[\pL\s0-9\-\.]+$/u' // Chỉ chấp nhận chữ, số, khoảng trắng, gạch ngang (Chống ký tự lạ)
            ], 
            'logo' => [
                'nullable', 
                'image', 
                'mimes:jpeg,png,jpg,svg,webp', 
                'max:3072', // Tối đa 3MB
                'dimensions:min_width=100,min_height=100' // Ảnh tối thiểu 100x100px
            ], 
            'description' => ['nullable', 'string', 'max:1000'],
        ], [
            'name.required' => 'Vui lòng nhập tên thương hiệu.',
            'name.unique' => 'Tên thương hiệu này đã tồn tại.',
            'name.regex' => 'Tên thương hiệu chứa ký tự không hợp lệ.',
            'logo.image' => 'File tải lên phải là hình ảnh.',
            'logo.mimes' => 'Chỉ hỗ trợ định dạng: jpeg, png, jpg, svg, webp.',
            'logo.max' => 'Dung lượng ảnh quá lớn (Tối đa 3MB).',
            'logo.dimensions' => 'Kích thước ảnh quá nhỏ (Tối thiểu 100x100px).',
        ]);

        DB::beginTransaction(); // Bắt đầu giao dịch bảo đảm toàn vẹn dữ liệu

        try {
            // Sanitize: Xóa các thẻ HTML độc hại nếu có
            $data = [
                'name' => strip_tags($request->name),
                'description' => strip_tags($request->description),
                'slug' => Str::slug($request->name)
            ];

            // Xử lý upload ảnh
            if ($request->hasFile('logo')) {
                $data['logo_url'] = $request->file('logo')->store('brands', 'public');
            }

            Brand::create($data);

            DB::commit(); // Mọi thứ OK thì mới lưu vào DB thật sự

            return redirect()->route('admin.brands.index')->with('success', 'Thêm thương hiệu mới thành công!');

        } catch (\Exception $e) {
            DB::rollBack(); // Có lỗi thì hoàn tác lại mọi thứ (kể cả file đã up cũng sẽ không được link vào DB)
            
            // Xóa file rác nếu đã lỡ upload lên mà DB lỗi (Optional cleanup logic here)
            if (isset($data['logo_url'])) Storage::disk('public')->delete($data['logo_url']);

            Log::error("Lỗi thêm Brand: " . $e->getMessage());
            return back()->withInput()->with('error', 'Lỗi hệ thống nghiêm trọng. Vui lòng thử lại sau.');
        }
    }

    public function edit($id)
    {
        $brand = Brand::findOrFail($id);
        return view('admin.brands.edit', compact('brand'));
    }

    // --- XỬ LÝ CẬP NHẬT (CAO CẤP) ---
    public function update(Request $request, $id)
    {
        $brand = Brand::findOrFail($id);

        $request->validate([
            'name' => [
                'required', 
                'string', 
                'max:255', 
                'regex:/^[\pL\s0-9\-\.]+$/u',
                Rule::unique('brands')->ignore($brand->id)
            ],
            'logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,svg,webp', 'max:3072', 'dimensions:min_width=100,min_height=100'],
            'description' => ['nullable', 'string', 'max:1000'],
        ], [
            'name.required' => 'Tên thương hiệu không được để trống.',
            'name.unique' => 'Tên thương hiệu bị trùng.',
            'logo.dimensions' => 'Ảnh logo quá nhỏ hoặc không đúng tỷ lệ.',
        ]);

        DB::beginTransaction();

        try {
            $data = [
                'name' => strip_tags($request->name),
                'description' => strip_tags($request->description),
            ];
            
            if ($brand->name != $request->name) {
                $data['slug'] = Str::slug($request->name);
            }

            if ($request->hasFile('logo')) {
                // Xóa ảnh cũ
                if ($brand->logo_url && Storage::disk('public')->exists($brand->logo_url)) {
                    Storage::disk('public')->delete($brand->logo_url);
                }
                $data['logo_url'] = $request->file('logo')->store('brands', 'public');
            }

            $brand->update($data);

            DB::commit();

            return redirect()->route('admin.brands.index')->with('success', 'Cập nhật thành công!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Lỗi sửa Brand ID $id: " . $e->getMessage());
            return back()->withInput()->with('error', 'Không thể cập nhật dữ liệu.');
        }
    }

    public function destroy($id)
    {
        try {
            $brand = Brand::findOrFail($id);
            
            // Check ràng buộc: Không cho xóa nếu Brand đã có sản phẩm (Logic mở rộng)
            // if ($brand->products()->exists()) { return back()->with('error', 'Hãng này đang có sản phẩm, không thể xóa!'); }

            if ($brand->logo_url && Storage::disk('public')->exists($brand->logo_url)) {
                Storage::disk('public')->delete($brand->logo_url);
            }
            $brand->delete();

            return redirect()->route('admin.brands.index')->with('success', 'Đã xóa thương hiệu.');
        } catch (\Exception $e) {
            Log::error("Lỗi xóa Brand ID $id: " . $e->getMessage());
            return back()->with('error', 'Lỗi hệ thống khi xóa.');
        }
    }
}