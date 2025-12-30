<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PurchaseOrderSeeder extends Seeder
{
    public function run()
    {
        // 1. LẤY DỮ LIỆU CẦN THIẾT
        $variantIds = DB::table('product_variants')->pluck('id')->toArray();
        if (empty($variantIds)) {
            $this->command->warn('Chưa có sản phẩm. Hãy chạy ProductSeeder trước!');
            return;
        }

        $supplierIds = DB::table('suppliers')->pluck('id')->toArray();
        if (empty($supplierIds)) {
            $supplierIds[] = DB::table('suppliers')->insertGetId([
                'name' => 'Nhà cung cấp Mặc định',
                'code' => 'SUP-DEFAULT',
                'contact_person' => 'Admin',
                'phone' => '0123456789',
                'email' => 'supplier@example.com',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Lấy User ID (Chỉ dùng cho Log kho)
        $adminId = DB::table('users')->value('id') ?? 1;

        // 2. TẠO 5 PHIẾU NHẬP HÀNG
        for ($i = 1; $i <= 5; $i++) {
            
            $randomSupplierId = $supplierIds[array_rand($supplierIds)];

            // A. Tạo Purchase Order
            $poId = DB::table('purchase_orders')->insertGetId([
                'code'          => 'PO-' . now()->format('Ymd') . '-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'supplier_id'   => $randomSupplierId,
                'status'        => 'completed', 
                'total_amount'  => 0, 
                // 'user_id'    => $adminId, // Đã xóa dòng này
                'created_at'    => Carbon::now()->subDays(rand(1, 10)),
                'updated_at'    => Carbon::now(),
            ]);

            $totalAmount = 0;
            
            $randomVariants = array_rand(array_flip($variantIds), rand(3, 5));
            if (!is_array($randomVariants)) $randomVariants = [$randomVariants];

            foreach ($randomVariants as $variantId) {
                $variant = DB::table('product_variants')->where('id', $variantId)->first();
                
                $quantity = rand(10, 50);
                $importPrice = $variant->original_price * 0.8; 
                $subtotal = $quantity * $importPrice;

                // B. Tạo PO Items (ĐÃ SỬA LỖI Ở ĐÂY)
                DB::table('po_items')->insert([
                    'purchase_order_id'  => $poId,
                    'product_variant_id' => $variantId,
                    'quantity'           => $quantity,
                    'import_price'       => $importPrice, // <--- Đổi 'price' thành 'import_price'
                    'subtotal'           => $subtotal,
                    'created_at'         => now(),
                    'updated_at'         => now(),
                ]);

                $totalAmount += $subtotal;

                // C. Cập nhật tồn kho
                $oldStock = $variant->stock_quantity;
                $newStock = $oldStock + $quantity;

                DB::table('product_variants')
                    ->where('id', $variantId)
                    ->update(['stock_quantity' => $newStock]);

                // D. Ghi Log Inventory
                DB::table('inventory_logs')->insert([
                    'product_variant_id' => $variantId,
                    'user_id'            => $adminId,
                    'type'               => 'import',
                    'old_quantity'       => $oldStock,
                    'change_amount'      => $quantity,
                    'new_quantity'       => $newStock,
                    'reference_type'     => 'purchase_order',
                    'reference_id'       => $poId,
                    'note'               => "Nhập hàng PO #$poId",
                    'created_at'         => now(),
                    'updated_at'         => now(),
                ]);
            }

            // Update tổng tiền
            DB::table('purchase_orders')->where('id', $poId)->update(['total_amount' => $totalAmount]);
        }

        $this->command->info('✅ PurchaseOrderSeeder hoàn tất!');
    }
}