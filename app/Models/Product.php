<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'products';

    // Đã chuẩn hóa tên cột theo View/Controller mới nhất
    protected $fillable = [
        'name', 
        'slug', 
        'sku_code', 
        'description', 
        'short_description', 
        'brand_id', 
        'category_id', // Quan trọng: Khóa ngoại danh mục
        'image',       // Quan trọng: Ảnh đại diện chính (View đang gọi là image)
        'price_min', 
        'stock_quantity',
        'status',      // 'published' hoặc 'draft'
        'is_featured', // boolean
        'views'        // Đếm lượt xem
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'price_min'   => 'decimal:0', // Giá tiền không cần số lẻ thập phân
        'status'      => 'string',
    ];

    // =========================================================================
    // RELATIONS (CÁC MỐI QUAN HỆ)
    // =========================================================================

    /**
     * 1. Thương hiệu (BelongsTo)
     * $product->brand->name
     */
    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    /**
     * 2. Danh mục (BelongsTo)
     * $product->category->name
     * Lưu ý: Hệ thống đang dùng quan hệ 1-N (1 sản phẩm thuộc 1 danh mục chính)
     */
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /**
     * 3. Biến thể (HasMany)
     * Size, Màu sắc, Tồn kho từng loại
     */
    public function variants()
    {
        return $this->hasMany(ProductVariant::class, 'product_id');
    }


    /**
     * 4. Ảnh chi tiết / Gallery (HasMany)
     * Dùng bảng riêng `product_images` thay vì JSON để dễ quản lý
     */
    public function gallery_images()
    {
        // Model tên là ProductImage, khóa ngoại product_id
        return $this->hasMany(ProductImage::class)->orderBy('sort_order', 'asc', 'product_id');
    }

    /**
     * 5. Đánh giá (HasMany)
     */
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    // Sửa trong Product.php
    public function categories() // Đổi tên thành số nhiều
    {
        // Và phải dùng belongsToMany nếu 1 sp thuộc nhiều danh mục
        return $this->belongsToMany(Category::class, 'product_categories'); 
    }
    
    // =========================================================================
    // ACCESSORS (Hàm phụ trợ)
    // =========================================================================
    
    // Đếm số lượng biến thể (Để hiển thị "Available in 5 sizes" ngoài view)
    // Hoặc có thể dùng withCount('variants') trong controller

    /**
     * Định nghĩa quan hệ với bảng product_images
     */
    public function images()
    {
        // Quan hệ 1-nhiều: Một sản phẩm có nhiều ảnh phụ
        return $this->hasMany(ProductImage::class, 'product_id');
    }

    // Tạo thuộc tính ảo: $product->stock_quantity
    public function getStockQuantityAttribute() {
        return $this->variants->sum('stock_quantity');
    }

    
    // Scope lấy sản phẩm đang Published
    public function scopeActive($query)
    {
        return $query->where('status', 'published');
    }

}