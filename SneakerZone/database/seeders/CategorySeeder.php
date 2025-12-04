<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run()
    {
        // --- QUAN TRỌNG: Tắt check khóa ngoại để được phép xóa dữ liệu cũ ---
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('categories')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        // -------------------------------------------------------------------

        $categories = [
            [
                'name' => 'Giày Nam',
                'children' => ['Bóng Rổ', 'Chạy Bộ', 'Bóng Đá', 'Lifestyle (Thời trang)', 'Gym & Training']
            ],
            [
                'name' => 'Giày Nữ',
                'children' => ['Chạy Bộ', 'Yoga', 'Lifestyle (Thời trang)', 'Sandal', 'Slip-on']
            ],
            [
                'name' => 'Trẻ Em',
                'children' => ['Bé Trai (3-8 tuổi)', 'Bé Gái (3-8 tuổi)', 'Tuổi Teen']
            ],
            [
                'name' => 'Phụ Kiện',
                'children' => ['Tất / Vớ', 'Balo & Túi', 'Mũ / Nón', 'Vệ sinh giày']
            ]
        ];

        foreach ($categories as $cat) {
            // 1. Tạo Danh mục Cha -> Lấy ID
            $parentId = DB::table('categories')->insertGetId([
                'name' => $cat['name'],
                'slug' => Str::slug($cat['name']),
                'parent_id' => null,
                'level' => 0,
                'is_visible' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 2. Tạo Danh mục Con theo ID cha
            foreach ($cat['children'] as $childName) {
                DB::table('categories')->insert([
                    'name' => $childName,
                    'slug' => Str::slug($childName . '-' . $cat['name']), // Slug duy nhất
                    'parent_id' => $parentId,
                    'level' => 1,
                    'is_visible' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}