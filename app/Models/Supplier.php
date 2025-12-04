<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',         // Mã NCC (SUP-NIKE)
        'name',         // Tên NCC
        'contact_name', // Người liên hệ
        'email',
        'phone',
        'address',
    ];

    // --- RELATIONSHIPS ---

    /**
     * Một nhà cung cấp có nhiều phiếu nhập hàng
     */
    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }
}