<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PurchaseOrderSeeder extends Seeder
{
    public function run()
    {
        // 1. Dọn dẹp dữ liệu cũ
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('purchase_orders')->truncate();
        // Lưu ý: Nếu bạn đã đổi tên bảng thành po_items trong migration thì sửa dòng dưới
        // Nếu chưa đổi (vẫn là po_items) thì giữ nguyên. Khuyên dùng: po_items
        DB::table('po_items')->truncate(); 
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 2. Lấy dữ liệu cần thiết
        $supplierId = DB::table('suppliers')->value('id');
        $adminId    = DB::table('users')->where('role', 'admin')->value('id') ?? 1; // Lấy ID admin
        $variants   = DB::table('product_variants')->limit(5)->get();

        if (!$supplierId || $variants->isEmpty()) {
            $this->command->info('Cần có Supplier và Product Variants trước!');
            return;
        }

        // 3. Tạo Phiếu Nhập (PO)
        $poId = DB::table('purchase_orders')->insertGetId([
            'code'         => 'PO-' . now()->format('Ymd') . '-001',
            'supplier_id'  => $supplierId,
            'user_id'      => $adminId, // <--- SỬA LỖI CHÍNH TẠI ĐÂY
            'status'       => 'completed',
            'total_amount' => 0, // Tính sau
            'note'         => 'Nhập hàng tồn kho ban đầu',
            'expected_at'  => now()->subDays(10),
            'created_at'   => now()->subDays(10),
            'updated_at'   => now()->subDays(10),
        ]);

        $totalPO = 0;

        foreach ($variants as $v) {
            $qty = 20;
            $importPrice = $v->original_price * 0.6; // Giá vốn = 60% giá niêm yết
            $lineTotal   = $qty * $importPrice;      // Thành tiền dòng này

            $totalPO += $lineTotal;

            // 4. Tạo chi tiết nhập (Items)
            // Lưu ý tên bảng phải khớp với Migration (po_items)
            DB::table('po_items')->insert([
                'purchase_order_id'  => $poId,
                'product_variant_id' => $v->id,
                'quantity'           => $qty,
                'import_price'       => $importPrice,
                'total'              => $lineTotal, // <--- Bổ sung cột này
            ]);

            // 5. Cập nhật tồn kho (Log) - Khớp với cấu trúc mới
            $oldStock = $v->stock_quantity;
            $newStock = $oldStock + $qty;

            // Cập nhật số lượng trong bảng variants
            DB::table('product_variants')->where('id', $v->id)->update([
                'stock_quantity' => $newStock
            ]);

            // Ghi log
            DB::table('inventory_logs')->insert([
                'product_variant_id' => $v->id,
                'user_id'            => $adminId,
                
                'change_amount'      => $qty,
                'old_quantity'       => $oldStock, // <--- Cột mới
                'new_quantity'       => $newStock, // <--- Thay cho remaining_stock
                
                'type'               => 'import',
                'reference_id'       => $poId,
                'note'               => 'Nhập hàng từ PO #' . $poId,
                'created_at'         => now()->subDays(10),
                'updated_at'         => now()->subDays(10),
            ]);
        }

        // 6. Update tổng tiền phiếu
        DB::table('purchase_orders')->where('id', $poId)->update(['total_amount' => $totalPO]);
    }
}