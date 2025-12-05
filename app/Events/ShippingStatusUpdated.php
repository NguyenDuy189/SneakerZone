<?php

namespace App\Events;

use App\Models\ShippingOrder;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ShippingStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public ShippingOrder $shipping;

    /**
     * Create a new event instance.
     *
     * @param ShippingOrder $shipping
     */
    public function __construct(ShippingOrder $shipping)
    {
        $this->shipping = $shipping;
    }

    /**
     * The name of the event to broadcast.
     *
     * @return string
     */
    public function broadcastAs(): string
    {
        return 'shipping.updated';
    }

    /**
     * The channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn(): Channel|array
    {
        // Kênh private theo ID đơn giao hàng
        return new PrivateChannel('shipping.' . $this->shipping->id);
    }

    /**
     * Dữ liệu gửi tới client
     *
     * @return array
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->shipping->id,
            'order_id' => $this->shipping->order_id,
            'status' => $this->shipping->status,
            'tracking_code' => $this->shipping->tracking_code,
            'expected_delivery_date' => $this->shipping->expected_delivery_date?->format('Y-m-d'),
            'current_location' => $this->shipping->current_location,
            'note' => $this->shipping->note,
            'shipper' => $this->shipping->shipper ? [
                'id' => $this->shipping->shipper->id,
                'full_name' => $this->shipping->shipper->full_name,
                'phone' => $this->shipping->shipper->phone,
            ] : null,
            'logs' => $this->shipping->logs->take(5)->map(fn($log) => [
                'id' => $log->id,
                'status' => $log->status,
                'description' => $log->description,
                'location' => $log->location,
                'user_name' => $log->user?->full_name ?? 'Hệ thống',
                'created_at' => $log->created_at->format('Y-m-d H:i:s'),
            ]),
        ];
    }
}
