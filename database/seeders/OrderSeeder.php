<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderSeeder extends Seeder
{
    public function run()
    {
        // 1. Dọn dẹp dữ liệu (KHÔNG XÓA inventory_logs để giữ lại log nhập hàng đầu kỳ)
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('orders')->truncate();
        DB::table('order_items')->truncate();
        DB::table('transactions')->truncate();
        DB::table('reviews')->truncate();
        DB::table('carts')->truncate();
        DB::table('cart_items')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 2. Lấy dữ liệu nguồn
        $users = DB::table('users')->where('role', 'customer')->get();
        // Chỉ lấy ID biến thể để query fresh data trong vòng lặp
        $variantIds = DB::table('product_variants')->pluck('id')->toArray(); 
        $products = DB::table('products')->get();

        if ($users->isEmpty() || empty($variantIds)) {
            $this->command->info("❌ Cần có user (customer) và product_variant để seed Order.");
            return;
        }

        // ===========================
        //  TẠO 20 ĐƠN HÀNG NGẪU NHIÊN
        // ===========================
        for ($i = 0; $i < 20; $i++) {

            $user = $users->random();
            $variantId = $variantIds[array_rand($variantIds)];
            
            // Lấy dữ liệu biến thể MỚI NHẤT để biết tồn kho hiện tại
            $variant = DB::table('product_variants')->find($variantId);
            $product = $products->firstWhere('id', $variant->product_id);

            // Nếu hết hàng thì bỏ qua vòng lặp này
            if ($variant->stock_quantity <= 0) continue;

            // Random số lượng mua (không quá tồn kho)
            $quantity = rand(1, min(3, $variant->stock_quantity));
            
            // Tính toán tồn kho
            $oldStock = $variant->stock_quantity;
            $newStock = $oldStock - $quantity;

            // Fake thông tin giao hàng
            $shippingInfo = [
                'contact_name' => $user->full_name,
                'phone'        => $user->phone ?? '09' . rand(10000000, 99999999),
                'address'      => 'Địa chỉ mẫu số ' . rand(1, 100),
                'city'         => 'Hồ Chí Minh',
                'district'     => 'Quận 1',
                'ward'         => 'Phường Bến Nghé'
            ];

            $price = $variant->sale_price;
            $shippingFee = 30000;
            $totalAmount = ($price * $quantity) + $shippingFee;
            
            // Random ngày tạo (trong 30 ngày qua)
            $orderDate = now()->subDays(rand(0, 30));

            // 3. Tạo Order
            $orderId = DB::table('orders')->insertGetId([
                'order_code'      => 'ORD-' . strtoupper(Str::random(8)),
                'user_id'         => $user->id,
                'status'          => 'completed',
                'payment_status'  => 'paid',
                'payment_method'  => 'vnpay',
                'shipping_fee'    => $shippingFee,
                'total_amount'    => $totalAmount,
                'shipping_address'=> json_encode($shippingInfo),
                'note'            => 'Giao hàng giờ hành chính',
                'created_at'      => $orderDate,
                'updated_at'      => $orderDate,
            ]);

            // 4. Tạo Order Item (Snapshot)
            DB::table('order_items')->insert([
                'order_id'           => $orderId,
                'product_variant_id' => $variant->id,
                // 'product_id' => $product->id, <--- Dòng này gây lỗi vì DB chưa có cột product_id
                'product_name'       => $product->name,
                'sku'                => $variant->sku,
                'thumbnail'          => $product->thumbnail ?? '/img/products/demo.jpg',
                'quantity'           => $quantity,
                'price'              => $price,
                'total_line'         => $price * $quantity,
                // 'size'            => ..., 'color' => ... (Nếu có)
            ]);

            // 5. Tạo Transaction
            DB::table('transactions')->insert([
                'order_id'       => $orderId,
                'payment_method' => 'vnpay',
                'trans_code'     => 'VNP-' . rand(1000000, 9999999),
                'amount'         => $totalAmount,
                'status'         => 'success',
                'created_at'     => $orderDate,
            ]);

            // 6. Cập nhật tồn kho & Ghi Log (FIXED ERROR HERE)
            
            // A. Trừ tồn kho trong bảng biến thể
            DB::table('product_variants')->where('id', $variant->id)->update([
                'stock_quantity' => $newStock
            ]);

            // B. Ghi log kho (Sửa tên cột cho đúng migration mới)
            DB::table('inventory_logs')->insert([
                'product_variant_id' => $variant->id,
                'user_id'            => $user->id, // Người mua làm giảm kho
                
                'change_amount'      => -$quantity,   // Số âm
                'old_quantity'       => $oldStock,    // Cột mới
                'new_quantity'       => $newStock,    // Cột mới (thay cho remaining_stock)
                
                'type'               => 'sale',
                'reference_id'       => $orderId,
                'note'               => 'Bán đơn hàng #' . $orderId,
                'created_at'         => $orderDate,
                'updated_at'         => $orderDate,
            ]);

            // 7. Tạo Review (50% cơ hội)
            if (rand(0, 1)) {
                DB::table('reviews')->insert([
                    'user_id'     => $user->id,
                    'product_id'  => $product->id,
                    'order_id'    => $orderId,
                    'rating'      => rand(4, 5),
                    'comment'     => 'Sản phẩm đẹp, giao hàng nhanh!',
                    'is_approved' => true,
                    'created_at'  => $orderDate->copy()->addDays(2),
                    'updated_at'  => $orderDate->copy()->addDays(2),
                ]);
            }
        }
    }
}