<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Exports\LenhSanXuatExport;
use App\Exports\ProductionOrderTemplateExport;
use App\Imports\ProductionOrderTemplateImport;
use App\Models\DanhMucHangHoa;
use App\Models\LenhSanXuat;
use App\Models\LenhSanXuatItem;
use App\Models\Order;
use App\Models\ProductionReport;
use App\Models\WarehouseTransaction;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class LenhSanXuatController extends Controller
{
    /**
     * Danh sách lệnh SX + filter theo Chart.
     */
    public function index(Request $request)
    {
        // Danh sách Charts có trong orders
        $charts = Order::whereNotNull('chart')->where('chart', '!=', '')
            ->distinct()->pluck('chart');

        $chartFilter = collect((array) $request->input('chart', []))
            ->merge(preg_split('/[\r\n,;]+/', (string) $request->input('chart_bulk', ''), -1, PREG_SPLIT_NO_EMPTY))
            ->map(fn($value) => trim((string) $value))
            ->filter()
            ->unique()
            ->values()
            ->all();

        // Dashboard: khi chọn Chart
        $summary = collect();
        if (!empty($chartFilter)) {
            $orders = Order::whereIn('chart', $chartFilter)->get();

            $summary = $orders->groupBy('ma_hh')->map(function ($group, $maHh) use ($chartFilter) {
                $totalQty = $group->sum('yrd');
                $hangHoa = DanhMucHangHoa::where('ma_hh', $maHh)->first();

                $slProduction = ProductionReport::where('size', $maHh)
                    ->where('cong_doan', '!=', 'Đã nhập kho')
                    ->sum('sl_dat');
                $nhap = WarehouseTransaction::where('ma_hh', $maHh)->nhapKho()->sum('so_luong');
                $xuat = WarehouseTransaction::where('ma_hh', $maHh)->xuatKho()->sum('so_luong');
                $tonKho = $nhap - $xuat;

                $daLenLenh = LenhSanXuatItem::whereHas('lenhSanXuat', function($q) use ($chartFilter) {
                    $q->whereIn('chart', $chartFilter);
                })->where('ma_hh', $maHh)->where('da_len_lenh', true)->exists();

                return (object) [
                    'ma_hh'        => $maHh,
                    'ten_hh'       => $hangHoa?->ten_hh ?? '',
                    'nhom_hh'      => $hangHoa?->nhom_hh ?? '',
                    'mau'          => $group->pluck('color')->unique()->filter()->implode(', '),
                    'so_don'       => $group->count(),
                    'tong_qty'     => $totalQty,
                    'sl_production' => $slProduction,
                    'ton_kho'      => $tonKho,
                    'thieu'        => max(0, $totalQty - $tonKho),
                    'du_hang'      => $tonKho >= $totalQty,
                    'da_len_lenh'  => $daLenLenh,
                ];
            })->sortKeys()->values();
        }

        // Danh sách lệnh SX đã tạo
        $lenhList = LenhSanXuat::with('items')
            ->when(!empty($chartFilter), fn($q) => $q->whereIn('chart', $chartFilter))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        // Lấy thông tin công đoạn cho từng lệnh con
        $allChildNos = collect();
        foreach ($lenhList as $lenh) {
            $allChildNos = $allChildNos->merge($lenh->items->pluck('lenh_child'));
        }

        $trackingStages = \App\Models\OrderTracking::whereIn('tracking_number_child', $allChildNos)
            ->select('tracking_number_child', 'cong_doan')
            ->get()
            ->keyBy('tracking_number_child');

        foreach ($lenhList as $lenh) {
            foreach ($lenh->items as $item) {
                $item->cong_doan = $trackingStages[$item->lenh_child]->cong_doan ?? 'Chờ sản xuất';
            }
        }

        $stages = \App\Models\OrderTracking::STAGES;

        return view('admin.lenh-san-xuat.index', compact(
            'charts',
            'chartFilter',
            'summary',
            'lenhList',
            'stages'
        ));
    }

    /**
     * Tạo lệnh SX từ Chart đã chọn.
     */
    public function store(Request $request)
    {
        $request->validate([
            'chart'       => 'nullable',
            'chart_bulk'  => 'nullable|string',
            'pct_hao_hut' => 'nullable|numeric|min:0|max:100',
        ]);

        $charts = collect((array) $request->input('chart', []))
            ->merge(preg_split('/[\r\n,;]+/', (string) $request->input('chart_bulk', ''), -1, PREG_SPLIT_NO_EMPTY))
            ->map(fn($value) => trim((string) $value))
            ->filter()
            ->unique()
            ->values();
        $pctHaoHut = $request->input('pct_hao_hut', 10);

        if ($charts->isEmpty()) {
            return redirect()->back()->with('warning', 'Vui long chon hoac dan danh sach Chart truoc khi tao lenh SX.');
        }

        if ($charts->count() > 1) {
            $created = collect();
            $skipped = collect();

            foreach ($charts as $bulkChart) {
                $ordersForChart = Order::where('chart', $bulkChart)->get();
                if ($ordersForChart->isEmpty()) {
                    $skipped->push($bulkChart);
                    continue;
                }

                $created->push($this->createLenhFromOrders($bulkChart, $ordersForChart, (float) $pctHaoHut));
            }

            if ($created->isEmpty()) {
                return redirect()->back()->with('error', 'Khong tim thay don hang cho cac Chart da chon.');
            }

            $msg = "Da tao {$created->count()} lenh SX tu {$charts->count()} Chart da chon.";
            if ($skipped->isNotEmpty()) {
                $msg .= ' Bo qua Chart khong co don hang: ' . $skipped->take(5)->implode(', ');
            }

            return redirect()->route('admin.lenh-san-xuat.index', ['chart' => $created->pluck('chart')->all()])
                ->with('success', $msg);
        }

        $chart = $charts->first();

        // Lấy orders theo Chart
        $orders = Order::where('chart', $chart)->get();
        if ($orders->isEmpty()) {
            return redirect()->back()->with('error', 'Không tìm thấy đơn hàng cho Chart này.');
        }

        // Xác định nhom_hh chính
        $grouped = $orders->groupBy('ma_hh');
        $nhomHhCounts = $grouped->map(function ($group, $maHh) {
            $hangHoa = DanhMucHangHoa::where('ma_hh', $maHh)->first();
            return $hangHoa?->nhom_hh ?? 'LSX';
        });
        $nhomHh = $nhomHhCounts->countBy()->sortDesc()->keys()->first() ?: 'LSX';

        // Xác định khách hàng
        $khachHang = $orders->first()->khachHang;
        $maKh = $khachHang ? $khachHang->ma_kh : 'UNK';

        // Tạo lệnh
        $lenhSo = LenhSanXuat::generateLenhSo($maKh, $nhomHh);
        $lenh = LenhSanXuat::create([
            'lenh_so'     => $lenhSo,
            'chart'       => $chart,
            'nhom_hh'     => $nhomHh,
            'pct_hao_hut' => $pctHaoHut,
        ]);

        // Tạo items theo ma_hh
        $stt = 1;
        foreach ($grouped->sortKeys() as $maHh => $group) {
            $totalYrd = $group->sum('yrd');
            $hangHoa = DanhMucHangHoa::where('ma_hh', $maHh)->first();
            $colors = $group->pluck('color')->unique()->filter()->implode(', ');

            LenhSanXuatItem::create([
                'lenh_san_xuat_id' => $lenh->id,
                'lenh_child'       => $lenhSo . '/' . $stt,
                'ma_hh'            => $maHh,
                'ten_hh'           => $hangHoa?->ten_hh ?? '',
                'mau'              => $colors,
                'tong_yrd'         => $totalYrd,
                'sl_can_sx'        => round($totalYrd * (1 + $pctHaoHut / 100), 2),
                'da_len_lenh'      => false,
                'stt'              => $stt,
            ]);
            $stt++;
        }

        return redirect()->route('admin.lenh-san-xuat.show', $lenh)
            ->with('success', "Đã tạo lệnh SX {$lenhSo} với {$lenh->items()->count()} mã HH.");
    }

    private function createLenhFromOrders(string $chart, $orders, float $pctHaoHut): LenhSanXuat
    {
        $grouped = $orders->groupBy('ma_hh');
        $nhomHhCounts = $grouped->map(function ($group, $maHh) {
            $hangHoa = DanhMucHangHoa::where('ma_hh', $maHh)->first();
            return $hangHoa?->nhom_hh ?? 'LSX';
        });
        $nhomHh = $nhomHhCounts->countBy()->sortDesc()->keys()->first() ?: 'LSX';

        $khachHang = $orders->first()->khachHang;
        $maKh = $khachHang ? $khachHang->ma_kh : 'UNK';

        $lenhSo = LenhSanXuat::generateLenhSo($maKh, $nhomHh);
        $lenh = LenhSanXuat::create([
            'lenh_so'     => $lenhSo,
            'chart'       => $chart,
            'nhom_hh'     => $nhomHh,
            'pct_hao_hut' => $pctHaoHut,
        ]);

        $stt = 1;
        foreach ($grouped->sortKeys() as $maHh => $group) {
            $totalYrd = $group->sum('yrd');
            $hangHoa = DanhMucHangHoa::where('ma_hh', $maHh)->first();
            $colors = $group->pluck('color')->unique()->filter()->implode(', ');

            LenhSanXuatItem::create([
                'lenh_san_xuat_id' => $lenh->id,
                'lenh_child'       => $lenhSo . '/' . $stt,
                'ma_hh'            => $maHh,
                'ten_hh'           => $hangHoa?->ten_hh ?? '',
                'mau'              => $colors,
                'tong_yrd'         => $totalYrd,
                'sl_can_sx'        => round($totalYrd * (1 + $pctHaoHut / 100), 2),
                'da_len_lenh'      => false,
                'stt'              => $stt,
            ]);
            $stt++;
        }

        return $lenh;
    }

    /**
     * Import orders from the production-order Excel template.
     */
    public function importTemplate(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            $import = new ProductionOrderTemplateImport();
            Excel::import($import, $request->file('file'));

            $message = "Import OK: total {$import->getProcessedRows()} rows | created {$import->getCreatedCount()} | updated {$import->getUpdatedCount()} | skipped {$import->getSkippedCount()}";
            $skippedRows = $import->getSkippedRows();
            if (!empty($skippedRows)) {
                $preview = collect($skippedRows)
                    ->take(5)
                    ->map(fn ($row) => "row {$row['row']}: {$row['reason']}")
                    ->implode('; ');
                $message .= ". Skipped: {$preview}";
            }

            $charts = $import->getImportedCharts();
            $redirect = redirect()->route('admin.lenh-san-xuat.index', $charts ? ['chart' => $charts] : []);

            return $redirect->with($import->getSkippedCount() > 0 ? 'warning' : 'success', $message);
        } catch (\Throwable $e) {
            return redirect()->route('admin.lenh-san-xuat.index')
                ->with('error', 'Import error: ' . $e->getMessage());
        }
    }

    public function template()
    {
        return Excel::download(
            new ProductionOrderTemplateExport(),
            'mau_import_lenh_san_xuat.xlsx'
        );
    }

    /**
     * Chi tiết lệnh SX — theo dõi tiến độ.
     */
    public function show(LenhSanXuat $lenhSanXuat)
    {
        $lenh = $lenhSanXuat->load('items');

        // Tính tiến độ cho từng item
        $items = $lenh->items->map(function ($item) {
            $slDaSx = ProductionReport::where('lenh_sx', $item->lenh_child)->sum('sl_dat');
            $slHu = ProductionReport::where('lenh_sx', $item->lenh_child)->sum('sl_hu');

            $nhap = WarehouseTransaction::where('ma_hh', $item->ma_hh)->nhapKho()->sum('so_luong');
            $xuat = WarehouseTransaction::where('ma_hh', $item->ma_hh)->xuatKho()->sum('so_luong');
            $tonKho = $nhap - $xuat;

            $hangHoa = DanhMucHangHoa::where('ma_hh', $item->ma_hh)->first();

            $item->sl_da_sx = $slDaSx;
            $item->sl_hu = $slHu;
            $item->ton_kho = $tonKho;
            $item->hang_hoa = $hangHoa;
            $item->progress = $item->tong_yrd > 0
                ? min(100, round(($tonKho + $slDaSx) / $item->tong_yrd * 100))
                : 0;

            return $item;
        });

        // Lấy danh sách các mã đã lên lệnh để tính tổng
        $activeItems = $items->where('da_len_lenh', true);

        // Stats
        $stats = (object) [
            'total_items'  => $items->count(),
            'da_len_lenh'  => $activeItems->count(),
            'tong_yrd'     => $activeItems->sum('tong_yrd'),
            'tong_can_sx'  => $activeItems->sum('sl_can_sx'),
            'tong_da_sx'   => $activeItems->sum('sl_da_sx'),
            'tong_ton_kho' => $activeItems->sum('ton_kho'),
        ];

        // Orders thuộc Chart này
        $orders = Order::where('chart', $lenh->chart)->get();

        // Danh sách tất cả lệnh (switch nhanh)
        $allLenh = LenhSanXuat::orderByDesc('created_at')->pluck('lenh_so', 'id');

        return view('admin.lenh-san-xuat.show', compact('lenh', 'items', 'stats', 'orders', 'allLenh'));
    }

    /**
     * Toggle đã lên lệnh cho items.
     */
    public function toggleItems(Request $request, LenhSanXuat $lenhSanXuat)
    {
        $request->validate([
            'items'             => 'required|array|min:1',
            'items.*.id'        => 'required|exists:lenh_san_xuat_items,id',
            'items.*.sl_can_sx' => 'nullable|numeric|min:0',
        ]);

        $count = 0;
        foreach ($request->items as $data) {
            $item = LenhSanXuatItem::find($data['id']);
            if (!$item) continue;
            
            $newStatus = isset($data['selected']);
            $newSlCanSx = isset($data['sl_can_sx']) ? floatval($data['sl_can_sx']) : $item->sl_can_sx;
            
            if ($item->da_len_lenh !== $newStatus || $item->sl_can_sx != $newSlCanSx) {
                $item->update([
                    'da_len_lenh' => $newStatus,
                    'sl_can_sx' => $newSlCanSx,
                ]);
                $count++;
            }
        }

        return redirect()->route('admin.lenh-san-xuat.show', $lenhSanXuat)
            ->with('success', "Đã cập nhật {$count} lệnh (Trạng thái lên lệnh và Số lượng cần SX).");
    }

    /**
     * Xuất Excel lệnh SX.
     */
    public function export(LenhSanXuat $lenhSanXuat)
    {
        $pctHaoHut = request('pct_hao_hut', $lenhSanXuat->pct_hao_hut);
        $filename = 'LENH_SX_' . str_replace(['-', '/'], '_', $lenhSanXuat->lenh_so) . '.xlsx';

        return Excel::download(
            new LenhSanXuatExport($lenhSanXuat->lenh_so, $pctHaoHut),
            $filename
        );
    }

    /**
     * Xóa lệnh SX.
     */
    public function destroy(LenhSanXuat $lenhSanXuat)
    {
        $lenhSo = $lenhSanXuat->lenh_so;
        $lenhSanXuat->delete(); // cascade deletes items
        return redirect()->route('admin.lenh-san-xuat.index')
            ->with('success', "Đã xóa lệnh SX: {$lenhSo}");
    }
}
