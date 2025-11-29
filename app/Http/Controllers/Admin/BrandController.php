<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log; // Dùng để ghi log lỗi cho dev xem ngầm

class BrandController extends Controller
{
    /**
     * Danh sách thương hiệu
     */
    public function index()
    {
        // Sử dụng paginate để tránh load quá nhiều dữ liệu gây crash nếu bảng lớn
        $brands = Brand::latest()->paginate(10);
        return view('admin.brands.index', compact('brands'));
    }

    /**
     * Form thêm mới
     */
    public function create()
    {
        return view('admin.brands.create');
    }

    /**
     * Xử lý thêm mới (Logic quan trọng)
     */
    public function store(Request $request)
    {
        // 1. Validate dữ liệu đầu vào với thông báo Tiếng Việt
        $request->validate([
            'name' => 'required|string|max:255|unique:brands,name',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Tối đa 2MB
            'description' => 'nullable|string',
        ], [
            'name.required' => 'Tên thương hiệu không được để trống.',
            'name.unique' => 'Tên thương hiệu này đã tồn tại, vui lòng chọn tên khác.',
            'name.max' => 'Tên thương hiệu không được vượt quá 255 ký tự.',
            'logo.image' => 'File tải lên phải là hình ảnh.',
            'logo.mimes' => 'Ảnh chỉ chấp nhận định dạng: jpeg, png, jpg, gif, svg.',
            'logo.max' => 'Dung lượng ảnh không được vượt quá 2MB.',
        ]);

        try {
            $data = $request->only(['name', 'description']);
            $data['slug'] = Str::slug($request->name);

            // 2. Xử lý upload ảnh an toàn
            if ($request->hasFile('logo') && $request->file('logo')->isValid()) {
                $data['logo_url'] = $request->file('logo')->store('brands', 'public');
            }

            // 3. Tạo dữ liệu
            Brand::create($data);

            return redirect()->route('admin.brands.index')
                ->with('success', 'Thêm thương hiệu mới thành công!');

        } catch (\Exception $e) {
            // Ghi log lỗi hệ thống (người dùng không thấy, dev thấy)
            Log::error("Lỗi thêm Brand: " . $e->getMessage());

            // Trả về thông báo lỗi thân thiện cho người dùng
            return redirect()->back()
                ->withInput() // Giữ lại dữ liệu cũ để họ không phải nhập lại
                ->with('error', 'Đã xảy ra lỗi hệ thống. Vui lòng thử lại sau.');
        }
    }

    /**
     * Form chỉnh sửa
     */
    public function edit($id)
    {
        // Dùng findOrFail để nếu ID sai sẽ báo lỗi 404 chuẩn thay vì crash
        $brand = Brand::findOrFail($id);
        return view('admin.brands.edit', compact('brand'));
    }

    /**
     * Xử lý cập nhật (Logic quan trọng)
     */
    public function update(Request $request, $id)
    {
        $brand = Brand::findOrFail($id);

        // 1. Validate (Chú ý phần unique phải bỏ qua ID hiện tại)
        $request->validate([
            'name' => 'required|string|max:255|unique:brands,name,' . $brand->id,
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ], [
            'name.required' => 'Tên thương hiệu không được để trống.',
            'name.unique' => 'Tên thương hiệu đã tồn tại.',
            'logo.image' => 'File tải lên phải là hình ảnh.',
            'logo.max' => 'Dung lượng ảnh quá lớn (tối đa 2MB).',
        ]);

        try {
            $data = $request->only(['name', 'description']);
            // Nếu đổi tên thì cập nhật lại slug, không thì thôi
            if ($brand->name != $request->name) {
                $data['slug'] = Str::slug($request->name);
            }

            // 2. Xử lý ảnh: Nếu có ảnh mới -> Xóa ảnh cũ -> Lưu ảnh mới
            if ($request->hasFile('logo') && $request->file('logo')->isValid()) {
                // Xóa ảnh cũ nếu tồn tại
                if ($brand->logo_url && Storage::disk('public')->exists($brand->logo_url)) {
                    Storage::disk('public')->delete($brand->logo_url);
                }
                
                // Lưu ảnh mới
                $data['logo_url'] = $request->file('logo')->store('brands', 'public');
            }

            $brand->update($data);

            return redirect()->route('admin.brands.index')
                ->with('success', 'Cập nhật thương hiệu thành công!');

        } catch (\Exception $e) {
            Log::error("Lỗi sửa Brand ID $id: " . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Không thể cập nhật dữ liệu. Vui lòng thử lại.');
        }
    }

    /**
     * Xử lý xóa
     */
    public function destroy($id)
    {
        try {
            $brand = Brand::findOrFail($id);

            // 1. Xóa ảnh trong Storage trước để không sinh rác
            if ($brand->logo_url && Storage::disk('public')->exists($brand->logo_url)) {
                Storage::disk('public')->delete($brand->logo_url);
            }

            // 2. Xóa dữ liệu trong DB
            $brand->delete();

            return redirect()->route('admin.brands.index')
                ->with('success', 'Đã xóa thương hiệu thành công.');

        } catch (\Exception $e) {
            Log::error("Lỗi xóa Brand ID $id: " . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Có lỗi xảy ra khi xóa dữ liệu.');
        }
    }
}