<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryLog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Gate;

class InventoryLogController extends Controller
{
    /**
     * Danh sách lịch sử kho
     */
    public function index(Request $request)
    {
        // 1. Kiểm tra quyền (Nếu có sử dụng Gate)
        if (function_exists('Gate') && Gate::allows('inventory.view') === false) {
            abort(403, 'Bạn không có quyền truy cập danh sách lịch sử kho.');
        }

        // 2. ĐỊNH NGHĨA CÁC LOẠI GIAO DỊCH (Quan trọng: Phải khớp với View)
        // View đang gửi lên: import, sale, return, check
        $allowedTypes = [
            'import', // Nhập hàng
            'sale',   // Bán hàng
            'return', // Trả hàng/Hoàn hàng
            'check',  // Kiểm kê
            'export', // Xuất kho khác (nếu có dùng)
            'adjust'  // Điều chỉnh khác (nếu có dùng)
        ];

        // 3. VALIDATE DỮ LIỆU ĐẦU VÀO
        $request->validate([
            'keyword'      => ['nullable', 'string', 'max:150'],
            'type'         => ['nullable', Rule::in($allowedTypes)], // Chỉ cho phép các loại đã định nghĩa
            'date'         => ['nullable', 'date'],
            'from'         => ['nullable', 'date'],
            'to'           => ['nullable', 'date', 'after_or_equal:from'],
            'user_id'      => ['nullable', 'integer', 'exists:users,id'],
            'variant_id'   => ['nullable', 'integer', 'exists:product_variants,id'],
            'reference_id' => ['nullable', 'integer'],
            'per_page'     => ['nullable', 'integer', 'in:10,20,50,100'],
        ], [
            'type.in' => 'Loại giao dịch không hợp lệ.',
            'to.after_or_equal' => 'Ngày kết thúc phải lớn hơn hoặc bằng ngày bắt đầu.',
        ]);

        // 4. XÂY DỰNG QUERY
        // Eager load: variant (kèm product), user để tránh N+1 query
        $query = InventoryLog::with(['variant.product', 'user'])->latest('id');

        // --- Lọc theo từ khóa (Tên SP hoặc SKU) ---
        if ($request->filled('keyword')) {
            $keyword = trim($request->keyword);
            $query->where(function ($q) use ($keyword) {
                // Tìm theo SKU biến thể
                $q->whereHas('variant', function ($q2) use ($keyword) {
                    $q2->where('sku', 'like', "%{$keyword}%");
                })
                // Hoặc tìm theo Tên sản phẩm cha
                ->orWhereHas('variant.product', function ($q3) use ($keyword) {
                    $q3->where('name', 'like', "%{$keyword}%");
                });
            });
        }

        // --- Lọc theo loại giao dịch (Type) ---
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // --- Lọc theo ngày cụ thể ---
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        // --- Lọc theo khoảng thời gian (Từ ngày - Đến ngày) ---
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        // --- Các bộ lọc ID khác ---
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('variant_id')) {
            $query->where('product_variant_id', $request->variant_id);
        }
        if ($request->filled('reference_id')) {
            $query->where('reference_id', $request->reference_id);
        }

        // 5. PHÂN TRANG
        $perPage = $request->input('per_page', 20);
        $logs = $query->paginate($perPage)->withQueryString();

        // 6. CHUẨN BỊ DỮ LIỆU TRẢ VỀ VIEW
        
        // Mảng label để hiển thị tiếng Việt đẹp mắt
        $typeLabels = [
            'import' => 'Nhập kho',
            'sale'   => 'Bán hàng',
            'return' => 'Trả hàng',
            'check'  => 'Kiểm kê',
            'export' => 'Xuất kho',
            'adjust' => 'Điều chỉnh',
        ];

        // Dữ liệu filter để điền lại vào form (re-populate)
        $filterData = [
            'keyword'      => $request->keyword,
            'type'         => $request->type,
            'date'         => $request->date,
            'from'         => $request->from,
            'to'           => $request->to,
            'user_id'      => $request->user_id,
            'variant_id'   => $request->variant_id,
            'reference_id' => $request->reference_id,
            'per_page'     => $perPage,
        ];

        return view('admin.inventory.logs.index', [
            'logs'         => $logs,
            'filters'      => $filterData,
            'typeLabels'   => $typeLabels,
            'allowedTypes' => $allowedTypes,
        ]);
    }

    /**
     * Hiển thị chi tiết một log
     */
    public function show($id)
    {
        $log = InventoryLog::with(['variant.product', 'user'])->findOrFail($id);

        if (function_exists('Gate') && Gate::allows('inventory.view') === false) {
            abort(403, 'Bạn không có quyền xem chi tiết.');
        }

        return view('admin.inventory.logs.show', compact('log'));
    }
}