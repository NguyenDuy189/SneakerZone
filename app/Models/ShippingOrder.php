<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShippingOrder extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'order_id',
        'shipper_id',
        'status',
        'tracking_code',
        'expected_delivery_date',
        'current_location',
        'note',
    ];

    // --- Các trạng thái ---
    const STATUS_PENDING = 'pending';
    const STATUS_ASSIGNED = 'assigned';
    const STATUS_PICKING = 'picking';
    const STATUS_DELIVERING = 'delivering';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_FAILED = 'failed';
    const STATUS_RETURNED = 'returned';

    // Mảng để hiển thị trong view
    const STATUS_LIST = [
        self::STATUS_PENDING,
        self::STATUS_ASSIGNED,
        self::STATUS_PICKING,
        self::STATUS_DELIVERING,
        self::STATUS_DELIVERED,
        self::STATUS_FAILED,
        self::STATUS_RETURNED,
    ];

    // Nhãn tiếng Việt
    const STATUS_LABELS = [
        self::STATUS_PENDING => 'Chờ gán',
        self::STATUS_ASSIGNED => 'Đã gán shipper',
        self::STATUS_PICKING => 'Lấy hàng',
        self::STATUS_DELIVERING => 'Đang giao',
        self::STATUS_DELIVERED => 'Đã giao',
        self::STATUS_FAILED => 'Giao thất bại',
        self::STATUS_RETURNED => 'Trả lại',
    ];

    // Quan hệ với Order
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // Quan hệ với Shipper
    public function shipper()
    {
        return $this->belongsTo(User::class, 'shipper_id');
    }

    // Quan hệ logs
    public function logs()
    {
        return $this->hasMany(ShippingLog::class);
    }
}
