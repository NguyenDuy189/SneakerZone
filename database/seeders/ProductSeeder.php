<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run()
    {
        // ===========================
        // 1. XÓA DỮ LIỆU CŨ (Reset sạch)
        // ===========================
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('product_images')->truncate();
        DB::table('product_variants')->truncate();
        DB::table('product_categories')->truncate();
        DB::table('products')->truncate();
        DB::table('inventory_logs')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // ===========================
        // 2. CHUẨN BỊ DỮ LIỆU NỀN
        // ===========================
        // Lấy Brand ID (Giả lập nếu chưa có)
        $nikeId = DB::table('brands')->where('name', 'Nike')->value('id') ?? 1;
        $adidasId = DB::table('brands')->where('name', 'Adidas')->value('id') ?? 2;

        // Lấy Size ID từ attribute_values
        $sizeIds = DB::table('attribute_values')
            ->whereIn('value', ['40', '41', '42'])
            ->pluck('id')
            ->toArray();

        // Fallback: Nếu chưa có attribute, tạo dữ liệu giả để seeder không chết
        if (empty($sizeIds)) {
             $this->command->info("Chưa có Attributes, hệ thống tự tạo size mẫu...");
             // Kiểm tra hoặc tạo Attribute Size
             $attrId = DB::table('attributes')->where('slug', 'size')->value('id');
             if (!$attrId) {
                 $attrId = DB::table('attributes')->insertGetId(['name' => 'Size', 'slug' => 'size']);
             }
             
             // Tạo value 40, 41, 42
             foreach(['40','41','42'] as $s) {
                 $existId = DB::table('attribute_values')->where('attribute_id', $attrId)->where('value', $s)->value('id');
                 $sizeIds[] = $existId ? $existId : DB::table('attribute_values')->insertGetId(['attribute_id' => $attrId, 'value' => $s, 'slug' => $s]);
             }
        }

        // Map Category
        $categoryMap = DB::table('categories')->pluck('id', 'name')->toArray();
        // Fix lỗi nếu chưa có category nào
        if(empty($categoryMap)) {
             $catId = DB::table('categories')->insertGetId(['name' => 'Giày Sneaker', 'slug' => 'giay-sneaker']);
             $categoryMap = ['Giày Sneaker' => $catId];
        }

        // ===========================
        // 3. DANH SÁCH SẢN PHẨM MẪU
        // ===========================
        $productBaseNames = [
            'Air Force 1', 'Air Max 90', 'Jordan 1 Low', 'Pegasus 39', 'Ultraboost 22', 
            'Stan Smith', 'Cortez', 'React Infinity', 'Zoom Fly 5', 'Blazer Mid',
            'Superstar', 'NMD R1', 'Gazelle', 'Yeezy 350', 'Adizero SL'
        ];

        $brands = [$nikeId, $adidasId];
        $catIds = array_values($categoryMap);

        $products = [];

        // Tạo danh sách 20 sản phẩm ngẫu nhiên
        for ($i = 0; $i < 20; $i++) {
            $brand = $brands[array_rand($brands)];
            $baseName = $productBaseNames[array_rand($productBaseNames)];
            $name = ($brand == $nikeId ? 'Nike ' : 'Adidas ') . $baseName . ' ' . rand(2023, 2025);
            $price = rand(20, 60) * 100000; // 2tr - 6tr
            $cat = $catIds[array_rand($catIds)];

            $products[] = ['name' => $name, 'brand' => $brand, 'price' => $price, 'cat' => $cat];
        }

        // ===========================
        // 4. INSERT DATA
        // ===========================
        foreach ($products as $p) {
            
            // A. Tạo Product
            $productId = DB::table('products')->insertGetId([
                'name'              => $p['name'],
                'slug'              => Str::slug($p['name'] . '-' . Str::random(4)),
                'sku_code'          => 'SP-' . strtoupper(Str::random(6)),
                'brand_id'          => $p['brand'],
                'category_id'       => $p['cat'],
                'description'       => '<p>Mô tả chi tiết sản phẩm ' . $p['name'] . '</p>',
                'short_description' => $p['name'] . ' - Hàng chính hãng, chất lượng cao.',
                'price_min'         => $p['price'], // Giá hiển thị thấp nhất
                'status'            => 'published',
                'is_featured'       => rand(0, 1),
                'thumbnail'         => '/img/products/demo.jpg', // Ảnh mặc định
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);

            // B. Gắn Category (Bảng trung gian)
            DB::table('product_categories')->insertOrIgnore([
                'product_id'  => $productId,
                'category_id' => $p['cat'],
            ]);

            // C. Tạo Variants (Biến thể theo Size)
            foreach ($sizeIds as $sizeId) {
                $sizeValue = DB::table('attribute_values')->where('id', $sizeId)->value('value');
                $stockQty = rand(10, 50);

                // --- SỬA LỖI Ở ĐÂY: Bỏ cột 'color' và 'size' ---
                $variantId = DB::table('product_variants')->insertGetId([
                    'product_id'     => $productId,
                    'sku'            => 'SKU-' . $productId . '-SZ' . $sizeValue,
                    'name'           => $p['name'] . ' - Size ' . $sizeValue, // Tên biến thể đã chứa size
                    
                    // 'color'       => 'Default', // ĐÃ XÓA VÌ DB KHÔNG CÓ CỘT NÀY
                    // 'size'        => $sizeValue, // ĐÃ XÓA VÌ DB KHÔNG CÓ CỘT NÀY
                    
                    'original_price' => $p['price'] + 500000,
                    'sale_price'     => $p['price'],
                    'stock_quantity' => $stockQty,
                    'image_url'      => '/img/products/demo.jpg',
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]);

                // D. Ghi Log Inventory (Dùng cấu trúc bảng mới nhất)
                DB::table('inventory_logs')->insert([
                    'product_variant_id' => $variantId,
                    'user_id'            => 1, // Giả định ID 1 là Admin
                    'type'               => 'import',
                    'old_quantity'       => 0,
                    'change_amount'      => $stockQty,
                    'new_quantity'       => $stockQty,
                    'reference_type'     => 'seeder',
                    'reference_id'       => null,
                    'note'               => 'Khởi tạo dữ liệu mẫu',
                    'created_at'         => now(),
                    'updated_at'         => now(),
                ]);
            }
        }

        $this->command->info('✅ Seeder hoàn tất! Đã tạo ' . count($products) . ' sản phẩm.');
    }
}