<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_code', 'user_id', 'status', 'payment_status', 'payment_method',
        'shipping_fee', 'total_amount', 'shipping_address', 'note'
    ];

    // Tự động ép kiểu JSON sang Mảng để dùng ngay trong View
    protected $casts = [
        'shipping_address' => 'array', // Rất quan trọng
        'total_amount' => 'decimal:2',
        'shipping_fee' => 'decimal:2',
    ];

    // Quan hệ: Đơn hàng thuộc về 1 khách (có thể null nếu khách vãng lai)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Quan hệ: Đơn hàng có nhiều chi tiết
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    // Helper: Lấy màu sắc badge cho trạng thái
    public function getStatusBadgeAttribute()
    {
        return match($this->status) {
            'pending' => 'bg-yellow-100 text-yellow-800',
            'processing' => 'bg-blue-100 text-blue-800',
            'shipping' => 'bg-purple-100 text-purple-800',
            'completed' => 'bg-green-100 text-green-800',
            'cancelled' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }
}