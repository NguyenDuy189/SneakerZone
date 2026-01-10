<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
        'order_id', // Có thể để đây dự phòng, dù controller hiện tại chưa dùng
        'rating',
        'comment',
        'is_approved',
    ];

    // Cast giúp chuyển đổi dữ liệu tự động khi lấy ra
    protected $casts = [
        'is_approved' => 'boolean',
        'rating'      => 'integer',
    ];

    // Quan hệ với User
    public function user() {
        return $this->belongsTo(User::class);
    }

    // Quan hệ với Product
    public function product() {
        return $this->belongsTo(Product::class);
    }

    // Quan hệ với Order (Optional)
    public function order() {
        return $this->belongsTo(Order::class);
    }
}