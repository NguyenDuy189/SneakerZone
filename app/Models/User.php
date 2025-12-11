<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    // --- KHAI BÁO HẰNG SỐ (Để dùng chung trong Controller) ---
    const ROLE_ADMIN = 'admin';
    const ROLE_STAFF = 'staff';
    const ROLE_CUSTOMER = 'customer';
    
    const STATUS_ACTIVE = 'active';
    const STATUS_BANNED = 'banned';

    /**
     * Các trường được phép gán dữ liệu
     */
    protected $fillable = [
        'full_name', // Đã sửa thành full_name theo migration của bạn
        'email',
        'password',
        'phone',
        'avatar',
        'role',
        'status',
        'address',
        'gender',
        'birthday',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // =========================================================================
    // 1. ĐỊNH NGHĨA QUAN HỆ (RELATIONSHIPS) - KHẮC PHỤC LỖI CỦA BẠN TẠI ĐÂY
    // =========================================================================

    /**
     * Một User có nhiều Đơn hàng (Orders)
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'user_id'); 
    }

    /**
     * Một User có nhiều Đánh giá (Reviews)
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'user_id');
    }

    // =========================================================================
    // 2. ACCESSORS (HÀM PHỤ TRỢ)
    // =========================================================================

    /**
     * Lấy Avatar: Nếu chưa có thì tự tạo ảnh theo tên
     * Sử dụng: $user->avatar_url
     */
    public function getAvatarUrlAttribute()
    {
        if ($this->avatar) {
            return asset('storage/' . $this->avatar);
        }
        // Tạo avatar từ 2 chữ cái đầu của tên
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->full_name) . '&background=random&color=fff';
    }

    /**
     * Tính tổng tiền khách đã mua (Chỉ tính đơn đã thanh toán)
     * Sử dụng: $user->total_spent
     */
    public function getTotalSpentAttribute()
    {
        // Lưu ý: Cần load quan hệ orders trước khi gọi để tối ưu
        return $this->orders
            ->where('payment_status', 'paid')
            ->sum('total_amount');
    }

    /**
     * Kiểm tra có phải Admin không
     */
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function addresses()
    {
        return $this->hasMany(UserAddress::class);
    }

    public function defaultAddress()
    {
        return $this->hasOne(UserAddress::class)->where('is_default', true);
    }

}