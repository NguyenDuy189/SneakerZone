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
     */
    public function index(Request $request)
    {
        $query = PurchaseOrder::with(['supplier'])
            ->withCount('items')
            ->latest('id');

        // 1. Lọc theo mã phiếu
        if ($request->filled('keyword')) {
            $keyword = trim($request->keyword);
            $query->where('code', 'like', "%{$keyword}%");
        }

        // 2. Lọc theo trạng thái
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // 3. Lọc theo NCC
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        // 4. Lọc theo ngày
        if ($request->filled('date')) {
            try {
                $query->whereDate('created_at', $request->date);
            } catch (\Exception $e) {}
        }

        $orders = $query->paginate(10)->withQueryString();
        $suppliers = Supplier::select('id', 'name')->orderBy('name')->get();

        return view('admin.inventory.purchase_orders.index', compact('orders', 'suppliers'));
    }

    /**
     * HIỂN THỊ FORM TẠO MỚI (ĐÃ FIX LỖI SQL COLUMN NOT FOUND)
     */
    public function create()
    {
        $suppliers = Supplier::whereNull('deleted_at')->orderBy('name')->get();

        // Query tối ưu: Load kèm thuộc tính (Màu, Size) qua quan hệ attributeValues
        $products = ProductVariant::with([
                'product' => function($q) {
                    $q->select('id', 'name', 'deleted_at');
                },
                'attributeValues.attribute' // Load quan hệ: Biến thể -> Giá trị (Đỏ) -> Thuộc tính (Màu sắc)
            ])
            // CHỈ SELECT CÁC CỘT CÓ THẬT TRONG BẢNG PRODUCT_VARIANTS
            ->select('id', 'product_id', 'sku', 'stock_quantity', 'original_price') 
            ->whereNull('deleted_at')
            ->whereHas('product', fn($q) => $q->whereNull('deleted_at'))
            ->get()
            ->map(function ($v) {
                $name = $v->product->name ?? 'Sản phẩm lỗi';
                
                // Xử lý hiển thị thuộc tính: "Giày Nike - Đỏ / 42"
                $attrDetails = [];

                // Cách 1: Lấy từ quan hệ attributeValues (Chuẩn nhất)
                if ($v->relationLoaded('attributeValues')) {
                    foreach ($v->attributeValues as $av) {
                        // $av->value là "Đỏ", "XL"...
                        $attrDetails[] = $av->value;
                    }
                }
                
                // Cách 2: Fallback nếu bạn có cột attribute_string (Json/String)
                if (empty($attrDetails) && !empty($v->attribute_string)) {
                    $attrDetails[] = $v->attribute_string;
                }

                if (!empty($attrDetails)) {
                    $name .= " - " . implode(' / ', $attrDetails);
                }

                return [
                    'id'    => $v->id,
                    'name'  => $name,
                    'sku'   => $v->sku,
                    'stock' => $v->stock_quantity ?? 0,
                    'price' => $v->original_price ?? 0 
                ];
            })->values();

        return view('admin.inventory.purchase_orders.create', compact('suppliers', 'products'));
    }

    /**
     * LƯU PHIẾU NHẬP
     */
    public function store(Request $request)
    {
        // 1. Validate Header
        $request->validate([
            'supplier_id' => ['required', Rule::exists('suppliers', 'id')->whereNull('deleted_at')],
            'note'        => ['nullable', 'string', 'max:1000'],
            'items'       => ['required', 'array', 'min:1'],
        ], [
            'supplier_id.required' => 'Vui lòng chọn nhà cung cấp.',
            'items.required'       => 'Danh sách sản phẩm không được để trống.',
        ]);

        // 2. Làm sạch items
        $rawItems = $request->input('items', []);
        $cleanItems = array_values(array_filter($rawItems, function ($item) {
            return !empty($item['variant_id']) && isset($item['quantity']) && $item['quantity'] > 0;
        }));

        if (empty($cleanItems)) {
            return back()->with('error', 'Vui lòng kiểm tra danh sách sản phẩm (Số lượng > 0).')->withInput();
        }
        $request->merge(['items' => $cleanItems]);

        // 3. Validate chi tiết
        $request->validate([
            'items.*.variant_id'   => ['required', 'distinct', Rule::exists('product_variants', 'id')->whereNull('deleted_at')],
            'items.*.quantity'     => ['required', 'integer', 'min:1', 'max:1000000'],
            'items.*.import_price' => ['required', 'numeric', 'min:0'],
        ], [
            'items.*.variant_id.distinct' => 'Sản phẩm bị trùng lặp, vui lòng gộp số lượng.',
            'items.*.variant_id.exists'   => 'Sản phẩm không tồn tại.',
        ]);

        try {
            DB::beginTransaction();

            // Tạo mã phiếu PO-...
            $poCode = 'PO-' . date('ymd') . '-' . strtoupper(Str::random(5));
            // Retry đơn giản nếu trùng mã
            if (PurchaseOrder::where('code', $poCode)->exists()) {
                $poCode = 'PO-' . date('ymd') . '-' . strtoupper(Str::random(5));
            }

            $purchaseOrder = PurchaseOrder::create([
                'code'          => $poCode,
                'supplier_id'   => $request->supplier_id,
                'status'        => PurchaseOrder::STATUS_PENDING,
                'note'          => $request->note,
                'expected_at'   => now(),
                'total_amount'  => 0,
                'created_by'    => Auth::id() ?? 1,
            ]);

            $grandTotal = 0;
            $itemsToInsert = [];

            foreach ($request->items as $item) {
                $qty   = (int) $item['quantity'];
                $price = (float) ($item['import_price'] ?? 0);
                $total = $qty * $price;

                $itemsToInsert[] = [
                    'purchase_order_id'  => $purchaseOrder->id,
                    'product_variant_id' => $item['variant_id'],
                    'quantity'           => $qty,
                    'import_price'       => $price,
                    'subtotal'           => $total,
                    'created_at'         => now(),
                    'updated_at'         => now(),
                ];

                $grandTotal += $total;
            }

            if (!empty($itemsToInsert)) {
                PurchaseOrderItem::insert($itemsToInsert);
            }

            $purchaseOrder->update(['total_amount' => $grandTotal]);

            DB::commit();

            return redirect()
                ->route('admin.purchase_orders.show', $purchaseOrder->id)
                ->with('success', 'Tạo phiếu nhập hàng thành công.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("PO Create Error: " . $e->getMessage());
            return back()->with('error', 'Lỗi hệ thống: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * CHI TIẾT PHIẾU
     */
    public function show($id)
    {
        $po = PurchaseOrder::with([
            'items.variant.product', 
            'supplier'
        ])->findOrFail($id);

        return view('admin.inventory.purchase_orders.show', compact('po'));
    }

    /**
     * CẬP NHẬT TRẠNG THÁI (DUYỆT / HỦY)
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

        if ($po->status !== PurchaseOrder::STATUS_PENDING) {
            return back()->with('error', 'Phiếu này đã được xử lý rồi.');
        }

        // --- XỬ LÝ HỦY ĐƠN ---
        if ($request->status == PurchaseOrder::STATUS_CANCELLED) {
            $po->update(['status' => PurchaseOrder::STATUS_CANCELLED]);
            return back()->with('success', 'Đã hủy phiếu nhập hàng.');
        }

        // --- XỬ LÝ DUYỆT NHẬP KHO ---
        if ($request->status == PurchaseOrder::STATUS_COMPLETED) {
            if ($po->items->isEmpty()) {
                return back()->with('error', 'Phiếu trống, không thể nhập kho.');
            }

            try {
                DB::beginTransaction();

                foreach ($po->items as $item) {
                    $variant = ProductVariant::lockForUpdate()->find($item->product_variant_id);

                    if (!$variant) continue; // Hoặc throw exception

                    $oldStock     = $variant->stock_quantity;
                    $changeAmount = $item->quantity;
                    $newStock     = $oldStock + $changeAmount;

                    // 1. Cập nhật tồn kho & giá nhập
                    $variant->update([
                        'stock_quantity' => $newStock,
                        'original_price' => $item->import_price 
                    ]);

                    // 2. Ghi Log
                    // Kiểm tra xem Model có hằng số TYPE_IMPORT không, nếu không dùng string cứng 'import'
                    $logType = defined(InventoryLog::class . '::TYPE_IMPORT') ? InventoryLog::TYPE_IMPORT : 'import';

                    InventoryLog::create([
                        'product_variant_id' => $variant->id,
                        'user_id'            => Auth::id() ?? 1,
                        'old_quantity'       => $oldStock,
                        'change_amount'      => $changeAmount,
                        'new_quantity'       => $newStock,
                        'type'               => $logType,
                        'reference_type'     => 'purchase_order',
                        'reference_id'       => $po->id,
                        'note'               => "Nhập kho PO: {$po->code}",
                    ]);
                }

                $po->update(['status' => PurchaseOrder::STATUS_COMPLETED]);

                DB::commit();
                return back()->with('success', 'Đã duyệt nhập kho thành công!');

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error("PO Approve Error: " . $e->getMessage());
                return back()->with('error', 'Lỗi nhập kho: ' . $e->getMessage());
            }
        }
    }
}