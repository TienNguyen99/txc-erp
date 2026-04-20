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
            'users'                  => User::count(),
            'orders'                 => Order::count(),
            'order_tracking'         => OrderTracking::count(),
            'production_reports'     => ProductionReport::count(),
            'warehouse_transactions' => WarehouseTransaction::count(),
        ];

        // --- Doanh Thu ---
        $totalRevenue = Order::selectRaw('SUM(qty * price_usd) as total')->value('total') ?? 0;
        $shippedRevenue = Order::whereIn('status', ['shipped', 'done'])
                            ->selectRaw('SUM(qty * price_usd) as total')
                            ->value('total') ?? 0;
        $stats['total_revenue'] = $totalRevenue;
        $stats['shipped_revenue'] = $shippedRevenue;

        // --- Chart: QTY Shipped vs Remaining ---
        $shippedQty = Order::whereIn('status', ['shipped', 'done'])->sum('qty');
        $remainingQty = Order::whereNotIn('status', ['shipped', 'done'])->sum('qty');

        $chartDataQty = [
            'labels' => ['Đã xuất', 'Còn lại'],
            'data'   => [(float)$shippedQty, (float)$remainingQty]
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

        // --- Lệnh Sản Xuất Filter ---
        $lenhSxList = LenhSanXuat::orderByDesc('created_at')->get();
        $selectedLenhId = $request->input('lenh_sx_id');
        $lenhSxItems = collect();
        $selectedLenh = null;

        if ($selectedLenhId) {
            $selectedLenh = LenhSanXuat::with('items')->find($selectedLenhId);
            if ($selectedLenh) {
                $lenhSxItems = $selectedLenh->items
                    ->where('da_len_lenh', true)
                    ->map(function ($item) {
                        // SL công đoạn Dệt
                        $item->sl_det = ProductionReport::where('lenh_sx', $item->lenh_child)
                            ->where('cong_doan', 'Dệt')
                            ->sum('sl_dat');

                        // SL công đoạn Định hình
                        $item->sl_dinh_hinh = ProductionReport::where('lenh_sx', $item->lenh_child)
                            ->where('cong_doan', 'Định hình')
                            ->sum('sl_dat');

                        // SL nhập kho
                        $item->sl_nhap_kho = WarehouseTransaction::where('lenh_sx', 'like', '%' . $item->lenh_child . '%')
                            ->where('cong_doan', 'NHAPKHO')
                            ->sum('so_luong');

                        return $item;
                    })->values();
            }
        }

        return view('admin.dashboard', compact(
            'stats', 'recentOrders', 'recentProduction', 'recentWarehouse',
            'chartDataOrder', 'chartDataProduction', 'chartDataQty',
            'lenhSxList', 'selectedLenhId', 'lenhSxItems', 'selectedLenh'
        ));
    }
}
