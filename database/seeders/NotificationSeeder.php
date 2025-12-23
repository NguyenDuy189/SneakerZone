<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Notification;

class NotificationSeeder extends Seeder
{
    public function run()
    {
        // Tạo thông báo thành công (Màu xanh lá)
        Notification::create([
            'title' => 'Đơn hàng hoàn tất',
            'message' => 'Đơn hàng #DH001 đã được giao thành công cho khách.',
            'type' => 'success',
            'is_read' => false,
        ]);

        // Tạo thông báo cảnh báo (Màu cam)
        Notification::create([
            'title' => 'Sắp hết hàng',
            'message' => 'Sản phẩm "Áo thun Polo" chỉ còn 2 cái trong kho.',
            'type' => 'warning',
            'is_read' => false,
        ]);

        // Tạo thông báo lỗi (Màu đỏ - nếu bạn có setup màu này)
        Notification::create([
            'title' => 'Lỗi thanh toán',
            'message' => 'Giao dịch #TX999 bị ngân hàng từ chối.',
            'type' => 'danger',
            'is_read' => true,
        ]);

        // Tạo thông báo thông tin (Màu xanh dương)
        Notification::create([
            'title' => 'Chào mừng Admin',
            'message' => 'Hệ thống đã cập nhật phiên bản dashboard mới nhất.',
            'type' => 'info',
            'is_read' => true,
        ]);
    }
}
