<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FlashSaleItem extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'flash_sale_id',
        'product_variant_id',
        'price',
        'quantity',
        'sold_count', // Quan trọng: Cần có cột này trong DB để tính thống kê
    ];

    /**
     * Liên kết ngược về chiến dịch Flash Sale
     */
    public function flashSale()
    {
        return $this->belongsTo(FlashSale::class);
    }

    /**
     * Liên kết sang bảng biến thể sản phẩm (ProductVariant)
     * Đây là hàm bị thiếu gây ra lỗi RelationNotFoundException
     */
    public function productVariant()
    {
        // Giả sử khóa ngoại là product_variant_id
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}