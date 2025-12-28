<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    // ==============================
    //   CONSTANTS (TRẠNG THÁI)
    // ==============================
    const STATUS_PENDING   = 'pending';
    const STATUS_CONFIRMED = 'confirmed'; // Hoặc 'processing' tùy quy ước
    const STATUS_SHIPPING  = 'shipping';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_RETURNED  = 'returned';

    // ==============================
    //   CẤU HÌNH TABLE
    // ==============================
    protected $table = 'orders';

    protected $fillable = [
        'user_id',
        'order_code',       // Mã đơn hàng (VD: ORD-XYZ)
        'total_amount',     // Tổng tiền cuối cùng
        'shipping_fee',     // Phí ship
        'discount_amount',  // Số tiền giảm giá
        'shipping_address', // Quan trọng: Lưu JSON snapshot địa chỉ lúc đặt
        'payment_method',   // cod, vnpay, momo...
        'payment_status',   // unpaid, paid, refunded
        'status',           // Trạng thái đơn hàng
        'note',             // Ghi chú khách hàng
        'paid_at'           // Thời gian thanh toán
    ];

    /**
     * Casts: Chuyển đổi dữ liệu tự động khi lấy từ DB ra
     */
    protected $casts = [
        'total_amount'     => 'float',
        'shipping_fee'     => 'float',
        'discount_amount'  => 'float',
        'created_at'       => 'datetime',
        'paid_at'          => 'datetime',
        // Rất quan trọng: Tự động chuyển JSON trong DB thành Array trong PHP
        'shipping_address' => 'array', 
    ];

    // ==============================
    //   RELATIONSHIPS (QUAN HỆ)
    // ==============================

    /**
     * 1. Người đặt hàng (User)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 2. Chi tiết sản phẩm trong đơn (Order Items)
     */
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * 3. Lịch sử đơn hàng (Logs)
     */
    public function histories()
    {
        // Mặc định sắp xếp cũ nhất trước -> để hiển thị timeline từ trên xuống dưới
        // Hoặc latest() nếu muốn mới nhất lên đầu.
        return $this->hasMany(OrderHistory::class)->orderBy('created_at', 'desc');
    }

    /**
     * 4. Đơn vận chuyển (Shipping - GHTK/GHN...)
     */
    public function shippingOrder()
    {
        return $this->hasOne(ShippingOrder::class);
    }

    /**
     * 5. Giao dịch thanh toán (Online Payment Logs)
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    // ==============================
    //   ACCESSORS (HÀM BỔ TRỢ)
    // ==============================

    /**
     * Format tiền tệ hiển thị View
     * Sử dụng: $order->formatted_total
     */
    public function getFormattedTotalAttribute()
    {
        return number_format($this->total_amount, 0, ',', '.') . ' VNĐ';
    }

    /**
     * Lấy class màu sắc Bootstrap/Tailwind cho trạng thái
     * Sử dụng: $order->status_badge
     */
    public function getStatusBadgeAttribute()
    {
        return match ($this->status) {
            self::STATUS_PENDING   => 'secondary', // Xám
            'processing'           => 'primary',   // Xanh dương (Đang đóng gói)
            self::STATUS_CONFIRMED => 'primary',
            self::STATUS_SHIPPING  => 'info',      // Xanh nhạt
            self::STATUS_COMPLETED => 'success',   // Xanh lá
            self::STATUS_CANCELLED => 'danger',    // Đỏ
            self::STATUS_RETURNED  => 'warning',   // Vàng
            default                => 'secondary',
        };
    }

    /**
     * Lấy tên tiếng Việt của trạng thái
     * Sử dụng: $order->status_label
     */
    public function getStatusLabelAttribute()
    {
        return match ($this->status) {
            self::STATUS_PENDING   => 'Chờ xử lý',
            'processing'           => 'Đang đóng gói',
            self::STATUS_CONFIRMED => 'Đã xác nhận',
            self::STATUS_SHIPPING  => 'Đang giao hàng',
            self::STATUS_COMPLETED => 'Hoàn thành',
            self::STATUS_CANCELLED => 'Đã hủy',
            self::STATUS_RETURNED  => 'Trả hàng',
            default                => ucfirst($this->status),
        };
    }

    /**
     * Lấy tên tiếng Việt của trạng thái thanh toán
     */
    public function getPaymentStatusLabelAttribute()
    {
        return match ($this->payment_status) {
            'unpaid'   => 'Chưa thanh toán',
            'paid'     => 'Đã thanh toán',
            'refunded' => 'Đã hoàn tiền',
            default    => ucfirst($this->payment_status),
        };
    }

    // ==============================
    //   SCOPES (QUERY BUILDER)
    // ==============================

    /**
     * Tìm kiếm đơn hàng (Dùng cho Admin)
     * Logic: Tìm theo Mã đơn HOẶC Tên/SĐT trong JSON shipping_address
     */
    public function scopeSearch($query, $keyword)
    {
        if (!$keyword) return $query;

        return $query->where(function ($q) use ($keyword) {
            $q->where('order_code', 'like', "%{$keyword}%")
              // Cú pháp tìm trong JSON của MySQL (->)
              ->orWhere('shipping_address->contact_name', 'like', "%{$keyword}%")
              ->orWhere('shipping_address->phone', 'like', "%{$keyword}%");
        });
    }

    /**
     * Lọc theo trạng thái
     */
    public function scopeByStatus($query, $status)
    {
        if (!$status) return $query;
        return $query->where('status', $status);
    }
}