<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrder extends Model
{
    use HasFactory;

    // Định nghĩa trạng thái phiếu
    const STATUS_PENDING   = 'pending';   // Mới tạo/Chờ duyệt
    const STATUS_COMPLETED = 'completed'; // Đã nhập kho
    const STATUS_CANCELLED = 'cancelled'; // Đã hủy

    protected $fillable = [
        'code',         // Mã phiếu (PO-2024...)
        'supplier_id',
        // 'user_id',   <-- Đã bỏ dòng này vì Database hiện tại chưa có cột user_id
        'status',
        'total_amount',
        'note',         // Đã có trong DB
        'expected_at',  // Đã có trong DB
    ];

    protected $casts = [
        'expected_at'  => 'datetime',
        'total_amount' => 'decimal:2', // Định dạng 2 số thập phân
    ];

    // --- RELATIONSHIPS ---

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    // Tạm thời comment lại quan hệ này vì chưa có cột user_id
    /*
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    */

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class, 'purchase_order_id');
    }

    // --- HELPERS ---

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }
}