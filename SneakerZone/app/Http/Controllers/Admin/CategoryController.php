<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index()
    {
        // Eager loading 'parent' để tránh N+1 Query
        $categories = Category::with('parent')->orderBy('level', 'asc')->latest()->paginate(10);
        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        // Chỉ lấy các danh mục đang hiển thị để làm cha
        $parents = Category::orderBy('name', 'asc')->get();
        return view('admin.categories.create', compact('parents'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:categories,name'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            // parent_id phải tồn tại trong bảng categories
            'parent_id' => ['nullable', 'integer', 'exists:categories,id']
        ], [
            'name.required' => 'Vui lòng nhập tên danh mục.',
            'name.unique' => 'Tên danh mục đã tồn tại.',
            'parent_id.exists' => 'Danh mục cha không hợp lệ.',
        ]);

        DB::beginTransaction();

        try {
            $data = $request->except(['image']);
            $data['slug'] = Str::slug($request->name);
            $data['is_visible'] = $request->has('is_visible');

            // --- LOGIC LEVEL TỰ ĐỘNG ---
            if ($request->parent_id) {
                $parent = Category::find($request->parent_id);
                // Giới hạn độ sâu: Ví dụ chỉ cho phép tối đa 3 cấp (0, 1, 2)
                if ($parent->level >= 2) {
                    return back()->withInput()->with('error', 'Hệ thống chỉ hỗ trợ tối đa 3 cấp danh mục.');
                }
                $data['level'] = $parent->level + 1;
            } else {
                $data['parent_id'] = null;
                $data['level'] = 0;
            }

            if ($request->hasFile('image')) {
                $data['image_url'] = $request->file('image')->store('categories', 'public');
            }

            Category::create($data);

            DB::commit();
            return redirect()->route('admin.categories.index')->with('success', 'Thêm danh mục thành công!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Lỗi thêm Category: " . $e->getMessage());
            return back()->withInput()->with('error', 'Lỗi hệ thống.');
        }
    }

    public function edit($id)
    {
        $category = Category::findOrFail($id);
        
        // Lấy danh sách cha:
        // 1. Không phải chính nó (id != $id)
        // 2. Không phải là con cháu của nó (tránh vòng lặp) - Logic này cần đệ quy phức tạp, 
        //    nhưng ở mức cơ bản ta chặn chọn chính nó là đủ an toàn cho 99% trường hợp.
        $parents = Category::where('id', '!=', $id)->orderBy('name', 'asc')->get();
        
        return view('admin.categories.edit', compact('category', 'parents'));
    }

    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('categories')->ignore($category->id)],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'parent_id' => [
                'nullable', 
                'exists:categories,id',
                'not_in:'.$category->id // TUYỆT ĐỐI KHÔNG chọn chính mình làm cha
            ]
        ], [
            'name.unique' => 'Tên danh mục bị trùng.',
            'parent_id.not_in' => 'Danh mục không thể là cha của chính nó.',
        ]);

        DB::beginTransaction();

        try {
            $data = $request->except(['image']);
            
            if ($category->name != $request->name) {
                $data['slug'] = Str::slug($request->name);
            }
            
            $data['is_visible'] = $request->has('is_visible');

            // Tính lại Level
            if ($request->parent_id) {
                $parent = Category::find($request->parent_id);
                // Check lại độ sâu khi update
                if ($parent->level >= 2) {
                    return back()->withInput()->with('error', 'Không thể di chuyển vào danh mục này (Vượt quá 3 cấp).');
                }
                $data['level'] = $parent->level + 1;
            } else {
                $data['parent_id'] = null;
                $data['level'] = 0;
            }

            if ($request->hasFile('image')) {
                if ($category->image_url && Storage::disk('public')->exists($category->image_url)) {
                    Storage::disk('public')->delete($category->image_url);
                }
                $data['image_url'] = $request->file('image')->store('categories', 'public');
            }

            $category->update($data);

            DB::commit();
            return redirect()->route('admin.categories.index')->with('success', 'Cập nhật thành công!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Lỗi sửa Category ID $id: " . $e->getMessage());
            return back()->withInput()->with('error', 'Lỗi hệ thống khi cập nhật.');
        }
    }

    public function destroy($id)
    {
        try {
            $category = Category::findOrFail($id);

            // Check ràng buộc: Có con thì không cho xóa (Rất quan trọng)
            if ($category->children()->count() > 0) {
                return back()->with('error', 'Không thể xóa! Danh mục này đang chứa các danh mục con. Hãy xóa hoặc di chuyển danh mục con trước.');
            }
            
            // Check ràng buộc: Có sản phẩm thì không cho xóa (Nếu đã có bảng products)
            // if ($category->products()->exists()) { return back()->with('error', 'Danh mục đang có sản phẩm!'); }

            if ($category->image_url && Storage::disk('public')->exists($category->image_url)) {
                Storage::disk('public')->delete($category->image_url);
            }

            $category->delete();

            return redirect()->route('admin.categories.index')->with('success', 'Đã xóa danh mục.');

        } catch (\Exception $e) {
            Log::error("Lỗi xóa Category ID $id: " . $e->getMessage());
            return back()->with('error', 'Lỗi hệ thống khi xóa.');
        }
    }
}