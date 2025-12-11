<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    // ==========================================================
    // ROLES (Định nghĩa vai trò để quản lý quyền)
    // ==========================================================

    const ROLE_ADMIN  = 'admin';
    const ROLE_USER   = 'user';
    const ROLE_SHIPPER = 'shipper';

    // ==========================================================
    // FILLABLE
    // ==========================================================

    protected $fillable = [
        'name',
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

    // ==========================================================
    // HIDDEN
    // ==========================================================

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // ==========================================================
    // CASTS
    // ==========================================================

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'birthday'          => 'date',
            'password'          => 'hashed',
        ];
    }

    // ==========================================================
    // RELATIONSHIPS
    // ==========================================================

    // User có nhiều Orders
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'user_id');
    }

    // User có nhiều Reviews
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'user_id');
    }

    // User có nhiều địa chỉ
    public function addresses(): HasMany
    {
        return $this->hasMany(UserAddress::class);
    }

    // Địa chỉ mặc định
    public function defaultAddress(): HasOne
    {
        return $this->hasOne(UserAddress::class)->where('is_default', true);
    }

    // Shipper có nhiều đơn hàng được gán
    public function shippingOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'shipper_id');
    }

    // ==========================================================
    // ACCESSORS & HELPER METHODS
    // ==========================================================

    /**
     * Lấy avatar URL của user
     */
    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            return asset('storage/' . $this->avatar);
        }

        // Avatar tự tạo theo tên
        $name = urlencode($this->name ?? 'User');

        return "https://ui-avatars.com/api/?name={$name}&background=random&color=fff";
    }

    /**
     * Tổng tiền user đã chi (chỉ tính đơn đã thanh toán)
     */
    public function getTotalSpentAttribute(): float
    {
        return $this->orders
            ->where('payment_status', 'paid')
            ->sum('grand_total');
    }

    /**
     * Kiểm tra có phải admin
     */
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /**
     * Kiểm tra shipper
     */
    public function isShipper(): bool
    {
        return $this->role === self::ROLE_SHIPPER;
    }

    /**
     * Kiểm tra user thường
     */
    public function isCustomer(): bool
    {
        return $this->role === self::ROLE_USER;
    }
}
