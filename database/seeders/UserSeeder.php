<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder {
    public function run() {
        // Tắt check khóa ngoại để truncate
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('users')->truncate();
        DB::table('user_addresses')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 1. ADMIN
        DB::table('users')->insert([
            'full_name' => 'Quản Trị Viên',
            'email' => 'admin@gmail.com',
            'phone' => '0999999999',
            'password' => Hash::make('123456'),
            'role' => 'admin',
            'status' => 'active',
            'created_at' => now(), 'updated_at' => now()
        ]);

        // 2. KHÁCH HÀNG THÂN THIẾT
        $userId = DB::table('users')->insertGetId([
            'full_name' => 'Nguyễn Văn An',
            'email' => 'khach1@gmail.com',
            'phone' => '0912345678',
            'password' => Hash::make('123456'),
            'role' => 'customer',
            'status' => 'active',
            'created_at' => now(), 'updated_at' => now()
        ]);

        // Địa chỉ của khách này
        DB::table('user_addresses')->insert([
            [
                'user_id' => $userId,
                'contact_name' => 'Nguyễn Văn An',
                'phone' => '0912345678',
                'city' => 'Hà Nội',
                'district' => 'Quận Cầu Giấy',
                'ward' => 'Phường Dịch Vọng',
                'address' => 'Số 10 ngõ 123 Xuân Thủy',
                'is_default' => true,
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'user_id' => $userId,
                'contact_name' => 'Anh An (Cơ quan)',
                'phone' => '0912345678',
                'city' => 'Hà Nội',
                'district' => 'Quận Đống Đa',
                'ward' => 'Phường Láng Hạ',
                'address' => 'Tòa nhà VPBank',
                'is_default' => false,
                'created_at' => now(), 'updated_at' => now()
            ]
        ]);
    }
}