<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingLog extends Model
{
    protected $fillable = ['shipping_order_id','status','description','location','user_id'];

    public function shippingOrder(){ return $this->belongsTo(ShippingOrder::class); }
    public function user(){ return $this->belongsTo(User::class); }
}
