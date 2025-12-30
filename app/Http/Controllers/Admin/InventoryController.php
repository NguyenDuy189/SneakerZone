<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductVariant;
use App\Models\InventoryLog; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class InventoryController extends Controller
{
    const LOW_STOCK_THRESHOLD = 10;

    /**
     * Hiển thị danh sách tồn kho
     */
    public function index(Request $request)
    {
        $query = ProductVariant::with('product')
            ->orderBy('stock_quantity', 'asc');

        // 1. Lọc theo trạng thái
        if ($request->has('status')) {
            switch ($request->status) {
                case 'out_of_stock':
                    $query->where('stock_quantity', 0);
                    break;
                case 'low_stock':
                    $query->where('stock_quantity', '>', 0)
                          ->where('stock_quantity', '<=', self::LOW_STOCK_THRESHOLD);
                    break;
                case 'in_stock':
                    $query->where('stock_quantity', '>', self::LOW_STOCK_THRESHOLD);
                    break;
            }
        }

        // 2. Tìm kiếm
        if ($request->filled('keyword')) {
            $keyword = trim($request->keyword);
            $query->where(function($q) use ($keyword) {
                $q->where('sku', 'like', "%$keyword%")
                  ->orWhereHas('product', function($subQ) use ($keyword) {
                      $subQ->where('name', 'like', "%$keyword%");
                  });
            });
        }

        $variants = $query->paginate(20)->withQueryString();

        return view('admin.inventory.index', compact('variants'));
    }

    /**
     * TÍCH HỢP VALIDATION & CẬP NHẬT NHANH
     */
    public function quickUpdate(Request $request, $id)
    {
        $variant = ProductVariant::findOrFail($id);

        // 1. Validation
        $validated = $request->validate([
            'stock_quantity' => 'required|integer|min:0',
            'original_price' => 'nullable|numeric|min:0',
            'note'           => 'nullable|string|max:255'
        ], [
            'stock_quantity.required' => 'Số lượng tồn kho không được để trống.',
            'stock_quantity.integer'  => 'Số lượng phải là số nguyên.',
            'stock_quantity.min'      => 'Số lượng không được nhỏ hơn 0.',
            'original_price.numeric'  => 'Giá nhập phải là số.',
        ]);

        DB::beginTransaction();
        try {
            // Lưu lại tồn kho cũ để ghi log
            $oldStock = $variant->stock_quantity;
            $newStock = (int) $validated['stock_quantity'];

            // 2. Cập nhật Biến thể
            $variant->stock_quantity = $newStock;
            
            if ($request->filled('original_price')) {
                $variant->original_price = $validated['original_price'];
            }
            
            $variant->save();

            // 3. Ghi Log Kho (ĐÃ SỬA LẠI CỘT CHO ĐÚNG DB)
            if ($oldStock != $newStock) {
                $diff = $newStock - $oldStock; // Có thể âm hoặc dương
                
                InventoryLog::create([
                    'product_variant_id' => $variant->id,
                    'user_id'            => Auth::id() ?? 1,
                    
                    // Xác định loại giao dịch
                    'type'               => 'check', // 'check' (kiểm kho) hợp lý hơn 'import/export' khi sửa tay
                    
                    // --- CÁC CỘT QUAN TRỌNG ĐÃ SỬA ---
                    'old_quantity'       => $oldStock,
                    'change_amount'      => $diff,      // Lưu số âm nếu giảm, dương nếu tăng
                    'new_quantity'       => $newStock,
                    // ---------------------------------

                    'reference_type'     => 'manual_update',
                    'reference_id'       => null,
                    'note'               => $request->note ?? 'Cập nhật nhanh từ trang quản lý kho',
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true, 
                'message' => 'Cập nhật kho thành công!',
                'new_stock' => $newStock,
                'status_html' => $this->getStatusBadgeHtml($newStock)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Helper tạo HTML badge
     */
    private function getStatusBadgeHtml($qty)
    {
        if ($qty == 0) {
            return '<span class="px-2 py-1 font-semibold leading-tight text-red-700 bg-red-100 rounded-full text-xs">Hết hàng</span>';
        } elseif ($qty <= self::LOW_STOCK_THRESHOLD) {
            return '<span class="px-2 py-1 font-semibold leading-tight text-amber-700 bg-amber-100 rounded-full text-xs">Sắp hết</span>';
        }
        return '<span class="px-2 py-1 font-semibold leading-tight text-green-700 bg-green-100 rounded-full text-xs">Sẵn hàng</span>';
    }
} 