<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product; 
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str; 

class MoreProductsSeeder extends Seeder
{
    public function run()
    {
        // 1. Lấy hoặc tạo Brand
        $brandId = DB::table('brands')->first()->id ?? DB::table('brands')->insertGetId(['name' => 'Nike', 'slug' => 'nike']);

        // 2. Định nghĩa danh sách sản phẩm
        $products = [
            [
                'name' => 'Nike Air Force 1 Low',
                'sku_code' => 'NIKE-AF1-001',
                'price_min' => 2500000,
                'is_featured' => 1,
            ],
            [
                'name' => 'Adidas Ultraboost Light',
                'sku_code' => 'ADI-UB-002',
                'price_min' => 4200000,
                'is_featured' => 1,
            ],
            [
                'name' => 'Air Jordan 1 Retro High OG',
                'sku_code' => 'JD1-HIGH-003',
                'price_min' => 5500000,
                'is_featured' => 0,
            ]
        ];

        // 3. Vòng lặp xử lý từng sản phẩm
        foreach ($products as $p) {
            $slug = Str::slug($p['name']); // Tạo slug từ tên

            // QUAN TRỌNG: Kiểm tra trùng bằng SLUG (cái đang gây lỗi)
            Product::updateOrCreate(
                ['slug' => $slug], // Điều kiện: Nếu tìm thấy slug này rồi...
                [                  // ...thì cập nhật các thông tin dưới đây
                    'name' => $p['name'],
                    'sku_code' => $p['sku_code'],
                    'description' => '<p>Mô tả chi tiết cho ' . $p['name'] . '</p>',
                    'short_description' => 'Sản phẩm hot nhất năm.',
                    'price_min' => $p['price_min'],
                    'thumbnail' => 'shoe_sample.jpg',
                    'status' => 1,
                    'is_featured' => $p['is_featured'],
                    'brand_id' => $brandId,
                ]
            );
        }
    }
}