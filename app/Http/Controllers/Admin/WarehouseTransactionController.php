<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DanhMucHangHoa;
use App\Models\Order;
use App\Models\WarehouseTransaction;
use App\Exports\WarehouseTransactionExport;
use App\Exports\WarehouseTransactionTemplateExport;
use App\Imports\WarehouseTransactionImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class WarehouseTransactionController extends Controller
{
    public function index(Request $request)
    {
        $data = WarehouseTransaction::when($request->search, fn($q, $s) => $q->where('lenh_sx', 'like', "%$s%")->orWhere('ma_nv', 'like', "%$s%"))
                    ->when($request->cong_doan, fn($q, $cd) => $q->where('cong_doan', $cd))
                    ->latest()->paginate(15)->withQueryString();

        // ═══ TỒN KHO ═══
        $thang = $request->thang ?? now()->month;
        $nam   = $request->nam   ?? now()->year;

        $startOfMonth = Carbon::create($nam, $thang, 1)->startOfMonth();

        $makeKey = fn($r) => ($r->ma_hh ?? '') . '|' . ($r->size ?? '') . '|' . ($r->mau ?? '');

        $tonDau = WarehouseTransaction::select(
                'ma_hh', 'size', 'mau',
                DB::raw("SUM(CASE WHEN cong_doan='NHAPKHO' THEN so_luong ELSE -so_luong END) as ton_dau")
            )
            ->where('ngay', '<', $startOfMonth)
            ->groupBy('ma_hh', 'size', 'mau')
            ->get()->keyBy($makeKey);

        $transactions = WarehouseTransaction::select('ma_hh', 'size', 'mau', 'cong_doan', 'ngay', DB::raw('SUM(so_luong) as so_luong'))
            ->whereMonth('ngay', $thang)
            ->whereYear('ngay', $nam)
            ->groupBy('ma_hh', 'size', 'mau', 'cong_doan', 'ngay')
            ->get();

        $nhapDates = $transactions->where('cong_doan', 'NHAPKHO')->pluck('ngay')->map->format('Y-m-d')->unique()->sort()->values();
        $xuatDates = $transactions->where('cong_doan', 'XUATKHO')->pluck('ngay')->map->format('Y-m-d')->unique()->sort()->values();

        $nhapByDay = [];
        $xuatByDay = [];
        foreach ($transactions as $t) {
            $key = $makeKey($t);
            $day = $t->ngay->format('Y-m-d');
            if ($t->cong_doan === 'NHAPKHO') {
                $nhapByDay[$key][$day] = ($nhapByDay[$key][$day] ?? 0) + $t->so_luong;
            } else {
                $xuatByDay[$key][$day] = ($xuatByDay[$key][$day] ?? 0) + $t->so_luong;
            }
        }

        $canDi = Order::where('status', '!=', 'shipped')
            ->select('ma_hh', DB::raw('SUM(yrd) as tong_yrd'))
            ->whereNotNull('ma_hh')
            ->groupBy('ma_hh')
            ->pluck('tong_yrd', 'ma_hh');

        $allKeys = collect($tonDau->keys())
            ->merge(collect(array_keys($nhapByDay)))
            ->merge(collect(array_keys($xuatByDay)))
            ->unique()->sort();

        $tonKho = $allKeys->map(function ($key) use ($tonDau, $nhapByDay, $xuatByDay, $nhapDates, $xuatDates, $canDi) {
            [$maHh, $size, $mau] = explode('|', $key, 3);
            $tonDauVal = $tonDau[$key]->ton_dau ?? 0;

            $nhapRows = [];
            $tongNhap = 0;
            foreach ($nhapDates as $d) {
                $val = $nhapByDay[$key][$d] ?? 0;
                $nhapRows[$d] = $val;
                $tongNhap += $val;
            }

            $xuatRows = [];
            $tongXuat = 0;
            foreach ($xuatDates as $d) {
                $val = $xuatByDay[$key][$d] ?? 0;
                $xuatRows[$d] = $val;
                $tongXuat += $val;
            }

            $tonCuoi = $tonDauVal + $tongNhap - $tongXuat;

            return [
                'ma_hh'     => $maHh,
                'size'      => $size,
                'mau'       => $mau,
                'ton_dau'   => $tonDauVal,
                'nhap_days' => $nhapRows,
                'tong_nhap' => $tongNhap,
                'xuat_days' => $xuatRows,
                'tong_xuat' => $tongXuat,
                'ton_cuoi'  => $tonCuoi,
                'can_di'    => $canDi[$maHh] ?? 0,
            ];
        })->sortBy(['ma_hh', 'mau'])->values();

        return view('admin.warehouse-transactions.index', compact(
            'data', 'tonKho', 'thang', 'nam', 'nhapDates', 'xuatDates'
        ));
    }

    public function create()
    {
        $hangHoas = DanhMucHangHoa::where('active', true)->pluck('ten_hh', 'id');
        return view('admin.warehouse-transactions.form', compact('hangHoas'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'cong_doan'   => 'required|in:NHAPKHO,XUATKHO',
            'ma_hh'       => 'nullable|string',
            'hang_hoa_id' => 'nullable|exists:danh_muc_hang_hoa,id',
            'ngay'        => 'required|date',
            'size'        => 'nullable|string',
            'mau'         => 'nullable|string',
            'so_luong'    => 'required|numeric|min:0.01',
            'ma_nv'       => 'nullable|string',
            'lenh_sx'     => 'nullable|string',
            'note'        => 'nullable|string',
        ]);
        WarehouseTransaction::create($validated);
        return redirect()->route('admin.warehouse-transactions.index')->with('success', 'Thêm giao dịch kho thành công.');
    }

    public function edit(WarehouseTransaction $warehouseTransaction)
    {
        $hangHoas = DanhMucHangHoa::where('active', true)->pluck('ten_hh', 'id');
        return view('admin.warehouse-transactions.form', compact('warehouseTransaction', 'hangHoas'));
    }

    public function update(Request $request, WarehouseTransaction $warehouseTransaction)
    {
        $validated = $request->validate([
            'cong_doan'   => 'required|in:NHAPKHO,XUATKHO',
            'ma_hh'       => 'nullable|string',
            'hang_hoa_id' => 'nullable|exists:danh_muc_hang_hoa,id',
            'ngay'        => 'required|date',
            'size'        => 'nullable|string',
            'mau'         => 'nullable|string',
            'so_luong'    => 'required|numeric|min:0.01',
            'ma_nv'       => 'nullable|string',
            'lenh_sx'     => 'nullable|string',
            'note'        => 'nullable|string',
        ]);
        $warehouseTransaction->update($validated);
        return redirect()->route('admin.warehouse-transactions.index')->with('success', 'Cập nhật giao dịch kho thành công.');
    }

    public function destroy(WarehouseTransaction $warehouseTransaction)
    {
        $warehouseTransaction->delete();
        return redirect()->route('admin.warehouse-transactions.index')->with('success', 'Xóa giao dịch kho thành công.');
    }

    public function tonKho(Request $request)
    {
        return redirect()->route('admin.warehouse-transactions.index', $request->only('thang', 'nam'));
    }

    /**
     * Trang nhập kho theo Lệnh SX.
     * Nhập mã lệnh → hiển thị danh sách hàng cần nhập → công nhân chỉ điền SL.
     */
    public function nhapTheoLenh(Request $request)
    {
        $lenhSx = $request->lenh_sx;
        $items = collect();

        if ($lenhSx) {
            // Tìm orders có lenh_sanxuat khớp
            $orders = Order::where('lenh_sanxuat', $lenhSx)->get();

            if ($orders->isEmpty()) {
                // Thử tìm qua tracking
                $orders = Order::whereHas('tracking', fn($q) => $q->where('pl_number', 'like', "%{$lenhSx}%"))
                    ->get();
            }

            $items = $orders->map(function ($order) {
                // Tính tồn kho hiện tại
                $nhap = WarehouseTransaction::where('ma_hh', $order->ma_hh)->nhapKho()->sum('so_luong');
                $xuat = WarehouseTransaction::where('ma_hh', $order->ma_hh)->xuatKho()->sum('so_luong');

                return (object) [
                    'order_id' => $order->id,
                    'ma_hang'  => $order->ma_hh,
                    'mau'      => $order->color,
                    'size'     => $order->tracking()->first()->kich ?? null,
                    'sl_don'   => $order->yrd,
                    'ton_kho'  => $nhap - $xuat,
                    'job_no'   => $order->job_no,
                    'fty_po'   => $order->fty_po,
                ];
            });
        }

        // Danh sách các lệnh SX đã có
        $danhSachLenh = Order::whereNotNull('lenh_sanxuat')
            ->where('lenh_sanxuat', '!=', '')
            ->distinct()->pluck('lenh_sanxuat');

        return view('admin.warehouse-transactions.nhap-theo-lenh', compact('items', 'lenhSx', 'danhSachLenh'));
    }

    /**
     * Xử lý nhập kho hàng loạt theo lệnh SX.
     */
    public function storeNhapTheoLenh(Request $request)
    {
        $request->validate([
            'lenh_sx'      => 'required|string',
            'ngay'         => 'required|date',
            'ma_nv'        => 'nullable|string',
            'rows'         => 'required|array|min:1',
            'rows.*.ma_hh' => 'required|string',
            'rows.*.mau'   => 'nullable|string',
            'rows.*.size'  => 'nullable|string',
            'rows.*.so_luong' => 'nullable|numeric|min:0',
        ]);

        $count = 0;
        foreach ($request->rows as $row) {
            $sl = floatval($row['so_luong'] ?? 0);
            if ($sl <= 0) continue;

            WarehouseTransaction::create([
                'cong_doan' => 'NHAPKHO',
                'ma_hh'     => $row['ma_hh'],
                'ngay'      => $request->ngay,
                'size'      => $row['size'] ?? null,
                'mau'       => $row['mau'] ?? null,
                'so_luong'  => $sl,
                'ma_nv'     => $request->ma_nv,
                'lenh_sx'   => $request->lenh_sx,
                'note'      => "Nhập theo lệnh SX",
            ]);
            $count++;
        }

        return redirect()
            ->route('admin.warehouse-transactions.nhap-theo-lenh', ['lenh_sx' => $request->lenh_sx])
            ->with('success', "Đã nhập kho {$count} mục theo lệnh {$request->lenh_sx}.");
    }

    public function export()
    {
        return Excel::download(new WarehouseTransactionExport, 'kho_nhap_xuat_' . now()->format('Ymd') . '.xlsx');
    }

    public function template()
    {
        return Excel::download(new WarehouseTransactionTemplateExport, 'mau_nhap_xuat_kho.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:5120',
        ]);

        $import = new WarehouseTransactionImport;
        Excel::import($import, $request->file('file'));

        return redirect()->route('admin.warehouse-transactions.index')
            ->with('success', "Import thành công {$import->getCount()} giao dịch kho.");
    }
}
