<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SupplierSeeder extends Seeder
{
    public function run()
    {
        // --- CHÈN 2 DÒNG NÀY VÀO ĐẦU HÀM ---
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('suppliers')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        // ------------------------------------

        DB::table('suppliers')->insert([
            ['name' => 'Nike Global Distribution', 'contact_name' => 'Mr. Smith', 'phone' => '+15550199', 'address' => 'Beaverton, Oregon, USA', 'created_at' => now()],
            ['name' => 'Adidas Asia Pacific', 'contact_name' => 'Ms. Yamamoto', 'phone' => '+8135550199', 'address' => 'Tokyo, Japan', 'created_at' => now()],
            ['name' => 'Công ty TNHH Phân Phối Thể Thao VN', 'contact_name' => 'Anh Hùng', 'phone' => '0909123456', 'address' => 'KCN Tân Bình, TP.HCM', 'created_at' => now()],
        ]);
    }
}