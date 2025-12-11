<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductVariant extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_id',
        'sku',
        'name',
        'image_url',
        'original_price',
        'sale_price',
        'stock_quantity',
    ];

    // ================================
    // RELATIONSHIPS
    // ================================

    // Thuộc về Product cha
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Quan hệ N-N với Attribute Values
    public function attributeValues()
    {
        return $this->belongsToMany(
            AttributeValue::class,
            'variant_attribute_values',
            'product_variant_id',
            'attribute_value_id'
        );
    }

    // Lịch sử tồn kho
    public function inventoryLogs()
    {
        return $this->hasMany(InventoryLog::class, 'product_variant_id');
    }

    // ================================
    // ACCESSORS
    // ================================

    // Hiển thị giá gốc format đẹp
    public function getOriginalPriceDisplayAttribute()
    {
        return number_format($this->original_price, 0, ',', '.') . '₫';
    }

    // Hiển thị giá sale format đẹp
    public function getSalePriceDisplayAttribute()
    {
        return number_format($this->sale_price, 0, ',', '.') . '₫';
    }

    // Hiển thị thuộc tính dạng: Size: 41 / Màu: Đỏ
    public function getAttributeStringAttribute()
    {
        return $this->attributeValues
            ->map(fn($av) => "{$av->attribute->name}: {$av->value}")
            ->implode(' / ');
    }

    // ================================
    // BUSINESS LOGIC
    // ================================

    // Kiểm tra còn hàng
    public function isInStock(int $qty = 1): bool
    {
        return $this->stock_quantity >= $qty;
    }

    // Chuẩn hóa giá Flash Sale
    public function getValidFlashSalePrice(float $flashSalePrice): float
    {
        // Nếu giá Flash Sale >= giá gốc thì tự set lại giảm 5%
        return $flashSalePrice >= $this->original_price
            ? $this->original_price * 0.95
            : $flashSalePrice;
    }
}
