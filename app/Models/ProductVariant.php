<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductVariant extends Model
{
    use HasFactory, SoftDeletes;

    // File: app/Models/ProductVariant.php
    // File: app/Models/ProductVariant.php
    
    protected $fillable = [
        'product_id',
        'sku',
        'name',       // <--- BẮT BUỘC THÊM CÁI NÀY
        'image_url',     // (Giữ lại nếu database của bạn dùng tên này)
        'original_price',
        'sale_price',
        'stock_quantity',
        'weight',
        'is_active'
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

    // 1. Định nghĩa quan hệ 'color' để Controller gọi được with('color')
    public function color()
    {
        // Trả về AttributeValue có loại là 'color' hoặc 'màu'
        return $this->belongsToMany(AttributeValue::class, 'variant_attribute_values', 'product_variant_id', 'attribute_value_id')
                    ->whereHas('attribute', function ($query) {
                        $query->where('code', 'color')
                              ->orWhere('name', 'like', '%Màu%');
                    });
    }

    // 2. Định nghĩa quan hệ 'size' để Controller gọi được with('size')
    public function size()
    {
        // Trả về AttributeValue có loại là 'size' hoặc 'kích thước'
        return $this->belongsToMany(AttributeValue::class, 'variant_attribute_values', 'product_variant_id', 'attribute_value_id')
                    ->whereHas('attribute', function ($query) {
                        $query->where('code', 'size')
                              ->orWhere('name', 'like', '%Size%')
                              ->orWhere('name', 'like', '%Kích%');
                    });
    }

    // Chuẩn hóa giá Flash Sale
    public function getValidFlashSalePrice(float $flashSalePrice): float
    {
        // Nếu giá Flash Sale >= giá gốc thì tự set lại giảm 5%
        return $flashSalePrice >= $this->original_price
            ? $this->original_price * 0.95
            : $flashSalePrice;
    }

    // Giúp gọi $variant->color sẽ trả về object Attributes Value (hoặc null)
    public function getColorAttribute()
    {
        return $this->attributeValues->first(function ($item) {
            // Tìm thuộc tính có tên chứa "Color" hoặc "Màu"
            return str_contains(strtolower($item->attribute->name ?? ''), 'color') 
                || str_contains(strtolower($item->attribute->name ?? ''), 'màu');
        });
    }

    // Giúp gọi $variant->size sẽ trả về object Attributes Value
    public function getSizeAttribute()
    {
        return $this->attributeValues->first(function ($item) {
            // Tìm thuộc tính có tên chứa "Size" hoặc "Kích thước"
            return str_contains(strtolower($item->attribute->name ?? ''), 'size') 
                || str_contains(strtolower($item->attribute->name ?? ''), 'kích');
        });
    }

    public function getPriceAttribute()
    {
        // Ưu tiên lấy giá sale, nếu bằng 0 thì lấy giá gốc
        return $this->sale_price > 0 ? $this->sale_price : $this->original_price;
    }
}
