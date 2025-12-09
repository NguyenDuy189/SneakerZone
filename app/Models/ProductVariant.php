<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductVariant extends Model
{
    use HasFactory,SoftDeletes;

    protected $fillable = [
        'product_id', 'sku', 'name', 'image_url', 
        'original_price', 'sale_price', 'stock_quantity'
    ];

    // Quan hệ: Thuộc về sản phẩm cha
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Quan hệ: Biến thể có nhiều giá trị thuộc tính (VD: Size 40, Màu Đỏ)
    // Sử dụng bảng trung gian 'variant_attribute_values'
    public function attributeValues()
    {
        return $this->belongsToMany(AttributeValue::class, 'variant_attribute_values', 'product_variant_id', 'attribute_value_id');
    }
    
    // Helper lấy tên thuộc tính để hiển thị (VD: "Đỏ - 40")
    public function getAttributeStringAttribute()
    {
        return $this->attributeValues->map(function($av) {
            return $av->attribute->name . ': ' . $av->value;
        })->implode(' / ');
    }
}