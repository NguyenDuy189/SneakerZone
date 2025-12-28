<?php

namespace App\Events;

use App\Models\Order;
use App\Models\OrderHistory;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $action;
    public Order $order;
    public ?OrderHistory $history;

    /**
     * @param Order $order
     * @param string $action (created, updated, cancelled, paid...)
     * @param OrderHistory|null $history
     */
    public function __construct(Order $order, string $action = 'updated', ?OrderHistory $history = null)
    {
        $this->order   = $order;
        $this->action  = $action;
        $this->history = $history;
    }

    /**
     * Tên sự kiện để lắng nghe ở Client (Bỏ qua namespace)
     * JS: .listen('.OrderStatusUpdated', (e) => { ... })
     */
    public function broadcastAs(): string
    {
        return 'OrderStatusUpdated';
    }

    /**
     * Định nghĩa các kênh phát sóng
     */
    public function broadcastOn(): array
    {
        $channels = [];

        // 1. Kênh riêng của đơn hàng (User đang xem chi tiết đơn)
        $channels[] = new PrivateChannel('orders.' . $this->order->id);

        // 2. Kênh cá nhân của User (Để reload danh sách đơn hàng)
        if ($this->order->user_id) {
            $channels[] = new PrivateChannel('users.' . $this->order->user_id);
        }

        // 3. Kênh Admin (Thông báo chung cho quản trị viên)
        $channels[] = new PrivateChannel('admin.orders');

        return $channels;
    }

    /**
     * Dữ liệu gửi đi (Payload)
     * Tận dụng Accessor từ Model để code gọn hơn
     */
    public function broadcastWith(): array
    {
        // Lấy thông tin khách hàng từ JSON shipping_address hoặc User gốc
        // Model đã cast shipping_address thành array, nên không cần json_decode thủ công nữa
        $shippingInfo = $this->order->shipping_address ?? [];
        
        $customerName = $shippingInfo['contact_name'] 
                        ?? $shippingInfo['name'] 
                        ?? optional($this->order->user)->name 
                        ?? 'Khách lẻ';
                        
        $customerPhone = $shippingInfo['phone'] 
                         ?? optional($this->order->user)->phone 
                         ?? '';

        return [
            'action' => $this->action, // created, updated, cancelled...

            // Thông tin đơn hàng (Dùng Accessor của Model)
            'order' => [
                'id'                   => $this->order->id,
                'code'                 => $this->order->order_code,
                
                // Trạng thái & Màu sắc (Lấy từ Model)
                'status'               => $this->order->status,
                'status_label'         => $this->order->status_label, // Tự động lấy tiếng Việt
                'status_badge'         => $this->order->status_badge, // Lấy class màu (danger, success...)
                
                // Thanh toán
                'payment_method'       => $this->order->payment_method,
                'payment_status'       => $this->order->payment_status,
                'payment_status_label' => $this->order->payment_status_label,

                // Tiền tệ
                'total_formatted'      => $this->order->formatted_total, // "150.000 VNĐ"

                // Thời gian (Gửi cả 2 dạng để JS linh hoạt)
                'updated_at'           => $this->order->updated_at->toIso8601String(), // Dùng cho new Date()
                'updated_at_pretty'    => $this->order->updated_at->format('H:i d/m/Y'), // Dùng để hiển thị ngay
            ],

            // Snapshot khách hàng
            'customer' => [
                'name'  => $customerName,
                'phone' => $customerPhone,
                'avatar' => optional($this->order->user)->avatar ?? 'default-avatar.png', // Nếu muốn hiện ảnh
            ],

            // Chi tiết lịch sử (người vừa thao tác)
            'history' => $this->history ? [
                'description' => $this->history->description,
                'created_at'  => $this->history->created_at->format('H:i d/m/Y'),
                'user_name'   => $this->history->user ? $this->history->user->name : 'Hệ thống',
                'action_type' => $this->history->action // cancelled, created...
            ] : null,
        ];
    }
}