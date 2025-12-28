<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\Order;

// 1. Kênh Admin: Chỉ cho Admin nghe
Broadcast::channel('admin.orders', function ($user) {
    // Kiểm tra logic admin của bạn, ví dụ:
    return $user->role === 'admin' || $user->type === 'admin'; 
});

// 2. Kênh Chi tiết đơn hàng: Chủ đơn hoặc Admin mới được nghe
Broadcast::channel('orders.{id}', function ($user, $id) {
    $order = Order::findOrNew($id);
    return (int) $user->id === (int) $order->user_id || $user->role === 'admin';
});

// 3. Kênh User: Chỉ user đó nghe kênh của chính mình
Broadcast::channel('users.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});