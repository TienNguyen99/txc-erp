<?php

namespace App\Http\Controllers;

use App\Services\DashboardMetricsService;
use Illuminate\Http\Request;

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
}
