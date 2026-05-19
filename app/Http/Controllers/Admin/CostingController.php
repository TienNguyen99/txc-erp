<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CostOverhead;
use App\Models\DanhMucHangHoa;
use App\Models\DinhMucNvl;
use App\Models\Order;
use App\Models\ProductionReport;
use App\Models\PurchaseOrderItem;
use App\Models\Setting;
use App\Models\WarehouseTransaction;
use Illuminate\Http\Request;

class CostingController extends Controller
{
    public function index(Request $request)
    {
        $month = (int) ($request->input('month') ?: now()->month);
        $year = (int) ($request->input('year') ?: now()->year);
        $exchangeRate = (float) (Setting::where('key', 'usd_to_vnd')->value('value') ?? 25400);

        $overhead = CostOverhead::firstOrCreate(
            ['month' => $month, 'year' => $year],
            ['allocation_basis' => 'output_qty']
        );

        $unitCosts = $this->buildAverageMaterialCosts($exchangeRate);
        $productionCosts = $this->buildProductionCosts($month, $year, $unitCosts, $overhead);
        $shipmentCosts = $this->buildShipmentCosts($month, $year, $unitCosts, $productionCosts, $exchangeRate);
        $itemCosts = $this->buildItemCosts($productionCosts, $shipmentCosts, $unitCosts);

        $stats = (object) [
            'material_actual' => $productionCosts->sum('material_actual_vnd'),
            'material_standard' => $productionCosts->sum('material_standard_vnd'),
            'conversion' => $productionCosts->sum('allocated_conversion_vnd'),
            'production_total' => $productionCosts->sum('total_cost_vnd'),
            'shipment_revenue' => $shipmentCosts->sum('revenue_vnd'),
            'shipment_cogs' => $shipmentCosts->sum('cogs_vnd'),
        ];
        $stats->gross_profit = $stats->shipment_revenue - $stats->shipment_cogs;
        $stats->gross_margin_pct = $stats->shipment_revenue > 0
            ? round($stats->gross_profit / $stats->shipment_revenue * 100, 2)
            : 0;

        return view('admin.costing.index', compact(
            'month',
            'year',
            'exchangeRate',
            'overhead',
            'productionCosts',
            'shipmentCosts',
            'itemCosts',
            'unitCosts',
            'stats'
        ));
    }

    public function storeOverhead(Request $request)
    {
        $validated = $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020|max:2100',
            'labor_cost_vnd' => 'nullable|numeric|min:0',
            'factory_overhead_vnd' => 'nullable|numeric|min:0',
            'other_cost_vnd' => 'nullable|numeric|min:0',
            'allocation_basis' => 'required|in:output_qty',
            'note' => 'nullable|string',
        ]);

        CostOverhead::updateOrCreate(
            ['month' => $validated['month'], 'year' => $validated['year']],
            [
                'labor_cost_vnd' => $validated['labor_cost_vnd'] ?? 0,
                'factory_overhead_vnd' => $validated['factory_overhead_vnd'] ?? 0,
                'other_cost_vnd' => $validated['other_cost_vnd'] ?? 0,
                'allocation_basis' => $validated['allocation_basis'],
                'note' => $validated['note'] ?? null,
            ]
        );

        return redirect()
            ->route('admin.costing.index', ['month' => $validated['month'], 'year' => $validated['year']])
            ->with('success', 'Đã cập nhật chi phí phân bổ giá vốn.');
    }

    private function buildAverageMaterialCosts(float $exchangeRate)
    {
        $warehouseCosts = WarehouseTransaction::nhapKho()
            ->whereNotNull('ma_hh')
            ->get()
            ->groupBy('ma_hh')
            ->map(function ($rows, $maHh) use ($exchangeRate) {
                $qty = $rows->sum(fn($r) => (float) $r->so_luong);
                $value = $rows->sum(function ($r) use ($exchangeRate) {
                    $unitUsd = (float) ($r->price_usd ?? 0);
                    $rate = (float) ($r->exchange_rate ?: $exchangeRate);
                    return (float) $r->so_luong * $unitUsd * $rate;
                });

                return [
                    'ma_hh' => $maHh,
                    'qty' => $qty,
                    'value_vnd' => $value,
                    'unit_cost_vnd' => $qty > 0 ? $value / $qty : 0,
                    'source' => 'warehouse',
                ];
            });

        $poCosts = PurchaseOrderItem::query()
            ->where('don_gia', '>', 0)
            ->get()
            ->groupBy('ma_hh')
            ->map(function ($rows, $maHh) use ($exchangeRate) {
                $qty = $rows->sum(fn($r) => (float) ($r->da_nhan > 0 ? $r->da_nhan : $r->so_luong));
                $value = $rows->sum(fn($r) => (float) ($r->da_nhan > 0 ? $r->da_nhan : $r->so_luong) * (float) $r->don_gia * $exchangeRate);

                return [
                    'ma_hh' => $maHh,
                    'qty' => $qty,
                    'value_vnd' => $value,
                    'unit_cost_vnd' => $qty > 0 ? $value / $qty : 0,
                    'source' => 'purchase_order',
                ];
            });

        $catalogCosts = DanhMucHangHoa::where(function ($q) {
                $q->where('gia_nvl', '>', 0)->orWhere('don_gia', '>', 0);
            })
            ->get()
            ->mapWithKeys(function ($hh) use ($exchangeRate) {
                $unit = (float) ($hh->gia_nvl ?: $hh->don_gia);
                return [$hh->ma_hh => [
                    'ma_hh' => $hh->ma_hh,
                    'qty' => 0,
                    'value_vnd' => 0,
                    'unit_cost_vnd' => $unit * $exchangeRate,
                    'source' => 'catalog',
                ]];
            });

        return $catalogCosts
            ->merge($poCosts)
            ->merge($warehouseCosts->filter(fn($row) => $row['unit_cost_vnd'] > 0))
            ->sortKeys();
    }

    private function buildProductionCosts(int $month, int $year, $unitCosts, CostOverhead $overhead)
    {
        $reports = ProductionReport::query()
            ->whereMonth('ngay_sx', $month)
            ->whereYear('ngay_sx', $year)
            ->whereNotNull('lenh_sx')
            ->where('lenh_sx', '!=', '')
            ->get();

        $finishedReceipts = WarehouseTransaction::nhapKho()
            ->whereMonth('ngay', $month)
            ->whereYear('ngay', $year)
            ->whereNotNull('lenh_sx')
            ->get()
            ->groupBy(fn($r) => $this->costKey($r->lenh_sx, $r->ma_hh));

        $groups = $reports
            ->groupBy(fn($r) => $this->costKey($r->lenh_sx, $r->size))
            ->map(function ($rows, $key) use ($finishedReceipts) {
                [$lenhSx, $maHh] = explode('|', $key, 2);
                $receiptQty = ($finishedReceipts[$key] ?? collect())->sum(fn($r) => (float) $r->so_luong);

                return (object) [
                    'key' => $key,
                    'lenh_sx' => $lenhSx,
                    'ma_hh' => $maHh,
                    'output_qty' => $receiptQty > 0 ? $receiptQty : $rows->sum(fn($r) => (float) $r->sl_dat),
                    'scrap_qty' => $rows->sum(fn($r) => (float) $r->sl_hu),
                    'stages' => $rows->pluck('cong_doan')->filter()->unique()->implode(', '),
                ];
            })
            ->filter(fn($row) => $row->ma_hh !== '' && $row->output_qty > 0)
            ->values();

        $totalOutput = max(0.0001, $groups->sum('output_qty'));
        $totalConversion = $overhead->total_cost_vnd;

        return $groups->map(function ($row) use ($unitCosts, $totalOutput, $totalConversion) {
            $materialActual = $this->actualMaterialCostForProduction($row->lenh_sx, $row->ma_hh, $unitCosts);
            $materialStandard = $this->standardMaterialCost($row->ma_hh, $row->output_qty, $unitCosts);
            $allocatedConversion = $totalConversion * ($row->output_qty / $totalOutput);
            $materialCost = $materialActual > 0 ? $materialActual : $materialStandard;
            $totalCost = $materialCost + $allocatedConversion;

            return (object) [
                'lenh_sx' => $row->lenh_sx,
                'ma_hh' => $row->ma_hh,
                'output_qty' => $row->output_qty,
                'scrap_qty' => $row->scrap_qty,
                'stages' => $row->stages,
                'material_actual_vnd' => $materialActual,
                'material_standard_vnd' => $materialStandard,
                'allocated_conversion_vnd' => $allocatedConversion,
                'total_cost_vnd' => $totalCost,
                'unit_cost_vnd' => $row->output_qty > 0 ? $totalCost / $row->output_qty : 0,
                'cost_source' => $materialActual > 0 ? 'actual_issue' : 'bom_standard',
            ];
        })->sortByDesc('total_cost_vnd')->values();
    }

    private function buildShipmentCosts(int $month, int $year, $unitCosts, $productionCosts, float $exchangeRate)
    {
        $productionUnitCosts = $productionCosts
            ->mapWithKeys(fn($row) => [$this->costKey($row->lenh_sx, $row->ma_hh) => $row->unit_cost_vnd]);

        return WarehouseTransaction::xuatKho()
            ->whereMonth('ngay', $month)
            ->whereYear('ngay', $year)
            ->whereNotNull('ma_hh')
            ->get()
            ->filter(function ($row) {
                return !str_contains(mb_strtolower((string) $row->note), 'vật tư')
                    && !str_contains(mb_strtolower((string) $row->note), 'vat tu');
            })
            ->map(function ($row) use ($unitCosts, $productionUnitCosts, $exchangeRate) {
                $qty = (float) $row->so_luong;
                $unitCost = (float) ($productionUnitCosts[$this->costKey($row->lenh_sx, $row->ma_hh)] ?? ($unitCosts[$row->ma_hh]['unit_cost_vnd'] ?? 0));
                $unitRevenue = (float) ($row->price_usd ?? 0) * (float) ($row->exchange_rate ?: $exchangeRate);

                return (object) [
                    'ngay' => $row->ngay,
                    'lenh_sx' => $row->lenh_sx,
                    'ma_hh' => $row->ma_hh,
                    'qty' => $qty,
                    'unit_cost_vnd' => $unitCost,
                    'cogs_vnd' => $qty * $unitCost,
                    'unit_revenue_vnd' => $unitRevenue,
                    'revenue_vnd' => $qty * $unitRevenue,
                    'gross_profit_vnd' => $qty * ($unitRevenue - $unitCost),
                ];
            })
            ->sortByDesc('ngay')
            ->values();
    }

    private function buildItemCosts($productionCosts, $shipmentCosts, $unitCosts)
    {
        $productNames = DanhMucHangHoa::whereIn('ma_hh', $productionCosts->pluck('ma_hh')->merge($shipmentCosts->pluck('ma_hh'))->unique())
            ->pluck('ten_hh', 'ma_hh');

        $productionByItem = $productionCosts->groupBy('ma_hh');
        $shipmentByItem = $shipmentCosts->groupBy('ma_hh');

        return $productionByItem->keys()
            ->merge($shipmentByItem->keys())
            ->merge($unitCosts->keys())
            ->unique()
            ->filter()
            ->map(function ($maHh) use ($productionByItem, $shipmentByItem, $unitCosts, $productNames) {
                $productionRows = $productionByItem[$maHh] ?? collect();
                $shipmentRows = $shipmentByItem[$maHh] ?? collect();

                $producedQty = $productionRows->sum('output_qty');
                $productionCost = $productionRows->sum('total_cost_vnd');
                $materialActual = $productionRows->sum('material_actual_vnd');
                $materialStandard = $productionRows->sum('material_standard_vnd');
                $conversion = $productionRows->sum('allocated_conversion_vnd');
                $shippedQty = $shipmentRows->sum('qty');
                $revenue = $shipmentRows->sum('revenue_vnd');
                $cogs = $shipmentRows->sum('cogs_vnd');

                $unitCost = $producedQty > 0
                    ? $productionCost / $producedQty
                    : (float) ($unitCosts[$maHh]['unit_cost_vnd'] ?? 0);

                if ($cogs <= 0 && $shippedQty > 0 && $unitCost > 0) {
                    $cogs = $shippedQty * $unitCost;
                }

                return (object) [
                    'ma_hh' => $maHh,
                    'ten_hh' => $productNames[$maHh] ?? '',
                    'produced_qty' => $producedQty,
                    'shipped_qty' => $shippedQty,
                    'ending_qty_estimate' => $producedQty - $shippedQty,
                    'material_actual_vnd' => $materialActual,
                    'material_standard_vnd' => $materialStandard,
                    'conversion_vnd' => $conversion,
                    'production_cost_vnd' => $productionCost,
                    'unit_cost_vnd' => $unitCost,
                    'cogs_vnd' => $cogs,
                    'revenue_vnd' => $revenue,
                    'gross_profit_vnd' => $revenue - $cogs,
                    'gross_margin_pct' => $revenue > 0 ? round(($revenue - $cogs) / $revenue * 100, 2) : 0,
                    'cost_source' => $producedQty > 0 ? 'production' : ($unitCosts[$maHh]['source'] ?? 'missing'),
                ];
            })
            ->sortBy('ma_hh')
            ->values();
    }

    private function actualMaterialCostForProduction(string $lenhSx, string $finishedGood, $unitCosts): float
    {
        return WarehouseTransaction::xuatKho()
            ->where('lenh_sx', $lenhSx)
            ->where('ma_hh', '!=', $finishedGood)
            ->get()
            ->sum(fn($row) => (float) $row->so_luong * (float) ($unitCosts[$row->ma_hh]['unit_cost_vnd'] ?? 0));
    }

    private function standardMaterialCost(string $finishedGood, float $outputQty, $unitCosts): float
    {
        $product = DanhMucHangHoa::where('ma_hh', $finishedGood)->first();
        if (!$product) {
            return 0;
        }

        return DinhMucNvl::with('nguyenLieu')
            ->where('san_pham_id', $product->id)
            ->get()
            ->sum(function ($bom) use ($outputQty, $unitCosts) {
                $materialCode = $bom->nguyenLieu?->ma_hh;
                if (!$materialCode) {
                    return 0;
                }

                $requiredQty = $outputQty * (float) $bom->so_luong * (1 + ((float) $bom->ti_le_hao_hut / 100));
                return $requiredQty * (float) ($unitCosts[$materialCode]['unit_cost_vnd'] ?? 0);
            });
    }

    private function costKey(?string $lenhSx, ?string $maHh): string
    {
        return trim((string) $lenhSx) . '|' . trim((string) $maHh);
    }
}
