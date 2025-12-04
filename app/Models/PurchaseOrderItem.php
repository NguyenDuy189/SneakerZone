<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseOrderItem extends Model
{
    use HasFactory;

    public $timestamps = false; // Bảng chi tiết thường không cần created_at/updated_at

    protected $fillable = [
        'purchase_order_id',
        'product_variant_id',
        'quantity',
        'import_price', // Giá nhập đơn vị
        'total',        // Thành tiền (qty * price)
    ];

    // --- RELATIONSHIPS ---

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id')->withTrashed();
    }
}