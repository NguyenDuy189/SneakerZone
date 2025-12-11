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
        'user_id',      // Người tạo phiếu
        'status',
        'total_amount',
        'note',
        'expected_at',  // Ngày dự kiến hàng về
    ];

    protected $casts = [
        'expected_at' => 'datetime',
        'total_amount' => 'decimal:0',
    ];

    // --- RELATIONSHIPS ---

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class, 'purchase_order_id');
    }


    // --- HELPERS ---

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }
}