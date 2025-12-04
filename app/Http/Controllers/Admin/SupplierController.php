<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SupplierController extends Controller
{
    /**
     * DANH SÁCH NHÀ CUNG CẤP
     * - Hỗ trợ tìm kiếm đa năng (Tên, Mã, SĐT, Email)
     * - Phân trang
     */
    public function index(Request $request)
    {
        $query = Supplier::withCount('purchaseOrders') // Đếm số phiếu nhập để biết NCC nào quan trọng
            ->latest('id');

        // Tìm kiếm
        if ($request->filled('keyword')) {
            $keyword = trim($request->keyword);
            $query->where(function($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                  ->orWhere('code', 'like', "%{$keyword}%")
                  ->orWhere('phone', 'like', "%{$keyword}%")
                  ->orWhere('email', 'like', "%{$keyword}%");
            });
        }

        $suppliers = $query->paginate(10)->withQueryString();

        return view('admin.inventory.suppliers.index', compact('suppliers'));
    }

    /**
     * FORM TẠO MỚI
     */
    public function create()
    {
        return view('admin.inventory.suppliers.create');
    }

    /**
     * LƯU NHÀ CUNG CẤP (STORE)
     */
    public function store(Request $request)
    {
        // 1. Chuẩn hóa dữ liệu trước khi validate
        $request->merge([
            'code' => strtoupper(trim($request->code)), // Mã luôn viết hoa
        ]);

        // 2. Validate chặt chẽ
        $validated = $request->validate([
            'code'         => ['required', 'string', 'max:20', 'unique:suppliers,code', 'regex:/^[A-Z0-9\-]+$/'],
            'name'         => ['required', 'string', 'max:255'],
            'contact_name' => ['nullable', 'string', 'max:100'],
            'phone'        => ['nullable', 'regex:/^(0)[0-9]{9}$/'], // Định dạng số VN
            'email'        => ['nullable', 'email', 'max:255'],
            'address'      => ['nullable', 'string', 'max:500'],
        ], [
            'code.required' => 'Vui lòng nhập Mã nhà cung cấp.',
            'code.unique'   => 'Mã này đã tồn tại, vui lòng chọn mã khác.',
            'code.regex'    => 'Mã chỉ được chứa chữ cái in hoa, số và dấu gạch ngang.',
            'name.required' => 'Tên nhà cung cấp là bắt buộc.',
            'phone.regex'   => 'Số điện thoại không đúng định dạng.',
            'email.email'   => 'Email không hợp lệ.',
        ]);

        try {
            Supplier::create($validated);

            return redirect()->route('admin.suppliers.index')
                ->with('success', 'Thêm nhà cung cấp mới thành công.');

        } catch (\Exception $e) {
            Log::error("Lỗi tạo NCC: " . $e->getMessage());
            return back()->with('error', 'Lỗi hệ thống. Vui lòng thử lại.')->withInput();
        }
    }

    /**
     * FORM CHỈNH SỬA
     */
    public function edit($id)
    {
        $supplier = Supplier::findOrFail($id);
        return view('admin.inventory.suppliers.edit', compact('supplier'));
    }

    /**
     * CẬP NHẬT (UPDATE)
     */
    public function update(Request $request, $id)
    {
        $supplier = Supplier::findOrFail($id);

        $request->merge([
            'code' => strtoupper(trim($request->code)),
        ]);

        $validated = $request->validate([
            // Ignore ID hiện tại để không báo lỗi trùng chính nó
            'code'         => ['required', 'string', 'max:20', Rule::unique('suppliers')->ignore($supplier->id), 'regex:/^[A-Z0-9\-]+$/'],
            'name'         => ['required', 'string', 'max:255'],
            'contact_name' => ['nullable', 'string', 'max:100'],
            'phone'        => ['nullable', 'regex:/^(0)[0-9]{9}$/'],
            'email'        => ['nullable', 'email', 'max:255'],
            'address'      => ['nullable', 'string', 'max:500'],
        ], [
            'code.unique'   => 'Mã nhà cung cấp này đã được sử dụng.',
            'phone.regex'   => 'Số điện thoại không đúng định dạng.',
        ]);

        try {
            $supplier->update($validated);

            return redirect()->route('admin.suppliers.index')
                ->with('success', 'Cập nhật thông tin nhà cung cấp thành công.');

        } catch (\Exception $e) {
            Log::error("Lỗi update NCC ID $id: " . $e->getMessage());
            return back()->with('error', 'Lỗi hệ thống. Vui lòng thử lại.');
        }
    }

    /**
     * XÓA (DESTROY) - CÓ KIỂM TRA RÀNG BUỘC
     */
    public function destroy($id)
    {
        $supplier = Supplier::withCount('purchaseOrders')->findOrFail($id);

        // 1. Kiểm tra tính toàn vẹn (Data Integrity Rule)
        // Không được xóa NCC đã từng nhập hàng, vì sẽ làm hỏng lịch sử nhập kho (Purchase Orders)
        if ($supplier->purchase_orders_count > 0) {
            return back()->with('error', 'Không thể xóa NCC này vì đã có lịch sử nhập hàng. Hãy chỉnh sửa thông tin thay vì xóa.');
        }

        try {
            $supplier->delete();
            return back()->with('success', 'Đã xóa nhà cung cấp khỏi hệ thống.');
        } catch (\Exception $e) {
            Log::error("Lỗi xóa NCC ID $id: " . $e->getMessage());
            return back()->with('error', 'Không thể xóa dữ liệu này.');
        }
    }
}