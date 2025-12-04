<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Order;
use App\Models\ShippingOrder;
use App\Models\ShippingLog;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ShippingSeeder extends Seeder
{
    public function run()
    {
        DB::transaction(function () {

            // 1️⃣ Tạo 5 Shipper
            $shippers = collect();
            for ($i = 1; $i <= 5; $i++) {
                $shippers->push(User::create([
                    'full_name' => "Shipper $i",
                    'email' => "shipper$i@example.com",
                    'password' => bcrypt('password'),
                    'role' => 'shipper',
                    'phone' => '090000000' . $i,
                ]));
            }

            // 2️⃣ Tạo 10 Customer
            $customers = collect();
            for ($i = 1; $i <= 10; $i++) {
                $customers->push(User::create([
                    'full_name' => "Customer $i",
                    'email' => "customer$i@example.com",
                    'password' => bcrypt('password'),
                    'role' => 'customer',
                    'phone' => '091000000' . $i,
                ]));
            }

            // 3️⃣ Tạo Order + ShippingOrder + ShippingLog
            foreach ($customers as $customer) {
                for ($j = 1; $j <= 3; $j++) {

                    // Tạo order_code ngẫu nhiên
                    $orderCode = 'ORD-' . now()->format('ymd') . '-' . strtoupper(Str::random(6));

                    // Tạo Order
                    $order = Order::create([
                        'order_code' => $orderCode,
                        'user_id' => $customer->id,
                        'status' => 'pending',
                        'payment_status' => 'unpaid',
                        'payment_method' => 'cod',
                        'shipping_fee' => rand(10000, 50000),
                        'discount_amount' => rand(0, 20000),
                        'total_amount' => rand(100000, 500000),
                        'shipping_address' => json_encode([
                            'name' => $customer->full_name,
                            'phone' => $customer->phone,
                            'address' => "Địa chỉ giao hàng $j của {$customer->full_name}",
                        ]),
                        'note' => "Ghi chú đơn hàng $j của {$customer->full_name}",
                    ]);

                    // Chọn shipper ngẫu nhiên
                    $shipper = $shippers->random();

                    // Tạo tracking code duy nhất
                    do {
                        $trackingCode = 'TRK-' . now()->format('ymd') . '-' . strtoupper(Str::random(6));
                    } while (ShippingOrder::where('tracking_code', $trackingCode)->exists());

                    // Tạo ShippingOrder
                    $shipping = ShippingOrder::create([
                        'order_id' => $order->id,
                        'shipper_id' => $shipper->id,
                        'status' => ShippingOrder::STATUS_ASSIGNED,
                        'expected_delivery_date' => now()->addDays(rand(1,5)),
                        'tracking_code' => $trackingCode,
                    ]);

                    // Log ban đầu
                    ShippingLog::create([
                        'shipping_order_id' => $shipping->id,
                        'status' => $shipping->status,
                        'description' => 'Đơn hàng được gán cho shipper #' . $shipper->id,
                        'user_id' => $customer->id,
                    ]);

                    // Log trạng thái tiếp theo
                    $statuses = ['picking','delivering','delivered'];
                    foreach ($statuses as $status) {
                        ShippingLog::create([
                            'shipping_order_id' => $shipping->id,
                            'status' => $status,
                            'description' => 'Cập nhật trạng thái: ' . $status,
                            'user_id' => $shipper->id,
                            'location' => 'Vị trí thử ' . rand(1,10),
                        ]);

                        $shipping->update([
                            'status' => $status,
                            'current_location' => 'Vị trí thử ' . rand(1,10),
                        ]);
                    }
                }
            }
        });
    }
}
