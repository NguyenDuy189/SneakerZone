<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run()
    {
        // 1. Dọn dẹp dữ liệu cũ
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('products')->truncate();
        DB::table('product_categories')->truncate();
        DB::table('product_variants')->truncate();
        DB::table('variant_attribute_values')->truncate();
        DB::table('inventory_logs')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 2. Lấy dữ liệu nền (Giả sử BrandSeeder và AttributeSeeder đã chạy)
        $nikeId = DB::table('brands')->where('name', 'Nike')->value('id') ?? 1;
        $dasId  = DB::table('brands')->where('name', 'Adidas')->value('id') ?? 2;
        
        // Lấy ID Size (40, 41, 42)
        $sizeIds = DB::table('attribute_values')
            ->whereIn('value', ['40', '41', '42'])
            ->pluck('id')->toArray();

        // Nếu chưa có size thì bỏ qua để tránh lỗi
        if (empty($sizeIds)) {
            $this->command->info('Vui lòng chạy AttributeSeeder trước!');
            return;
        }

        // DANH SÁCH SẢN PHẨM MẪU
        $realShoes = [
            // NIKE
            ['name' => 'Nike Air Force 1 Low 07', 'brand' => $nikeId, 'price' => 2900000, 'cat' => 'Lifestyle'],
            ['name' => 'Nike Air Jordan 1 High Chicago', 'brand' => $nikeId, 'price' => 5500000, 'cat' => 'Bóng Rổ'],
            ['name' => 'Nike Pegasus 40', 'brand' => $nikeId, 'price' => 3500000, 'cat' => 'Chạy Bộ'],
            ['name' => 'Nike Dunk Low Panda', 'brand' => $nikeId, 'price' => 3200000, 'cat' => 'Lifestyle'],
            ['name' => 'Nike Zoom Fly 5', 'brand' => $nikeId, 'price' => 4100000, 'cat' => 'Chạy Bộ'],
            
            // ADIDAS
            ['name' => 'Adidas Ultraboost Light', 'brand' => $dasId, 'price' => 4500000, 'cat' => 'Chạy Bộ'],
            ['name' => 'Adidas Stan Smith', 'brand' => $dasId, 'price' => 2500000, 'cat' => 'Lifestyle'],
            ['name' => 'Adidas Superstar', 'brand' => $dasId, 'price' => 2600000, 'cat' => 'Lifestyle'],
            ['name' => 'Adidas Forum Low', 'brand' => $dasId, 'price' => 2800000, 'cat' => 'Bóng Rổ'],
            ['name' => 'Adidas Yeezy Boost 350 V2', 'brand' => $dasId, 'price' => 6500000, 'cat' => 'Lifestyle'],
        ];

        foreach ($realShoes as $shoe) {
            // A. Insert PRODUCT
            $productId = DB::table('products')->insertGetId([
                'name'              => $shoe['name'],
                'slug'              => Str::slug($shoe['name']),
                'sku_code'          => 'SP-' . strtoupper(Str::random(6)),
                'brand_id'          => $shoe['brand'],
                'description'       => '<p>Mô tả chi tiết về ' . $shoe['name'] . ' chính hãng...</p>',
                'short_description' => $shoe['name'] . ' - Hàng chính hãng, full box.',
                'price_min'         => $shoe['price'],
                'status'            => 'published',
                'is_featured'       => ($shoe['price'] > 4000000),
                'thumbnail'         => '/img/products/demo.jpg', // Dùng ảnh demo chung để tránh lỗi 404
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);

            // B. Insert PRODUCT_CATEGORY
            $catId = DB::table('categories')->where('name', 'like', '%' . $shoe['cat'] . '%')->value('id');
            if ($catId) {
                DB::table('product_categories')->insert([
                    'product_id'  => $productId,
                    'category_id' => $catId
                ]);
            }

            // C. Insert VARIANTS (Size 40, 41, 42)
            foreach ($sizeIds as $sId) {
                $sizeName = DB::table('attribute_values')->where('id', $sId)->value('value');
                
                // Random số lượng tồn kho cho biến thể này
                $initialStock = rand(10, 50); 

                // 1. Tạo biến thể
                $variantId = DB::table('product_variants')->insertGetId([
                    'product_id'     => $productId,
                    'sku'            => 'SKU-' . $productId . '-SZ' . $sizeName,
                    'name'           => $shoe['name'] . ' - Size ' . $sizeName,
                    'original_price' => $shoe['price'] + 500000,
                    'sale_price'     => $shoe['price'],
                    'stock_quantity' => $initialStock, // <--- Số lượng tồn thực tế
                    'image_url'      => '/img/products/demo.jpg',
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]);

                // 2. Gắn thuộc tính Size
                DB::table('variant_attribute_values')->insert([
                    'product_variant_id' => $variantId,
                    'attribute_value_id' => $sId
                ]);

                // 3. Ghi Log Nhập kho (FIX LỖI TẠI ĐÂY)
                DB::table('inventory_logs')->insert([
                    'product_variant_id' => $variantId,
                    'user_id'            => 1, // ID Admin mặc định
                    
                    'change_amount'      => $initialStock, // +Số lượng nhập
                    'old_quantity'       => 0,             // Trước đó là 0
                    'new_quantity'       => $initialStock, // Sau khi nhập
                    
                    'type'               => 'import',
                    'note'               => 'Khởi tạo tồn kho đầu kỳ (Seeder)',
                    'created_at'         => now(),
                    'updated_at'         => now(),
                ]);
            }
        }
    }
}
