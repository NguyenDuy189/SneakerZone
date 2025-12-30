<?php

namespace App\Http\Services;

use App\Models\Order;
use App\Models\User;
use App\Models\ProductVariant;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    // CẬP NHẬT HÀM NÀY (Trong DashboardService)
    public function getDashboardData($request)
    {
        $range = $this->getDateRange($request);
        
        // Tính toán status chi tiết để dùng cho cả Chart và List
        $statusData = $this->getOrderStatusStats($range);

        return [
            'range'         => $range,
            'revenue'       => $this->calculateMetric(Order::class, 'total_amount', $range, 'sum'),
            'orders'        => $this->calculateMetric(Order::class, 'id', $range, 'count'),
            'customers'     => $this->calculateMetric(User::class, 'id', $range, 'count'),
            'avg_order'     => $this->calculateAOV($range),

            'main_chart'    => $this->getMainChartData($range),
            'category_chart'=> $this->getCategoryRevenueChart($range),
            
            // Dữ liệu cho biểu đồ Donut & List liệt kê
            'status_chart'  => $statusData, 

            'top_products'  => $this->getTopProducts($range),
            'recent_orders' => Order::with('user')->latest()->take(6)->get(),
            
            // QUERY ALERT KHO HÀNG (Lấy kèm Product để hiện tên gốc)
            'low_stock'     => ProductVariant::with('product')
                                ->where('stock_quantity', '<=', 10)
                                ->orderBy('stock_quantity', 'asc') // Ưu tiên hết hàng lên trước
                                ->limit(10) // Lấy 10 cảnh báo
                                ->get(),
        ];
    }

    public function getExportData($request)
    {
        $range = $this->getDateRange($request);
        return Order::with(['user', 'items.productVariant.product'])
            ->whereBetween('created_at', [$range['start'], $range['end']])
            ->get();
    }

    // ================= PRIVATE HELPERS =================

    private function getDateRange($request)
    {
        $filter = $request->input('date_range', 'this_month');
        $start = Carbon::now();
        $end   = Carbon::now();
        $label = 'Tùy chọn';

        // Xử lý Custom Date
        if ($filter === 'custom' && $request->has(['start_date', 'end_date'])) {
            $start = Carbon::parse($request->input('start_date'))->startOfDay();
            $end   = Carbon::parse($request->input('end_date'))->endOfDay();
            $label = $start->format('d/m') . ' - ' . $end->format('d/m');
        } else {
            // Các preset có sẵn
            switch ($filter) {
                case 'today': 
                    $start = Carbon::today(); $end = Carbon::today()->endOfDay(); $label = 'Hôm nay'; break;
                case 'yesterday': 
                    $start = Carbon::yesterday(); $end = Carbon::yesterday()->endOfDay(); $label = 'Hôm qua'; break;
                case '7_days': 
                    $start = Carbon::now()->subDays(6)->startOfDay(); $label = '7 ngày qua'; break;
                case '30_days': 
                    $start = Carbon::now()->subDays(29)->startOfDay(); $label = '30 ngày qua'; break;
                case 'last_month': 
                    $start = Carbon::now()->subMonth()->startOfMonth(); 
                    $end = Carbon::now()->subMonth()->endOfMonth(); 
                    $label = 'Tháng trước'; break;
                case 'this_month':
                default:
                    $start = Carbon::now()->startOfMonth(); 
                    $label = 'Tháng này'; break;
            }
        }
        
        return ['start' => $start, 'end' => $end, 'label' => $label, 'filter_key' => $filter];
    }

    private function calculateMetric($model, $column, $range, $type)
    {
        // Tính toán chỉ số hiện tại và tăng trưởng so với kỳ trước
        $query = $model::query()->whereBetween('created_at', [$range['start'], $range['end']]);
        $current = $type == 'sum' ? $query->sum($column) : $query->count();

        // Tính kỳ trước (Previous Period)
        $diffInDays = $range['start']->diffInDays($range['end']) + 1;
        $prevStart = $range['start']->copy()->subDays($diffInDays);
        $prevEnd = $range['end']->copy()->subDays($diffInDays);

        $prevQuery = $model::query()->whereBetween('created_at', [$prevStart, $prevEnd]);
        $prev = $type == 'sum' ? $prevQuery->sum($column) : $prevQuery->count();

        $growth = ($prev > 0) ? (($current - $prev) / $prev) * 100 : 100;

        return ['value' => $current, 'growth' => round($growth, 1), 'is_up' => $growth >= 0];
    }

    private function calculateAOV($range)
    {
        // Giá trị trung bình đơn hàng (Average Order Value)
        $rev = Order::whereBetween('created_at', [$range['start'], $range['end']])->sum('total_amount');
        $cnt = Order::whereBetween('created_at', [$range['start'], $range['end']])->count();
        return ['value' => $cnt > 0 ? $rev / $cnt : 0];
    }

    private function getMainChartData($range)
    {
        // Lấy dữ liệu từng ngày trong khoảng đã chọn
        $dates = [];
        $revenueData = [];
        $ordersData = [];

        $period = \Carbon\CarbonPeriod::create($range['start'], $range['end']);

        foreach ($period as $date) {
            $formatted = $date->format('Y-m-d');
            $dates[] = $date->format('d/m');
            
            // Query tối ưu hơn: Gom nhóm theo ngày
            $dayRevenue = Order::whereDate('created_at', $formatted)->sum('total_amount');
            $dayOrders  = Order::whereDate('created_at', $formatted)->count();
            
            $revenueData[] = $dayRevenue;
            $ordersData[] = $dayOrders;
        }

        return [
            'labels' => $dates,
            'revenue' => $revenueData,
            'orders' => $ordersData
        ];
    }

    private function getCategoryRevenueChart($range)
    {
        // Query phức tạp: Join OrderItems -> Variant -> Product -> Category
        // Giả định bảng categories có tồn tại và products có category_id
        // Nếu DB của bạn chưa có bảng categories, bạn có thể thay bằng 'products.brand' hoặc trường khác
        
        $data = DB::table('order_items')
            ->join('product_variants', 'order_items.product_variant_id', '=', 'product_variants.id')
            ->join('products', 'product_variants.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id') // Left join phòng trường hợp null
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.status', 'completed')
            ->whereBetween('orders.created_at', [$range['start'], $range['end']])
            ->select(
                DB::raw('COALESCE(categories.name, "Khác") as cat_name'), // Nếu ko có category thì gọi là "Khác"
                DB::raw('SUM(order_items.quantity * order_items.price) as revenue')
            )
            ->groupBy('cat_name')
            ->orderByDesc('revenue')
            ->get();

        return [
            'labels' => $data->pluck('cat_name'),
            'data'   => $data->pluck('revenue'),
        ];
    }

    private function getOrderStatusCounts($range)
    {
        return [
            'completed' => Order::whereBetween('created_at', [$range['start'], $range['end']])->where('status', 'completed')->count(),
            'pending'   => Order::whereBetween('created_at', [$range['start'], $range['end']])->whereIn('status', ['pending', 'processing'])->count(),
            'shipping'  => Order::whereBetween('created_at', [$range['start'], $range['end']])->where('status', 'shipping')->count(),
            'cancelled' => Order::whereBetween('created_at', [$range['start'], $range['end']])->where('status', 'cancelled')->count(),
        ];
    }

    private function getTopProducts($range)
    {
        return DB::table('order_items')
            ->select(
                'products.name as product_name',
                'product_variants.name as variant_name',
                DB::raw('SUM(order_items.quantity) as sold'),
                DB::raw('SUM(order_items.quantity * order_items.price) as revenue')
            )
            ->join('product_variants', 'order_items.product_variant_id', '=', 'product_variants.id')
            ->join('products', 'product_variants.product_id', '=', 'products.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.status', 'completed')
            ->whereBetween('orders.created_at', [$range['start'], $range['end']])
            ->groupBy('product_variants.id', 'product_variants.name', 'products.name')
            ->orderByDesc('revenue')
            ->limit(5)
            ->get();
    }

    // THÊM MỚI HELPER NÀY ĐỂ TẠO LIST CHI TIẾT
    private function getOrderStatusStats($range)
    {
        $query = Order::whereBetween('created_at', [$range['start'], $range['end']]);

        // Đếm số lượng
        $completed = (clone $query)->where('status', 'completed')->count();
        $processing = (clone $query)->whereIn('status', ['pending', 'processing'])->count();
        $shipping = (clone $query)->where('status', 'shipping')->count();
        $cancelled = (clone $query)->whereIn('status', ['cancelled', 'returned'])->count();

        // Cấu trúc dữ liệu trả về (gồm cả màu sắc để View hiển thị)
        return [
            'counts' => [$completed, $processing, $shipping, $cancelled], // Dùng cho ChartJS
            'list' => [
                [
                    'label' => 'Hoàn thành',
                    'count' => $completed,
                    'color' => 'bg-emerald-500', // Xanh lá
                    'text'  => 'text-emerald-600',
                    'icon'  => 'fa-check-circle'
                ],
                [
                    'label' => 'Đang xử lý',
                    'count' => $processing,
                    'color' => 'bg-amber-500',   // Cam
                    'text'  => 'text-amber-600',
                    'icon'  => 'fa-clock'
                ],
                [
                    'label' => 'Đang giao hàng',
                    'count' => $shipping,
                    'color' => 'bg-blue-500',    // Xanh dương
                    'text'  => 'text-blue-600',
                    'icon'  => 'fa-truck'
                ],
                [
                    'label' => 'Đã hủy / Trả',
                    'count' => $cancelled,
                    'color' => 'bg-red-500',     // Đỏ
                    'text'  => 'text-red-600',
                    'icon'  => 'fa-times-circle'
                ]
            ]
        ];
    }
}