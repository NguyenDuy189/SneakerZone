<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryLog extends Model
{
    use HasFactory;

    // Các loại giao dịch kho
    const TYPE_IMPORT = 'import'; // Nhập hàng
    const TYPE_SALE   = 'sale';   // Bán hàng
    const TYPE_RETURN = 'return'; // Khách trả hàng
    const TYPE_CHECK  = 'check';  // Kiểm kê/Cân bằng kho

    protected $fillable = [
        'product_variant_id',
        'user_id',        // Người thực hiện (Admin hoặc Khách mua)
        'old_quantity',   // Tồn cũ
        'change_amount',  // Số lượng thay đổi (+/-)
        'new_quantity',   // Tồn mới
        'type',           // Loại giao dịch
        'reference_id',   // ID đơn hàng hoặc PO
        'note',
    ];

    // --- RELATIONSHIPS ---

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id')->withTrashed();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withDefault([
            'name' => 'System/Guest' // Nếu user bị xóa hoặc null
        ]);
    }
}