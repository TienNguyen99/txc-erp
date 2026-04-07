<?php
// app/Http/Controllers/WarehouseTransactionController.php
namespace App\Http\Controllers;

use App\Models\WarehouseTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class WarehouseTransactionController extends Controller
{
    // =====================
    // DANH SÁCH GIAO DỊCH
    // =====================
    public function index(Request $request)
    {
        $query = WarehouseTransaction::query();

        // Lọc theo tháng
        if ($request->thang && $request->nam) {
            $query->whereMonth('ngay', $request->thang)
                  ->whereYear('ngay', $request->nam);
        } else {
            $query->thangNay();
        }

        // Lọc theo loại
        if ($request->cong_doan) {
            $query->where('cong_doan', $request->cong_doan);
        }

        // Lọc theo màu/size
        if ($request->mau)  $query->where('mau', $request->mau);
        if ($request->size) $query->where('size', $request->size);

        $transactions = $query->orderBy('ngay', 'desc')
                               ->orderBy('id', 'desc')
                               ->paginate(20);

        // Danh sách màu + size cho filter
        $danhSachMau  = WarehouseTransaction::select('mau')->distinct()->pluck('mau');
        $danhSachSize = WarehouseTransaction::select('size')->distinct()->pluck('size');

        return view('warehouse.index', compact(
            'transactions', 'danhSachMau', 'danhSachSize'
        ));
    }

    // =====================
    // FORM NHẬP LIỆU
    // =====================
    public function create()
    {
        $danhSachMau  = WarehouseTransaction::select('mau')->distinct()->pluck('mau');
        $danhSachSize = WarehouseTransaction::select('size')->distinct()->pluck('size');
        return view('warehouse.create', compact('danhSachMau', 'danhSachSize'));
    }

    // =====================
    // LƯU GIAO DỊCH
    // =====================
    public function store(Request $request)
    {
        $request->validate([
            'cong_doan' => 'required|in:NHAPKHO,XUATKHO',
            'ngay'      => 'required|date',
            'mau'       => 'required|string',
            'size'      => 'required|string',
            'so_luong'  => 'required|numeric|min:0.01',
            'ma_nv'     => 'nullable|string',
            'lenh_sx'   => 'nullable|string',
            'note'      => 'nullable|string',
        ]);

        WarehouseTransaction::create($request->all());

        return redirect()->route('warehouse.index')
                         ->with('success', 'Đã lưu giao dịch kho thành công!');
    }

    // =====================
    // SỬA GIAO DỊCH
    // =====================
    public function edit(WarehouseTransaction $warehouse)
    {
        $danhSachMau  = WarehouseTransaction::select('mau')->distinct()->pluck('mau');
        $danhSachSize = WarehouseTransaction::select('size')->distinct()->pluck('size');
        return view('warehouse.edit', compact('warehouse', 'danhSachMau', 'danhSachSize'));
    }

    public function update(Request $request, WarehouseTransaction $warehouse)
    {
        $request->validate([
            'cong_doan' => 'required|in:NHAPKHO,XUATKHO',
            'ngay'      => 'required|date',
            'mau'       => 'required|string',
            'size'      => 'required|string',
            'so_luong'  => 'required|numeric|min:0.01',
        ]);

        $warehouse->update($request->all());

        return redirect()->route('warehouse.index')
                         ->with('success', 'Đã cập nhật giao dịch!');
    }

    // =====================
    // XÓA GIAO DỊCH
    // =====================
    public function destroy(WarehouseTransaction $warehouse)
    {
        $warehouse->delete();
        return redirect()->route('warehouse.index')
                         ->with('success', 'Đã xóa giao dịch!');
    }

    // =====================
    // TỒN KHO (TRANG CHÍNH)
    // =====================
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

        return view('warehouse.ton-kho', compact(
            'tonKho', 'thang', 'nam'
        ));
    }
}