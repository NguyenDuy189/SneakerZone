<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class DashboardService
{
    public function getDashboardData(array $filters = [])
    {
        $range = $this->getDateRange($filters['date_range'] ?? 'this_month');
        $cStart = $range['start'];
        $cEnd = $range['end'];

        // Tính giai đoạn trước để so sánh tăng trưởng
        $diff = $cStart->diffInDays($cEnd) + 1;
        $pStart = $cStart->copy()->subDays($diff);
        $pEnd = $cEnd->copy()->subDays($diff);

        // Cache dữ liệu 5 phút để giảm tải Database
        $key = "dash_v2_{$cStart->timestamp}_{$cEnd->timestamp}";

        return Cache::remember($key, 300, function () use ($cStart, $cEnd, $pStart, $pEnd) {
            
            // 1. Các chỉ số KPI (Cards)
            $revenue = $this->getMetric(Order::class, 'total_amount', 'sum', $cStart, $cEnd, $pStart, $pEnd, ['status' => 'completed']);
            $orders  = $this->getMetric(Order::class, 'id', 'count', $cStart, $cEnd, $pStart, $pEnd);
            $users   = $this->getMetric(User::class, 'id', 'count', $cStart, $cEnd, $pStart, $pEnd);
            
            // Tính giá trị trung bình đơn (AOV)
            $aovVal = $orders['value'] > 0 ? round($revenue['value'] / $orders['value']) : 0;
            $aov = ['value' => $aovVal]; // Demo: AOV không tính growth để đơn giản hóa

            return [
                'filters'       => ['date_range' => request('date_range', 'this_month')],
                'date_label'    => $cStart->format('d/m') . ' - ' . $cEnd->format('d/m/Y'),
                'revenue'       => $revenue,
                'orders'        => $orders,
                'users'         => $users,
                'aov'           => $aov,
                'charts'        => $this->getChartData($cStart, $cEnd),
                'order_status'  => $this->getOrderStatusStats($cStart, $cEnd), // Biểu đồ tròn
                'top_products'  => $this->getTopProducts($cStart, $cEnd),
                'top_customers' => $this->getTopCustomers($cStart, $cEnd),
                'recent_orders' => Order::with('user')->latest()->limit(6)->get(),
                'notifications' => Notification::latest()->limit(5)->get(),
            ];
        });
    }

    // --- CÁC HÀM PHỤ TRỢ (PRIVATE) ---

    private function getMetric($model, $col, $type, $cS, $cE, $pS, $pE, $conds = [])
    {
        $curr = $model::whereBetween('created_at', [$cS, $cE]);
        $prev = $model::whereBetween('created_at', [$pS, $pE]);

        foreach ($conds as $k => $v) {
            $curr->where($k, $v);
            $prev->where($k, $v);
        }

        $v1 = $type == 'sum' ? $curr->sum($col) : $curr->count();
        $v2 = $type == 'sum' ? $prev->sum($col) : $prev->count();

        $growth = 0;
        if ($v2 > 0) $growth = round((($v1 - $v2) / $v2) * 100, 1);
        elseif ($v1 > 0) $growth = 100;

        return ['value' => $v1, 'growth' => $growth, 'is_positive' => $growth >= 0];
    }

    private function getChartData($start, $end)
    {
        // Query gom nhóm theo ngày
        $data = Order::where('status', 'completed')
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('DATE(created_at) as date, SUM(total_amount) as rev, COUNT(id) as cnt')
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        $labels = []; $revenue = []; $orders = [];
        $period = \Carbon\CarbonPeriod::create($start, $end);

        foreach ($period as $date) {
            $d = $date->format('Y-m-d');
            $labels[] = $date->format('d/m');
            $revenue[] = $data[$d]->rev ?? 0;
            $orders[] = $data[$d]->cnt ?? 0;
        }

        return compact('labels', 'revenue', 'orders');
    }

    private function getOrderStatusStats($start, $end)
    {
        // Thống kê trạng thái đơn hàng cho biểu đồ tròn
        $stats = Order::whereBetween('created_at', [$start, $end])
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
        
        // Đảm bảo luôn có đủ key để không lỗi JS
        return array_merge(['pending'=>0, 'processing'=>0, 'completed'=>0, 'cancelled'=>0], $stats);
    }

    private function getTopProducts($start, $end)
    {
        // Dùng Collection để an toàn tuyệt đối với lỗi SQL 'group by'
        $orders = Order::with(['items.product'])
            ->where('status', 'completed')
            ->whereBetween('created_at', [$start, $end])
            ->get();

        $stats = [];
        foreach ($orders as $order) {
            foreach ($order->items as $item) {
                if (!$item->product) continue;
                $pid = $item->product->id;
                // Lấy giá ưu tiên: giá trong item -> giá trong product -> 0
                $price = $item->price ?? ($item->product->price ?? 0);
                $qty = $item->quantity ?? 1;

                if (!isset($stats[$pid])) {
                    $stats[$pid] = [
                        'name' => $item->product->name,
                        'sold' => 0,
                        'revenue' => 0,
                        'image' => $item->product->image ?? null // Nếu có ảnh
                    ];
                }
                $stats[$pid]['sold'] += $qty;
                $stats[$pid]['revenue'] += ($qty * $price);
            }
        }
        return collect($stats)->sortByDesc('revenue')->take(5);
    }

    private function getTopCustomers($start, $end)
    {
        $orders = Order::with('user')
            ->where('status', 'completed')
            ->whereBetween('created_at', [$start, $end])
            ->get();

        $stats = [];
        foreach ($orders as $order) {
            if (!$order->user) continue;
            $uid = $order->user->id;
            if (!isset($stats[$uid])) {
                $stats[$uid] = ['name' => $order->user->name, 'email' => $order->user->email, 'spent' => 0, 'count' => 0];
            }
            $stats[$uid]['spent'] += $order->total_amount;
            $stats[$uid]['count']++;
        }
        return collect($stats)->sortByDesc('spent')->take(5);
    }

    private function getDateRange($key)
    {
        $now = Carbon::now();
        switch ($key) {
            case 'today': return ['start' => $now->copy()->startOfDay(), 'end' => $now->copy()->endOfDay()];
            case 'yesterday': return ['start' => $now->copy()->subDay()->startOfDay(), 'end' => $now->copy()->subDay()->endOfDay()];
            case '7_days': return ['start' => $now->copy()->subDays(6)->startOfDay(), 'end' => $now->copy()->endOfDay()];
            case '30_days': return ['start' => $now->copy()->subDays(29)->startOfDay(), 'end' => $now->copy()->endOfDay()];
            case 'last_month': return ['start' => $now->copy()->subMonth()->startOfMonth(), 'end' => $now->copy()->subMonth()->endOfMonth()];
            default: return ['start' => $now->copy()->startOfMonth(), 'end' => $now->copy()->endOfDay()];
        }
    }
}