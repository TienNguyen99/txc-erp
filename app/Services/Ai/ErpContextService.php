<?php

namespace App\Services\Ai;

use App\Models\DanhMucHangHoa;
use App\Models\LenhSanXuat;
use App\Models\Order;
use App\Models\OrderTracking;
use App\Models\ProductionReport;
use App\Models\PurchaseOrder;
use App\Models\WarehouseTransaction;
use Illuminate\Support\Collection;

class ErpContextService
{
    /**
     * @return array<string, mixed>
     */
    public function build(): array
    {
        $summary = $this->summary();
        $inventory = $this->inventoryAlerts();

        return [
            'summary' => $summary,
            'risks' => $this->risks($summary, $inventory),
            'inventory_alerts' => $inventory->values()->all(),
            'latest_orders' => $this->latestOrders()->all(),
            'production_30_days' => $this->production30Days(),
        ];
    }

    /**
     * @return array<string, int|float>
     */
    private function summary(): array
    {
        return [
            'total_orders' => Order::count(),
            'open_orders' => Order::whereNotIn('status', ['done', 'shipped'])->count(),
            'total_trackings' => OrderTracking::count(),
            'open_trackings' => OrderTracking::whereNotIn('cong_doan', array_merge(
                OrderTracking::warehouseDoneStages(),
                OrderTracking::deliveredStages()
            ))->count(),
            'warehouse_transactions_this_month' => WarehouseTransaction::thangNay()->count(),
            'open_production_orders' => LenhSanXuat::whereHas('items', fn ($query) => $query->where('da_len_lenh', true))->count(),
            'open_purchase_orders' => PurchaseOrder::whereNotIn('trang_thai', ['received', 'cancelled'])->count(),
        ];
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function inventoryAlerts(): Collection
    {
        $stockIn = WarehouseTransaction::nhapKho()
            ->selectRaw('ma_hh, sum(so_luong) as total')
            ->groupBy('ma_hh')
            ->pluck('total', 'ma_hh');

        $stockOut = WarehouseTransaction::xuatKho()
            ->selectRaw('ma_hh, sum(so_luong) as total')
            ->groupBy('ma_hh')
            ->pluck('total', 'ma_hh');

        return DanhMucHangHoa::query()
            ->select('ma_hh', 'ten_hh', 'ton_toi_thieu')
            ->whereNotNull('ma_hh')
            ->get()
            ->map(function (DanhMucHangHoa $item) use ($stockIn, $stockOut) {
                $onHand = (float) (($stockIn[$item->ma_hh] ?? 0) - ($stockOut[$item->ma_hh] ?? 0));
                $minStock = (float) ($item->ton_toi_thieu ?? 0);

                return [
                    'ma_hh' => $item->ma_hh,
                    'ten_hh' => $item->ten_hh,
                    'on_hand' => $onHand,
                    'min_stock' => $minStock,
                    'status' => $onHand < 0 ? 'negative' : ($minStock > 0 && $onHand < $minStock ? 'below_min' : 'ok'),
                ];
            })
            ->filter(fn (array $row) => $row['status'] !== 'ok')
            ->sortBy(fn (array $row) => $row['on_hand'] - $row['min_stock'])
            ->take(10)
            ->values();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function latestOrders(): Collection
    {
        return Order::query()
            ->with('khachHang')
            ->latest()
            ->limit(8)
            ->get()
            ->map(fn (Order $order) => [
                'job_no' => $order->job_no,
                'customer' => $order->khachHang?->ten_kh,
                'ma_hh' => $order->ma_hh,
                'ten_hh' => $order->ten_hh,
                'qty' => (float) ($order->qty ?? 0),
                'yrd' => (float) ($order->yrd ?? 0),
                'status' => $order->status,
                'need_date' => $order->sig_need_date?->format('Y-m-d'),
            ]);
    }

    /**
     * @return array<string, float>
     */
    private function production30Days(): array
    {
        $totals = ProductionReport::query()
            ->whereDate('ngay_sx', '>=', now()->subDays(30)->startOfDay())
            ->selectRaw('sum(sl_dat) as passed, sum(sl_hu) as failed')
            ->first();

        $passed = (float) ($totals->passed ?? 0);
        $failed = (float) ($totals->failed ?? 0);

        return [
            'passed' => $passed,
            'failed' => $failed,
            'defect_rate' => ($passed + $failed) > 0 ? round(($failed / ($passed + $failed)) * 100, 2) : 0.0,
        ];
    }

    /**
     * @param array<string, int|float> $summary
     * @param Collection<int, array<string, mixed>> $inventory
     * @return array<int, string>
     */
    private function risks(array $summary, Collection $inventory): array
    {
        $risks = [];

        if ($summary['open_orders'] > 0) {
            $risks[] = 'Có ' . $summary['open_orders'] . ' đơn hàng chưa hoàn tất.';
        }

        if ($summary['open_trackings'] > 0) {
            $risks[] = 'Có ' . $summary['open_trackings'] . ' tracking còn trong luồng xử lý.';
        }

        $negativeStock = $inventory->where('status', 'negative')->count();
        if ($negativeStock > 0) {
            $risks[] = 'Có ' . $negativeStock . ' mã hàng tồn kho âm.';
        }

        $belowMin = $inventory->where('status', 'below_min')->count();
        if ($belowMin > 0) {
            $risks[] = 'Có ' . $belowMin . ' mã hàng dưới tồn tối thiểu.';
        }

        if ($summary['open_purchase_orders'] > 0) {
            $risks[] = 'Có ' . $summary['open_purchase_orders'] . ' đơn mua hàng chưa hoàn tất.';
        }

        return $risks;
    }
}
