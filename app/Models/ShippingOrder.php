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

    const STATUS_LABELS = [
        'pending'       => 'Chờ xử lý',
        'assigning'     => 'Đang phân công',
        'shipping'      => 'Đang giao',
        'delivered'     => 'Giao thành công',
        'failed'        => 'Giao thất bại',
        'cancelled'     => 'Đã hủy',
    ];
    

    // --- Các trạng thái ---
    const STATUS_PENDING = 'pending';
    const STATUS_ASSIGNED = 'assigned';
    const STATUS_PICKING = 'picking';
    const STATUS_DELIVERING = 'delivering';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_FAILED = 'failed';
    const STATUS_RETURNED = 'returned';

    // Mảng để hiển thị trong view (key = value tiếng Anh, text hiển thị tiếng Việt)
    const STATUS_LIST = [
        self::STATUS_PENDING     => 'Chờ gán',
        self::STATUS_ASSIGNED    => 'Đã gán shipper',
        self::STATUS_PICKING     => 'Lấy hàng',
        self::STATUS_DELIVERING  => 'Đang giao',
        self::STATUS_DELIVERED   => 'Đã giao',
        self::STATUS_FAILED      => 'Giao thất bại',
        self::STATUS_RETURNED    => 'Trả lại',
    ];

    /**
     * Lấy nhãn tiếng Việt từ key
     */
    public static function getStatusLabel($key)
    {
        return self::STATUS_LIST[$key] ?? $key;
    }

    // --- Quan hệ ---
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function shipper()
    {
        return $this->belongsTo(User::class, 'shipper_id');
    }

    public function logs()
    {
        return $this->hasMany(ShippingLog::class);
    }
}
