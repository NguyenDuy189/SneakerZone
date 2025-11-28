<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller; // <--- QUAN TRỌNG: Phải thêm dòng này để gọi Controller cha
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller {
    public function index()
    {
        // 1. CARDS: TỔNG QUAN
        // Tổng doanh thu (Chỉ tính đơn đã hoàn thành)
        $totalRevenue = Order::where('status', 'completed')->sum('total_amount');
        
        // Số đơn hàng cần xử lý (Pending hoặc Processing)
        $pendingOrders = Order::whereIn('status', ['pending', 'processing'])->count();
        
        // Tổng khách hàng
        $totalUsers = User::count();
        
        // Khách mới tháng này
        $usersThisMonth = User::whereMonth('created_at', Carbon::now()->month)->count();

        // 2. CHART: DOANH THU THEO THÁNG
        $monthlyStats = Order::where('status', 'completed')
            ->select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('SUM(total_amount) as total')
            )
            ->whereYear('created_at', date('Y'))
            ->groupBy('month')
            ->pluck('total', 'month')
            ->toArray();

        $chartLabels = [];
        $chartData = [];
        for ($i = 1; $i <= 12; $i++) {
            $chartLabels[] = "Tháng $i";
            $chartData[] = $monthlyStats[$i] ?? 0;
        }

        // 3. DANH SÁCH: THÔNG BÁO & ĐƠN HÀNG GẦN ĐÂY
        $notifications = Notification::latest()->take(4)->get();
        
        // Lấy 5 đơn mới nhất, eager load User để tránh N+1 query
        $recentOrders = Order::with('user')
            ->latest()
            ->take(5)
            ->get();

        return view('admin.dashboard.index', compact(
            'totalRevenue',
            'pendingOrders',
            'totalUsers',
            'usersThisMonth',
            'chartLabels',
            'chartData',
            'notifications',
            'recentOrders'
        ));
    }
}