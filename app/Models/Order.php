<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_code',
        'user_id',
        'status',          // pending, processing, shipping, completed, cancelled
        'payment_status',  // unpaid, paid
        'payment_method',
        'shipping_fee',
        'discount_amount',
        'total_amount',
        'shipping_address',
        'note'
    ];

    // Tự động chuyển JSON trong DB thành Array khi gọi $order->shipping_address
    protected $casts = [
        'shipping_address' => 'array',
        'total_amount' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scope lấy đơn thành công để tính doanh thu (Giả sử status 'completed' là thành công)
    public function scopeSuccessful($query)
    {
        return $query->where('status', 'completed');
    }
}