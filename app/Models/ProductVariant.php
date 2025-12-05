<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    use HasFactory;

    // Đảm bảo fillable khớp với DB của bạn
    protected $fillable = [
        'product_id', 'sku', 'price', 'stock_quantity', 'image'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * ACCESSOR: Tự động lấy giá gốc chuẩn
     * Logic: Nếu giá variant > 0 thì lấy, nếu không thì lấy giá product cha
     * Gọi bằng: $variant->original_price_display
     */
    public function getOriginalPriceDisplayAttribute()
    {
        // 1. Kiểm tra giá riêng của biến thể
        if (!empty($this->price) && $this->price > 0) {
            return $this->price;
        }

        // 2. Nếu biến thể giá = 0, fallback về giá sản phẩm cha
        // Lưu ý: Đảm bảo đã eager load 'product' để tránh query n+1
        if ($this->relationLoaded('product') && $this->product) {
            return $this->product->price ?? 0;
        }

        // 3. Trường hợp chưa load relation, query nhẹ để lấy (phòng hờ)
        return $this->product->price ?? 0;
    }
}