<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Order;
use App\Models\OrderTracking;
use App\Models\ProductionReport;
use App\Models\WarehouseTransaction;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'users'                  => User::count(),
            'orders'                 => Order::count(),
            'order_tracking'         => OrderTracking::count(),
            'production_reports'     => ProductionReport::count(),
            'warehouse_transactions' => WarehouseTransaction::count(),
        ];

        $recentOrders     = Order::latest()->take(5)->get();
        $recentProduction = ProductionReport::latest()->take(5)->get();
        $recentWarehouse  = WarehouseTransaction::latest()->take(5)->get();

        // --- Chart 1: Order Status Distribution ---
        $orderStatuses = Order::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')->toArray();
        $chartDataOrder = [
            'labels' => array_keys($orderStatuses),
            'data'   => array_values($orderStatuses)
        ];

        // --- Chart 2: Production output by date (Last 7 days) ---
        $last7Days = collect();
        for ($i = 6; $i >= 0; $i--) {
            $last7Days->push(now()->subDays($i)->format('Y-m-d'));
        }

        $productionData = ProductionReport::where('ngay_sx', '>=', now()->subDays(6)->format('Y-m-d'))
            ->selectRaw('DATE(ngay_sx) as date, sum(sl_dat) as total')
            ->groupBy('date')
            ->pluck('total', 'date')->toArray();

        $chartDataProduction = [
            'labels' => $last7Days->map(fn($d) => \Carbon\Carbon::parse($d)->format('d/m'))->toArray(),
            'data'   => $last7Days->map(fn($d) => $productionData[$d] ?? 0)->toArray()
        ];

        return view('admin.dashboard', compact(
            'stats', 'recentOrders', 'recentProduction', 'recentWarehouse',
            'chartDataOrder', 'chartDataProduction'
        ));
    }
}
