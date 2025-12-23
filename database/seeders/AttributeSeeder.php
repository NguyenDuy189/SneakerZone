<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AttributeSeeder extends Seeder
{
    public function run()
    {
        // --- BẮT ĐẦU SỬA: Tắt check khóa ngoại để Truncate ---
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('attributes')->truncate();
        DB::table('attribute_values')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        // --- KẾT THÚC SỬA ---

        // 1. Tạo Attribute
        $sizeId = DB::table('attributes')->insertGetId([
            'name' => 'Size', 'code' => 'size', 'type' => 'text', 'created_at' => now()
        ]);
        $colorId = DB::table('attributes')->insertGetId([
            'name' => 'Màu sắc', 'code' => 'color', 'type' => 'color_hex', 'created_at' => now()
        ]);

        // 2. Tạo Values (Size 36 -> 45)
        $sizeInserts = [];
        for ($i = 36; $i <= 45; $i++) {
            $sizeInserts[] = ['attribute_id' => $sizeId, 'value' => (string)$i, 'created_at' => now()];
        }
        // Thêm size lẻ
        $sizeInserts[] = ['attribute_id' => $sizeId, 'value' => '40.5', 'created_at' => now()];
        $sizeInserts[] = ['attribute_id' => $sizeId, 'value' => '42.5', 'created_at' => now()];
        
        DB::table('attribute_values')->insert($sizeInserts);

        // 3. Tạo Values (Màu sắc)
        $colors = [
            ['Trắng (White)', '#FFFFFF'],
            ['Đen (Black)', '#000000'],
            ['Đỏ (Red)', '#FF0000'],
            ['Xanh Dương (Blue)', '#0000FF'],
            ['Xám (Grey)', '#808080'],
            ['Kem (Cream)', '#FFFDD0'],
        ];

        foreach ($colors as $c) {
            DB::table('attribute_values')->insert([
                'attribute_id' => $colorId,
                'value' => $c[0],
                'meta_value' => $c[1],
                'created_at' => now()
            ]);
        }
    }
}