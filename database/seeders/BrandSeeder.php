<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BrandSeeder extends Seeder
{
    public function run()
    {
        // --- THÊM 2 DÒNG NÀY ĐỂ TẮT CHECK KHÓA NGOẠI ---
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('brands')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        // -----------------------------------------------

        $brands = [
            ['name' => 'Nike', 'desc' => 'Just Do It. Thương hiệu thể thao hàng đầu thế giới.'],
            ['name' => 'Adidas', 'desc' => 'Impossible Is Nothing. Biểu tượng 3 sọc.'],
            ['name' => 'Puma', 'desc' => 'Forever Faster. Nhanh và phong cách.'],
            ['name' => 'New Balance', 'desc' => 'Chất lượng và sự thoải mái tối đa.'],
            ['name' => 'Converse', 'desc' => 'Biểu tượng văn hóa sát mặt đất.'],
            ['name' => 'Vans', 'desc' => 'Off The Wall. Dành cho Skater.'],
        ];

        foreach ($brands as $brand) {
            DB::table('brands')->insert([
                'name' => $brand['name'],
                'slug' => Str::slug($brand['name']),
                'description' => $brand['desc'],
                'logo_url' => '/img/brands/' . Str::slug($brand['name']) . '.png',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}