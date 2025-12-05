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
     * DANH SÁCH PHIẾU NHẬP
     */
    public function index(Request $request)
    {
        $query = PurchaseOrder::with(['supplier', 'creator', 'items'])
            ->latest('id');

        if ($request->filled('keyword')) {
            $query->where('code', 'like', "%{$request->keyword}%");
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        $orders = $query->paginate(10)->withQueryString();
        $suppliers = Supplier::select('id', 'name')->get();

        return view('admin.inventory.purchase_orders.index', compact('orders', 'suppliers'));
    }

    /**
     * FORM TẠO MỚI
     */
    public function create()
    {
        $suppliers = Supplier::whereNull('deleted_at')->get();

        $products = ProductVariant::with('product')
            ->whereNull('deleted_at')
            ->whereHas('product', fn($q) => $q->whereNull('deleted_at'))
            ->get()
            ->map(function ($v) {
                return [
                    'id'    => $v->id,
                    'name'  => $v->product->name . " ({$v->size}/{$v->color})",
                    'sku'   => $v->sku,
                    'image' => $v->image_url ?? $v->product->thumbnail,
                    'stock' => $v->stock_quantity
                ];
            });

        return view('admin.inventory.purchase_orders.create', compact('suppliers', 'products'));
    }

    /**
     * LƯU PHIẾU NHẬP – VALIDATE CỰC CHẶT
     */
    public function store(Request $request)
{
    // ---------------- VALIDATION CƠ BẢN ---------------- //
    $request->validate([
        'supplier_id' => [
            'required',
            Rule::exists('suppliers', 'id')->whereNull('deleted_at')
        ],

        'expected_at' => 'nullable|date|after_or_equal:today',
        'note'        => 'nullable|string|max:500',

        'items'       => 'required|array|min:1|max:200',

        'items.*.variant_id' => [
            'required',
            'distinct',
            Rule::exists('product_variants', 'id')->whereNull('deleted_at')
        ],

        'items.*.quantity' => [
            'required',
            'integer',
            'min:1',
            'max:1000000'
        ],

        'items.*.import_price' => [
            'required',
            'numeric',
            'min:0',
            'max:1000000000',
            'regex:/^\d{1,12}(\.\d{1,2})?$/'
        ],

    ], [
        'supplier_id.required' => 'Vui lòng chọn nhà cung cấp.',
        'supplier_id.exists'   => 'Nhà cung cấp không hợp lệ hoặc đã bị xóa.',

        'items.required'       => 'Phiếu nhập phải có ít nhất 1 sản phẩm.',
        'items.min'            => 'Phiếu nhập phải có ít nhất 1 sản phẩm.',
        'items.max'            => 'Một phiếu nhập không được vượt quá 200 sản phẩm.',

        'items.*.variant_id.required' => 'Vui lòng chọn sản phẩm.',
        'items.*.variant_id.exists'   => 'Sản phẩm không hợp lệ hoặc đã bị xóa.',
        'items.*.variant_id.distinct' => 'Không được nhập trùng sản phẩm.',

        'items.*.quantity.required' => 'Vui lòng nhập số lượng.',
        'items.*.quantity.min'      => 'Số lượng phải lớn hơn 0.',
        'items.*.quantity.max'      => 'Số lượng quá lớn.',

        'items.*.import_price.required' => 'Vui lòng nhập giá nhập.',
        'items.*.import_price.min'      => 'Giá nhập không hợp lệ.',
    ]);

    // ============================ KIỂM TRA FORM DỮ LIỆU TRỐNG ============================ //
    // Nếu user thêm dòng mới nhưng để trống toàn bộ → không tính là một item
    $cleanItems = array_filter($request->items, function ($item) {
        return !(
            empty($item['variant_id']) &&
            empty($item['quantity']) &&
            empty($item['import_price'])
        );
    });

    if (count($cleanItems) === 0) {
        return back()
            ->withErrors(['items' => 'Bạn chưa chọn sản phẩm nào.'])
            ->withInput();
    }

    // Cập nhật lại mảng items dùng để xử lý
    $request->merge(['items' => $cleanItems]);

    // ---------------- KIỂM TRA TRÙNG BẰNG ARRAY STRICT ---------------- //
    $variantIds = array_column($request->items, 'variant_id');
    if (count($variantIds) !== count(array_unique($variantIds))) {
        return back()->with('error', 'Sản phẩm trong phiếu nhập không được trùng nhau.')->withInput();
    }

    // ============================ TẠO PHIẾU NHẬP ============================ //
    try {
        DB::beginTransaction();

        $po = PurchaseOrder::create([
            'code'         => 'PO-' . now()->format('ymd') . '-' . strtoupper(Str::random(4)),
            'supplier_id'  => $request->supplier_id,
            'user_id'      => Auth::id(),
            'status'       => PurchaseOrder::STATUS_PENDING,
            'note'         => $request->note,
            'expected_at'  => $request->expected_at,
            'total_amount' => 0,
        ]);

        $grandTotal = 0;

        foreach ($request->items as $item) {
            $lineTotal = $item['quantity'] * $item['import_price'];
            $grandTotal += $lineTotal;

            PurchaseOrderItem::create([
                'purchase_order_id'  => $po->id,
                'product_variant_id' => $item['variant_id'],
                'quantity'           => $item['quantity'],
                'import_price'       => $item['import_price'],
                'total'              => $lineTotal
            ]);
        }

        $po->update(['total_amount' => $grandTotal]);

        DB::commit();

        return redirect()
            ->route('admin.purchase_orders.show', $po->id)
            ->with('success', 'Tạo phiếu nhập thành công.');

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error("PO Create Error: " . $e->getMessage());

        return back()->with('error', 'Lỗi hệ thống: ' . $e->getMessage())->withInput();
    }
}

    /**
     * CHI TIẾT PHIẾU NHẬP
     */
    public function show($id)
    {
        $po = PurchaseOrder::with(['items.variant.product', 'supplier', 'creator'])
            ->findOrFail($id);

        return view('admin.inventory.purchase_orders.show', compact('po'));
    }

    /**
     * CẬP NHẬT TRẠNG THÁI (DUYỆT NHẬP / HỦY)
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
            return back()->with('error', 'Phiếu đã được xử lý, không thể thay đổi.');
        }

        // ---------------- DUYỆT NHẬP KHO ---------------- //
        if ($request->status === PurchaseOrder::STATUS_COMPLETED) {
            try {
                DB::beginTransaction();

                foreach ($po->items as $item) {
                    $variant = ProductVariant::lockForUpdate()->find($item->product_variant_id);

                    if (!$variant) {
                        throw new \Exception("Sản phẩm ID {$item->product_variant_id} không tồn tại.");
                    }

                    $old = $variant->stock_quantity;
                    $new = $old + $item->quantity;

                    $variant->update(['stock_quantity' => $new]);

                    InventoryLog::create([
                        'product_variant_id' => $variant->id,
                        'user_id'            => Auth::id(),
                        'old_quantity'       => $old,
                        'change_amount'      => $item->quantity,
                        'new_quantity'       => $new,
                        'type'               => InventoryLog::TYPE_IMPORT,
                        'reference_id'       => $po->id,
                        'note'               => "Nhập kho từ phiếu {$po->code}",
                    ]);
                }

                $po->update(['status' => PurchaseOrder::STATUS_COMPLETED]);

                DB::commit();
                return back()->with('success', 'Đã nhập kho thành công!');

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error("PO Approve Error: " . $e->getMessage());
                return back()->with('error', 'Lỗi nhập kho: ' . $e->getMessage());
            }
        }

        // ---------------- HỦY PHIẾU ---------------- //
        $po->update(['status' => PurchaseOrder::STATUS_CANCELLED]);

        return back()->with('success', 'Đã hủy phiếu nhập thành công.');
    }
}
