<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // --- XÓA HOẶC COMMENT ĐOẠN NÀY ---
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
        // ----------------------------------

        // CHỈ GIỮ LẠI PHẦN GỌI SEEDER CỦA CHÚNG TA
        $this->call([
            SettingSeeder::class,
            BannerSeeder::class,
            BrandSeeder::class,
            CategorySeeder::class,
            SupplierSeeder::class,
            AttributeSeeder::class,
            UserSeeder::class,
            ProductSeeder::class,
            PurchaseOrderSeeder::class,
            DiscountSeeder::class,
            FlashSaleSeeder::class,
            OrderSeeder::class,
        ]);
    }
}