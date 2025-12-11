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
        'image_url',
        'is_visible'
    ];

    /**
     * Quan hệ: Lấy danh mục cha
     */
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    // Bổ sung — Quan hệ đến bảng Products
    public function products()
    {
        return $this->hasMany(Product::class, 'category_id');
    }
}