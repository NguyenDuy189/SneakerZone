<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAddress extends Model
{
    protected $fillable = [
        'user_id',
        'contact_name',
        'phone',
        'city',
        'district',
        'ward',
        'address',
        'is_default',
    ];

    /**
     * Quan hệ: Địa chỉ thuộc về 1 User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Lấy full address theo dạng chuỗi
     * Sử dụng: $address->full_address
     */
    public function getFullAddressAttribute()
    {
        return trim(
            collect([
                $this->address,
                $this->ward,
                $this->district,
                $this->city
            ])->filter()->implode(', ')
        );
    }

    /**
     * Địa chỉ mặc định
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }
}
