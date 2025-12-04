<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PurchaseOrderSeeder extends Seeder {
    public function run() {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('purchase_orders')->truncate();
        DB::table('po_items')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $supplierId = DB::table('suppliers')->first()->id;
        $variants = DB::table('product_variants')->limit(5)->get();

        // Tạo Phiếu Nhập
        $poId = DB::table('purchase_orders')->insertGetId([
            'code' => 'PO-' . date('Ymd') . '-001',
            'supplier_id' => $supplierId,
            'total_amount' => 0, // Tính sau
            'status' => 'completed',
            'created_at' => now()->subDays(10), // Nhập cách đây 10 ngày
            'updated_at' => now()->subDays(10)
        ]);

        $totalPO = 0;
        foreach ($variants as $v) {
            $qty = 20;
            $importPrice = $v->original_price * 0.6; // Giá nhập = 60% giá niêm yết
            $totalPO += ($qty * $importPrice);

            // Chi tiết nhập
            DB::table('po_items')->insert([
                'purchase_order_id' => $poId,
                'product_variant_id' => $v->id,
                'quantity' => $qty,
                'import_price' => $importPrice
            ]);

            // Cập nhật tồn kho (Log)
            DB::table('inventory_logs')->insert([
                'product_variant_id' => $v->id,
                'change_amount' => $qty,
                'remaining_stock' => $v->stock_quantity + $qty,
                'type' => 'import',
                'reference_id' => $poId,
                'note' => 'Nhập hàng định kỳ',
                'created_at' => now()->subDays(10)
            ]);
        }

        // Cập nhật lại tổng tiền PO
        DB::table('purchase_orders')->where('id', $poId)->update(['total_amount' => $totalPO]);
    }
}