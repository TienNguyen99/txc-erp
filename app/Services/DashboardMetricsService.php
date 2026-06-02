<?php

namespace App\Services;

use App\Models\LenhSanXuat;
use App\Models\LenhSanXuatItem;
use App\Models\DanhMucKhachHang;
use App\Models\DanhMucHangHoa;
use App\Models\DinhMucNvl;
use App\Models\KhachHangNhomHang;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Order;
use App\Models\OrderTracking;
use App\Models\ProductionReport;
use App\Models\Setting;
use App\Models\WarehouseTransaction;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class DashboardMetricsService
{
    // Entry point duy nhất cho dashboard: gom đủ dữ liệu view cần.
    public function build(array $inputFilters = []): array
    {
        $filters = $this->normalizeFilters($inputFilters);
        $stats = $this->buildStats($filters);
        $charts = $this->buildCharts($filters);
        $opsDashboard = $this->buildOperationsDashboard($filters);
        [$lenhSxTracking, $lenhSxStats] = $this->buildLenhSanXuatTracking();

        $nhomHangOptions = KhachHangNhomHang::query()
            ->when($filters['khach_hang_id'] ?? null, fn($q, $id) => $q->where('khach_hang_id', $id))
            ->orderBy('ma_nhom')
            ->get()
            ->map(fn($row) => [
                'ma_nhom' => $row->ma_nhom,
                'ten_nhom' => $row->ten_nhom,
                'label' => ($row->ten_nhom ?: $row->ma_nhom) . ' (' . $row->ma_nhom . ')',
            ])
            ->unique('ma_nhom')
            ->values();

        $nhomHangByKhachHang = KhachHangNhomHang::orderBy('ma_nhom')
            ->get()
            ->groupBy('khach_hang_id')
            ->map(fn($rows) => $rows->map(fn($row) => [
                'ma_nhom' => $row->ma_nhom,
                'ten_nhom' => $row->ten_nhom,
                'label' => ($row->ten_nhom ?: $row->ma_nhom) . ' (' . $row->ma_nhom . ')',
            ])->values());

        return [
            'filters' => $filters,
            'khachHangOptions' => DanhMucKhachHang::orderBy('ten_kh')->pluck('ten_kh', 'id'),
            'nhomHangOptions' => $nhomHangOptions,
            'nhomHangByKhachHang' => $nhomHangByKhachHang,
            'stats' => $stats,
            'chartDataOrderStatus' => $charts['order_status'],
            'chartDataProductionTime' => $charts['production_time'],
            'chartDataTrackingStatus' => $charts['tracking_status'],
            'chartDataProductionStage' => $charts['production_stage'],
            'chartDataProductionCa' => $charts['production_ca'],
            'lenhSxTracking' => $lenhSxTracking,
            'lenhSxStats' => $lenhSxStats,
            'stages' => OrderTracking::STAGES,
            'opsDashboard' => $opsDashboard,
            'reportDashboard' => $this->buildReportDashboard($filters, $opsDashboard),
        ];
    }

    /**
     * Dashboard chuyên biệt cho quản lý sản xuất.
     *
     * Chỉ dựng các khối vận hành cần thiết, tránh gọi finance, inventory và
     * procurement của dashboard điều hành tổng để giữ thời gian phản hồi thấp.
     */
    public function buildProduction(array $inputFilters = []): array
    {
        $filters = $this->normalizeProductionFilters($inputFilters);
        $currentStateFilters = array_replace($filters, ['date_from' => null, 'date_to' => null]);
        [$lenhSxTracking, $lenhSxStats] = $this->buildLenhSanXuatTracking();
        $productionOrdersNeedAttention = $lenhSxTracking
            ->whereNotIn('trang_thai', ['done'])
            ->take(10)
            ->values();

        return [
            'filters' => $filters,
            'summary' => $this->buildProductionSummary($filters),
            'outputTrend' => $this->buildProductionOutputTrend($filters),
            'stageOutput' => $this->buildProductionStageChart($filters),
            'shiftOutput' => $this->buildProductionCaChart($filters),
            'stageBacklog' => $this->buildWipBlock($currentStateFilters),
            'productionOrdersNeedAttention' => $productionOrdersNeedAttention,
            'materialShortages' => $this->buildBomMaterialShortages($filters),
            'pendingReports' => $this->buildPendingProductionReports($filters),
            'lenhSxTracking' => $lenhSxTracking,
            'lenhSxStats' => $lenhSxStats,
        ];
    }

    private function normalizeFilters(array $input): array
    {
        $today = now()->toDateString();
        $from = $input['date_from'] ?? now()->startOfMonth()->toDateString();
        $to = $input['date_to'] ?? $today;

        return [
            'date_from' => $from,
            'date_to' => $to,
            'khach_hang_id' => $input['khach_hang_id'] ?? null,
            'nhom_hang' => $input['nhom_hang'] ?? null,
        ];
    }

    private function normalizeProductionFilters(array $input): array
    {
        return [
            'date_from' => $input['date_from'] ?? now()->subDays(13)->toDateString(),
            'date_to' => $input['date_to'] ?? now()->toDateString(),
            'khach_hang_id' => null,
            'nhom_hang' => $input['nhom_hang'] ?? null,
        ];
    }

    private function buildProductionSummary(array $filters): array
    {
        $reportTotals = $this->productionQuery($filters)
            ->selectRaw('COALESCE(SUM(sl_dat), 0) as output, COALESCE(SUM(sl_hu), 0) as defect')
            ->first();

        $statusCounts = $this->productionQuery($filters)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $totalProductionOrders = LenhSanXuat::query()
            ->when($filters['nhom_hang'] ?? null, fn($q, $nhomHang) => $q->where('nhom_hh', 'like', $nhomHang . '%'))
            ->count();

        $activeItems = LenhSanXuatItem::query()
            ->where('da_len_lenh', true)
            ->when($filters['nhom_hang'] ?? null, fn($q, $nhomHang) => $q->where('ma_hh', 'like', $nhomHang . '%'))
            ->count();

        $output = (float) ($reportTotals->output ?? 0);
        $defect = (float) ($reportTotals->defect ?? 0);

        return [
            'output' => $output,
            'defect' => $defect,
            'defect_rate' => $this->percentage($defect, $output + $defect, 2),
            'pending_reports' => (int) ($statusCounts['pending'] ?? 0),
            'approved_reports' => (int) ($statusCounts['approved'] ?? 0),
            'total_production_orders' => $totalProductionOrders,
            'active_items' => $activeItems,
        ];
    }

    private function buildProductionOutputTrend(array $filters): Collection
    {
        $from = Carbon::parse($filters['date_from']);
        $to = Carbon::parse($filters['date_to']);
        $start = $from->copy()->max($to->copy()->subDays(13));
        $dates = collect();

        for ($date = $start->copy(); $date->lte($to); $date->addDay()) {
            $dates->push($date->format('Y-m-d'));
        }

        $rows = $this->productionQuery($filters)
            ->whereDate('ngay_sx', '>=', $start)
            ->selectRaw('DATE(ngay_sx) as date, COALESCE(SUM(sl_dat), 0) as output, COALESCE(SUM(sl_hu), 0) as defect')
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        return $dates->map(function (string $date) use ($rows) {
            return [
                'date' => Carbon::parse($date)->format('d/m'),
                'output' => (float) ($rows[$date]->output ?? 0),
                'defect' => (float) ($rows[$date]->defect ?? 0),
            ];
        })->values();
    }

    private function buildPendingProductionReports(array $filters): Collection
    {
        return $this->productionQuery($filters)
            ->whereIn('status', ['pending', 'approved'])
            ->select('id', 'ngay_sx', 'lenh_sx', 'cong_doan', 'ca', 'sl_dat', 'sl_hu', 'status')
            ->latest('ngay_sx')
            ->latest('id')
            ->limit(8)
            ->get();
    }

    private function buildOperationsDashboard(array $filters): array
    {
        $action = $this->buildActionDashboardBlock($filters);
        $otd = $this->buildOtdDeliveryBlock($filters);
        $wip = $this->buildWipBlock($filters);
        $quality = $this->buildProductivityQualityBlock($filters);
        $inventory = $this->buildInventoryMrpBlock($filters);
        $procurement = $this->buildProcurementBlock($filters);
        $finance = $this->buildFinanceBlock($filters);

        return compact('action', 'otd', 'wip', 'quality', 'inventory', 'procurement', 'finance');
    }

    private function buildReportDashboard(array $filters, array $opsDashboard): array
    {
        $orderStatusCounts = $this->orderQuery($filters)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $deliveredOrders = $this->orderQuery($filters)
            ->with('khachHang')
            ->where('status', 'shipped')
            ->latest()
            ->limit(8)
            ->get()
            ->map(fn($order) => [
                'job_no' => $order->job_no,
                'fty_po' => $order->fty_po,
                'customer' => $order->khachHang?->ten_kh ?? 'N/A',
                'ma_hh' => $order->ma_hh,
                'qty' => (float) ($order->yrd ?? 0),
                'due_date' => $order->sig_need_date?->format('d/m/Y') ?? '-',
            ]);

        $allUndeliveredLots = $this->trackingQuery($filters)
            ->with('order.khachHang')
            ->whereNotIn('cong_doan', OrderTracking::deliveredStages())
            ->whereHas('order', fn($q) => $q->where('status', '!=', 'shipped'))
            ->get()
            ->groupBy(fn($tracking) => $tracking->tracking_number ?: 'NO_LOT_' . $tracking->id)
            ->map(function (Collection $rows, string $trackingNumber) {
                $dueDates = $rows->pluck('order.sig_need_date')->filter();
                $dueDate = $dueDates->sort()->first();
                $customer = $rows->pluck('order.khachHang.ten_kh')->filter()->unique()->take(2)->implode(', ') ?: 'N/A';
                $stages = $rows->pluck('cong_doan')->filter()->unique()->take(3)->implode(', ') ?: 'Chờ sản xuất';

                return [
                    'tracking_number' => str_starts_with($trackingNumber, 'NO_LOT_') ? '-' : $trackingNumber,
                    'customer' => $customer,
                    'total_items' => $rows->count(),
                    'total_qty' => $rows->sum(fn($tracking) => (float) ($tracking->sl_don_hang ?? 0)),
                    'stage' => $stages,
                    'due_date' => $dueDate ? $dueDate->format('d/m/Y') : '-',
                    'days_left' => $dueDate ? now()->startOfDay()->diffInDays($dueDate->copy()->startOfDay(), false) : null,
                ];
            })
            ->sortBy(fn($row) => $row['days_left'] ?? 999999)
            ->values();

        $undeliveredLots = $allUndeliveredLots
            ->take(10)
            ->values();

        $latePurchaseOrders = PurchaseOrder::with('nhaCungCap')
            ->whereNotIn('trang_thai', ['received', 'cancelled'])
            ->whereDate('ngay_giao_du_kien', '<', now()->startOfDay())
            ->orderBy('ngay_giao_du_kien')
            ->limit(10)
            ->get()
            ->map(fn($po) => [
                'so_po' => $po->so_po,
                'supplier' => $po->nhaCungCap?->ten_ncc ?? 'N/A',
                'ngay_dat' => $po->ngay_dat?->format('d/m/Y') ?? '-',
                'ngay_giao_du_kien' => $po->ngay_giao_du_kien?->format('d/m/Y') ?? '-',
                'trang_thai' => $po->trang_thai,
                'days_late' => $po->ngay_giao_du_kien ? $po->ngay_giao_du_kien->startOfDay()->diffInDays(now()->startOfDay()) : null,
            ]);

        $nearDueProduction = $opsDashboard['otd']['at_risk_lots']
            ->take(10)
            ->map(fn($row) => [
                'tracking_number' => $row['tracking_number'],
                'customer' => $row['customer'],
                'stage' => $row['stage'],
                'due_date' => $row['due_date'],
                'days_left' => $row['days_left'],
                'total_items' => $row['total_items'],
            ]);
        $lotRiskRows = $opsDashboard['otd']['at_risk_lots'];
        $pendingVatLots = $this->buildPendingVatLots($filters);
        $attentionLotKeys = $lotRiskRows
            ->pluck('tracking_number')
            ->merge($allUndeliveredLots->pluck('tracking_number'))
            ->merge($pendingVatLots->pluck('tracking_number'))
            ->filter(fn($trackingNumber) => $trackingNumber && $trackingNumber !== '-')
            ->unique()
            ->values();

        $stuckStages = $opsDashboard['wip']['aging']
            ->filter(fn($row) => (int) $row['over_7_days'] > 0)
            ->take(8)
            ->values();

        return [
            'near_due_production' => $nearDueProduction,
            'lot_risk_summary' => [
                'late' => $lotRiskRows->filter(fn($row) => (int) $row['days_left'] < 0)->count(),
                'due_today' => $lotRiskRows->filter(fn($row) => (int) $row['days_left'] === 0)->count(),
                'next_7_days' => $lotRiskRows->filter(fn($row) => (int) $row['days_left'] > 0 && (int) $row['days_left'] <= 7)->count(),
                'no_due' => $allUndeliveredLots->filter(fn($row) => $row['days_left'] === null)->count(),
                'vat_pending' => $pendingVatLots->count(),
                'total' => $attentionLotKeys->count(),
            ],
            'order_status_counts' => [
                'pending' => (int) ($orderStatusCounts['pending'] ?? 0),
                'in_production' => (int) ($orderStatusCounts['in_production'] ?? 0),
                'done' => (int) ($orderStatusCounts['done'] ?? 0),
                'shipped' => (int) ($orderStatusCounts['shipped'] ?? 0),
            ],
            'delivered_orders' => $deliveredOrders,
            'undelivered_lot_count' => $allUndeliveredLots->count(),
            'undelivered_lots' => $undeliveredLots,
            'pending_vat_lots' => $pendingVatLots,
            'material_shortages' => $opsDashboard['action']['bom_material_shortages'],
            'stuck_stages' => $stuckStages,
            'late_po_count' => $opsDashboard['procurement']['late_po_count'],
            'late_purchase_orders' => $latePurchaseOrders,
            'missing_cost_data' => $opsDashboard['action']['missing_cost_data'],
        ];
    }

    private function orderQuery(array $filters)
    {
        return Order::query()
            ->when($filters['date_from'] ?? null, fn($q, $date) => $q->whereDate('created_at', '>=', $date))
            ->when($filters['date_to'] ?? null, fn($q, $date) => $q->whereDate('created_at', '<=', $date))
            ->when($filters['khach_hang_id'] ?? null, fn($q, $id) => $q->where('khach_hang_id', $id))
            ->when($filters['nhom_hang'] ?? null, fn($q, $nhomHang) => $q->where('ma_hh', 'like', $nhomHang . '%'));
    }

    private function trackingQuery(array $filters)
    {
        return OrderTracking::query()
            ->when($filters['date_from'] ?? null, fn($q, $date) => $q->whereDate('order_tracking.created_at', '>=', $date))
            ->when($filters['date_to'] ?? null, fn($q, $date) => $q->whereDate('order_tracking.created_at', '<=', $date))
            ->when(($filters['khach_hang_id'] ?? null) || ($filters['nhom_hang'] ?? null), function ($q) use ($filters) {
                $q->whereHas('order', function ($orderQ) use ($filters) {
                    $orderQ
                        ->when($filters['khach_hang_id'] ?? null, fn($sub, $id) => $sub->where('khach_hang_id', $id))
                        ->when($filters['nhom_hang'] ?? null, fn($sub, $nhomHang) => $sub->where('ma_hh', 'like', $nhomHang . '%'));
                });
            });
    }

    private function invoicedTrackingQuery(array $filters)
    {
        return OrderTracking::query()
            ->whereNotNull('invoice_issued_at')
            ->when($filters['date_from'] ?? null, fn($q, $date) => $q->whereDate('invoice_issued_at', '>=', $date))
            ->when($filters['date_to'] ?? null, fn($q, $date) => $q->whereDate('invoice_issued_at', '<=', $date))
            ->when(($filters['khach_hang_id'] ?? null) || ($filters['nhom_hang'] ?? null), function ($q) use ($filters) {
                $q->whereHas('order', function ($orderQ) use ($filters) {
                    $orderQ
                        ->when($filters['khach_hang_id'] ?? null, fn($sub, $id) => $sub->where('khach_hang_id', $id))
                        ->when($filters['nhom_hang'] ?? null, fn($sub, $nhomHang) => $sub->where('ma_hh', 'like', $nhomHang . '%'));
                });
            });
    }

    private function buildPendingVatLots(array $filters): Collection
    {
        return $this->trackingQuery($filters)
            ->with('order.khachHang')
            ->whereNotNull('tracking_number')
            ->whereNull('invoice_issued_at')
            ->get()
            ->groupBy('tracking_number')
            ->map(function (Collection $rows, string $trackingNumber) {
                $dueDate = $rows->pluck('order.sig_need_date')->filter()->sort()->first();
                $deliveredDate = $rows->pluck('ngay_xe_lay_hang')->filter()->sort()->last()
                    ?: $rows->pluck('updated_at')->filter()->sort()->last();
                $baseDate = $dueDate ?: $deliveredDate;

                return [
                    'tracking_number' => $trackingNumber,
                    'customer' => $rows->pluck('order.khachHang.ten_kh')->filter()->unique()->take(3)->implode(', ') ?: 'N/A',
                    'due_date' => $dueDate ? Carbon::parse($dueDate)->format('d/m/Y') : '-',
                    'delivered_date' => $deliveredDate ? Carbon::parse($deliveredDate)->format('d/m/Y') : '-',
                    'days_left' => $baseDate ? (int) now()->startOfDay()->diffInDays(Carbon::parse($baseDate)->startOfDay(), false) : 0,
                    'total_items' => $rows->count(),
                    'total_qty' => $rows->sum(fn($tracking) => (float) ($tracking->sl_don_hang ?? 0)),
                    'stage' => 'Chưa xuất VAT',
                ];
            })
            ->sortBy('days_left')
            ->values();
    }

    private function productionQuery(array $filters)
    {
        return ProductionReport::query()
            ->when($filters['date_from'] ?? null, fn($q, $date) => $q->whereDate('ngay_sx', '>=', $date))
            ->when($filters['date_to'] ?? null, fn($q, $date) => $q->whereDate('ngay_sx', '<=', $date));
    }

    private function buildActionDashboardBlock(array $filters): array
    {
        $readyToShipLots = $this->buildReadyToShipLots($filters);
        $bomMaterialShortages = $this->buildBomMaterialShortages($filters);
        $missingCostData = $this->buildMissingCostData($filters);

        return [
            'ready_to_ship_lots' => $readyToShipLots,
            'bom_material_shortages' => $bomMaterialShortages,
            'missing_cost_data' => $missingCostData,
            'alerts' => [
                'ready_to_ship' => $readyToShipLots->count(),
                'material_shortages' => $bomMaterialShortages->count(),
                'missing_cost_data' => $missingCostData->count(),
            ],
        ];
    }

    private function buildReadyToShipLots(array $filters): Collection
    {
        $openTrackings = $this->trackingQuery($filters)
            ->with('order.khachHang')
            ->whereNotNull('tracking_number')
            ->whereNotIn('cong_doan', OrderTracking::deliveredStages())
            ->whereHas('order', fn($q) => $q->where('status', '!=', 'shipped'))
            ->get();

        $maHhList = $openTrackings->pluck('order.ma_hh')->filter()->unique()->values();
        $stockNhap = WarehouseTransaction::nhapKho()
            ->whereIn('ma_hh', $maHhList)
            ->selectRaw('ma_hh, sum(so_luong) as total')
            ->groupBy('ma_hh')
            ->pluck('total', 'ma_hh');
        $stockXuat = WarehouseTransaction::xuatKho()
            ->whereIn('ma_hh', $maHhList)
            ->selectRaw('ma_hh, sum(so_luong) as total')
            ->groupBy('ma_hh')
            ->pluck('total', 'ma_hh');

        return $openTrackings
            ->groupBy('tracking_number')
            ->map(function (Collection $rows, string $lotNo) use ($stockNhap, $stockXuat) {
                $demandByItem = $rows->groupBy(fn($t) => $t->order?->ma_hh ?: $t->size)
                    ->map(fn(Collection $items) => $items->sum(fn($t) => (float) ($t->sl_don_hang ?? $t->order?->yrd ?? 0)));

                if ($demandByItem->isEmpty()) {
                    return null;
                }

                $shortage = $demandByItem->map(function ($required, $maHh) use ($stockNhap, $stockXuat) {
                    $onHand = (float) (($stockNhap[$maHh] ?? 0) - ($stockXuat[$maHh] ?? 0));
                    return max(0, (float) $required - $onHand);
                })->sum();

                if ($shortage > 0) {
                    return null;
                }

                $first = $rows->sortBy(fn($t) => optional($t->order)->sig_need_date?->format('Y-m-d') ?? '9999-12-31')->first();

                return [
                    'tracking_number' => $lotNo,
                    'customer' => $rows->pluck('order.khachHang.ten_kh')->filter()->unique()->take(2)->implode(', ') ?: 'N/A',
                    'due_date' => optional($first->order)->sig_need_date?->format('d/m/Y') ?: '-',
                    'total_items' => $rows->count(),
                    'total_qty' => $demandByItem->sum(),
                ];
            })
            ->filter()
            ->sortBy('due_date')
            ->take(10)
            ->values();
    }

    private function buildBomMaterialShortages(array $filters): Collection
    {
        $activeItems = LenhSanXuatItem::where('da_len_lenh', true)
            ->when($filters['nhom_hang'] ?? null, fn($q, $nhomHang) => $q->where('ma_hh', 'like', $nhomHang . '%'))
            ->get();
        if ($activeItems->isEmpty()) {
            return collect();
        }

        $products = DanhMucHangHoa::whereIn('ma_hh', $activeItems->pluck('ma_hh')->filter()->unique())
            ->get()
            ->keyBy('ma_hh');
        $bomByProduct = DinhMucNvl::with('nguyenLieu')
            ->whereIn('san_pham_id', $products->pluck('id'))
            ->get()
            ->groupBy('san_pham_id');

        $requiredByMaterial = collect();
        foreach ($activeItems as $item) {
            $product = $products[$item->ma_hh] ?? null;
            if (!$product) {
                continue;
            }

            foreach (($bomByProduct[$product->id] ?? collect()) as $bom) {
                $material = $bom->nguyenLieu;
                if (!$material?->ma_hh) {
                    continue;
                }

                $required = (float) $item->sl_can_sx * (float) $bom->so_luong * (1 + ((float) $bom->ti_le_hao_hut / 100));
                $current = $requiredByMaterial[$material->ma_hh] ?? [
                    'ma_hh' => $material->ma_hh,
                    'ten_hh' => $material->ten_hh,
                    'required' => 0,
                ];
                $current['required'] += $required;
                $requiredByMaterial[$material->ma_hh] = $current;
            }
        }

        $materialCodes = $requiredByMaterial->keys();
        $stockNhap = WarehouseTransaction::nhapKho()
            ->whereIn('ma_hh', $materialCodes)
            ->selectRaw('ma_hh, sum(so_luong) as total')
            ->groupBy('ma_hh')
            ->pluck('total', 'ma_hh');
        $stockXuat = WarehouseTransaction::xuatKho()
            ->whereIn('ma_hh', $materialCodes)
            ->selectRaw('ma_hh, sum(so_luong) as total')
            ->groupBy('ma_hh')
            ->pluck('total', 'ma_hh');

        return $requiredByMaterial
            ->map(function ($row, $maHh) use ($stockNhap, $stockXuat) {
                $onHand = (float) (($stockNhap[$maHh] ?? 0) - ($stockXuat[$maHh] ?? 0));
                $row['on_hand'] = $onHand;
                $row['shortage'] = max(0, (float) $row['required'] - $onHand);
                return $row;
            })
            ->filter(fn($row) => $row['shortage'] > 0)
            ->sortByDesc('shortage')
            ->take(10)
            ->values();
    }

    private function buildMissingCostData(array $filters): Collection
    {
        $activeMaHh = $this->orderQuery($filters)
            ->where('status', '!=', 'shipped')
            ->whereNotNull('ma_hh')
            ->pluck('ma_hh')
            ->merge($this->trackingQuery($filters)->whereNotIn('cong_doan', OrderTracking::deliveredStages())->pluck('size'))
            ->filter()
            ->unique()
            ->values();

        $products = DanhMucHangHoa::whereIn('ma_hh', $activeMaHh)->get();
        $bomCounts = DinhMucNvl::whereIn('san_pham_id', $products->pluck('id'))
            ->selectRaw('san_pham_id, count(*) as total')
            ->groupBy('san_pham_id')
            ->pluck('total', 'san_pham_id');

        return $products
            ->map(function ($product) use ($bomCounts) {
                $issues = [];
                if (($bomCounts[$product->id] ?? 0) <= 0) {
                    $issues[] = 'Thiếu BOM';
                }
                if ((float) ($product->gia_nvl ?? 0) <= 0 && (float) ($product->don_gia ?? 0) <= 0) {
                    $issues[] = 'Thiếu giá';
                }

                if (empty($issues)) {
                    return null;
                }

                return [
                    'ma_hh' => $product->ma_hh,
                    'ten_hh' => $product->ten_hh,
                    'issues' => implode(', ', $issues),
                ];
            })
            ->filter()
            ->take(10)
            ->values();
    }

    private function buildOtdDeliveryBlock(array $filters): array
    {
        $today = now()->startOfDay();
        $to = now()->addDays(7)->endOfDay();

        $otdByMonth = $this->trackingQuery($filters)
            ->with('order.khachHang')
            ->whereIn('cong_doan', OrderTracking::deliveredStages())
            ->whereNotNull('ngay_xe_lay_hang')
            ->whereHas('order', fn($q) => $q->whereNotNull('sig_need_date'))
            ->whereDate('updated_at', '>=', now()->subMonths(6)->startOfMonth())
            ->get()
            ->groupBy(fn($t) => Carbon::parse($t->ngay_xe_lay_hang)->format('Y-m'))
            ->map(function (Collection $rows) {
                $total = $rows->count();
                $onTime = $rows->filter(function ($t) {
                    $actual = Carbon::parse($t->ngay_xe_lay_hang)->startOfDay();
                    $due = optional($t->order)->sig_need_date ? Carbon::parse($t->order->sig_need_date)->startOfDay() : null;
                    return $due && $actual->lte($due);
                })->count();
                return [
                    'total' => $total,
                    'on_time' => $onTime,
                    'rate' => $this->percentage($onTime, $total, 1),
                ];
            });

        $otdByCustomer = $this->trackingQuery($filters)
            ->with('order.khachHang')
            ->whereIn('cong_doan', OrderTracking::deliveredStages())
            ->whereNotNull('ngay_xe_lay_hang')
            ->whereHas('order', fn($q) => $q->whereNotNull('sig_need_date'))
            ->whereDate('updated_at', '>=', now()->subMonths(3)->startOfDay())
            ->get()
            ->groupBy(fn($t) => $t->order?->khachHang?->ten_kh ?? 'Khách hàng chưa gán')
            ->map(function (Collection $rows, string $customer) {
                $total = $rows->count();
                $onTime = $rows->filter(function ($t) {
                    $actual = Carbon::parse($t->ngay_xe_lay_hang)->startOfDay();
                    $due = optional($t->order)->sig_need_date ? Carbon::parse($t->order->sig_need_date)->startOfDay() : null;
                    return $due && $actual->lte($due);
                })->count();
                return [
                    'customer' => $customer,
                    'total' => $total,
                    'on_time' => $onTime,
                    'rate' => $this->percentage($onTime, $total, 1),
                ];
            })
            ->sortByDesc('total')
            ->take(8)
            ->values();

        $atRiskLots = $this->trackingQuery($filters)
            ->with('order.khachHang')
            ->whereNotIn('cong_doan', OrderTracking::deliveredStages())
            ->whereHas('order', fn($q) => $q->whereNotNull('sig_need_date')->whereDate('sig_need_date', '<=', $to))
            ->orderBy(Order::select('sig_need_date')->whereColumn('orders.id', 'order_tracking.order_id')->limit(1))
            ->get()
            ->groupBy(fn($t) => $t->tracking_number ?: 'NO-LOT-' . $t->id)
            ->map(function (Collection $rows) use ($today) {
                $first = $rows
                    ->sortBy(fn($t) => optional($t->order)->sig_need_date?->format('Y-m-d') ?? '9999-12-31')
                    ->first();
                $due = optional($first->order)->sig_need_date ? Carbon::parse($first->order->sig_need_date)->startOfDay() : $today;

                return [
                    'tracking_number' => $first->tracking_number ?? '-',
                    'customer' => $rows->pluck('order.khachHang.ten_kh')->filter()->unique()->take(3)->implode(', ') ?: 'N/A',
                    'stage' => $rows->pluck('cong_doan')->filter()->unique()->take(3)->implode(', ') ?: 'Chờ sản xuất',
                    'due_date' => $due->format('d/m/Y'),
                    'days_left' => (int) $today->diffInDays($due, false),
                    'total_items' => $rows->count(),
                ];
            })
            ->sortBy('days_left')
            ->take(20)
            ->values();

        $pickupDueLots = $this->trackingQuery($filters)
            ->with('order.khachHang')
            ->whereNotIn('cong_doan', OrderTracking::deliveredStages())
            ->whereNotNull('ngay_xe_lay_hang')
            ->whereDate('ngay_xe_lay_hang', '<=', $to)
            ->get()
            ->groupBy(fn($t) => $t->tracking_number ?: 'NO-LOT-' . $t->id)
            ->map(function (Collection $rows) use ($today) {
                $first = $rows
                    ->sortBy(fn($t) => optional($t->ngay_xe_lay_hang)?->format('Y-m-d') ?? '9999-12-31')
                    ->first();
                $pickupDate = $first->ngay_xe_lay_hang
                    ? Carbon::parse($first->ngay_xe_lay_hang)->startOfDay()
                    : $today;

                return [
                    'tracking_number' => $first->tracking_number ?? '-',
                    'customer' => $rows->pluck('order.khachHang.ten_kh')->filter()->unique()->take(3)->implode(', ') ?: 'N/A',
                    'stage' => $rows->pluck('cong_doan')->filter()->unique()->take(3)->implode(', ') ?: 'Chờ sản xuất',
                    'pickup_date' => $pickupDate->format('d/m/Y'),
                    'days_left' => (int) $today->diffInDays($pickupDate, false),
                    'total_items' => $rows->count(),
                ];
            })
            ->sortBy('days_left')
            ->take(20)
            ->values();

        return [
            'monthly' => $otdByMonth,
            'by_customer' => $otdByCustomer,
            'at_risk_lots' => $atRiskLots,
            'pickup_due_lots' => $pickupDueLots,
        ];
    }

    private function buildWipBlock(array $filters): array
    {
        $wipStages = $this->trackingQuery($filters)
            ->whereNotIn('cong_doan', OrderTracking::deliveredStages())
            ->selectRaw('COALESCE(cong_doan, "Chờ sản xuất") as stage, count(*) as total')
            ->groupBy('stage')
            ->orderByDesc('total')
            ->get()
            ->map(fn($r) => ['stage' => $r->stage, 'total' => (int) $r->total]);

        $aging = $this->trackingQuery($filters)
            ->whereNotIn('cong_doan', OrderTracking::deliveredStages())
            ->get()
            ->groupBy(fn($t) => $t->cong_doan ?: 'Chờ sản xuất')
            ->map(function (Collection $rows, string $stage) {
                $ages = $rows->map(fn($t) => (float) Carbon::parse($t->created_at)->diffInDays(now()));
                $avg = $ages->count() ? round($ages->avg(), 1) : 0;
                $over7 = $ages->filter(fn($d) => $d > 7)->count();
                return [
                    'stage' => $stage,
                    'avg_days' => $avg,
                    'over_7_days' => $over7,
                    'count' => $rows->count(),
                ];
            })
            ->sortByDesc('avg_days')
            ->values();

        return [
            'stages' => $wipStages,
            'aging' => $aging,
        ];
    }

    private function buildProductivityQualityBlock(array $filters): array
    {
        $outputByCa = $this->productionQuery($filters)
            ->whereNotNull('ca')
            ->selectRaw('ca, sum(sl_dat) as output, sum(sl_hu) as defect')
            ->groupBy('ca')
            ->orderBy('ca')
            ->get()
            ->map(fn($r) => [
                'ca' => $r->ca,
                'output' => (float) $r->output,
                'defect_rate' => $this->percentage((float) $r->defect, ((float) $r->output + (float) $r->defect), 2),
            ]);

        $outputByEmployee = $this->productionQuery($filters)
            ->whereNotNull('ma_nv')
            ->where('ma_nv', '!=', '')
            ->selectRaw('ma_nv, sum(sl_dat) as output, sum(sl_hu) as defect')
            ->groupBy('ma_nv')
            ->orderByDesc('output')
            ->limit(10)
            ->get()
            ->map(fn($r) => [
                'ma_nv' => $r->ma_nv,
                'output' => (float) $r->output,
                'defect_rate' => $this->percentage((float) $r->defect, ((float) $r->output + (float) $r->defect), 2),
            ]);

        $totals = $this->productionQuery($filters)
            ->selectRaw('sum(sl_dat) as output, sum(sl_hu) as defect')
            ->first();

        return [
            'output_by_ca' => $outputByCa,
            'output_by_employee' => $outputByEmployee,
            'defect_rate_30d' => $this->percentage((float) ($totals->defect ?? 0), (float) (($totals->output ?? 0) + ($totals->defect ?? 0)), 2),
        ];
    }

    private function buildInventoryMrpBlock(array $filters): array
    {
        $stockNhap = WarehouseTransaction::nhapKho()
            ->selectRaw('ma_hh, sum(so_luong) as total')
            ->groupBy('ma_hh')
            ->pluck('total', 'ma_hh');
        $stockXuat = WarehouseTransaction::xuatKho()
            ->selectRaw('ma_hh, sum(so_luong) as total')
            ->groupBy('ma_hh')
            ->pluck('total', 'ma_hh');

        $recentUsage = WarehouseTransaction::xuatKho()
            ->whereDate('ngay', '>=', now()->subDays(30)->startOfDay())
            ->selectRaw('ma_hh, sum(so_luong) as total')
            ->groupBy('ma_hh')
            ->pluck('total', 'ma_hh');

        $catalog = \App\Models\DanhMucHangHoa::select('ma_hh', 'ten_hh', 'ton_toi_thieu')
            ->whereNotNull('ma_hh')
            ->get();

        $stockRows = $catalog->map(function ($h) use ($stockNhap, $stockXuat, $recentUsage) {
            $onHand = (float) (($stockNhap[$h->ma_hh] ?? 0) - ($stockXuat[$h->ma_hh] ?? 0));
            $min = (float) ($h->ton_toi_thieu ?? 0);
            $dailyUsage = ((float) ($recentUsage[$h->ma_hh] ?? 0)) / 30;
            $coverageDays = $dailyUsage > 0 ? round($onHand / $dailyUsage, 1) : null;

            return [
                'ma_hh' => $h->ma_hh,
                'ten_hh' => $h->ten_hh,
                'on_hand' => $onHand,
                'min_stock' => $min,
                'coverage_days' => $coverageDays,
                'is_negative' => $onHand < 0,
                'is_below_min' => $onHand >= 0 && $onHand < $min,
            ];
        });

        $negativeStocks = $stockRows->where('is_negative', true)->values();
        $belowMinStocks = $stockRows->where('is_below_min', true)->sortBy(fn($r) => $r['on_hand'] - $r['min_stock'])->values();

        $requiredForPlan = LenhSanXuatItem::where('da_len_lenh', true)
            ->when($filters['nhom_hang'] ?? null, fn($q, $nhomHang) => $q->where('ma_hh', 'like', $nhomHang . '%'))
            ->selectRaw('ma_hh, sum(sl_can_sx) as required')
            ->groupBy('ma_hh')
            ->pluck('required', 'ma_hh');

        $topShortages = $requiredForPlan->map(function ($required, $maHh) use ($stockRows) {
            $stock = $stockRows->firstWhere('ma_hh', $maHh);
            $onHand = (float) ($stock['on_hand'] ?? 0);
            $shortage = (float) $required - $onHand;
            return [
                'ma_hh' => $maHh,
                'required' => (float) $required,
                'on_hand' => $onHand,
                'shortage' => $shortage,
            ];
        })
            ->filter(fn($r) => $r['shortage'] > 0)
            ->sortByDesc('shortage')
            ->take(15)
            ->values();

        return [
            'negative_stocks' => $negativeStocks,
            'below_min_stocks' => $belowMinStocks,
            'top_shortages' => $topShortages,
        ];
    }

    private function buildProcurementBlock(array $filters): array
    {
        $today = now()->startOfDay();

        $openPoCount = PurchaseOrder::whereNotIn('trang_thai', ['received', 'cancelled'])
            ->when($filters['date_from'] ?? null, fn($q, $date) => $q->whereDate('ngay_dat', '>=', $date))
            ->when($filters['date_to'] ?? null, fn($q, $date) => $q->whereDate('ngay_dat', '<=', $date))
            ->count();
        $latePoCount = PurchaseOrder::whereNotIn('trang_thai', ['received', 'cancelled'])
            ->when($filters['date_from'] ?? null, fn($q, $date) => $q->whereDate('ngay_dat', '>=', $date))
            ->when($filters['date_to'] ?? null, fn($q, $date) => $q->whereDate('ngay_dat', '<=', $date))
            ->whereDate('ngay_giao_du_kien', '<', $today)
            ->count();

        $avgLeadTime = PurchaseOrder::whereNotNull('ngay_nhan_thuc_te')
            ->selectRaw('AVG(DATEDIFF(ngay_nhan_thuc_te, ngay_dat)) as avg_days')
            ->value('avg_days');

        $supplierOtd = PurchaseOrder::with('nhaCungCap')
            ->when($filters['date_from'] ?? null, fn($q, $date) => $q->whereDate('ngay_dat', '>=', $date))
            ->when($filters['date_to'] ?? null, fn($q, $date) => $q->whereDate('ngay_dat', '<=', $date))
            ->where('trang_thai', 'received')
            ->whereNotNull('ngay_nhan_thuc_te')
            ->whereNotNull('ngay_giao_du_kien')
            ->get()
            ->groupBy(fn($po) => $po->nhaCungCap?->ten_ncc ?? 'NCC chưa gán')
            ->map(function (Collection $rows, string $supplier) {
                $total = $rows->count();
                $onTime = $rows->filter(fn($po) => Carbon::parse($po->ngay_nhan_thuc_te)->startOfDay()->lte(Carbon::parse($po->ngay_giao_du_kien)->startOfDay()))->count();
                return [
                    'supplier' => $supplier,
                    'total' => $total,
                    'on_time' => $onTime,
                    'rate' => $this->percentage($onTime, $total, 1),
                ];
            })
            ->sortByDesc('total')
            ->take(8)
            ->values();

        $priceTrend = PurchaseOrderItem::query()
            ->join('purchase_orders', 'purchase_orders.id', '=', 'purchase_order_items.purchase_order_id')
            ->whereDate('purchase_orders.ngay_dat', '>=', now()->subMonths(6)->startOfMonth())
            ->selectRaw('DATE_FORMAT(purchase_orders.ngay_dat, "%Y-%m") as ym, AVG(purchase_order_items.don_gia) as avg_price')
            ->groupBy('ym')
            ->orderBy('ym')
            ->get()
            ->map(fn($r) => ['month' => $r->ym, 'avg_price' => round((float) $r->avg_price, 4)]);

        return [
            'open_po_count' => $openPoCount,
            'late_po_count' => $latePoCount,
            'avg_lead_time_days' => $avgLeadTime ? round((float) $avgLeadTime, 1) : null,
            'supplier_otd' => $supplierOtd,
            'price_trend' => $priceTrend,
        ];
    }

    private function buildFinanceBlock(array $filters): array
    {
        $orders = $this->orderQuery($filters)->with(['khachHang', 'tracking'])->get();
        $materialCostMap = \App\Models\DanhMucHangHoa::pluck('gia_nvl', 'ma_hh');

        $orderMargins = $orders->map(function ($o) use ($materialCostMap) {
            $qty = (float) ($o->yrd ?? 0);
            $price = (float) ($o->price_usd ?? $o->price_usd_auto ?? 0);
            $revenue = $qty * $price;
            $trackingInvoiceRevenue = $o->tracking
                ->whereNotNull('invoice_issued_at')
                ->sum(fn($tracking) => (float) ($tracking->sl_don_hang ?? 0) * $price);
            $invoicedRevenue = $trackingInvoiceRevenue > 0
                ? $trackingInvoiceRevenue
                : ($o->status === 'shipped' ? $revenue : 0);
            $unitCost = (float) ($materialCostMap[$o->ma_hh] ?? 0);
            $cost = $qty * $unitCost;
            $margin = $revenue - $cost;
            $marginRate = $revenue > 0 ? round(($margin / $revenue) * 100, 2) : null;

            return [
                'customer_id' => $o->khach_hang_id,
                'job_no' => $o->job_no,
                'customer' => $o->khachHang?->ten_kh ?? 'Khách hàng chưa gán',
                'ma_hh' => $o->ma_hh ?: 'N/A',
                'ten_hh' => $o->ten_hh ?: ($o->ma_hh ?: 'N/A'),
                'qty' => $qty,
                'created_date' => $o->created_at?->format('Y-m-d'),
                'revenue' => $revenue,
                'invoiced_revenue' => $invoicedRevenue,
                'cost' => $cost,
                'margin' => $margin,
                'margin_rate' => $marginRate,
            ];
        });

        $topOrders = $orderMargins
            ->sortByDesc('margin')
            ->take(10)
            ->values();

        $byCustomer = $orderMargins
            ->groupBy(fn($row) => $row['customer_id'] ?: 0)
            ->map(function (Collection $rows) {
                $revenue = $rows->sum('revenue');
                $cost = $rows->sum('cost');
                $margin = $revenue - $cost;
                $invoicedRevenue = (float) $rows->sum('invoiced_revenue');

                return [
                    'customer' => $rows->first()['customer'],
                    'revenue' => $revenue,
                    'invoiced_revenue' => $invoicedRevenue,
                    'uninvoiced_revenue' => max(0, $revenue - $invoicedRevenue),
                    'invoice_rate' => $revenue > 0 ? round(($invoicedRevenue / $revenue) * 100, 1) : null,
                    'cost' => $cost,
                    'margin' => $margin,
                    'margin_rate' => $revenue > 0 ? round(($margin / $revenue) * 100, 2) : null,
                ];
            })
            ->sortByDesc('revenue')
            ->take(10)
            ->values();

        $byProduct = $orderMargins
            ->groupBy('ma_hh')
            ->map(function (Collection $rows, string $maHh) {
                $revenue = $rows->sum('revenue');
                $cost = $rows->sum('cost');
                $margin = $revenue - $cost;
                $invoicedRevenue = (float) $rows->sum('invoiced_revenue');

                return [
                    'ma_hh' => $maHh,
                    'ten_hh' => $rows->pluck('ten_hh')->filter()->first() ?: $maHh,
                    'qty' => $rows->sum('qty'),
                    'order_count' => $rows->count(),
                    'revenue' => $revenue,
                    'invoiced_revenue' => $invoicedRevenue,
                    'uninvoiced_revenue' => max(0, $revenue - $invoicedRevenue),
                    'invoice_rate' => $revenue > 0 ? round(($invoicedRevenue / $revenue) * 100, 1) : null,
                    'margin' => $margin,
                    'margin_rate' => $revenue > 0 ? round(($margin / $revenue) * 100, 2) : null,
                ];
            })
            ->sortByDesc('revenue')
            ->take(10)
            ->values();

        $previousRows = $this->buildFinanceRowsFor($this->previousPeriodFilters($filters));
        $orderRevenue = (float) $orderMargins->sum('revenue');
        $invoicedRevenue = (float) $orderMargins->sum('invoiced_revenue');
        $previousOrderRevenue = (float) $previousRows->sum('revenue');
        $previousInvoicedRevenue = (float) $previousRows->sum('invoiced_revenue');
        $revenueTimeseries = [
            'day' => $this->buildRevenueTimeseries($filters, 'day'),
            'month' => $this->buildRevenueTimeseries($filters, 'month'),
            'year' => $this->buildRevenueTimeseries($filters, 'year'),
        ];

        return [
            'top_orders' => $topOrders,
            'by_customer' => $byCustomer,
            'by_product' => $byProduct,
            'charts' => [
                'revenue_trend' => $revenueTimeseries['day'],
                'revenue_timeseries' => $revenueTimeseries,
                'product_revenue' => [
                    'labels' => $byProduct->take(6)->pluck('ma_hh')->all(),
                    'data' => $byProduct->take(6)->map(fn($row) => round((float) $row['revenue'], 2))->all(),
                ],
                'customer_revenue' => [
                    'labels' => $byCustomer->take(6)->pluck('customer')->map(fn($name) => \Illuminate\Support\Str::limit($name, 18))->all(),
                    'order_revenue' => $byCustomer->take(6)->map(fn($row) => round((float) $row['revenue'], 2))->all(),
                    'invoiced_revenue' => $byCustomer->take(6)->map(fn($row) => round((float) $row['invoiced_revenue'], 2))->all(),
                    'uninvoiced_revenue' => $byCustomer->take(6)->map(fn($row) => round((float) $row['uninvoiced_revenue'], 2))->all(),
                ],
            ],
            'summary' => [
                'order_revenue' => $orderRevenue,
                'invoiced_revenue' => $invoicedRevenue,
                'uninvoiced_revenue' => max(0, $orderRevenue - $invoicedRevenue),
                'invoice_rate' => $orderRevenue > 0
                    ? round(($invoicedRevenue / $orderRevenue) * 100, 1)
                    : null,
                'trend' => [
                    'order_revenue_pct' => $this->trendPercentage($orderRevenue, $previousOrderRevenue),
                    'invoiced_revenue_pct' => $this->trendPercentage($invoicedRevenue, $previousInvoicedRevenue),
                    'previous_order_revenue' => $previousOrderRevenue,
                    'previous_invoiced_revenue' => $previousInvoicedRevenue,
                ],
            ],
        ];
    }

    private function previousPeriodFilters(array $filters): array
    {
        $from = Carbon::parse($filters['date_from']);
        $to = Carbon::parse($filters['date_to']);
        $days = $from->diffInDays($to) + 1;
        $previousTo = $from->copy()->subDay();
        $previousFrom = $previousTo->copy()->subDays($days - 1);

        return array_merge($filters, [
            'date_from' => $previousFrom->toDateString(),
            'date_to' => $previousTo->toDateString(),
        ]);
    }

    private function trendPercentage(float $current, float $previous): ?float
    {
        if ($previous <= 0) {
            return $current > 0 ? 100.0 : null;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }

    private function buildRevenueTimeseries(array $filters, string $granularity): array
    {
        $end = Carbon::parse($filters['date_to']);

        if ($granularity === 'month') {
            $start = $end->copy()->startOfMonth()->subMonths(11);
            $end = $end->copy()->endOfMonth();
            $periodFilters = array_merge($filters, [
                'date_from' => $start->toDateString(),
                'date_to' => $end->toDateString(),
            ]);
            $buckets = collect();
            for ($cursor = $start->copy(); $cursor->lte($end); $cursor->addMonth()) {
                $buckets->push([
                    'key' => $cursor->format('Y-m'),
                    'label' => $cursor->format('m/Y'),
                ]);
            }
            $orderRows = $this->buildFinanceRowsFor($periodFilters)
                ->groupBy(fn($row) => Carbon::parse($row['created_date'])->format('Y-m'));
            $invoicedRows = $this->buildInvoicedRevenueRowsFor($periodFilters)
                ->groupBy(fn($row) => Carbon::parse($row['invoice_date'])->format('Y-m'));
        } elseif ($granularity === 'year') {
            $start = $end->copy()->startOfYear()->subYears(4);
            $end = $end->copy()->endOfYear();
            $periodFilters = array_merge($filters, [
                'date_from' => $start->toDateString(),
                'date_to' => $end->toDateString(),
            ]);
            $buckets = collect();
            for ($cursor = $start->copy(); $cursor->lte($end); $cursor->addYear()) {
                $buckets->push([
                    'key' => $cursor->format('Y'),
                    'label' => $cursor->format('Y'),
                ]);
            }
            $orderRows = $this->buildFinanceRowsFor($periodFilters)
                ->groupBy(fn($row) => Carbon::parse($row['created_date'])->format('Y'));
            $invoicedRows = $this->buildInvoicedRevenueRowsFor($periodFilters)
                ->groupBy(fn($row) => Carbon::parse($row['invoice_date'])->format('Y'));
        } else {
            $start = Carbon::parse($filters['date_from']);
            $periodFilters = $filters;
            $buckets = collect();
            for ($cursor = $start->copy(); $cursor->lte($end); $cursor->addDay()) {
                $buckets->push([
                    'key' => $cursor->format('Y-m-d'),
                    'label' => $cursor->format('d/m'),
                ]);
            }
            $orderRows = $this->buildFinanceRowsFor($periodFilters)->groupBy('created_date');
            $invoicedRows = $this->buildInvoicedRevenueRowsFor($periodFilters)->groupBy('invoice_date');
        }

        $orderRevenue = $buckets
            ->map(fn($bucket) => round((float) ($orderRows[$bucket['key']] ?? collect())->sum('revenue'), 2))
            ->values();
        $invoicedRevenue = $buckets
            ->map(fn($bucket) => round((float) ($invoicedRows[$bucket['key']] ?? collect())->sum('invoiced_revenue'), 2))
            ->values();

        $change = $invoicedRevenue->map(function ($value, int $index) use ($invoicedRevenue) {
            if ($index === 0) {
                return [
                    'amount' => null,
                    'pct' => null,
                ];
            }

            $previous = (float) ($invoicedRevenue[$index - 1] ?? 0);
            return [
                'amount' => round((float) $value - $previous, 2),
                'pct' => $this->trendPercentage((float) $value, $previous),
            ];
        });

        return [
            'labels' => $buckets->pluck('label')->all(),
            'order_revenue' => $orderRevenue->all(),
            'invoiced_revenue' => $invoicedRevenue->all(),
            'change_amount' => $change->pluck('amount')->all(),
            'change_pct' => $change->pluck('pct')->all(),
        ];
    }

    private function buildFinanceRowsFor(array $filters): Collection
    {
        $materialCostMap = \App\Models\DanhMucHangHoa::pluck('gia_nvl', 'ma_hh');

        return $this->orderQuery($filters)
            ->with(['khachHang', 'tracking'])
            ->get()
            ->map(function ($o) use ($materialCostMap) {
                $qty = (float) ($o->yrd ?? 0);
                $price = (float) ($o->price_usd ?? $o->price_usd_auto ?? 0);
                $revenue = $qty * $price;
                $trackingInvoiceRevenue = $o->tracking
                    ->whereNotNull('invoice_issued_at')
                    ->sum(fn($tracking) => (float) ($tracking->sl_don_hang ?? 0) * $price);
                $invoicedRevenue = $trackingInvoiceRevenue > 0
                    ? $trackingInvoiceRevenue
                    : ($o->status === 'shipped' ? $revenue : 0);
                $cost = $qty * (float) ($materialCostMap[$o->ma_hh] ?? 0);

                return [
                    'customer_id' => $o->khach_hang_id,
                    'customer' => $o->khachHang?->ten_kh ?? 'Khách hàng chưa gán',
                    'ma_hh' => $o->ma_hh ?: 'N/A',
                    'ten_hh' => $o->ten_hh ?: ($o->ma_hh ?: 'N/A'),
                    'qty' => $qty,
                    'created_date' => $o->created_at?->format('Y-m-d'),
                    'revenue' => $revenue,
                    'invoiced_revenue' => $invoicedRevenue,
                    'cost' => $cost,
                    'margin' => $revenue - $cost,
                ];
            });
    }

    // Các số KPI chính hiển thị ở card đầu trang.
    private function buildInvoicedRevenueRowsFor(array $filters): Collection
    {
        return OrderTracking::query()
            ->with('order')
            ->whereNotNull('invoice_issued_at')
            ->when($filters['date_from'] ?? null, fn($q, $date) => $q->whereDate('invoice_issued_at', '>=', $date))
            ->when($filters['date_to'] ?? null, fn($q, $date) => $q->whereDate('invoice_issued_at', '<=', $date))
            ->when(($filters['khach_hang_id'] ?? null) || ($filters['nhom_hang'] ?? null), function ($q) use ($filters) {
                $q->whereHas('order', function ($orderQ) use ($filters) {
                    $orderQ
                        ->when($filters['khach_hang_id'] ?? null, fn($sub, $id) => $sub->where('khach_hang_id', $id))
                        ->when($filters['nhom_hang'] ?? null, fn($sub, $nhomHang) => $sub->where('ma_hh', 'like', $nhomHang . '%'));
                });
            })
            ->get()
            ->map(function (OrderTracking $tracking) {
                $order = $tracking->order;
                $price = (float) ($order?->price_usd ?? $order?->price_usd_auto ?? 0);
                $qty = (float) ($tracking->sl_don_hang ?? $order?->yrd ?? 0);

                return [
                    'invoice_date' => $tracking->invoice_issued_at?->format('Y-m-d'),
                    'invoiced_revenue' => $qty * $price,
                ];
            })
            ->filter(fn($row) => $row['invoice_date'] !== null)
            ->values();
    }

    private function buildStats(array $filters): array
    {
        $totalOrders = $this->orderQuery($filters)->count();
        $pendingOrders = $this->orderQuery($filters)->whereNotIn('status', ['shipped', 'done'])->count();
        $pctPendingOrders = $this->percentage($pendingOrders, $totalOrders, 1);

        $totalTrackings = $this->trackingQuery($filters)->count();
        $pendingTrackings = $this->trackingQuery($filters)->whereNotIn('cong_doan', array_merge(OrderTracking::warehouseDoneStages(), OrderTracking::deliveredStages()))->count();
        $pctPendingTrackings = $this->percentage($pendingTrackings, $totalTrackings, 1);

        $totalQtyRequired = (float) ($this->orderQuery($filters)->sum('yrd') ?? 0);
        $totalQtyProduced = (float) (WarehouseTransaction::nhapKho()->sum('so_luong') ?? 0);
        $unproducedQty = max(0, $totalQtyRequired - $totalQtyProduced);
        $pctUnproduced = $this->percentage($unproducedQty, $totalQtyRequired, 1);

        $totalSlDat = (float) ($this->productionQuery($filters)->sum('sl_dat') ?? 0);
        $totalSlHu = (float) ($this->productionQuery($filters)->sum('sl_hu') ?? 0);
        $lossRate = $this->percentage($totalSlHu, $totalSlDat + $totalSlHu, 2);

        $exchangeRate = (float) (Setting::where('key', 'usd_to_vnd')->value('value') ?? 25400);
        $totalRevenueUsd = (float) ($this->orderQuery($filters)->selectRaw('SUM(yrd * COALESCE(price_usd, price_usd_auto, 0)) as total')->value('total') ?? 0);
        $totalRevenueVnd = $totalRevenueUsd * $exchangeRate;
        $trackingInvoiceRevenueVnd = (float) (OrderTracking::query()
            ->join('orders', 'order_tracking.order_id', '=', 'orders.id')
            ->whereNotNull('order_tracking.invoice_issued_at')
            ->when($filters['date_from'] ?? null, fn($q, $date) => $q->whereDate('orders.created_at', '>=', $date))
            ->when($filters['date_to'] ?? null, fn($q, $date) => $q->whereDate('orders.created_at', '<=', $date))
            ->when($filters['khach_hang_id'] ?? null, fn($q, $id) => $q->where('orders.khach_hang_id', $id))
            ->when($filters['nhom_hang'] ?? null, fn($q, $nhomHang) => $q->where('orders.ma_hh', 'like', $nhomHang . '%'))
            ->selectRaw(
                'SUM(order_tracking.sl_don_hang * COALESCE(orders.price_usd, orders.price_usd_auto, 0) * COALESCE(order_tracking.invoice_exchange_rate, ?)) as total',
                [$exchangeRate]
            )
            ->value('total') ?? 0);
        $shippedWithoutInvoiceRevenueVnd = (float) ($this->orderQuery($filters)
            ->where('status', 'shipped')
            ->whereDoesntHave('tracking', fn($q) => $q->whereNotNull('invoice_issued_at'))
            ->selectRaw('SUM(yrd * COALESCE(price_usd, price_usd_auto, 0) * ?) as total', [$exchangeRate])
            ->value('total') ?? 0);
        $invoicedRevenueVnd = $trackingInvoiceRevenueVnd + $shippedWithoutInvoiceRevenueVnd;

        return [
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
            'order_revenue' => $totalRevenueVnd,
            'invoiced_revenue' => $invoicedRevenueVnd,
            'uninvoiced_revenue' => max(0, $totalRevenueVnd - $invoicedRevenueVnd),
            'invoice_rate' => $totalRevenueVnd > 0 ? round(($invoicedRevenueVnd / $totalRevenueVnd) * 100, 1) : null,
        ];
    }

    // Gom tất cả dataset chart để controller/view chỉ cần dùng lại.
    private function buildCharts(array $filters): array
    {
        return [
            'order_status' => $this->buildOrderStatusChart($filters),
            'production_time' => $this->buildProductionTimeChart($filters),
            'tracking_status' => $this->buildTrackingStatusChart($filters),
            'production_stage' => $this->buildProductionStageChart($filters),
            'production_ca' => $this->buildProductionCaChart($filters),
        ];
    }

    // Doughnut trạng thái đơn: map mã trạng thái nội bộ sang label hiển thị.
    private function buildOrderStatusChart(array $filters): array
    {
        $orderStatuses = $this->orderQuery($filters)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $statusLabels = [
            'pending' => 'Chờ xử lý',
            'in_production' => 'Đang SX',
            'done' => 'Hoàn thành',
            'shipped' => 'Đã giao',
        ];

        $orderedStatuses = [];
        foreach ($statusLabels as $key => $label) {
            $orderedStatuses[$label] = $orderStatuses[$key] ?? 0;
        }

        return [
            'labels' => array_keys($orderedStatuses),
            'data' => array_values($orderedStatuses),
        ];
    }

    // Line chart 7 ngày: tạo trục thời gian cố định, ngày thiếu dữ liệu sẽ về 0.
    private function buildProductionTimeChart(array $filters): array
    {
        $endDate = Carbon::parse($filters['date_to'] ?? now()->toDateString());
        $last7Days = collect();
        for ($i = 6; $i >= 0; $i--) {
            $last7Days->push($endDate->copy()->subDays($i)->format('Y-m-d'));
        }

        $productionDataByDate = $this->productionQuery($filters)
            ->where('ngay_sx', '>=', $endDate->copy()->subDays(6)->format('Y-m-d'))
            ->selectRaw('DATE(ngay_sx) as date, sum(sl_dat) as total')
            ->groupBy('date')
            ->pluck('total', 'date')
            ->toArray();

        return [
            'labels' => $last7Days->map(fn(string $d) => Carbon::parse($d)->format('d/m'))->toArray(),
            'data' => $last7Days->map(fn(string $d) => $productionDataByDate[$d] ?? 0)->toArray(),
        ];
    }

    // Doughnut theo công đoạn hiện tại của tracking.
    private function buildTrackingStatusChart(array $filters): array
    {
        $trackingStatuses = $this->trackingQuery($filters)
            ->selectRaw('cong_doan, count(*) as total_count')
            ->groupBy('cong_doan')
            ->pluck('total_count', 'cong_doan')
            ->toArray();

        return [
            'labels' => array_keys($trackingStatuses),
            'data' => array_values($trackingStatuses),
        ];
    }

    // Bar chart sản lượng đạt theo công đoạn sản xuất.
    private function buildProductionStageChart(array $filters): array
    {
        $productionByStage = $this->productionQuery($filters)
            ->selectRaw('cong_doan, sum(sl_dat) as total')
            ->groupBy('cong_doan')
            ->pluck('total', 'cong_doan')
            ->toArray();

        return [
            'labels' => array_keys($productionByStage),
            'data' => array_values($productionByStage),
        ];
    }

    // Bar chart theo ca làm việc.
    private function buildProductionCaChart(array $filters): array
    {
        $productionByCa = $this->productionQuery($filters)
            ->selectRaw('ca, sum(sl_dat) as total')
            ->whereNotNull('ca')
            ->where('ca', '!=', '')
            ->groupBy('ca')
            ->pluck('total', 'ca')
            ->toArray();

        return [
            'labels' => array_map(static fn(string $ca) => 'Ca ' . $ca, array_keys($productionByCa)),
            'data' => array_values($productionByCa),
        ];
    }

    // Khối theo dõi Lệnh SX:
    // 1) nạp dữ liệu theo lô
    // 2) gom tổng hợp trước (stock, sản lượng) để tránh query lặp
    // 3) tính progress/trạng thái từng lệnh
    private function buildLenhSanXuatTracking(): array
    {
        $latestLenh = LenhSanXuat::with('items')->latest()->limit(20)->get();

        $allChildNos = $latestLenh->flatMap(fn($lenh) => $lenh->items->pluck('lenh_child'))->filter()->values();
        $trackingStages = OrderTracking::whereIn('tracking_number_child', $allChildNos)
            ->select('tracking_number_child', 'cong_doan')
            ->get()
            ->keyBy('tracking_number_child');

        $allMaHh = $latestLenh
            ->flatMap(fn($lenh) => $lenh->items->where('da_len_lenh', true)->pluck('ma_hh'))
            ->filter()
            ->unique()
            ->values();

        $stockNhapByMaHh = WarehouseTransaction::nhapKho()
            ->whereIn('ma_hh', $allMaHh)
            ->selectRaw('ma_hh, sum(so_luong) as total')
            ->groupBy('ma_hh')
            ->pluck('total', 'ma_hh');

        $stockXuatByMaHh = WarehouseTransaction::xuatKho()
            ->whereIn('ma_hh', $allMaHh)
            ->selectRaw('ma_hh, sum(so_luong) as total')
            ->groupBy('ma_hh')
            ->pluck('total', 'ma_hh');

        $productionByLenhChild = ProductionReport::whereIn('lenh_sx', $allChildNos)
            ->selectRaw('lenh_sx, sum(sl_dat) as total')
            ->groupBy('lenh_sx')
            ->pluck('total', 'lenh_sx');

        // Map từng lệnh sang object dashboard để Blade render trực tiếp.
        $lenhSxTracking = $latestLenh->map(function ($lenh) use ($trackingStages, $stockNhapByMaHh, $stockXuatByMaHh, $productionByLenhChild) {
            $activeItems = $lenh->items->where('da_len_lenh', true);
            $totalItems = $lenh->items->count();
            $activeCount = $activeItems->count();
            $tongYrd = (float) $activeItems->sum('tong_yrd');
            $tongCanSx = (float) $activeItems->sum('sl_can_sx');

            foreach ($lenh->items as $item) {
                $item->cong_doan = $trackingStages[$item->lenh_child]->cong_doan ?? 'Chờ sản xuất';
            }

            $tongDaSx = 0.0;
            $tongTonKho = 0.0;
            foreach ($activeItems as $item) {
                $tongDaSx += (float) ($productionByLenhChild[$item->lenh_child] ?? 0);
                $nhap = (float) ($stockNhapByMaHh[$item->ma_hh] ?? 0);
                $xuat = (float) ($stockXuatByMaHh[$item->ma_hh] ?? 0);
                $tongTonKho += ($nhap - $xuat);
            }

            // Quy tắc trạng thái lệnh: chưa lên lệnh -> mới; đủ kho -> done; có SX -> producing; còn lại waiting.
            $progress = $tongYrd > 0 ? min(100, round((($tongTonKho + $tongDaSx) / $tongYrd) * 100)) : 0;
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
                'id' => $lenh->id,
                'lenh_so' => $lenh->lenh_so,
                'chart' => $lenh->chart,
                'nhom_hh' => $lenh->nhom_hh,
                'total_items' => $totalItems,
                'active_items' => $activeCount,
                'tong_yrd' => $tongYrd,
                'tong_can_sx' => $tongCanSx,
                'tong_da_sx' => $tongDaSx,
                'tong_ton_kho' => $tongTonKho,
                'progress' => $progress,
                'trang_thai' => $trangThai,
                'created_at' => $lenh->created_at,
                'items' => $lenh->items,
            ];
        });

        $lenhSxStats = (object) [
            'total' => $lenhSxTracking->count(),
            'new' => $lenhSxTracking->where('trang_thai', 'new')->count(),
            'waiting' => $lenhSxTracking->where('trang_thai', 'waiting')->count(),
            'producing' => $lenhSxTracking->where('trang_thai', 'producing')->count(),
            'done' => $lenhSxTracking->where('trang_thai', 'done')->count(),
        ];

        return [$lenhSxTracking, $lenhSxStats];
    }

    // Helper chống chia 0, thống nhất cách tính % toàn dashboard.
    private function percentage(float|int $numerator, float|int $denominator, int $precision = 1): float
    {
        if ($denominator <= 0) {
            return 0.0;
        }

        return round(($numerator / $denominator) * 100, $precision);
    }
}
