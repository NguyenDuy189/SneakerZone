<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes; // 1. Thêm SoftDeletes

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes; // 2. Sử dụng Trait

    // ==========================================================
    // ROLES
    // ==========================================================

    const ROLE_ADMIN    = 'admin';
    const ROLE_STAFF    = 'staff';     // Thêm Staff nếu cần phân quyền nhân viên
    const ROLE_CUSTOMER = 'customer';  // 3. Đổi ROLE_USER -> ROLE_CUSTOMER cho khớp Controller
    const ROLE_SHIPPER  = 'shipper';

    // ==========================================================
    // FILLABLE
    // ==========================================================

    protected $fillable = [
        'full_name', // 4. Đổi 'name' -> 'full_name' cho khớp Controller
        'email',
        'password',
        'phone',
        'avatar',
        'role',
        'status',    // active/banned
        'gender',
        'birthday',
        'address',   // Địa chỉ nhanh (nếu có)
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

    // User có nhiều sổ địa chỉ
    public function addresses(): HasMany
    {
        return $this->hasMany(UserAddress::class);
    }

    // Lấy địa chỉ mặc định (trả về 1 object)
    public function defaultAddress(): HasOne
    {
        return $this->hasOne(UserAddress::class)->where('is_default', true);
    }

    // Shipper giao nhiều đơn
    public function shippingOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'shipper_id');
    }

    // ==========================================================
    // ACCESSORS & HELPER METHODS
    // ==========================================================

    /**
     * Lấy avatar URL (Accessor: $user->avatar_url)
     */
    public function getAvatarUrlAttribute(): string
    {
        // Nếu có ảnh trong storage
        if ($this->avatar && \Illuminate\Support\Facades\Storage::disk('public')->exists($this->avatar)) {
            return asset('storage/' . $this->avatar);
        }

        // Nếu không có, tạo ảnh theo tên (full_name)
        $name = urlencode($this->full_name ?? 'User');
        return "https://ui-avatars.com/api/?name={$name}&background=random&color=fff";
    }

    /**
     * Tổng tiền đã chi tiêu (Accessor: $user->total_spent)
     * Chỉ tính đơn đã thanh toán ('paid')
     */
    public function getTotalSpentAttribute(): float
    {
        // Lưu ý: Nếu user có quá nhiều đơn, gọi $this->orders sẽ load hết vào RAM.
        // Cách tối ưu hơn nếu dùng nhiều là dùng relationship aggregate (withSum)
        return $this->orders
            ->where('payment_status', 'paid')
            ->sum('total_amount'); // 5. Đổi 'grand_total' -> 'total_amount'
    }

    /**
     * Check Roles
     */
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isStaff(): bool
    {
        return $this->role === self::ROLE_STAFF;
    }

    public function isShipper(): bool
    {
        return $this->role === self::ROLE_SHIPPER;
    }

    public function isCustomer(): bool
    {
        return $this->role === self::ROLE_CUSTOMER;
    }
}