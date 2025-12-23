<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\DashboardService;

class DashboardController extends Controller
{
    protected $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /**
     * Hiển thị Dashboard Admin
     */
    public function index(Request $request)
    {
        // 1. Validate dữ liệu đầu vào để bảo mật
        $filters = $request->validate([
            'date_range' => 'nullable|string|in:today,yesterday,7_days,30_days,this_month,last_month',
        ]);

        // 2. Lấy dữ liệu từ Service
        $data = $this->dashboardService->getDashboardData($filters);

        

        // 3. Trả về View với dữ liệu
        return view('admin.dashboard.index', $data);
    }
}