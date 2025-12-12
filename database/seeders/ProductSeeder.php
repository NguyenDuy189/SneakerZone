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
        // 1. XÓA DỮ LIỆU CŨ
        // ===========================
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('product_images')->truncate();
        DB::table('product_variants')->truncate();
        DB::table('product_categories')->truncate();
        DB::table('products')->truncate();
        DB::table('inventory_logs')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // ===========================
        // 2. LẤY DỮ LIỆU NỀN
        // ===========================
        $nikeId = DB::table('brands')->where('name', 'Nike')->value('id') ?? 1;
        $adidasId = DB::table('brands')->where('name', 'Adidas')->value('id') ?? 2;

        $sizeIds = DB::table('attribute_values')
            ->whereIn('value', ['40', '41', '42'])
            ->pluck('id')
            ->toArray();

        if (empty($sizeIds)) {
            $this->command->warn("Không tìm thấy size (40, 41, 42). Vui lòng chạy AttributeSeeder trước.");
            return;
        }

        $categoryMap = DB::table('categories')
            ->select('id', 'name')
            ->get()
            ->mapWithKeys(fn($c) => [Str::lower($c->name) => $c->id])
            ->toArray();

        // ===========================
        // 3. DANH SÁCH TÊN SẢN PHẨM MẪU
        // ===========================
        $productBaseNames = [
            'Air Force', 'Air Max', 'Jordan', 'Pegasus', 'Ultraboost', 
            'Stan Smith', 'Cortez', 'React Infinity', 'Zoom Fly', 'Blazer',
            'Superstar', 'NMD', 'Gazelle', 'Yeezy Boost', 'Adizero', 
            'KD', 'LeBron', 'Metcon', 'Flyknit', 'Epic React'
        ];

        $brands = [$nikeId, $adidasId];
        $categories = array_keys($categoryMap);

        $products = [];

        // Tạo 25 sản phẩm ngẫu nhiên
        for ($i = 0; $i < 25; $i++) {
            $brand = $brands[array_rand($brands)];
            $name = ($brand == $nikeId ? 'Nike ' : 'Adidas ') . $productBaseNames[array_rand($productBaseNames)] . ' ' . rand(2020, 2025);
            $price = rand(2000000, 6000000);
            $cat = $categories[array_rand($categories)];

            $products[] = ['name' => $name, 'brand' => $brand, 'price' => $price, 'cat' => $cat];
        }

        // ===========================
        // 4. TẠO SẢN PHẨM & VARIANTS
        // ===========================
        foreach ($products as $p) {
            $catId = $categoryMap[$p['cat']] ?? array_values($categoryMap)[0];

            // A. Tạo product
            $productId = DB::table('products')->insertGetId([
                'name'              => $p['name'],
                'slug'              => Str::slug($p['name'] . '-' . Str::random(4)),
                'sku_code'          => 'SP-' . strtoupper(Str::random(6)),
                'brand_id'          => $p['brand'],
                'category_id'       => $catId,
                'description'       => '<p>Mô tả chi tiết ' . $p['name'] . '</p>',
                'short_description' => $p['name'] . ' - Hàng chính hãng.',
                'price_min'         => $p['price'],
                'status'            => 'published',
                'is_featured'       => $p['price'] > 4000000,
                'thumbnail'         => '/img/products/demo.jpg',
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);

            // B. Gắn product_category
            DB::table('product_categories')->insert([
                'product_id'  => $productId,
                'category_id' => $catId,
            ]);

            // C. Tạo variants
            foreach ($sizeIds as $sizeId) {
                $sizeValue = DB::table('attribute_values')->where('id', $sizeId)->value('value');
                $stockQty = rand(10, 50);

                $variantId = DB::table('product_variants')->insertGetId([
                    'product_id'     => $productId,
                    'sku'            => 'SKU-' . $productId . '-SZ' . $sizeValue,
                    'name'           => $p['name'] . ' - Size ' . $sizeValue,
                    'original_price' => $p['price'] + rand(200000, 500000),
                    'sale_price'     => $p['price'],
                    'stock_quantity' => $stockQty,
                    'image_url'      => '/img/products/demo.jpg',
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]);

                // D. Log tồn kho
                DB::table('inventory_logs')->insert([
                    'product_variant_id' => $variantId,
                    'change_amount'      => $stockQty,
                    'remaining_stock'    => $stockQty,
                    'type'               => 'import',
                    'note'               => 'Seeder khởi tạo tồn kho',
                    'created_at'         => now(),
                    'updated_at'         => now(),
                ]);
            }
        }

        $this->command->info('Seeder đã tạo ' . count($products) . ' sản phẩm thành công!');
    }
}
