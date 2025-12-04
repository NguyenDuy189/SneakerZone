<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingOrder extends Model
{
    protected $fillable = [
        'order_id','shipper_id','status','current_location','tracking_code','expected_delivery_date','note'
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_ASSIGNED = 'assigned';
    const STATUS_PICKING  = 'picking';
    const STATUS_DELIVERING = 'delivering';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_FAILED = 'failed';
    const STATUS_RETURNED = 'returned';

    public function order() { return $this->belongsTo(Order::class); }
    public function shipper(){ return $this->belongsTo(User::class, 'shipper_id'); }
    public function logs(){ return $this->hasMany(ShippingLog::class); }
}
