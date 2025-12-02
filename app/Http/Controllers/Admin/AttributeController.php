<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule; // Thêm thư viện này để check trùng lặp nâng cao

class AttributeController extends Controller
{
    // 1. Danh sách thuộc tính
    public function index()
    {
        $attributes = Attribute::withCount('values')->latest()->paginate(10);
        return view('admin.attributes.index', compact('attributes'));
    }

    // 2. Lưu thuộc tính cha (Có check trùng tên)
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:attributes,name',
            'type' => 'required|in:text,color,image'
        ], [
            'name.required' => 'Vui lòng nhập tên thuộc tính.',
            'name.unique' => 'Tên thuộc tính này đã tồn tại (VD: Size, Màu sắc...).',
            'type.required' => 'Vui lòng chọn loại hiển thị.'
        ]);

        Attribute::create([
            'name' => $request->name,
            'code' => Str::slug($request->name),
            'type' => $request->type,
            'is_filterable' => true
        ]);

        return back()->with('success', 'Đã thêm thuộc tính mới thành công.');
    }

    // 3. Trang chi tiết (Cấu hình giá trị)
    public function show($id)
    {
        // Lấy thuộc tính và các giá trị con (Sắp xếp theo vị trí hoặc ID)
        $attribute = Attribute::with(['values' => function($q) {
            $q->orderBy('position', 'asc')->orderBy('id', 'asc');
        }])->findOrFail($id);

        return view('admin.attributes.show', compact('attribute'));
    }

    // 4. Xóa thuộc tính cha
    public function destroy($id)
    {
        Attribute::destroy($id);
        return back()->with('success', 'Đã xóa thuộc tính và toàn bộ giá trị con.');
    }

    // --- PHẦN QUẢN LÝ GIÁ TRỊ CON (VALUES) ---

    // 5. Thêm giá trị con (Có check trùng giá trị trong cùng 1 nhóm)
    public function storeValue(Request $request, $id)
    {
        // Validate: Giá trị này đã tồn tại trong thuộc tính này chưa?
        $request->validate([
            'value' => [
                'required',
                'string',
                'max:255',
                Rule::unique('attribute_values')->where(function ($query) use ($id) {
                    return $query->where('attribute_id', $id);
                })
            ],
            'meta_value' => 'nullable|string|max:255'
        ], [
            'value.required' => 'Vui lòng nhập giá trị.',
            'value.unique' => 'Giá trị này đã tồn tại trong danh sách.',
        ]);

        // Tự động tính vị trí tiếp theo (để sắp xếp)
        $nextPosition = AttributeValue::where('attribute_id', $id)->max('position') + 1;

        AttributeValue::create([
            'attribute_id' => $id,
            'value' => $request->value,       // Tên hiển thị (Đỏ, Xanh, 40, XL)
            'meta_value' => $request->meta_value, // Mã màu (#FF0000)
            'position' => $nextPosition
        ]);

        return back()->with('success', 'Đã thêm giá trị mới.');
    }

    // 6. Xóa giá trị con
    public function destroyValue($id)
    {
        AttributeValue::destroy($id);
        return back()->with('success', 'Đã xóa giá trị.');
    }
}