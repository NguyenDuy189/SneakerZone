<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryLog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Gate;

class InventoryLogController extends Controller
{
    public function index(Request $request)
    {
        // Quyền: nếu bạn đã cấu hình Gate 'inventory.view' thì kiểm tra, nếu không, xóa dòng này.
        if (function_exists('Gate') && Gate::allows('inventory.view') === false) {
            abort(403, 'Bạn không có quyền truy cập danh sách lịch sử kho.');
        }

        // --- BUILD ALLOWED TYPES (AN TOÀN) ---
        $allowedTypes = [];
        // Nếu model định nghĩa constants, sử dụng chúng
        if (defined(InventoryLog::class . '::TYPE_IMPORT')) {
            $allowedTypes[] = constant(InventoryLog::class . '::TYPE_IMPORT');
        }
        if (defined(InventoryLog::class . '::TYPE_EXPORT')) {
            $allowedTypes[] = constant(InventoryLog::class . '::TYPE_EXPORT');
        }
        if (defined(InventoryLog::class . '::TYPE_ADJUST')) {
            $allowedTypes[] = constant(InventoryLog::class . '::TYPE_ADJUST');
        }

        // Nếu không có constant nào, fallback sang bộ mặc định
        if (empty($allowedTypes)) {
            $allowedTypes = ['import', 'export', 'adjust'];
        }

        // Nếu có allowedTypes thì rule in, ngược lại fallback rule an toàn
        $typeRule = !empty($allowedTypes) ? ['nullable', Rule::in($allowedTypes)] : ['nullable', 'string', 'max:50'];

        // VALIDATE input filters (rất chặt)
        $request->validate([
            'keyword'     => ['nullable', 'string', 'max:150'],
            'type'        => $typeRule,
            'date'        => ['nullable', 'date'],
            'from'        => ['nullable', 'date'],
            'to'          => ['nullable', 'date', 'after_or_equal:from'],
            'user_id'     => ['nullable', 'integer', 'exists:users,id'],
            'variant_id'  => ['nullable', 'integer', 'exists:product_variants,id'],
            'reference_id'=> ['nullable', 'integer'],
            'per_page'    => ['nullable', 'integer', 'in:10,20,50,100'],
        ], [
            'keyword.max' => 'Từ khóa tìm kiếm tối đa 150 ký tự.',
            'type.in'     => 'Loại giao dịch không hợp lệ.',
            'date.date'   => 'Ngày không hợp lệ.',
            'to.after_or_equal' => 'Ngày kết thúc phải lớn hơn hoặc bằng ngày bắt đầu.',
            'user_id.exists' => 'Người dùng không tồn tại.',
            'variant_id.exists' => 'Phiên bản sản phẩm không tồn tại.',
            'per_page.in' => 'Giá trị phân trang không hợp lệ.',
        ]);

        // Build query với eager load
        $query = InventoryLog::with(['variant.product', 'user'])->latest('id');

        // Keyword: tìm theo SKU hoặc tên sản phẩm (safe: trim + limit)
        if ($request->filled('keyword')) {
            $keyword = mb_substr(trim($request->keyword), 0, 150);
            $query->where(function ($q) use ($keyword) {
                $q->whereHas('variant', function ($q2) use ($keyword) {
                    $q2->where('sku', 'like', "%{$keyword}%");
                })->orWhereHas('variant.product', function ($q3) use ($keyword) {
                    $q3->where('name', 'like', "%{$keyword}%");
                });
            });
        }

        // Type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Single date
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        // Date range
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        // User filter
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Variant filter (product_variant_id)
        if ($request->filled('variant_id')) {
            $query->where('product_variant_id', $request->variant_id);
        }

        // Reference filter (ví dụ PO id / SO id)
        if ($request->filled('reference_id')) {
            $query->where('reference_id', $request->reference_id);
        }

        // Pagination size (validate đã đảm bảo per_page hợp lệ)
        $perPage = (int) $request->input('per_page', 20);
        if (!in_array($perPage, [10,20,50,100])) {
            $perPage = 20;
        }

        $logs = $query->paginate($perPage)->withQueryString();

        // Dữ liệu hỗ trợ view (tiếng Việt)
        $filterData = [
            'keyword'     => $request->keyword,
            'type'        => $request->type,
            'date'        => $request->date,
            'from'        => $request->from,
            'to'          => $request->to,
            'user_id'     => $request->user_id,
            'variant_id'  => $request->variant_id,
            'reference_id'=> $request->reference_id,
            'per_page'    => $perPage,
        ];

        $typeLabels = [
            'import'  => 'Nhập kho',
            'export'  => 'Xuất kho',
            'adjust'  => 'Điều chỉnh',
        ];

        return view('admin.inventory.logs.index', [
            'logs' => $logs,
            'filters' => $filterData,
            'typeLabels' => $typeLabels,
            'allowedTypes' => $allowedTypes,
        ]);
    }

    /**
     * Hiển thị chi tiết một log
     */
    public function show($id)
    {
        $log = InventoryLog::with(['variant.product', 'user'])->findOrFail($id);

        // Quyền xem chi tiết (dùng Gate nếu bạn đã định nghĩa)
        if (function_exists('Gate') && Gate::denies('inventory.view')) {
            abort(403, 'Bạn không có quyền xem chi tiết lịch sử kho.');
        }

        return view('admin.inventory.logs.show', [
            'log' => $log
        ]);
    }
}
