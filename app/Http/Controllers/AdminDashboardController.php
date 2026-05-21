<?php

namespace App\Http\Controllers;

use App\Exports\DashboardExecutiveReportExport;
use App\Services\DashboardMetricsService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class AdminDashboardController extends Controller
{
    public function __construct(private readonly DashboardMetricsService $dashboardMetricsService)
    {
    }

    public function index(Request $request)
    {
        return view('admin.dashboard', $this->dashboardMetricsService->build($request->only([
            'date_from',
            'date_to',
            'khach_hang_id',
            'nhom_hang',
        ])));
    }

    public function export(Request $request)
    {
        $filters = $request->only([
            'date_from',
            'date_to',
            'khach_hang_id',
            'nhom_hang',
        ]);

        return Excel::download(
            new DashboardExecutiveReportExport($filters),
            'bao-cao-dieu-hanh-' . now()->format('Ymd-His') . '.xlsx'
        );
    }
}
