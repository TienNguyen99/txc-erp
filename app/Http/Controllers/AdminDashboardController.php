<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Order;
use App\Models\OrderTracking;
use App\Models\ProductionReport;
use App\Models\WarehouseTransaction;
use App\Models\LenhSanXuat;
use App\Models\LenhSanXuatItem;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function index(Request $request)
    {
        $stats = [
            'orders' => Order::count(),
            'pending_orders' => Order::whereIn('status', ['pending', 'in_production'])->count(),
        ];

        // --- Doanh Thu ---
        $exchangeRate = \App\Models\Setting::where('key', 'usd_to_vnd')->value('value') ?? 25400;

        $totalRevenueUsd = Order::selectRaw('SUM(yrd * COALESCE(price_usd, price_usd_auto, 0)) as total')->value('total') ?? 0;
        $totalRevenueVnd = $totalRevenueUsd * $exchangeRate;

        // Doanh thu xuất kho thực tế theo tỷ giá lúc xuất
        $shippedRevenueVnd = WarehouseTransaction::where('cong_doan', 'XUATKHO')
            ->selectRaw('SUM(so_luong * price_usd * exchange_rate) as total')
            ->value('total') ?? 0;

        $stats['total_revenue'] = $totalRevenueVnd;
        $stats['shipped_revenue'] = $shippedRevenueVnd;

        // --- Chart: QTY Shipped vs Remaining ---
        $shippedQty = Order::whereIn('status', ['shipped', 'done'])->sum('yrd');
        $remainingQty = Order::whereNotIn('status', ['shipped', 'done'])->sum('yrd');

        $chartDataQty = [
            'labels' => ['Đã xuất', 'Còn lại'],
            'data' => [(float) $shippedQty, (float) $remainingQty]
        ];

        $recentOrders = Order::latest()->take(5)->get();
        $recentProduction = ProductionReport::latest()->take(5)->get();
        $recentWarehouse = WarehouseTransaction::latest()->take(5)->get();

        // --- Chart 1: Order Status Distribution (by YRD) ---
        $orderStatuses = Order::selectRaw('status, sum(yrd) as total_qty')
            ->groupBy('status')
            ->pluck('total_qty', 'status')->toArray();
        $chartDataOrder = [
            'labels' => array_keys($orderStatuses),
            'data' => array_values($orderStatuses)
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
            'data' => $last7Days->map(fn($d) => $productionData[$d] ?? 0)->toArray()
        ];

        return view('admin.dashboard', compact(
            'stats',
            'recentOrders',
            'recentProduction',
            'recentWarehouse',
            'chartDataOrder',
            'chartDataProduction'
        ));
    }
}
