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
}
