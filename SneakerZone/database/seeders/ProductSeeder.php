<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run()
    {
        // Xóa sạch dữ liệu cũ liên quan sản phẩm
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('products')->truncate();
        DB::table('product_categories')->truncate();
        DB::table('product_variants')->truncate();
        DB::table('variant_attribute_values')->truncate();
        DB::table('inventory_logs')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Lấy ID các dữ liệu nền cần thiết
        $nikeId = DB::table('brands')->where('name', 'Nike')->value('id');
        $dasId = DB::table('brands')->where('name', 'Adidas')->value('id');
        
        // Lấy ID Size (Giả sử ta lấy các size phổ biến: 40, 41, 42)
        $sizeIds = DB::table('attribute_values')
            ->whereIn('value', ['40', '41', '42'])
            ->pluck('id')->toArray();

        // DANH SÁCH SẢN PHẨM THỰC TẾ
        $realShoes = [
            // NIKE
            ['name' => 'Nike Air Force 1 Low 07', 'brand' => $nikeId, 'price' => 2900000, 'cat' => 'Lifestyle (Thời trang)'],
            ['name' => 'Nike Air Jordan 1 High Chicago', 'brand' => $nikeId, 'price' => 5500000, 'cat' => 'Bóng Rổ'],
            ['name' => 'Nike Pegasus 40', 'brand' => $nikeId, 'price' => 3500000, 'cat' => 'Chạy Bộ'],
            ['name' => 'Nike Dunk Low Panda', 'brand' => $nikeId, 'price' => 3200000, 'cat' => 'Lifestyle (Thời trang)'],
            ['name' => 'Nike Zoom Fly 5', 'brand' => $nikeId, 'price' => 4100000, 'cat' => 'Chạy Bộ'],
            
            // ADIDAS
            ['name' => 'Adidas Ultraboost Light', 'brand' => $dasId, 'price' => 4500000, 'cat' => 'Chạy Bộ'],
            ['name' => 'Adidas Stan Smith', 'brand' => $dasId, 'price' => 2500000, 'cat' => 'Lifestyle (Thời trang)'],
            ['name' => 'Adidas Superstar', 'brand' => $dasId, 'price' => 2600000, 'cat' => 'Lifestyle (Thời trang)'],
            ['name' => 'Adidas Forum Low', 'brand' => $dasId, 'price' => 2800000, 'cat' => 'Bóng Rổ'],
            ['name' => 'Adidas Yeezy Boost 350 V2', 'brand' => $dasId, 'price' => 6500000, 'cat' => 'Lifestyle (Thời trang)'],
        ];

        foreach ($realShoes as $shoe) {
            // 1. Insert PRODUCT
            $productId = DB::table('products')->insertGetId([
                'name' => $shoe['name'],
                'slug' => Str::slug($shoe['name']),
                'sku_code' => 'SKU-' . strtoupper(Str::random(6)),
                'brand_id' => $shoe['brand'],
                'description' => '<p>Mô tả chi tiết về ' . $shoe['name'] . ' chính hãng...</p>',
                'short_description' => $shoe['name'] . ' - Hàng chính hãng, full box.',
                'price_min' => $shoe['price'],
                'status' => 'published',
                'is_featured' => ($shoe['price'] > 4000000), // Giá cao thì cho làm Featured
                'thumbnail' => '/img/products/' . Str::slug($shoe['name']) . '.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 2. Insert PRODUCT_CATEGORY (Tìm ID category theo tên)
            // Lưu ý: Đoạn này tìm tương đối theo tên trong mảng realShoes
            $catId = DB::table('categories')->where('name', 'like', '%' . $shoe['cat'] . '%')->value('id');
            if ($catId) {
                DB::table('product_categories')->insert([
                    'product_id' => $productId,
                    'category_id' => $catId
                ]);
            }

            // 3. Insert VARIANTS (Mỗi giày tạo 3 Size: 40, 41, 42)
            foreach ($sizeIds as $sId) {
                $sizeName = DB::table('attribute_values')->where('id', $sId)->value('value');
                
                // Insert Variant
                $variantId = DB::table('product_variants')->insertGetId([
                    'product_id' => $productId,
                    'sku' => 'SKU-' . $productId . '-SZ' . $sizeName,
                    'name' => $shoe['name'] . ' - Size ' . $sizeName,
                    'original_price' => $shoe['price'] + 500000, // Giá gốc cao hơn
                    'sale_price' => $shoe['price'],
                    'stock_quantity' => rand(5, 50), // Random tồn kho
                    'image_url' => '/img/products/' . Str::slug($shoe['name']) . '.jpg',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Insert Variant Attributes (Nối variant với size)
                DB::table('variant_attribute_values')->insert([
                    'product_variant_id' => $variantId,
                    'attribute_value_id' => $sId
                ]);

                // Insert Inventory Log (Tạo lịch sử nhập đầu kỳ)
                DB::table('inventory_logs')->insert([
                    'product_variant_id' => $variantId,
                    'change_amount' => 50,
                    'remaining_stock' => 50,
                    'type' => 'import',
                    'note' => 'Khởi tạo tồn kho mẫu',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}