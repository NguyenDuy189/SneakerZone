<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    // Cập nhật mảng fillable khớp với Migration của bạn
    protected $fillable = [
        'order_id',
        'payment_method',
        'trans_code',    // Sửa từ transaction_code thành trans_code
        'amount',
        'status',
        'response_log',  // Thêm cột này
    ];

    // Helper hiển thị tên phương thức thanh toán
    public function getMethodNameAttribute()
    {
        return match($this->payment_method) {
            'cod' => 'Thanh toán khi nhận hàng (COD)',
            'vnpay' => 'Ví VNPAY',
            'momo' => 'Ví MoMo',
            'banking' => 'Chuyển khoản ngân hàng',
            default => 'Khác',
        };
    }
}