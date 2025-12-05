<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderSeeder extends Seeder
{
    public function run()
    {
        // =========================
        // 1. Dọn dẹp dữ liệu cũ
        // =========================
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('orders')->truncate();
        DB::table('order_items')->truncate();
        DB::table('transactions')->truncate();
        DB::table('reviews')->truncate();
        DB::table('carts')->truncate();
        DB::table('cart_items')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // =========================
        // 2. Lấy dữ liệu nguồn
        // =========================
        $users = DB::table('users')->where('role', 'customer')->get();
        $variantIds = DB::table('product_variants')->pluck('id')->toArray(); 
        $products = DB::table('products')->get();

        if ($users->isEmpty() || empty($variantIds)) {
            $this->command->info("❌ Cần có user (customer) và product_variant để seed Order.");
            return;
        }

        // =========================
        // 3. Tạo đơn hàng & giỏ hàng
        // =========================
        for ($i = 0; $i < 20; $i++) {

            $user = $users->random();
            $variantId = $variantIds[array_rand($variantIds)];
            
            $variant = DB::table('product_variants')->find($variantId);
            $product = $products->firstWhere('id', $variant->product_id);

            if ($variant->stock_quantity <= 0) continue;

            $quantity = rand(1, min(3, $variant->stock_quantity));
            $oldStock = $variant->stock_quantity;
            $newStock = $oldStock - $quantity;

            // =========================
            // 3a. Tạo giỏ hàng nếu chưa có
            // =========================
            $cartId = DB::table('carts')->insertGetId([
                'user_id' => $user->id,
                'session_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // =========================
            // 3b. Thêm sản phẩm vào giỏ hàng
            // =========================
            DB::table('cart_items')->insert([
                'cart_id' => $cartId,
                'product_variant_id' => $variant->id,
                'quantity' => $quantity,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // =========================
            // 4. Tạo đơn hàng
            // =========================
            $shippingInfo = [
                'contact_name' => $user->full_name,
                'phone' => $user->phone ?? '09'.rand(10000000, 99999999),
                'address' => 'Địa chỉ mẫu số '.rand(1,100),
                'city' => 'Hồ Chí Minh',
                'district' => 'Quận 1',
                'ward' => 'Phường Bến Nghé',
            ];

            $price = $variant->sale_price;
            $shippingFee = 30000;
            $totalAmount = ($price * $quantity) + $shippingFee;
            $orderDate = now()->subDays(rand(0,30));

            $orderId = DB::table('orders')->insertGetId([
                'order_code' => 'ORD-' . strtoupper(Str::random(8)),
                'user_id' => $user->id,
                'status' => 'completed',
                'payment_status' => 'paid',
                'payment_method' => 'vnpay',
                'shipping_fee' => $shippingFee,
                'discount_amount' => 0,
                'total_amount' => $totalAmount,
                'shipping_address' => json_encode($shippingInfo),
                'note' => 'Giao hàng giờ hành chính',
                'created_at' => $orderDate,
                'updated_at' => $orderDate,
            ]);

            // =========================
            // 5. Tạo order item (snapshot)
            // =========================
            DB::table('order_items')->insert([
                'order_id' => $orderId,
                'product_variant_id' => $variant->id,
                'product_name' => $product->name,
                'sku' => $variant->sku,
                'thumbnail' => $product->thumbnail ?? '/img/products/demo.jpg',
                'quantity' => $quantity,
                'price' => $price,
                'total_line' => $price * $quantity,
            ]);

            // =========================
            // 6. Tạo transaction
            // =========================
            DB::table('transactions')->insert([
                'order_id' => $orderId,
                'payment_method' => 'vnpay',
                'trans_code' => 'VNP-'.rand(1000000,9999999),
                'amount' => $totalAmount,
                'status' => 'success',
                'created_at' => $orderDate,
                'updated_at' => $orderDate,
            ]);

            // =========================
            // 7. Cập nhật tồn kho & log
            // =========================
            DB::table('product_variants')->where('id',$variant->id)->update([
                'stock_quantity' => $newStock
            ]);

            DB::table('inventory_logs')->insert([
                'product_variant_id' => $variant->id,
                'user_id' => $user->id,
                'change_amount' => -$quantity,
                'old_quantity' => $oldStock,
                'new_quantity' => $newStock,
                'type' => 'sale',
                'reference_id' => $orderId,
                'note' => 'Bán đơn hàng #' . $orderId,
                'created_at' => $orderDate,
                'updated_at' => $orderDate,
            ]);

            // =========================
            // 8. Tạo review 50% chance
            // =========================
            if (rand(0,1)) {
                DB::table('reviews')->insert([
                    'user_id' => $user->id,
                    'product_id' => $product->id,
                    'order_id' => $orderId,
                    'rating' => rand(4,5),
                    'comment' => 'Sản phẩm đẹp, giao hàng nhanh!',
                    'is_approved' => true,
                    'created_at' => $orderDate->copy()->addDays(2),
                    'updated_at' => $orderDate->copy()->addDays(2),
                ]);
            }
        }

        $this->command->info("✅ Seeder Order & Cart hoàn tất.");
    }
}
