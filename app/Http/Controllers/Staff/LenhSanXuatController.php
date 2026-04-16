<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\LenhSanXuat;
use App\Models\LenhSanXuatItem;
use App\Models\ProductionReport;
use App\Models\WarehouseTransaction;
use App\Models\DanhMucHangHoa;
use Illuminate\Http\Request;

class LenhSanXuatController extends Controller
{
    /**
     * Danh sách lệnh con — công nhân quét QR dẫn đến đây.
     * URL: /lenh-sx/{lenhSo}
     */
    public function index(string $lenhSo)
    {
        // Tìm lệnh SX
        $lenh = LenhSanXuat::where('lenh_so', $lenhSo)->first();

        if (!$lenh) {
            return view('staff.lenh-sx.not-found', ['trackingNumber' => $lenhSo]);
        }

        // Lấy items đã lên lệnh
        $children = $lenh->items()->where('da_len_lenh', true)->get()->map(function ($item) {
            $hangHoa = DanhMucHangHoa::where('ma_hh', $item->ma_hh)->first();
            $totalSlDat = ProductionReport::where('lenh_sx', $item->lenh_child)->sum('sl_dat');

            $nhap = WarehouseTransaction::where('ma_hh', $item->ma_hh)->nhapKho()->sum('so_luong');
            $xuat = WarehouseTransaction::where('ma_hh', $item->ma_hh)->xuatKho()->sum('so_luong');
            $tonKho = $nhap - $xuat;

            $progress = $item->tong_yrd > 0 ? min(100, round($totalSlDat / $item->tong_yrd * 100)) : 0;

            return (object) [
                'lenh_child' => $item->lenh_child,
                'stt' => $item->stt,
                'ma_hh' => $item->ma_hh,
                'ten_hh' => $hangHoa?->ten_hh ?? $item->ten_hh,
                'hinh_anh' => $hangHoa?->hinh_anh ?? '',
                'mau' => $item->mau,
                'tong_sl' => $item->tong_yrd,
                'sl_can_sx' => $item->sl_can_sx,
                'sl_da_sx' => $totalSlDat,
                'ton_kho' => $tonKho,
                'progress' => $progress,
            ];
        });

        $trackingNumber = $lenhSo; // for view compatibility

        return view('staff.lenh-sx.index', compact('trackingNumber', 'children'));
    }

    /**
     * Chi tiết lệnh con — form nhập công đoạn + số lượng.
     * URL: /lenh-sx/{lenhSo}/{stt}
     */
    public function scan(string $trackingNumber, int $stt)
    {
        $lenhSx = $trackingNumber . '/' . $stt;

        // Tìm item từ bảng lenh_san_xuat_items
        $item = LenhSanXuatItem::where('lenh_child', $lenhSx)->first();

        $maHh = $item?->ma_hh ?? null;
        $mau = $item?->mau ?? null;

        // Lấy thông tin hàng hóa
        $hangHoa = $maHh ? DanhMucHangHoa::where('ma_hh', $maHh)->first() : null;

        // Lịch sử SX
        $reports = ProductionReport::where('lenh_sx', $lenhSx)->get();
        $history = ProductionReport::where('lenh_sx', $lenhSx)
            ->orderByDesc('created_at')->get();

        // Lịch sử nhập kho
        $warehouseHistory = WarehouseTransaction::where('lenh_sx', 'like', "%{$lenhSx}%")
            ->orderByDesc('created_at')->get();

        // Tổng SL
        $totalSlDat = $history->sum('sl_dat');
        $totalSlHu = $history->sum('sl_hu');
        $totalNhapKho = $warehouseHistory->where('cong_doan', 'NHAPKHO')->sum('so_luong');
        $totalSlDonHang = $item?->tong_yrd ?? 0;
        $slCanSx = $item?->sl_can_sx ?? 0;

        // Tồn kho
        $tonKho = 0;
        if ($maHh) {
            $nhap = WarehouseTransaction::where('ma_hh', $maHh)->nhapKho()->sum('so_luong');
            $xuat = WarehouseTransaction::where('ma_hh', $maHh)->xuatKho()->sum('so_luong');
            $tonKho = $nhap - $xuat;
        }

        return view('staff.lenh-sx.scan', compact(
            'lenhSx', 'trackingNumber', 'stt',
            'reports', 'hangHoa', 'maHh', 'mau',
            'history', 'warehouseHistory',
            'totalSlDat', 'totalSlHu', 'totalNhapKho',
            'totalSlDonHang', 'slCanSx', 'tonKho'
        ));
    }

    /**
     * Ghi báo cáo sản xuất.
     */
    public function report(Request $request, string $trackingNumber, int $stt)
    {
        $lenhSx = $trackingNumber . '/' . $stt;

        $request->validate([
            'cong_doan' => 'required|string',
            'sl_dat' => 'required|numeric|min:0',
            'sl_hu' => 'nullable|numeric|min:0',
            'ca' => 'nullable|string',
            'ma_nv' => 'nullable|string|max:100',
        ]);

        $item = LenhSanXuatItem::where('lenh_child', $lenhSx)->first();
        $maHh = $item?->ma_hh ?? $request->input('ma_hh', '');
        $mau = $item?->mau ?? '';

        ProductionReport::create([
            'cong_doan' => $request->cong_doan,
            'ngay_sx' => now()->toDateString(),
            'ca' => $request->ca ?? '1',
            'ma_nv' => $request->ma_nv ?? '',
            'lenh_sx' => $lenhSx,
            'mau' => $mau,
            'size' => $maHh,
            'sl_dat' => $request->sl_dat,
            'sl_hu' => $request->sl_hu ?? 0,
        ]);

        return redirect()->route('lenh-sx.scan', [$trackingNumber, $stt])
            ->with('success', "Đã ghi báo cáo SX: SL đạt {$request->sl_dat}" .
                ($request->sl_hu > 0 ? ", SL hư {$request->sl_hu}" : ''));
    }

    /**
     * Ghi nhập kho.
     */
    public function nhapKho(Request $request, string $trackingNumber, int $stt)
    {
        $lenhSx = $trackingNumber . '/' . $stt;

        $request->validate([
            'so_luong' => 'required|numeric|min:0.01',
            'ma_nv' => 'nullable|string|max:100',
        ]);

        $item = LenhSanXuatItem::where('lenh_child', $lenhSx)->first();
        $maHh = $item?->ma_hh ?? $request->input('ma_hh', '');

        if (empty($maHh)) {
            return redirect()->back()->with('error', 'Không tìm thấy mã hàng hóa cho lệnh này.');
        }

        WarehouseTransaction::create([
            'cong_doan' => 'NHAPKHO',
            'ma_hh' => $maHh,
            'ngay' => now()->toDateString(),
            'size' => $item?->ma_hh ?? null,
            'mau' => $item?->mau ?? null,
            'so_luong' => $request->so_luong,
            'ma_nv' => $request->ma_nv ?? '',
            'lenh_sx' => $lenhSx,
            'note' => 'Nhập kho qua QR scan',
        ]);

        return redirect()->route('lenh-sx.scan', [$trackingNumber, $stt])
            ->with('success', "Đã nhập kho {$request->so_luong} cho mã {$maHh}.");
    }
}
