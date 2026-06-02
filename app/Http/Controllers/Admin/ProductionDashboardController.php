<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DashboardMetricsService;
use Illuminate\Http\Request;

class ProductionDashboardController extends Controller
{
    public function __construct(private readonly DashboardMetricsService $dashboardMetricsService)
    {
    }

    public function index(Request $request)
    {
        $filters = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'nhom_hang' => ['nullable', 'string', 'max:100'],
        ]);

        return view('admin.production-dashboard.index', $this->dashboardMetricsService->buildProduction($filters));
    }
}
