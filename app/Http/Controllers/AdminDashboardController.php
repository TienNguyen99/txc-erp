<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Order;
use App\Models\OrderTracking;
use App\Models\ProductionReport;
use App\Models\WarehouseTransaction;
use App\Models\DanhMucHangHoa;
use App\Models\LenhSanXuat;
use App\Models\LenhSanXuatItem;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function index(Request $request)
    {
        // --- Card 1: Đơn chưa hoàn thành ---
        $totalOrders = Order::count();
        $pendingOrders = Order::whereNotIn('status', ['shipped', 'done'])->count();
        $pctPendingOrders = $totalOrders > 0 ? round(($pendingOrders / $totalOrders) * 100, 1) : 0;

        // --- Card 2: Lệnh chưa hoàn thành (OrderTracking) ---
        $totalTrackings = OrderTracking::count();
        $pendingTrackings = OrderTracking::whereNotIn('cong_doan', ['Đã nhập kho', 'Đã giao'])->count();
        $pctPendingTrackings = $totalTrackings > 0 ? round(($pendingTrackings / $totalTrackings) * 100, 1) : 0;

        // --- Card 3: Sản lượng chưa SX ---
        $totalQtyRequired = Order::sum('yrd');
        // Tính tổng sản lượng đã nhập kho (coi như đã SX xong)
        $totalQtyProduced = WarehouseTransaction::nhapKho()->sum('so_luong');
        $unproducedQty = max(0, $totalQtyRequired - $totalQtyProduced);
        $pctUnproduced = $totalQtyRequired > 0 ? round(($unproducedQty / $totalQtyRequired) * 100, 1) : 0;

        // --- Card 4: Tỷ lệ hao hụt NVL ---
        $totalSlDat = ProductionReport::sum('sl_dat');
        $totalSlHu = ProductionReport::sum('sl_hu');
        $lossRate = ($totalSlDat + $totalSlHu) > 0 ? round(($totalSlHu / ($totalSlDat + $totalSlHu)) * 100, 2) : 0;

        // --- Doanh thu (Vẫn giữ cho báo cáo chung nếu cần, hoặc ẩn đi) ---
        $exchangeRate = \App\Models\Setting::where('key', 'usd_to_vnd')->value('value') ?? 25400;
        $totalRevenueUsd = Order::selectRaw('SUM(yrd * COALESCE(price_usd, price_usd_auto, 0)) as total')->value('total') ?? 0;
        $totalRevenueVnd = $totalRevenueUsd * $exchangeRate;

        // --- Chart 0: Trạng thái đơn hàng (Order.status) ---
        $orderStatuses = Order::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')->toArray();
        $statusLabels = [
            'pending'       => 'Chờ xử lý',
            'in_production' => 'Đang SX',
            'done'          => 'Hoàn thành',
            'shipped'       => 'Đã giao',
        ];
        $orderedStatuses = [];
        foreach ($statusLabels as $key => $label) {
            $orderedStatuses[$label] = $orderStatuses[$key] ?? 0;
        }
        $chartDataOrderStatus = [
            'labels' => array_keys($orderedStatuses),
            'data'   => array_values($orderedStatuses),
        ];

        // --- Chart 1: Sản lượng sản xuất theo thời gian (7 ngày qua) ---
        $last7Days = collect();
        for ($i = 6; $i >= 0; $i--) {
            $last7Days->push(now()->subDays($i)->format('Y-m-d'));
        }

        $productionDataByDate = ProductionReport::where('ngay_sx', '>=', now()->subDays(6)->format('Y-m-d'))
            ->selectRaw('DATE(ngay_sx) as date, sum(sl_dat) as total')
            ->groupBy('date')
            ->pluck('total', 'date')->toArray();

        $chartDataProductionTime = [
            'labels' => $last7Days->map(fn($d) => \Carbon\Carbon::parse($d)->format('d/m'))->toArray(),
            'data' => $last7Days->map(fn($d) => $productionDataByDate[$d] ?? 0)->toArray()
        ];

        // --- Chart 2: Trạng thái lệnh sản xuất (OrderTracking) ---
        $trackingStatuses = OrderTracking::selectRaw('cong_doan, count(*) as total_count')
            ->groupBy('cong_doan')
            ->pluck('total_count', 'cong_doan')->toArray();
        $chartDataTrackingStatus = [
            'labels' => array_keys($trackingStatuses),
            'data' => array_values($trackingStatuses)
        ];

        // --- Chart 3: Sản lượng sản xuất theo công đoạn ---
        $productionByStage = ProductionReport::selectRaw('cong_doan, sum(sl_dat) as total')
            ->groupBy('cong_doan')
            ->pluck('total', 'cong_doan')->toArray();
        $chartDataProductionStage = [
            'labels' => array_keys($productionByStage),
            'data' => array_values($productionByStage)
        ];

        // --- Chart 4: Sản lượng sản xuất theo ca (đơn vị) ---
        $productionByCa = ProductionReport::selectRaw('ca, sum(sl_dat) as total')
            ->whereNotNull('ca')->where('ca', '!=', '')
            ->groupBy('ca')
            ->pluck('total', 'ca')->toArray();
        $chartDataProductionCa = [
            'labels' => array_map(fn($c) => "Ca " . $c, array_keys($productionByCa)),
            'data' => array_values($productionByCa)
        ];

        // --- THEO DÕI LỆNH SẢN XUẤT ---
        $latestLenh = LenhSanXuat::with('items')->latest()->limit(20)->get();
        
        $allChildNos = collect();
        foreach ($latestLenh as $lenh) {
            $allChildNos = $allChildNos->merge($lenh->items->pluck('lenh_child'));
        }

        $trackingStages = OrderTracking::whereIn('tracking_number_child', $allChildNos)
            ->select('tracking_number_child', 'cong_doan')
            ->get()
            ->keyBy('tracking_number_child');

        $lenhSxTracking = $latestLenh->map(function ($lenh) use ($trackingStages) {
                $activeItems = $lenh->items->where('da_len_lenh', true);
                $totalItems = $lenh->items->count();
                $activeCount = $activeItems->count();
                $tongYrd = $activeItems->sum('tong_yrd');
                $tongCanSx = $activeItems->sum('sl_can_sx');

                // Gán công đoạn cho từng item
                foreach ($lenh->items as $item) {
                    $item->cong_doan = $trackingStages[$item->lenh_child]->cong_doan ?? 'Chờ sản xuất';
                }

                // Tính tổng đã SX và tồn kho cho các item đã lên lệnh
                $tongDaSx = 0;
                $tongTonKho = 0;
                foreach ($activeItems as $item) {
                    $tongDaSx += ProductionReport::where('lenh_sx', $item->lenh_child)->sum('sl_dat');
                    $nhap = WarehouseTransaction::where('ma_hh', $item->ma_hh)->nhapKho()->sum('so_luong');
                    $xuat = WarehouseTransaction::where('ma_hh', $item->ma_hh)->xuatKho()->sum('so_luong');
                    $tongTonKho += ($nhap - $xuat);
                }

                // Xác định trạng thái
                $progress = $tongYrd > 0 ? min(100, round(($tongTonKho + $tongDaSx) / $tongYrd * 100)) : 0;
                if ($activeCount === 0) {
                    $trangThai = 'new';
                } elseif ($tongTonKho >= $tongYrd && $tongYrd > 0) {
                    $trangThai = 'done';
                } elseif ($tongDaSx > 0) {
                    $trangThai = 'producing';
                } else {
                    $trangThai = 'waiting';
                }

                return (object) [
                    'id'           => $lenh->id,
                    'lenh_so'      => $lenh->lenh_so,
                    'chart'        => $lenh->chart,
                    'nhom_hh'      => $lenh->nhom_hh,
                    'total_items'  => $totalItems,
                    'active_items' => $activeCount,
                    'tong_yrd'     => $tongYrd,
                    'tong_can_sx'  => $tongCanSx,
                    'tong_da_sx'   => $tongDaSx,
                    'tong_ton_kho' => $tongTonKho,
                    'progress'     => $progress,
                    'trang_thai'   => $trangThai,
                    'created_at'   => $lenh->created_at,
                    'items'        => $lenh->items,
                ];
            });

        // Stats tổng hợp cho section Lệnh SX
        $lenhSxStats = (object) [
            'total'     => $lenhSxTracking->count(),
            'new'       => $lenhSxTracking->where('trang_thai', 'new')->count(),
            'waiting'   => $lenhSxTracking->where('trang_thai', 'waiting')->count(),
            'producing' => $lenhSxTracking->where('trang_thai', 'producing')->count(),
            'done'      => $lenhSxTracking->where('trang_thai', 'done')->count(),
        ];

        // Gửi dữ liệu ra view
        $stats = [
            'pending_orders' => $pendingOrders,
            'total_orders' => $totalOrders,
            'pct_pending_orders' => $pctPendingOrders,
            
            'pending_trackings' => $pendingTrackings,
            'total_trackings' => $totalTrackings,
            'pct_pending_trackings' => $pctPendingTrackings,
            
            'unproduced_qty' => $unproducedQty,
            'total_qty_required' => $totalQtyRequired,
            'pct_unproduced' => $pctUnproduced,
            
            'loss_rate' => $lossRate,
            'total_revenue' => $totalRevenueVnd,
        ];

        $stages = OrderTracking::STAGES;

        return view('admin.dashboard', compact(
            'stats',
            'chartDataOrderStatus',
            'chartDataProductionTime',
            'chartDataTrackingStatus',
            'chartDataProductionStage',
            'chartDataProductionCa',
            'lenhSxTracking',
            'lenhSxStats',
            'stages'
        ));
    }
}
