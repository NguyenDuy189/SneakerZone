<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FlashSale extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 
        'start_time', 
        'end_time', 
        'is_active'
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Relationship: Một chiến dịch có nhiều sản phẩm
     */
    public function items()
    {
        return $this->hasMany(FlashSaleItem::class);
    }

    /**
     * Kiểm tra trạng thái đang chạy thực tế (Dùng cho logic backend/frontend)
     */
    public function getIsRunningAttribute()
    {
        $now = now();
        return $this->is_active && $this->start_time <= $now && $this->end_time >= $now;
    }
}   