<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderSeeder extends Seeder
{
    public function run()
    {
        // 1. DỌN DẸP DỮ LIỆU CŨ (Chỉ xóa dữ liệu liên quan đến đơn hàng)
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('orders')->truncate();
        DB::table('order_items')->truncate();
        DB::table('transactions')->truncate();
        DB::table('reviews')->truncate();
        DB::table('carts')->truncate();
        DB::table('cart_items')->truncate();
        
        // LƯU Ý: Không truncate inventory_logs ở đây nữa 
        // để tránh mất dữ liệu nhập hàng từ PurchaseOrderSeeder
        // DB::table('inventory_logs')->truncate(); 

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 2. LẤY DỮ LIỆU NỀN
        $users = DB::table('users')->where('role', 'customer')->get();
        // Nếu không có customer nào, lấy tạm tất cả user
        if ($users->isEmpty()) {
            $users = DB::table('users')->get();
        }

        $variants = DB::table('product_variants')->get();
        $products = DB::table('products')->get();

        if ($users->isEmpty() || $variants->isEmpty()) {
            $this->command->warn("❌ Không có user hoặc product_variant để seed Order.");
            return;
        }

        // ===========================
        // 3. TẠO 10 ĐƠN HÀNG NGẪU NHIÊN
        // ===========================
        for ($i = 0; $i < 10; $i++) {

            $user = $users->random();
            $variant = $variants->random(); // Lấy biến thể ngẫu nhiên
            
            // Lấy lại thông tin variant mới nhất từ DB để đảm bảo tồn kho chính xác
            $currentVariant = DB::table('product_variants')->where('id', $variant->id)->first();
            
            $product = $products->firstWhere('id', $currentVariant->product_id);

            // Fake shipping info
            $shippingInfo = [
                'name' => fake()->name(),
                'phone' => fake()->phoneNumber(),
                'address' => fake()->address()
            ];

            // A. Tạo giỏ hàng (Cart)
            $cartId = DB::table('carts')->insertGetId([
                'user_id' => $user->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('cart_items')->insert([
                'cart_id' => $cartId,
                'product_variant_id' => $currentVariant->id,
                'quantity' => rand(1, 3),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $quantity = rand(1, 3);
            // Đảm bảo không bán quá số lượng tồn kho
            if ($quantity > $currentVariant->stock_quantity) {
                $quantity = $currentVariant->stock_quantity > 0 ? $currentVariant->stock_quantity : 0;
            }
            
            if ($quantity == 0) continue; // Bỏ qua nếu hết hàng

            $price = $currentVariant->sale_price > 0 ? $currentVariant->sale_price : $currentVariant->original_price;
            $shippingFee = 30000;

            // B. Tạo Order
            $orderId = DB::table('orders')->insertGetId([
                'order_code' => 'ORD-' . strtoupper(Str::random(6)),
                'user_id' => $user->id,
                'status' => 'completed',
                'payment_status' => 'paid',
                'payment_method' => 'vnpay',
                'shipping_fee' => $shippingFee,
                'total_amount' => ($price * $quantity) + $shippingFee,
                'shipping_address' => json_encode($shippingInfo),
                'note' => fake()->sentence(),
                'created_at' => now()->subDays(rand(1, 15)),
                'updated_at' => now()->subDays(rand(1, 15)),
            ]);

            // C. Order Items
            DB::table('order_items')->insert([
                'order_id' => $orderId,
                'product_variant_id' => $currentVariant->id,
                'product_name' => $product->name,
                'sku' => $currentVariant->sku,
                'thumbnail' => $product->thumbnail ?? '/img/products/demo.jpg',
                'quantity' => $quantity,
                'price' => $price,
                'total_line' => $price * $quantity,
            ]);

            // D. Transaction
            DB::table('transactions')->insert([
                'order_id' => $orderId,
                'payment_method' => 'vnpay',
                'trans_code' => 'VNP-' . rand(100000, 999999),
                'amount' => ($price * $quantity) + $shippingFee,
                'status' => 'success',
                'created_at' => now()->subDays(rand(1, 15)),
            ]);

            // E. Review
            DB::table('reviews')->insert([
                'user_id' => $user->id,
                'product_id' => $product->id,
                'order_id' => $orderId,
                'rating' => rand(4, 5),
                'comment' => fake()->sentence(12),
                'is_approved' => true,
                'created_at' => now(),
            ]);

            // F. Cập nhật tồn kho & Ghi Log (PHẦN QUAN TRỌNG)
            
            $oldStock = $currentVariant->stock_quantity;
            $newStock = $oldStock - $quantity;

            // 1. Trừ tồn kho trong bảng product_variants
            DB::table('product_variants')
                ->where('id', $currentVariant->id)
                ->update(['stock_quantity' => $newStock]);

            // 2. Ghi log với cấu trúc chuẩn
            DB::table('inventory_logs')->insert([
                'product_variant_id' => $currentVariant->id,
                'user_id'            => $user->id, // Người mua làm thay đổi kho
                'type'               => 'sale',    // Loại giao dịch
                
                // Cột chuẩn mới
                'old_quantity'       => $oldStock,
                'change_amount'      => -$quantity, // Số âm vì là bán ra
                'new_quantity'       => $newStock,
                
                'reference_type'     => 'order',
                'reference_id'       => $orderId,
                'note'               => 'Bán đơn hàng #' . $orderId,
                'created_at'         => now()->subDays(rand(1, 15)),
                'updated_at'         => now(),
            ]);
        }

        $this->command->info("✔ Đã seed 10 đơn hàng thành công!");
    }
}