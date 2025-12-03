<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    public $timestamps = false; // Bảng này thường không cần created_at/updated_at

    protected $fillable = [
        'order_id', 'product_variant_id', 
        'sku', 'product_name', 'thumbnail', // Dữ liệu Snapshot
        'quantity', 'price', 'total_line'
    ];

    // Quan hệ ngược về Order
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // Quan hệ về biến thể (Để lấy thông tin realtime nếu cần, nhưng cẩn thận vì biến thể có thể bị xóa)
    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id')->withTrashed();
    }
}