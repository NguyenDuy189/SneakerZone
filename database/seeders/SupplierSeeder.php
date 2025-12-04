<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        // Tắt check khóa ngoại để truncate bảng cũ
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('suppliers')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $suppliers = [
            [
                'code'         => 'SUP-NIKE', // <--- Đã bổ sung
                'name'         => 'Nike Global Distribution',
                'contact_name' => 'Mr. Smith',
                'email'        => 'contact@nike-global.com', // Bổ sung luôn email cho chuẩn
                'phone'        => '+15550199',
                'address'      => 'Beaverton, Oregon, USA',
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
            [
                'code'         => 'SUP-ADIDAS', // <--- Đã bổ sung
                'name'         => 'Adidas Asia Pacific',
                'contact_name' => 'Ms. Yamamoto',
                'email'        => 'support@adidas.jp',
                'phone'        => '+8135550199',
                'address'      => 'Tokyo, Japan',
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
            [
                'code'         => 'SUP-LOCAL', // <--- Đã bổ sung
                'name'         => 'Công ty TNHH Phân Phối Thể Thao VN',
                'contact_name' => 'Anh Hùng',
                'email'        => 'hung.nguyen@sportvn.com',
                'phone'        => '0909123456',
                'address'      => 'KCN Tân Bình, TP.HCM',
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
        ];

        DB::table('suppliers')->insert($suppliers);
    }
}