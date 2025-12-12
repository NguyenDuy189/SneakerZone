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
    public $action; // 'created' hoặc 'updated'
    public $history;

    public function __construct(Order $order, $action = 'updated', $history = null)
    {
        $this->order = $order;
        $this->action = $action;
        $this->history = $history;
    }

    public function broadcastOn()
    {
        // 1. Kênh riêng của đơn hàng (để update trang chi tiết)
        $channels = [new PrivateChannel('orders.' . $this->order->id)];

        // 2. Kênh chung của admin (để update trang danh sách khi có đơn mới)
        if ($this->action === 'created') {
            $channels[] = new PrivateChannel('admin.orders');
        }

        return $channels;
    }

    public function broadcastWith()
    {
        return [
            'action' => $this->action,
            'order_id' => $this->order->id,
            'order_code' => $this->order->order_code,
            'total' => number_format($this->order->total_amount, 0, ',', '.'),
            'status' => $this->order->status,
            'payment_status' => $this->order->payment_status,
            'created_at' => $this->order->created_at->format('H:i d/m/Y'),
            'customer_name' => $this->order->shipping_address['contact_name'] ?? 'Khách lẻ',
            'history' => $this->history ? [
                'user_name' => $this->history->user->full_name ?? 'Hệ thống',
                'description' => $this->history->description,
                'action_text' => match($this->history->action) {
                    'created' => 'Tạo đơn hàng',
                    'update_status' => 'Cập nhật trạng thái',
                    default => 'Hệ thống'
                },
                'time' => $this->history->created_at->format('H:i d/m')
            ] : null
        ];
    }
}