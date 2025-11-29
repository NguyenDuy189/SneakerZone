<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class CategoryController extends Controller
{
    public function index()
    {
        // Sắp xếp theo level để danh mục cha hiện trước
        $categories = Category::with('parent')->orderBy('level', 'asc')->orderBy('id', 'desc')->paginate(10);
        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        // Lấy danh sách để chọn làm cha (chỉ lấy tên và id)
        // Sắp xếp theo tên để dễ tìm
        $parents = Category::orderBy('name', 'asc')->get();
        return view('admin.categories.create', compact('parents'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'parent_id' => 'nullable|exists:categories,id'
        ], [
            'name.required' => 'Vui lòng nhập tên danh mục.',
            'image.image' => 'File tải lên phải là hình ảnh.',
            'image.max' => 'Ảnh tối đa 2MB.',
        ]);

        try {
            $data = $request->except(['image']);
            $data['slug'] = Str::slug($request->name);
            $data['is_visible'] = $request->has('is_visible') ? true : false;

            // --- LOGIC TÍNH CẤP ĐỘ (LEVEL) ---
            if ($request->parent_id) {
                $parent = Category::find($request->parent_id);
                $data['level'] = $parent->level + 1;
            } else {
                $data['parent_id'] = null;
                $data['level'] = 0; // Cấp cao nhất
            }

            // Xử lý ảnh
            if ($request->hasFile('image')) {
                $data['image_url'] = $request->file('image')->store('categories', 'public');
            }

            Category::create($data);

            return redirect()->route('admin.categories.index')
                ->with('success', 'Thêm danh mục mới thành công!');

        } catch (\Exception $e) {
            Log::error("Lỗi thêm Category: " . $e->getMessage());
            return back()->withInput()->with('error', 'Lỗi hệ thống, vui lòng thử lại sau.');
        }
    }

    public function edit($id)
    {
        $category = Category::findOrFail($id);
        // Lấy danh sách cha, TRỪ CHÍNH NÓ ra (tránh lỗi vòng lặp)
        $parents = Category::where('id', '!=', $id)->orderBy('name', 'asc')->get();
        
        return view('admin.categories.edit', compact('category', 'parents'));
    }

    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            // Không được chọn chính mình làm cha
            'parent_id' => 'nullable|not_in:'.$id.'|exists:categories,id'
        ], [
            'name.required' => 'Tên danh mục không được để trống.',
            'parent_id.not_in' => 'Không thể chọn chính danh mục này làm danh mục cha.',
        ]);

        try {
            $data = $request->except(['image']);
            
            // Cập nhật slug nếu tên đổi
            if ($category->name != $request->name) {
                $data['slug'] = Str::slug($request->name);
            }
            
            $data['is_visible'] = $request->has('is_visible') ? true : false;

            // --- TÍNH LẠI LEVEL ---
            if ($request->parent_id) {
                $parent = Category::find($request->parent_id);
                $data['level'] = $parent->level + 1;
            } else {
                $data['parent_id'] = null;
                $data['level'] = 0;
            }

            // Xử lý ảnh
            if ($request->hasFile('image')) {
                if ($category->image_url && Storage::disk('public')->exists($category->image_url)) {
                    Storage::disk('public')->delete($category->image_url);
                }
                $data['image_url'] = $request->file('image')->store('categories', 'public');
            }

            $category->update($data);

            return redirect()->route('admin.categories.index')
                ->with('success', 'Cập nhật danh mục thành công!');

        } catch (\Exception $e) {
            Log::error("Lỗi sửa Category ID $id: " . $e->getMessage());
            return back()->withInput()->with('error', 'Lỗi hệ thống, không thể cập nhật.');
        }
    }

    public function destroy($id)
    {
        try {
            $category = Category::findOrFail($id);

            // Kiểm tra ràng buộc: Nếu có con thì không cho xóa
            if ($category->children()->count() > 0) {
                return back()->with('error', 'Không thể xóa! Danh mục này đang chứa các danh mục con. Vui lòng xóa hoặc di chuyển danh mục con trước.');
            }

            if ($category->image_url && Storage::disk('public')->exists($category->image_url)) {
                Storage::disk('public')->delete($category->image_url);
            }

            $category->delete();

            return redirect()->route('admin.categories.index')
                ->with('success', 'Đã xóa danh mục.');

        } catch (\Exception $e) {
            Log::error("Lỗi xóa Category ID $id: " . $e->getMessage());
            return back()->with('error', 'Lỗi hệ thống khi xóa.');
        }
    }
}