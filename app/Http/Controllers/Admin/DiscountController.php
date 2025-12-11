<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Discount;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class DiscountController extends Controller
{
    /**
     * Trang danh sách + filter nâng cao
     */
    public function index(Request $request)
    {
        $query = Discount::query()->latest('id');

        // Tìm theo code
        if ($request->filled('keyword')) {
            $keyword = trim($request->keyword);
            $query->where('code', 'like', "%$keyword%");
        }

        // Lọc theo loại
        if ($request->filled('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        // Lọc theo trạng thái
        if ($request->filled('status') && $request->status !== 'all') {

            if ($request->status === 'active') {
                $query->where('start_date', '<=', now())
                    ->where('end_date', '>=', now());
            }

            if ($request->status === 'expired') {
                $query->where('end_date', '<', now());
            }

            if ($request->status === 'upcoming') {
                $query->where('start_date', '>', now());
            }
        }

        $discounts = $query->paginate(10)->withQueryString();

        return view('admin.discounts.index', compact('discounts'));
    }

    /**
     * Form tạo
     */
    public function create()
    {
        return view('admin.discounts.create');
    }

    /**
     * Lưu mới (validate nâng cấp)
     */
    public function store(Request $request)
    {
        // 1. Format lại code trước khi validate
        $request->merge([
            'code' => strtoupper(trim($request->code)),
            // Nếu bỏ trống max_usage hoặc min_order, set về null hoặc 0 để không bị lỗi required
            'max_usage' => $request->max_usage ?? 0,
            'min_order_amount' => $request->min_order_amount ?? 0,
        ]);

        $this->validateData($request);

        Discount::create([
            'code' => $request->code,
            'type' => $request->type,
            'value' => $request->value,
            // Nếu type là fixed thì max_discount_value phải là null
            'max_discount_value' => $request->type === 'fixed' ? null : $request->max_discount_value,
            'max_usage' => $request->max_usage,
            'min_order_amount' => $request->min_order_amount,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'used_count' => 0,
            // SỬA: Lưu thêm trạng thái kích hoạt
            'is_active' => $request->has('is_active') ? true : false,
        ]);

        return redirect()->route('admin.discounts.index')
            ->with('success', 'Tạo mã giảm giá thành công.');
    }

    /**
     * Form chỉnh sửa
     */
    public function edit($id)
    {
        $discount = Discount::findOrFail($id);
        return view('admin.discounts.edit', compact('discount'));
    }

    /**
     * Cập nhật (validate nâng cấp)
     */
    public function update(Request $request, $id)
    {
        $discount = Discount::findOrFail($id);

        $request->merge([
            'code' => strtoupper(trim($request->code)),
        ]);

        $this->validateData($request, $discount->id);

        $discount->update([
            'code' => $request->code,
            'type' => $request->type,
            'value' => $request->value,
            'max_usage' => $request->max_usage,
            'max_discount_value' => $request->max_discount_value, // NEW
            'min_order_amount' => $request->min_order_amount,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);

        return redirect()->route('admin.discounts.index')
            ->with('success', 'Cập nhật mã giảm giá thành công.');
    }

    /**
     * Xóa
     */
    public function destroy($id)
    {
        $discount = Discount::findOrFail($id);

        // 1. Không cho xoá nếu đã được sử dụng
        if ($discount->used_count > 0) {
            return back()->with('error', 'Không thể xoá mã đã được sử dụng.');
        }

        $now = now();

        // 2. Không cho xoá khi voucher đang hoạt động
        $isActiveNow =
            (!empty($discount->start_date) && $discount->start_date <= $now) &&
            (!empty($discount->end_date) && $discount->end_date >= $now);

        if ($isActiveNow) {
            return back()->with('error', 'Mã đang hoạt động, không thể xoá.');
        }

        // 3. Còn lại (upcoming, expired) => có thể xoá
        $discount->delete();

        return back()->with('success', 'Xóa mã giảm giá thành công.');
    }


    /**
     * VALIDATE CHUYÊN NGHIỆP — CHẶT CHẼ — SẢN PHẨM THỰC TẾ
     */
    private function validateData(Request $request, $ignoreId = null)
    {
        Validator::make($request->all(), [
            'code' => [
                'required',
                'string',
                'max:50',
                'regex:/^[A-Z0-9\-]+$/',
                Rule::unique('discounts', 'code')->ignore($ignoreId),
            ],
            'type' => ['required', Rule::in(['percentage', 'fixed'])],

            // Validate giá trị giảm
            'value' => [
                'required',
                'numeric',
                'min:1',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->type === 'percentage' && $value > 100) {
                        $fail('Giảm phần trăm không được quá 100%.');
                    }
                }
            ],

            // Validate giảm tối đa
            'max_discount_value' => [
                'nullable',
                'numeric',
                'min:1000',
                function ($attribute, $value, $fail) use ($request) {
                    // Chỉ check lỗi nếu Type là Fixed VÀ User có nhập giá trị (khác null)
                    // Logic này kết hợp với việc clear input ở View sẽ giải quyết vấn đề
                    if ($request->type === 'fixed' && !empty($value)) {
                        $fail('Loại giảm giá cố định không cần nhập Giảm tối đa.');
                    }
                }
            ],

            // SỬA: Đổi required -> nullable để cho phép bỏ trống (vô hạn)
            'max_usage' => 'nullable|integer|min:0',
            'min_order_amount' => 'nullable|numeric|min:0',

            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ], [
            'code.unique' => 'Mã giảm giá này đã tồn tại.',
            'code.regex' => 'Mã chỉ được chứa chữ in hoa, số và dấu gạch ngang.',
            'end_date.after_or_equal' => 'Ngày kết thúc phải sau ngày bắt đầu.',
        ])->validate();
    }
}
