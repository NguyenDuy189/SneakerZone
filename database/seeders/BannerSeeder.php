<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BannerSeeder extends Seeder {
    public function run() {
        DB::table('banners')->truncate();
        DB::table('banners')->insert([
            [
                'title' => 'BST Mùa Hè 2024',
                'image_url' => '/banners/summer-sale-2024.jpg',
                'link' => '/collections/summer',
                'position' => 'home_slider',
                'is_active' => true,
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'title' => 'Nike Air Jordan - Huyền Thoại Trở Lại',
                'image_url' => '/banners/jordan-comeback.jpg',
                'link' => '/brands/nike',
                'position' => 'home_slider',
                'is_active' => true,
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'title' => 'Black Friday - Sale up to 50%',
                'image_url' => '/banners/black-friday.jpg',
                'link' => '/flash-sale',
                'position' => 'sidebar',
                'is_active' => true,
                'created_at' => now(), 'updated_at' => now()
            ]
        ]);
    }
}