<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    // Định nghĩa các trạng thái đơn hàng để dùng chung toàn hệ thống
    const STATUS_PENDING = 'pending';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_SHIPPING = 'shipping';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_RETURNED = 'returned';

    protected $fillable = [
        'user_id',
        'order_number',
        'status',          // Trạng thái đơn: pending, shipping...
        'payment_status',  // Trạng thái tiền: unpaid, paid
        'payment_method',  // COD, Banking, Momo...
        'grand_total',
        'shipping_address',
        'phone',
        'note',
    ];

    // Tự động chuyển đổi dữ liệu
    protected $casts = [
        'grand_total' => 'float',
        'created_at'  => 'datetime',
    ];

    // --- RELATIONSHIPS (QUAN HỆ) ---

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    // --- ACCESSORS (ĐỊNH DẠNG DỮ LIỆU HIỂN THỊ) ---

    // Gọi $order->formatted_total sẽ ra "1.000.000 VNĐ"
    public function getFormattedTotalAttribute()
    {
        return number_format($this->grand_total, 0, ',', '.') . ' VNĐ';
    }

    // Helper lấy màu sắc badge hiển thị trong Admin (Bootstrap classes)
    // Gọi $order->status_badge
    public function getStatusBadgeAttribute()
    {
        return match ($this->status) {
            self::STATUS_PENDING   => 'secondary', // Màu xám
            self::STATUS_CONFIRMED => 'primary',   // Màu xanh dương
            self::STATUS_SHIPPING  => 'info',      // Màu xanh nhạt
            self::STATUS_COMPLETED => 'success',   // Màu xanh lá
            self::STATUS_CANCELLED => 'danger',    // Màu đỏ
            self::STATUS_RETURNED  => 'warning',   // Màu vàng
            default                => 'secondary',
        };
    }

    // Helper hiển thị tên trạng thái tiếng Việt
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

    // --- SCOPES (HỖ TRỢ TRUY VẤN NHANH) ---

    // Dùng: Order::search('098...')->get()
    public function scopeSearch($query, $keyword)
    {
        return $query->where('order_number', 'like', "%{$keyword}%")
                     ->orWhere('phone', 'like', "%{$keyword}%");
    }
    
    // Dùng: Order::byStatus('pending')->get()
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Khách hàng đặt đơn
     */
    public function customer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Shipper được gán (nếu có)
     */
    public function shipper()
    {
        return $this->belongsTo(User::class, 'shipper_id');
    }

    /**
     * Liên quan đến Shipping
     */
    public function shipping()
    {
        return $this->hasOne(ShippingOrder::class);
    }
    // ... các khai báo fillable, casts cũ của bạn giữ nguyên ...
    protected $guarded = []; // Hoặc $fillable = [...]

    // --- THÊM ĐOẠN NÀY ---
    /**
     * Quan hệ 1-nhiều với bảng lịch sử đơn hàng
     */
    public function histories()
    {
        return $this->hasMany(OrderHistory::class)->orderBy('created_at', 'desc');
    }
}