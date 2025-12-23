<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    // ==============================
    //   ORDER STATUS CONSTANTS
    // ==============================
    const STATUS_PENDING   = 'pending';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_SHIPPING  = 'shipping';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_RETURNED  = 'returned';

    // ==============================
    //   MASS ASSIGNMENT
    // ==============================
    protected $fillable = [
        'user_id',
        'shipper_id',
        'order_number',
        'status',
        'payment_status',
        'payment_method',
        'grand_total',
        'shipping_address',
        'phone',
        'note',
    ];

    protected $casts = [
        'grand_total' => 'float',
        'created_at'  => 'datetime',
    ];

    // ==============================
    //   RELATIONSHIPS
    // ==============================

    // Chủ đơn hàng (khách)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Khách hàng (alias rõ nghĩa hơn)
    public function customer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Shipper được gán
    public function shipper()
    {
        return $this->belongsTo(User::class, 'shipper_id');
    }

    // Các sản phẩm trong đơn
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    // Thông tin vận chuyển (1–1)
    public function shipping()
    {
        return $this->hasOne(ShippingOrder::class);
    }

    // Lịch sử trạng thái đơn hàng
    public function histories()
    {
        return $this->hasMany(OrderHistory::class)->orderBy('created_at', 'desc');
    }

    // ==============================
    //   ACCESSORS
    // ==============================

    public function getFormattedTotalAttribute()
    {
        return number_format($this->grand_total, 0, ',', '.') . ' VNĐ';
    }

    public function getStatusBadgeAttribute()
    {
        return match ($this->status) {
            self::STATUS_PENDING   => 'secondary',
            self::STATUS_CONFIRMED => 'primary',
            self::STATUS_SHIPPING  => 'info',
            self::STATUS_COMPLETED => 'success',
            self::STATUS_CANCELLED => 'danger',
            self::STATUS_RETURNED  => 'warning',
            default                => 'secondary',
        };
    }

    public function getStatusLabelAttribute()
    {
        return match ($this->status) {
            self::STATUS_PENDING   => 'Chờ xử lý',
            self::STATUS_CONFIRMED => 'Đã xác nhận',
            self::STATUS_SHIPPING  => 'Đang giao hàng',
            self::STATUS_COMPLETED => 'Hoàn thành',
            self::STATUS_CANCELLED => 'Đã hủy',
            self::STATUS_RETURNED  => 'Trả hàng',
            default                => $this->status,
        };
    }

    // ==============================
    //   QUERY SCOPES
    // ==============================

    public function scopeSearch($query, $keyword)
    {
        return $query->where('order_number', 'like', "%{$keyword}%")
            ->orWhere('phone', 'like', "%{$keyword}%");
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }
}
