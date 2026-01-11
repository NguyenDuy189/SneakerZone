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
        // Eager loading 'supplier'. Bỏ 'creator' nếu Model chưa định nghĩa để tránh lỗi
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
        $suppliers = Supplier::whereNull('deleted_at')->orderBy('name')->get();

        // Load sản phẩm để chọn
        $products = ProductVariant::with('product')
            ->whereNull('deleted_at')
            ->whereHas('product', fn($q) => $q->whereNull('deleted_at'))
            ->get()
            ->map(function ($v) {
                // Tạo tên hiển thị an toàn (tránh lỗi nếu color/size null)
                $variantName = $v->product->name;
                if ($v->color || $v->size) {
                    $variantName .= " - " . ($v->color ?? '') . "/" . ($v->size ?? '');
                }

                return [
                    'id'    => $v->id,
                    'name'  => $variantName,
                    'sku'   => $v->sku,
                    'image' => $v->image_url ?? $v->product->thumbnail,
                    'stock' => $v->stock_quantity,
                    'price' => $v->original_price
                ];
            });

        return view('admin.inventory.purchase_orders.create', compact('suppliers', 'products'));
    }

    /**
     * LƯU PHIẾU NHẬP (STORE)
     */
    /**
     * LƯU PHIẾU NHẬP (STORE)
     */
    public function store(Request $request)
    {
        // 1. Làm sạch dữ liệu items
        $rawItems = $request->input('items', []);
        $cleanItems = array_filter($rawItems, function ($item) {
            return !empty($item['variant_id']) && !empty($item['quantity']) && isset($item['import_price']);
        });
        
        $request->merge(['items' => $cleanItems]);

        // 2. Validate
        $request->validate([
            'supplier_id' => ['required', Rule::exists('suppliers', 'id')->whereNull('deleted_at')],
            // ĐÃ XÓA: dòng validate 'expected_at' vì giờ tự động lấy
            'note'        => 'nullable|string|max:1000',
            'items'       => 'required|array|min:1|max:200',
            'items.*.variant_id' => ['required', 'distinct', Rule::exists('product_variants', 'id')->whereNull('deleted_at')],
            'items.*.quantity'   => ['required', 'integer', 'min:1', 'max:1000000'],
            'items.*.import_price' => ['required', 'numeric', 'min:0', 'max:10000000000'],
        ]);

        try {
            DB::beginTransaction();

            // 3. Tạo mã phiếu PO-...
            $poCode = 'PO-' . date('ymd') . '-' . strtoupper(Str::random(5));
            while (PurchaseOrder::where('code', $poCode)->exists()) {
                $poCode = 'PO-' . date('ymd') . '-' . strtoupper(Str::random(5));
            }

            // 4. Tạo Header Phiếu
            $purchaseOrder = PurchaseOrder::create([
                'code'         => $poCode,
                'supplier_id'  => $request->supplier_id,
                'status'       => PurchaseOrder::STATUS_PENDING,
                'note'         => $request->note,
                
                // --- THAY ĐỔI TẠI ĐÂY ---
                // Thay vì lấy $request->expected_at, ta dùng hàm now() để lấy thời gian hiện tại
                'expected_at'  => now(), 
                
                'total_amount' => 0, 
            ]);

            $grandTotal = 0;

            // 5. Tạo chi tiết items
            foreach ($request->items as $item) {
                $quantity = (int) $item['quantity'];
                $price    = (float) $item['import_price'];
                $total    = $quantity * $price;

                PurchaseOrderItem::create([
                    'purchase_order_id'  => $purchaseOrder->id,
                    'product_variant_id' => $item['variant_id'],
                    'quantity'           => $quantity,
                    'import_price'       => $price,
                    'subtotal'           => $total,
                ]);

                $grandTotal += $total;
            }

            // Update tổng tiền
            $purchaseOrder->update(['total_amount' => $grandTotal]);

            DB::commit();

            return redirect()
                ->route('admin.purchase_orders.show', $purchaseOrder->id)
                ->with('success', 'Đã tạo phiếu nhập hàng thành công.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("PO Create Failed: " . $e->getMessage());
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
     * Đây là hàm quan trọng nhất để fix lỗi InventoryLog
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

        // ================= DUYỆT ĐƠN (NHẬP KHO) ================= //
        if ($request->status === PurchaseOrder::STATUS_COMPLETED) {
            try {
                DB::beginTransaction();

                foreach ($po->items as $item) {
                    // Lock dữ liệu
                    $variant = ProductVariant::lockForUpdate()->find($item->product_variant_id);

                    if (!$variant) {
                        throw new \Exception("Sản phẩm ID {$item->product_variant_id} không tồn tại.");
                    }

                    // 1. Tính toán
                    $oldStock     = $variant->stock_quantity;
                    $changeAmount = $item->quantity;
                    $newStock     = $oldStock + $changeAmount;

                    // 2. Cập nhật Variant
                    $variant->update([
                        'stock_quantity' => $newStock,
                        'original_price' => $item->import_price 
                    ]);

                    // 3. Ghi Log Kho (ĐÃ SỬA ĐÚNG STRUCUTRE BẢNG LOG CỦA BẠN)
                    InventoryLog::create([
                        'product_variant_id' => $variant->id,
                        'user_id'            => Auth::id() ?? 1, // Lấy ID admin hoặc mặc định 1
                        
                        // --- CÁC CỘT QUAN TRỌNG ĐÃ FIX ---
                        'old_quantity'       => $oldStock,      // Tồn đầu
                        'change_amount'      => $changeAmount,  // Số thay đổi (Tránh lỗi 1364)
                        'new_quantity'       => $newStock,      // Tồn cuối
                        // ---------------------------------
                        
                        'type'               => InventoryLog::TYPE_IMPORT, // Hoặc 'import'
                        'reference_type'     => 'purchase_order',
                        'reference_id'       => $po->id,
                        'note'               => "Nhập kho từ đơn: {$po->code}",
                    ]);
                }

                $po->update(['status' => PurchaseOrder::STATUS_COMPLETED]);

                DB::commit();
                return back()->with('success', 'Đã duyệt đơn và nhập kho thành công!');

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error("PO Approve Error PO#{$id}: " . $e->getMessage());
                return back()->with('error', 'Lỗi khi nhập kho: ' . $e->getMessage());
            }
        }

        // ================= HỦY ĐƠN ================= //
        if ($request->status === PurchaseOrder::STATUS_CANCELLED) {
            $po->update(['status' => PurchaseOrder::STATUS_CANCELLED]);
            return back()->with('success', 'Đã hủy phiếu nhập hàng.');
        }

        return back()->with('error', 'Trạng thái không hợp lệ.');
    }
}