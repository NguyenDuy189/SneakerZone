<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id', 'sku', 'price', 'stock_quantity', 'image'
    ];

    protected $casts = [
        'price' => 'float',
        'stock_quantity' => 'integer',
    ];

    // =============================
    // RELATION
    // =============================
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // =============================
    // ACCESSOR: Giá gốc chuẩn
    // =============================
    public function getOriginalPriceDisplayAttribute(): float
    {
        // Nếu giá variant > 0 thì lấy
        if ($this->original_price > 0) {
            return round($this->original_price, 2);
        }

        // Nếu fallback từ product cha (giả sử product có cột price)
        if ($this->relationLoaded('product') && $this->product) {
            return round($this->product->price ?? 0, 2);
        }

        // Nếu chưa load relation
        return round($this->product()->value('price') ?? 0, 2);
    }

    // =============================
    // ACCESSOR: Giá hiển thị dưới dạng string có format VNĐ
    // =============================
    public function getOriginalPriceFormattedAttribute(): string
    {
        return number_format($this->original_price_display, 0, ',', '.') . '₫';
    }

    // =============================
    // CHECK STOCK
    // =============================
    public function isInStock(int $quantity = 1): bool
    {
        return $this->stock_quantity >= $quantity;
    }

    // =============================
    // HELPER: Giá hợp lệ cho Flash Sale
    // =============================
    public function getValidFlashSalePrice(float $flashSalePrice): float
    {
        // Giá Flash Sale phải nhỏ hơn giá gốc
        return $flashSalePrice >= $this->original_price_display
            ? $this->original_price_display * 0.95 // tự giảm 5% nếu nhập sai
            : $flashSalePrice;
    }

    public function attributeValues()
    {
        return $this->belongsToMany(
            AttributeValue::class,
            'variant_attribute_values',
            'product_variant_id',
            'attribute_value_id'
        );
    }

}
