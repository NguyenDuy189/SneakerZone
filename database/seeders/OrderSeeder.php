<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderSeeder extends Seeder
{
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('orders')->truncate();
        DB::table('order_items')->truncate();
        DB::table('transactions')->truncate();
        DB::table('reviews')->truncate();
        DB::table('carts')->truncate();
        DB::table('cart_items')->truncate();
        DB::table('inventory_logs')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Lấy toàn bộ khách hàng và variant
        $users = DB::table('users')->where('role', 'customer')->get();
        $variants = DB::table('product_variants')->get();
        $products = DB::table('products')->get();

        if ($users->count() == 0 || $variants->count() == 0) {
            dd("❌ Không có user or product_variant để seed Order.");
        }

        // ===========================
        //  TẠO 10 ĐƠN HÀNG NGẪU NHIÊN
        // ===========================
        for ($i = 0; $i < 10; $i++) {

            $user = $users->random();
            $variant = $variants->random();
            $product = $products->firstWhere('id', $variant->product_id);

            // Fake shipping info
            $shippingInfo = [
                'name' => fake()->name(),
                'phone' => fake()->phoneNumber(),
                'address' => fake()->address()
            ];

            // 1. Tạo giỏ hàng
            $cartId = DB::table('carts')->insertGetId([
                'user_id' => $user->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('cart_items')->insert([
                'cart_id' => $cartId,
                'product_variant_id' => $variant->id,
                'quantity' => rand(1, 3),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $quantity = rand(1, 3);
            $price = $variant->sale_price;
            $shippingFee = 30000;

            // 2. Tạo Order
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

            // 3. Order Items (snapshot)
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

            // 4. Transaction
            DB::table('transactions')->insert([
                'order_id' => $orderId,
                'payment_method' => 'vnpay',
                'trans_code' => 'VNP-' . rand(100000, 999999),
                'amount' => ($price * $quantity) + $shippingFee,
                'status' => 'success',
                'created_at' => now()->subDays(rand(1, 15)),
            ]);

            // 5. Review
            DB::table('reviews')->insert([
                'user_id' => $user->id,
                'product_id' => $product->id,
                'order_id' => $orderId,
                'rating' => rand(4, 5),
                'comment' => fake()->sentence(12),
                'is_approved' => true,
                'created_at' => now(),
            ]);

            // 6. Inventory Log
            DB::table('inventory_logs')->insert([
                'product_variant_id' => $variant->id,
                'change_amount' => -$quantity,
                'remaining_stock' => $variant->stock_quantity - $quantity,
                'type' => 'sale',
                'reference_id' => $orderId,
                'note' => 'Bán đơn hàng #' . $orderId,
                'created_at' => now()->subDays(rand(1, 15)),
            ]);
        }

        echo "✔ Đã seed 10 đơn hàng thành công!\n";
    }
}
