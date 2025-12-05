<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $order;
    public $history; // Thêm biến này

    public function __construct(Order $order, $history = null)
    {
        $this->order = $order;
        $this->history = $history;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('orders.' . $this->order->id);
    }

    public function broadcastWith()
    {
        return [
            'order_status' => $this->order->status,
            'payment_status' => $this->order->payment_status,
            'history' => $this->history ? [
                'user_name' => $this->history->user->name ?? 'Hệ thống',
                'action' => $this->history->action,
                'description' => $this->history->description,
                'time' => $this->history->created_at->format('H:i - d/m/Y'),
            ] : null
        ];
    }
}