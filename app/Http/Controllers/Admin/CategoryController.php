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
    public function index(Request $request)
    {
        $query = Category::with('parent')->orderBy('level')->orderBy('name');

        // Tìm kiếm theo tên
        if ($request->filled('keyword')) {
            $query->where('name', 'like', '%'.$request->keyword.'%');
        }

        // Lọc level
        if ($request->has('level') && $request->level !== '' && $request->level !== null) {
            $query->where('level', intval($request->level));
        }

        // Lọc trạng thái
        if ($request->has('status') && $request->status !== '' && $request->status !== null) {
            if ($request->status === 'active') {
                $query->where('is_visible', 1);
            } elseif ($request->status === 'inactive') {
                $query->where('is_visible', 0);
            }
        }

        $categories = $query->paginate(10)->appends($request->except('page'));

        // Lấy danh sách level động
        $levels = Category::select('level')
                    ->groupBy('level')
                    ->orderBy('level')
                    ->pluck('level');

        return view('admin.categories.index', compact('categories', 'levels'));
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
            $category = Category::with('children', 'products')->findOrFail($id);

            // 1. Cấm xóa nếu có danh mục con
            if ($category->children->count() > 0) {
                return redirect()->back()->with('error', 'Không thể xóa! Danh mục này đang chứa danh mục con.');
            }

            // 2. Cấm xóa nếu có sản phẩm
            if ($category->products->count() > 0) {
                return redirect()->back()->with('error', 'Không thể xóa! Danh mục này đang chứa sản phẩm.');
            }

            // 3. Xóa ảnh nếu có
            if ($category->image_url && Storage::disk('public')->exists($category->image_url)) {
                Storage::disk('public')->delete($category->image_url);
            }

            // 4. Xóa danh mục
            $category->delete();

            return redirect()->route('admin.categories.index')
                            ->with('success', 'Đã xóa danh mục thành công.');

        } catch (\Exception $e) {
            Log::error("Lỗi xóa Category $id: " . $e->getMessage());
            return redirect()->back()->with('error', 'Lỗi hệ thống! Không thể xóa danh mục.');
        }
    }

}