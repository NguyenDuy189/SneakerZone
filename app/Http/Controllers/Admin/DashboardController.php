<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\DashboardRequest;
use App\Http\Services\DashboardService;
use App\Http\Exports\DashboardExport;
use Maatwebsite\Excel\Facades\Excel;

class DashboardController extends Controller
{
    protected $service;

    public function __construct(DashboardService $service)
    {
        $this->service = $service;
    }

    public function index(DashboardRequest $request)
    {
        // Truyền cả request để service xử lý custom date
        $data = $this->service->getDashboardData($request);
        return view('admin.dashboard.index', $data);
    }

    public function export(DashboardRequest $request)
    {
        $data = $this->service->getExportData($request);
        $dateStr = now()->format('d-m-Y');
        return Excel::download(new DashboardExport($data), "baocao-doanhthu-{$dateStr}.xlsx");
    }
}