<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    // ==============================
    //   CONSTANTS (TRẠNG THÁI)
    // ==============================
    const STATUS_PENDING   = 'pending';
    const STATUS_CONFIRMED = 'processing'; // Sửa lại 'processing' cho khớp với Controller
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
        'total_amount'    => 'float',
        'shipping_fee'    => 'float',
        'discount_amount' => 'float',
        'created_at'      => 'datetime',
        'paid_at'         => 'datetime',
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
    //   ACCESSORS (HÀM BỔ TRỢ QUAN TRỌNG)
    // ==============================

    /**
     * Lấy tên người nhận hàng (Ưu tiên từ Shipping Address)
     * View dùng: $order->receiver_name
     */
    public function getReceiverNameAttribute()
    {
        return $this->shipping_address['contact_name'] 
            ?? $this->shipping_address['name'] 
            ?? optional($this->user)->full_name 
            ?? optional($this->user)->name 
            ?? 'Khách vãng lai';
    }

    /**
     * Lấy SĐT người nhận hàng
     * View dùng: $order->receiver_phone
     */
    public function getReceiverPhoneAttribute()
    {
        return $this->shipping_address['phone'] 
            ?? optional($this->user)->phone 
            ?? '---';
    }

    /**
     * Lấy địa chỉ giao hàng đầy đủ (Ghép chuỗi)
     * View dùng: $order->full_address
     */
    // Trong file: App/Models/Order.php

    // App\Models\Order.php

    public function getFullAddressAttribute()
    {
        $data = $this->shipping_address;

        // 1. Xử lý trường hợp quên khai báo $casts (Data vẫn là chuỗi JSON)
        if (is_string($data)) {
            $data = json_decode($data, true);
        }

        // 2. Nếu data rỗng hoặc không hợp lệ
        if (!is_array($data) || empty($data)) {
            return 'Chưa cập nhật địa chỉ giao hàng';
        }

        // 3. ƯU TIÊN 1: Lấy đúng key "address" (trường hợp của bạn hiện tại)
        // Vì dữ liệu của bạn key 'address' đã chứa full: "a, Xã Đức Lĩnh..."
        if (!empty($data['address'])) {
            return $data['address'];
        }

        // 4. ƯU TIÊN 2: Xử lý lỗi chính tả (adress)
        if (!empty($data['adress'])) {
            return $data['adress'];
        }

        // 5. ƯU TIÊN 3: Dữ liệu bị tách lẻ (Cần ghép lại)
        $parts = array_filter([
            $data['street'] ?? null,
            $data['ward'] ?? null,
            $data['district'] ?? null,
            $data['city'] ?? null
        ]);

        return !empty($parts) ? implode(', ', $parts) : 'Chưa cập nhật địa chỉ giao hàng';
    }

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
            self::STATUS_PENDING   => 'warning',   // Vàng (Chờ xử lý)
            self::STATUS_CONFIRMED => 'info',      // Xanh dương nhạt (Đang đóng gói)
            self::STATUS_SHIPPING  => 'primary',   // Xanh dương đậm (Đang giao)
            self::STATUS_COMPLETED => 'success',   // Xanh lá (Hoàn thành)
            self::STATUS_CANCELLED => 'danger',    // Đỏ (Hủy)
            self::STATUS_RETURNED  => 'secondary', // Xám (Trả hàng)
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
            self::STATUS_CONFIRMED => 'Đang đóng gói',
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
              // Tìm trong JSON (MySQL 5.7+)
              ->orWhere('shipping_address->contact_name', 'like', "%{$keyword}%")
              ->orWhere('shipping_address->phone', 'like', "%{$keyword}%")
              // Tìm dự phòng trong User nếu JSON lỗi
              ->orWhereHas('user', function($subQ) use ($keyword) {
                  $subQ->where('name', 'like', "%{$keyword}%")
                       ->orWhere('email', 'like', "%{$keyword}%");
              });
        });
    }

    /**
     * Boot Model: Tự động tạo mã đơn hàng nếu thiếu
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (empty($order->order_code)) {
                $order->order_code = 'ORD-' . strtoupper(Str::random(10));
            }
        });
    }
}