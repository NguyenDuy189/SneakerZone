<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $table = 'categories';

    protected $fillable = [
        'name',
        'slug',
        'parent_id',
        'level',
        'image_url', // Ảnh đại diện danh mục (hiển thị ở trang chủ Bento Grid)
        'is_visible'
    ];

    /**
     * Quan hệ: Danh mục cha
     */
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * Quan hệ: Danh mục con
     */
    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    /**
     * Quan hệ: Sản phẩm thuộc danh mục này
     */
    public function products()
    {
        return $this->hasMany(Product::class, 'category_id');
    }
}