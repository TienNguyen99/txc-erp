<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Exports\LenhSanXuatExport;
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

        $chartFilter = array_filter((array) $request->input('chart', []));

        // Dashboard: khi chọn Chart
        $summary = collect();
        if (!empty($chartFilter)) {
            $orders = Order::whereIn('chart', $chartFilter)->get();

            $summary = $orders->groupBy('ma_hh')->map(function ($group, $maHh) {
                $totalQty = $group->sum('yrd');
                $hangHoa = DanhMucHangHoa::where('ma_hh', $maHh)->first();

                $slProduction = ProductionReport::where('size', $maHh)
                    ->where('cong_doan', '!=', 'Đã nhập kho')
                    ->sum('sl_dat');
                $nhap = WarehouseTransaction::where('ma_hh', $maHh)->nhapKho()->sum('so_luong');
                $xuat = WarehouseTransaction::where('ma_hh', $maHh)->xuatKho()->sum('so_luong');
                $tonKho = $nhap - $xuat;

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
                ];
            })->sortKeys()->values();
        }

        // Danh sách lệnh SX đã tạo
        $lenhList = LenhSanXuat::with('items')
            ->when(!empty($chartFilter), fn($q) => $q->whereIn('chart', $chartFilter))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.lenh-san-xuat.index', compact(
            'charts',
            'chartFilter',
            'summary',
            'lenhList'
        ));
    }

    /**
     * Tạo lệnh SX từ Chart đã chọn.
     */
    public function store(Request $request)
    {
        $request->validate([
            'chart'       => 'required|string',
            'pct_hao_hut' => 'nullable|numeric|min:0|max:100',
        ]);

        $chart = $request->chart;
        $pctHaoHut = $request->input('pct_hao_hut', 10);

        // Kiểm tra đã có lệnh cho Chart này chưa
        $existing = LenhSanXuat::where('chart', $chart)->first();
        if ($existing) {
            return redirect()->route('admin.lenh-san-xuat.show', $existing)
                ->with('warning', "Chart {$chart} đã có lệnh SX: {$existing->lenh_so}");
        }

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

        // Tạo lệnh
        $lenhSo = LenhSanXuat::generateLenhSo($nhomHh);
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

        // Stats
        $stats = (object) [
            'total_items'  => $items->count(),
            'da_len_lenh'  => $items->where('da_len_lenh', true)->count(),
            'tong_yrd'     => $items->sum('tong_yrd'),
            'tong_can_sx'  => $items->sum('sl_can_sx'),
            'tong_da_sx'   => $items->sum('sl_da_sx'),
            'tong_ton_kho' => $items->sum('ton_kho'),
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
            'items'          => 'required|array|min:1',
            'items.*.id'     => 'required|exists:lenh_san_xuat_items,id',
        ]);

        $count = 0;
        foreach ($request->items as $data) {
            $item = LenhSanXuatItem::find($data['id']);
            if (!$item) continue;
            $newStatus = isset($data['selected']);
            if ($item->da_len_lenh !== $newStatus) {
                $item->update(['da_len_lenh' => $newStatus]);
                $count++;
            }
        }

        return redirect()->route('admin.lenh-san-xuat.show', $lenhSanXuat)
            ->with('success', "Đã cập nhật {$count} lệnh.");
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
