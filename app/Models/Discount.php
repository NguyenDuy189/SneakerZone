<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    protected $fillable = [
        'code',
        'type',
        'value',
        'max_usage',
        'used_count',
        'min_order_amount',
        'max_discount_value',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date'   => 'datetime',
    ];

    public function isActive()
    {
        if ($this->isExpired()) return false;
        if ($this->isDisabled()) return false;

        return true;
    }

    // Kiểm tra còn hiệu lực
    public function isValid()
    {
        // Hết số lượt dùng
        if ($this->max_usage > 0 && $this->used_count >= $this->max_usage) {
            return false;
        }

        // Chưa đến ngày bắt đầu
        if ($this->start_date && now()->lt($this->start_date)) {
            return false;
        }

        // Hết hạn
        if ($this->end_date && now()->gt($this->end_date)) {
            return false;
        }

        return true;
    }
    public function isExpired()
    {
        if (!$this->end_date) return false;

        return now()->greaterThan($this->end_date);
    }

    public function isDisabled()
    {
        // Khi đã sử dụng hết giới hạn
        if ($this->max_usage > 0 && $this->used_count >= $this->max_usage) {
            return true;
        }

        // Khi chưa tới ngày bắt đầu
        if ($this->start_date && now()->lessThan($this->start_date)) {
            return true;
        }

        return false;
    }
}
