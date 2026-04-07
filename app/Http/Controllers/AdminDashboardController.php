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

        return view('admin.dashboard', compact('stats', 'recentOrders', 'recentProduction', 'recentWarehouse'));
    }
}
