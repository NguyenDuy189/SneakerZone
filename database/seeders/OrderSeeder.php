<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderSeeder extends Seeder {
    public function run() {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('orders')->truncate();
        DB::table('order_items')->truncate();
        DB::table('transactions')->truncate();
        DB::table('reviews')->truncate();
        DB::table('carts')->truncate();
        DB::table('cart_items')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Lấy User Khách hàng (người thứ 2 trong bảng User)
        $user = DB::table('users')->where('role', 'customer')->first();
        // Lấy 1 biến thể sản phẩm bất kỳ
        $variant = DB::table('product_variants')->first();
        // Lấy tên SP gốc
        $prodName = DB::table('products')->where('id', $variant->product_id)->value('name');

        // 1. TẠO GIỎ HÀNG ẢO (Cart)
        $cartId = DB::table('carts')->insertGetId([
            'user_id' => $user->id,
            'created_at' => now(), 'updated_at' => now()
        ]);
        DB::table('cart_items')->insert([
            'cart_id' => $cartId,
            'product_variant_id' => $variant->id,
            'quantity' => 1,
            'created_at' => now(), 'updated_at' => now()
        ]);

        // 2. TẠO ĐƠN HÀNG ĐÃ HOÀN THÀNH (Completed)
        $orderId = DB::table('orders')->insertGetId([
            'order_code' => 'ORD-' . time(),
            'user_id' => $user->id,
            'status' => 'completed', // Đã giao xong
            'payment_status' => 'paid',
            'payment_method' => 'vnpay',
            'shipping_fee' => 30000,
            'total_amount' => $variant->sale_price + 30000,
            'shipping_address' => json_encode([
                'name' => 'Nguyễn Văn An',
                'phone' => '0912345678',
                'address' => '123 Xuân Thủy, Hà Nội'
            ]),
            'note' => 'Giao giờ hành chính',
            'created_at' => now()->subDays(2), // Mua cách đây 2 ngày
            'updated_at' => now()->subDays(2)
        ]);

        // 3. TẠO ORDER ITEMS (Quan trọng: Snapshot)
        DB::table('order_items')->insert([
            'order_id' => $orderId,
            'product_variant_id' => $variant->id,
            // Snapshot dữ liệu cứng
            'product_name' => $variant->name, 
            'sku' => $variant->sku,
            'thumbnail' => '/img/products/demo.jpg', // Giả lập
            'quantity' => 1,
            'price' => $variant->sale_price,
            'total_line' => $variant->sale_price
        ]);

        // 4. TẠO TRANSACTION (Lịch sử thanh toán)
        DB::table('transactions')->insert([
            'order_id' => $orderId,
            'payment_method' => 'vnpay',
            'trans_code' => 'VNPAY-' . rand(100000, 999999),
            'amount' => $variant->sale_price + 30000,
            'status' => 'success',
            'created_at' => now()->subDays(2)
        ]);

        // 5. TẠO REVIEW (Đánh giá sau khi mua)
        DB::table('reviews')->insert([
            'user_id' => $user->id,
            'product_id' => $variant->product_id,
            'order_id' => $orderId,
            'rating' => 5,
            'comment' => 'Giày quá đẹp, đóng gói cẩn thận. Sẽ ủng hộ shop tiếp!',
            'is_approved' => true,
            'created_at' => now()
        ]);
        
        // 6. Cập nhật Log kho (Trừ hàng khi bán)
        DB::table('inventory_logs')->insert([
            'product_variant_id' => $variant->id,
            'change_amount' => -1, // Trừ 1
            'remaining_stock' => $variant->stock_quantity - 1,
            'type' => 'sale',
            'reference_id' => $orderId,
            'note' => 'Bán đơn hàng #' . $orderId,
            'created_at' => now()->subDays(2)
        ]);
    }
}