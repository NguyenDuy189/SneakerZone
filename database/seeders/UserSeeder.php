<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        // 1. Dọn dẹp dữ liệu cũ (Tắt check khóa ngoại để truncate không lỗi)
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('users')->truncate();
        DB::table('user_addresses')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Mật khẩu chung: 123456
        $password = Hash::make('123456');

        // =============================================
        // 2. TẠO TÀI KHOẢN ADMIN
        // =============================================
        DB::table('users')->insert([
            'full_name'  => 'Quản Trị Viên',
            'email'      => 'admin@gmail.com',
            'phone'      => '0900000000',
            'password'   => $password,
            'role'       => 'admin', // Role quy định quyền Admin
            'status'     => 'active',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // =============================================
        // 3. TẠO TÀI KHOẢN SHIPPER (GIAO HÀNG)
        // =============================================
        DB::table('users')->insert([
            'full_name'  => 'Nguyễn Văn Ship',
            'email'      => 'shipper@gmail.com',
            'phone'      => '0911111111',
            'password'   => $password,
            'role'       => 'shipper', // Role quy định quyền Shipper
            'status'     => 'active', // Shipper đang hoạt động
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // =============================================
        // 4. TẠO TÀI KHOẢN KHÁCH HÀNG
        // =============================================
        $customerId = DB::table('users')->insertGetId([
            'full_name'  => 'Nguyễn Văn Khách',
            'email'      => 'khach@gmail.com',
            'phone'      => '0922222222',
            'password'   => $password,
            'role'       => 'customer',
            'status'     => 'active',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // =============================================
        // 5. TẠO ĐỊA CHỈ CHO KHÁCH HÀNG
        // =============================================
        DB::table('user_addresses')->insert([
            [
                'user_id'      => $customerId,
                'contact_name' => 'Nguyễn Văn Khách',
                'phone'        => '0922222222',
                'city'         => 'Hà Nội',
                'district'     => 'Quận Cầu Giấy',
                'ward'         => 'Phường Dịch Vọng',
                'address'      => 'Số 10 ngõ 123 Xuân Thủy',
                'is_default'   => true, // Địa chỉ mặc định
                'created_at'   => now(),
                'updated_at'   => now()
            ],
            [
                'user_id'      => $customerId,
                'contact_name' => 'Anh Khách (Cơ quan)',
                'phone'        => '0922222222',
                'city'         => 'Hà Nội',
                'district'     => 'Quận Đống Đa',
                'ward'         => 'Phường Láng Hạ',
                'address'      => 'Tòa nhà VPBank',
                'is_default'   => false, // Địa chỉ phụ
                'created_at'   => now(),
                'updated_at'   => now()
            ]
        ]);
    }
}