<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseOrderItem extends Model
{
    use HasFactory;

    // Khai báo tên bảng vì không theo chuẩn (po_items thay vì purchase_order_items)
    protected $table = 'po_items'; 

    // Đã xóa dòng: public $timestamps = false; 
    // Lý do: Trong file Migration bạn có dòng $table->timestamps(), nên Model cần bật tính năng này.

    protected $fillable = [
        'purchase_order_id',
        'product_variant_id',
        'quantity',
        'import_price',
        'subtotal', // <-- QUAN TRỌNG: Đổi 'total' thành 'subtotal' để khớp với Database
    ];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}