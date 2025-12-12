<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // Sử dụng xóa mềm

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'products';

    protected $fillable = [
        'name', 'slug', 'sku_code', 
        'description', 'short_description', 
        'brand_id', 'thumbnail', 'gallery', 
        'price_min', 'status', 'is_featured'
    ];

    // Tự động chuyển JSON trong DB thành Mảng PHP và ngược lại
    protected $casts = [
        'gallery' => 'array',
        'is_featured' => 'boolean',
        'price_min' => 'decimal:2',
    ];

    // --- QUAN HỆ ---

    // 1. Thuộc về 1 Thương hiệu
    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    // 2. Thuộc nhiều Danh mục (Bảng trung gian product_categories)
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'product_categories');
    }

    // 3. Có nhiều biến thể (Sẽ làm ở bước sau)
    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    // Quan hệ: 1 Sản phẩm thuộc 1 Danh mục
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // Quan hệ: 1 Sản phẩm có nhiều Ảnh Gallery (Bảng product_images)
    public function gallery_images()
    {
        return $this->hasMany(ProductImage::class);
    }

    // Quan hệ: 1 Sản phẩm có nhiều Biến thể (Size/Color)

    // Quan hệ: 1 Sản phẩm có nhiều Đánh giá
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}