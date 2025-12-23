<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FlashSaleSeeder extends Seeder {
    public function run() {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('flash_sales')->truncate();
        DB::table('flash_sale_items')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 1. Tạo chương trình Sale
        $saleId = DB::table('flash_sales')->insertGetId([
            'name' => 'Flash Sale Cuối Tuần',
            'start_time' => now()->subDay(), // Bắt đầu từ hôm qua
            'end_time' => now()->addDays(2), // Kết thúc sau 2 ngày nữa
            'is_active' => true,
            'created_at' => now(), 'updated_at' => now()
        ]);

        // 2. Lấy ngẫu nhiên 3 biến thể sản phẩm để Sale
        $variants = DB::table('product_variants')->limit(3)->get();

        foreach($variants as $v) {
            DB::table('flash_sale_items')->insert([
                'flash_sale_id' => $saleId,
                'product_variant_id' => $v->id,
                'price' => $v->sale_price * 0.8, // Giảm thêm 20% so với giá bán
                'quantity' => 20, // Chỉ sale 20 đôi
                'sold_count' => rand(0, 15) // Giả vờ đã bán được một ít
            ]);
        }
    }
}