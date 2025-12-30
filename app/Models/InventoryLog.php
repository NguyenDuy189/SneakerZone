<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryLog extends Model
{
    use HasFactory;

    // Các hằng số định nghĩa loại giao dịch
    const TYPE_IMPORT = 'import'; // Nhập hàng từ NCC
    const TYPE_SALE   = 'sale';   // Bán hàng cho khách
    const TYPE_RETURN = 'return'; // Khách trả lại hàng
    const TYPE_CHECK  = 'check';  // Kiểm kê kho (cân bằng)
    const TYPE_CANCEL = 'cancel'; // Hủy đơn hàng (hoàn kho)

    protected $table = 'inventory_logs';

    protected $fillable = [
        'product_variant_id',
        'user_id',          // Người thực hiện
        'old_quantity',     // Tồn đầu
        'change_amount',    // Số lượng thay đổi (+/-)
        'new_quantity',     // Tồn cuối
        'type',             // Loại giao dịch (import/sale...)
        'reference_type',   // purchase_order / order / manual
        'reference_id',     // ID tham chiếu
        'note',
    ];

    // --- RELATIONSHIPS ---

    public function variant(): BelongsTo
    {
        // withTrashed: Giữ log hiển thị được ngay cả khi biến thể SP đã bị xóa mềm
        return $this->belongsTo(ProductVariant::class, 'product_variant_id')->withTrashed();
    }

    public function user(): BelongsTo
    {
        // withDefault: Nếu User bị xóa khỏi hệ thống, log sẽ hiện là System
        return $this->belongsTo(User::class)->withDefault([
            'name' => 'System / Đã xóa',
            'email' => 'N/A'
        ]);
    }
}