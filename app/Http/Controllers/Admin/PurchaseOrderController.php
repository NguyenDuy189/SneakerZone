<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\ProductVariant;
use App\Models\Supplier;
use App\Models\InventoryLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class PurchaseOrderController extends Controller
{
    /**
     * HIỂN THỊ DANH SÁCH PHIẾU NHẬP
     * * Hỗ trợ tìm kiếm, lọc theo trạng thái, nhà cung cấp, ngày tháng.
     */
    public function index(Request $request)
    {
        // Sử dụng Eager Loading để tối ưu truy vấn (tránh lỗi N+1)
        $query = PurchaseOrder::with(['supplier', 'creator'])
            ->withCount('items') // Đếm số sản phẩm trong phiếu
            ->latest('id');

        // 1. Lọc theo từ khóa (Mã phiếu)
        if ($request->filled('keyword')) {
            $keyword = trim($request->keyword);
            $query->where('code', 'like', "%{$keyword}%");
        }

        // 2. Lọc theo trạng thái
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // 3. Lọc theo Nhà cung cấp
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        // 4. Lọc theo ngày tạo
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        $orders = $query->paginate(10)->withQueryString();
        $suppliers = Supplier::select('id', 'name')->orderBy('name')->get();

        return view('admin.inventory.purchase_orders.index', compact('orders', 'suppliers'));
    }

    /**
     * HIỂN THỊ FORM TẠO MỚI
     */
    public function create()
    {
        // Chỉ lấy nhà cung cấp còn hoạt động
        $suppliers = Supplier::whereNull('deleted_at')->orderBy('name')->get();

        // Lấy danh sách sản phẩm để chọn (Lưu ý: Nếu dữ liệu lớn nên dùng AJAX Select2)
        $products = ProductVariant::with('product')
            ->whereNull('deleted_at')
            ->whereHas('product', fn($q) => $q->whereNull('deleted_at'))
            ->get()
            ->map(function ($v) {
                return [
                    'id'    => $v->id,
                    'name'  => $v->product->name . " - " . $v->color . "/" . $v->size,
                    'sku'   => $v->sku,
                    'image' => $v->image_url ?? $v->product->thumbnail,
                    'stock' => $v->stock_quantity,
                    'price' => $v->original_price // Gợi ý giá nhập cũ
                ];
            });

        return view('admin.inventory.purchase_orders.create', compact('suppliers', 'products'));
    }

    /**
     * XỬ LÝ LƯU PHIẾU NHẬP (STORE)
     * * Validate chặt chẽ mảng items, tính toán tổng tiền, sử dụng Transaction.
     */
    public function store(Request $request)
    {
        // 1. Lọc bỏ các dòng item rỗng (User bấm thêm dòng nhưng không nhập)
        $rawItems = $request->input('items', []);
        $cleanItems = array_filter($rawItems, function ($item) {
            return !empty($item['variant_id']) && !empty($item['quantity']) && isset($item['import_price']);
        });
        
        // Merge lại mảng items đã làm sạch vào request để validate
        $request->merge(['items' => $cleanItems]);

        // 2. Validation Rules
        $request->validate([
            'supplier_id' => [
                'required',
                Rule::exists('suppliers', 'id')->whereNull('deleted_at')
            ],
            'expected_at' => 'nullable|date|after_or_equal:today',
            'note'        => 'nullable|string|max:1000',
            'items'       => 'required|array|min:1|max:200',
            
            'items.*.variant_id' => [
                'required',
                'distinct', // Không cho phép chọn trùng sản phẩm trong 1 phiếu
                Rule::exists('product_variants', 'id')->whereNull('deleted_at')
            ],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:1000000'],
            'items.*.import_price' => ['required', 'numeric', 'min:0', 'max:10000000000'],
        ], [
            'supplier_id.required'        => 'Vui lòng chọn nhà cung cấp.',
            'items.required'              => 'Vui lòng nhập ít nhất 1 sản phẩm.',
            'items.*.variant_id.distinct' => 'Sản phẩm trong phiếu không được trùng nhau.',
            'items.*.quantity.min'        => 'Số lượng nhập phải lớn hơn 0.',
        ]);

        try {
            DB::beginTransaction();

            // 3. Tạo Purchase Order Header
            $poCode = 'PO-' . date('ymd') . '-' . strtoupper(Str::random(5));
            
            // Kiểm tra trùng mã (dù hiếm)
            while (PurchaseOrder::where('code', $poCode)->exists()) {
                $poCode = 'PO-' . date('ymd') . '-' . strtoupper(Str::random(5));
            }

            $purchaseOrder = PurchaseOrder::create([
                'code'         => $poCode,
                'supplier_id'  => $request->supplier_id,
                'user_id'      => Auth::id(),
                'status'       => PurchaseOrder::STATUS_PENDING, // Mặc định là Chờ duyệt
                'note'         => $request->note,
                'expected_at'  => $request->expected_at,
                'total_amount' => 0, // Sẽ update sau khi tính items
            ]);

            $grandTotal = 0;

            // 4. Tạo chi tiết items
            foreach ($request->items as $item) {
                $quantity = (int) $item['quantity'];
                $price    = (float) $item['import_price'];
                $total    = $quantity * $price;

                PurchaseOrderItem::create([
                    'purchase_order_id'  => $purchaseOrder->id,
                    'product_variant_id' => $item['variant_id'],
                    'quantity'           => $quantity,
                    'import_price'       => $price,
                    'total'              => $total
                ]);

                $grandTotal += $total;
            }

            // 5. Cập nhật tổng tiền phiếu
            $purchaseOrder->update(['total_amount' => $grandTotal]);

            DB::commit();

            return redirect()
                ->route('admin.purchase_orders.show', $purchaseOrder->id)
                ->with('success', 'Đã tạo phiếu nhập hàng thành công. Vui lòng kiểm tra và duyệt nhập kho.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("PO Create Failed: " . $e->getMessage());
            return back()->with('error', 'Lỗi hệ thống: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * CHI TIẾT PHIẾU NHẬP
     */
    public function show($id)
    {
        $po = PurchaseOrder::with([
            'items.variant.product', // Load sâu để lấy tên và ảnh sản phẩm
            'supplier', 
            'creator'
        ])->findOrFail($id);

        return view('admin.inventory.purchase_orders.show', compact('po'));
    }

    /**
     * CẬP NHẬT TRẠNG THÁI (DUYỆT ĐƠN / HỦY ĐƠN)
     * * Logic quan trọng:
     * - Nếu Duyệt (Completed): Cộng tồn kho, cập nhật giá vốn, ghi log.
     * - Nếu Hủy (Cancelled): Chỉ đổi trạng thái, không tác động kho.
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => ['required', Rule::in([
                PurchaseOrder::STATUS_COMPLETED, 
                PurchaseOrder::STATUS_CANCELLED
            ])]
        ]);

        $po = PurchaseOrder::with('items')->findOrFail($id);

        // Chặn nếu phiếu không phải đang ở trạng thái Pending
        if ($po->status !== PurchaseOrder::STATUS_PENDING) {
            return back()->with('error', 'Phiếu này đã được xử lý trước đó, không thể thay đổi.');
        }

        // ================= TRƯỜNG HỢP: DUYỆT NHẬP KHO ================= //
        if ($request->status === PurchaseOrder::STATUS_COMPLETED) {
            try {
                DB::beginTransaction();

                foreach ($po->items as $item) {
                    // Lock dòng dữ liệu để tránh Race Condition (nhiều người cùng nhập 1 lúc)
                    $variant = ProductVariant::lockForUpdate()->find($item->product_variant_id);

                    if (!$variant) {
                        throw new \Exception("Sản phẩm (ID: {$item->product_variant_id}) không còn tồn tại trong hệ thống.");
                    }

                    // 1. Tính toán tồn kho mới
                    $oldStock = $variant->stock_quantity;
                    $newStock = $oldStock + $item->quantity;

                    // 2. Cập nhật Variant (Tồn kho + Giá nhập mới nhất)
                    $variant->update([
                        'stock_quantity' => $newStock,
                        'original_price' => $item->import_price // Cập nhật giá vốn theo lần nhập mới nhất
                    ]);

                    // 3. Ghi Log Kho (Inventory History)
                    InventoryLog::create([
                        'product_variant_id' => $variant->id,
                        'user_id'            => Auth::id(),
                        'type'               => 'import',             // Loại giao dịch
                        'quantity'           => $item->quantity,      // Số lượng biến động
                        'current_stock'      => $newStock,            // Tồn sau khi đổi
                        'reference_type'     => 'purchase_order',     // Tham chiếu đến bảng PO
                        'reference_id'       => $po->id,              // ID phiếu nhập
                        'note'               => "Nhập kho từ đơn: {$po->code}",
                    ]);
                }

                // Cập nhật trạng thái phiếu
                $po->update(['status' => PurchaseOrder::STATUS_COMPLETED]);

                DB::commit();
                return back()->with('success', 'Đã duyệt đơn và cập nhật kho hàng thành công!');

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error("PO Approve Error PO#{$id}: " . $e->getMessage());
                return back()->with('error', 'Lỗi khi nhập kho: ' . $e->getMessage());
            }
        }

        // ================= TRƯỜNG HỢP: HỦY PHIẾU ================= //
        if ($request->status === PurchaseOrder::STATUS_CANCELLED) {
            $po->update(['status' => PurchaseOrder::STATUS_CANCELLED]);
            return back()->with('success', 'Đã hủy phiếu nhập hàng.');
        }

        return back()->with('error', 'Trạng thái không hợp lệ.');
    }
}