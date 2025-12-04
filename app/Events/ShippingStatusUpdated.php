<?php

namespace App\Events;

use App\Models\ShippingOrder;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ShippingStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Shipping order instance
     *
     * @var \App\Models\ShippingOrder
     */
    public $shippingOrder;

    /**
     * Create a new event instance.
     */
    public function __construct(ShippingOrder $shippingOrder)
    {
        // Dữ liệu mà frontend sẽ nhận được
        $this->shippingOrder = $shippingOrder->load([
            'order',
            'shipper',
            'logs'    // nếu có relationship ShippingLog
        ]);
    }

    /**
     * Kênh realtime mà event sẽ phát lên.
     * Mỗi đơn hàng có 1 channel riêng.
     */
    public function broadcastOn(): Channel
    {
        return new Channel("shipping.{$this->shippingOrder->id}");
    }

    /**
     * Tên event gửi lên frontend.
     */
    public function broadcastAs(): string
    {
        return 'shipping.status.updated';
    }

    /**
     * Payload gửi cho client.
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->shippingOrder->id,
            'order_id' => $this->shippingOrder->order_id,
            'shipper' => $this->shippingOrder->shipper,
            'status' => $this->shippingOrder->status,
            'current_location' => $this->shippingOrder->current_location,
            'logs' => $this->shippingOrder->logs,
            'updated_at' => $this->shippingOrder->updated_at,
        ];
    }
}
