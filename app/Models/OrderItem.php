<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ProductVariant;

class OrderItem extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'order_id',
        'product_variant_id',
        'product_name',
        'sku',
        'thumbnail',
        'quantity',
        'price',
        'total_line',
    ];

    protected $casts = [
        'price' => 'float',
        'total_line' => 'float',
    ];

    // QUAN HỆ
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id')->withTrashed();
    }

    // ACCESSORS
    public function getFormattedPriceAttribute()
    {
        return number_format($this->price, 0, ',', '.') . ' VNĐ';
    }

    public function getFormattedTotalAttribute()
    {
        return number_format($this->total_line, 0, ',', '.') . ' VNĐ';
    }

    public function product()
    {
        // Nếu bảng order_items không có product_id, hãy xem nó liên kết với product qua cột nào?
        // Mặc định Laravel tìm 'product_id'.
        return $this->belongsTo(Product::class, 'product_id'); 
    }

    // --- [THÊM ĐOẠN NÀY VÀO] ---
    public function productVariant()
    {
        // Giả định trong bảng order_items cột khóa ngoại là 'product_variant_id'
        // Nếu tên cột của bạn khác (ví dụ: 'variant_id'), hãy sửa tham số thứ 2
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}
