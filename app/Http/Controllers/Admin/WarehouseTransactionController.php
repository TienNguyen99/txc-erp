<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DanhMucHangHoa;
use App\Models\WarehouseTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class WarehouseTransactionController extends Controller
{
    public function index(Request $request)
    {
        $data = WarehouseTransaction::when($request->search, fn($q, $s) => $q->where('lenh_sx', 'like', "%$s%")->orWhere('ma_nv', 'like', "%$s%"))
                    ->when($request->cong_doan, fn($q, $cd) => $q->where('cong_doan', $cd))
                    ->latest()->paginate(15)->withQueryString();
        return view('admin.warehouse-transactions.index', compact('data'));
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
        $thang = $request->thang ?? now()->month;
        $nam   = $request->nam   ?? now()->year;

        $startOfMonth = Carbon::create($nam, $thang, 1)->startOfMonth();

        // Tồn đầu tháng = Nhập - Xuất TRƯỚC ngày 1 tháng này
        $tonDau = WarehouseTransaction::select(
                'mau', 'size',
                DB::raw("SUM(CASE WHEN cong_doan='NHAPKHO' THEN so_luong ELSE -so_luong END) as ton_dau")
            )
            ->where('ngay', '<', $startOfMonth)
            ->groupBy('mau', 'size')
            ->get()->keyBy(fn($r) => $r->mau . '_' . $r->size);

        // Nhập trong tháng
        $nhapThang = WarehouseTransaction::select(
                'mau', 'size',
                DB::raw("SUM(so_luong) as tong_nhap")
            )
            ->where('cong_doan', 'NHAPKHO')
            ->whereMonth('ngay', $thang)
            ->whereYear('ngay', $nam)
            ->groupBy('mau', 'size')
            ->get()->keyBy(fn($r) => $r->mau . '_' . $r->size);

        // Xuất trong tháng
        $xuatThang = WarehouseTransaction::select(
                'mau', 'size',
                DB::raw("SUM(so_luong) as tong_xuat")
            )
            ->where('cong_doan', 'XUATKHO')
            ->whereMonth('ngay', $thang)
            ->whereYear('ngay', $nam)
            ->groupBy('mau', 'size')
            ->get()->keyBy(fn($r) => $r->mau . '_' . $r->size);

        // Gộp tất cả keys
        $allKeys = collect($tonDau->keys())
            ->merge($nhapThang->keys())
            ->merge($xuatThang->keys())
            ->unique()->sort();

        $tonKho = $allKeys->map(function ($key) use ($tonDau, $nhapThang, $xuatThang) {
            [$mau, $size] = explode('_', $key, 2);
            $tonDauVal  = $tonDau[$key]->ton_dau   ?? 0;
            $nhapVal    = $nhapThang[$key]->tong_nhap ?? 0;
            $xuatVal    = $xuatThang[$key]->tong_xuat ?? 0;
            $tonCuoi    = $tonDauVal + $nhapVal - $xuatVal;

            return [
                'mau'      => $mau,
                'size'     => $size,
                'ton_dau'  => $tonDauVal,
                'nhap'     => $nhapVal,
                'xuat'     => $xuatVal,
                'ton_cuoi' => $tonCuoi,
            ];
        });

        return view('warehouse.tonkho', compact(
            'tonKho', 'thang', 'nam'
        ));
    }
}
