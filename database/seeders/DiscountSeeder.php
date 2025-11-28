<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DiscountSeeder extends Seeder
{
    public function run()
    {
        // 1. Tắt khóa ngoại để xóa dữ liệu cũ an toàn
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('discounts')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 2. Tạo dữ liệu mã giảm giá
        DB::table('discounts')->insert([
            // Mã 1: Giảm tiền cố định (50k) cho người mới
            [
                'code' => 'WELCOME50',
                'type' => 'fixed', // Trừ tiền trực tiếp
                'value' => 50000,
                'max_usage' => 1000, // Dùng được 1000 lần
                'used_count' => 5,   // Đã dùng 5 lần
                'min_order_amount' => 500000, // Đơn tối thiểu 500k
                'start_date' => now(),
                'end_date' => now()->addYear(), // Hạn 1 năm
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Mã 2: Giảm theo phần trăm (10%)
            [
                'code' => 'VIP10',
                'type' => 'percentage', // Trừ %
                'value' => 10, // 10%
                'max_usage' => 100,
                'used_count' => 0,
                'min_order_amount' => 1000000, // Đơn tối thiểu 1 triệu
                'start_date' => now(),
                'end_date' => now()->addMonth(), // Hạn 1 tháng
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Mã 3: Mã đã hết hạn (Để test logic lỗi)
            [
                'code' => 'HET_HAN',
                'type' => 'fixed',
                'value' => 20000,
                'max_usage' => 100,
                'used_count' => 0,
                'min_order_amount' => 0,
                'start_date' => now()->subMonth(), // Bắt đầu từ tháng trước
                'end_date' => now()->subDay(),     // Hết hạn hôm qua
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Mã 4: Mã đã hết lượt dùng (Để test logic hết lượt)
            [
                'code' => 'HET_LUOT',
                'type' => 'fixed',
                'value' => 100000,
                'max_usage' => 10,
                'used_count' => 10, // Đã dùng hết 10/10
                'min_order_amount' => 0,
                'start_date' => now(),
                'end_date' => now()->addYear(),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}