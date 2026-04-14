<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\ProductionReport;
use App\Models\WarehouseTransaction;
use App\Models\DanhMucHangHoa;
use Illuminate\Http\Request;

class LenhSanXuatController extends Controller
{
    /**
     * Trang quét QR — hiển thị thông tin lệnh SX.
     * Public route, không cần login.
     */
    public function scan(string $lenhSx)
    {
        // Tìm production report theo lenh_sx
        $reports = ProductionReport::where('lenh_sx', $lenhSx)->get();

        // Nếu không tìm thấy report, thử tìm từ tracking_number
        $trackingNumber = null;
        $maHh = null;
        if ($reports->isEmpty()) {
            // Parse lenh_sx: OT-XXXXXXXX-XXX/N → tracking = OT-XXXXXXXX-XXX
            if (str_contains($lenhSx, '/')) {
                $parts = explode('/', $lenhSx);
                $trackingNumber = $parts[0];
            }
        } else {
            $maHh = $reports->first()->size;
            $trackingNumber = str_contains($lenhSx, '/') ? explode('/', $lenhSx)[0] : null;
        }

        // Lấy thông tin hàng hóa
        $hangHoa = $maHh ? DanhMucHangHoa::where('ma_hh', $maHh)->first() : null;

        // Lịch sử báo cáo SX cho lệnh này
        $history = ProductionReport::where('lenh_sx', $lenhSx)
            ->orderByDesc('created_at')
            ->get();

        // Lịch sử nhập kho cho lệnh này
        $warehouseHistory = WarehouseTransaction::where('lenh_sx', 'like', "%{$lenhSx}%")
            ->orderByDesc('created_at')
            ->get();

        // Tổng SL đã SX / đã nhập kho
        $totalSlDat = $history->sum('sl_dat');
        $totalSlHu = $history->sum('sl_hu');
        $totalNhapKho = $warehouseHistory->where('cong_doan', 'NHAPKHO')->sum('so_luong');

        // Tồn kho hiện tại
        $tonKho = 0;
        if ($maHh) {
            $nhap = WarehouseTransaction::where('ma_hh', $maHh)->nhapKho()->sum('so_luong');
            $xuat = WarehouseTransaction::where('ma_hh', $maHh)->xuatKho()->sum('so_luong');
            $tonKho = $nhap - $xuat;
        }

        return view('staff.lenh-sx.scan', compact(
            'lenhSx',
            'reports',
            'hangHoa',
            'maHh',
            'trackingNumber',
            'history',
            'warehouseHistory',
            'totalSlDat',
            'totalSlHu',
            'totalNhapKho',
            'tonKho'
        ));
    }

    /**
     * Ghi báo cáo sản xuất từ QR scan.
     */
    public function report(Request $request, string $lenhSx)
    {
        $request->validate([
            'cong_doan' => 'required|string',
            'sl_dat'    => 'required|numeric|min:0',
            'sl_hu'     => 'nullable|numeric|min:0',
            'ca'        => 'nullable|string',
            'ma_nv'     => 'nullable|string|max:100',
        ]);

        // Lấy ma_hh từ production report có trước đó
        $existing = ProductionReport::where('lenh_sx', $lenhSx)->first();
        $maHh = $existing?->size ?? $request->input('ma_hh', '');

        ProductionReport::create([
            'cong_doan'  => $request->cong_doan,
            'ngay_sx'    => now()->toDateString(),
            'ca'         => $request->ca ?? '1',
            'ma_nv'      => $request->ma_nv ?? '',
            'lenh_sx'    => $lenhSx,
            'mau'        => $existing?->mau ?? '',
            'size'       => $maHh,
            'sl_dat'     => $request->sl_dat,
            'sl_hu'      => $request->sl_hu ?? 0,
        ]);

        return redirect()->route('lenh-sx.scan', $lenhSx)
            ->with('success', "Đã ghi báo cáo SX: SL đạt {$request->sl_dat}" .
                ($request->sl_hu > 0 ? ", SL hư {$request->sl_hu}" : ''));
    }

    /**
     * Ghi nhập kho từ QR scan.
     */
    public function nhapKho(Request $request, string $lenhSx)
    {
        $request->validate([
            'so_luong' => 'required|numeric|min:0.01',
            'ma_nv'    => 'nullable|string|max:100',
        ]);

        // Lấy ma_hh từ production report
        $existing = ProductionReport::where('lenh_sx', $lenhSx)->first();
        $maHh = $existing?->size ?? $request->input('ma_hh', '');

        if (empty($maHh)) {
            return redirect()->back()->with('error', 'Không tìm thấy mã hàng hóa cho lệnh này.');
        }

        WarehouseTransaction::create([
            'cong_doan' => 'NHAPKHO',
            'ma_hh'     => $maHh,
            'ngay'      => now()->toDateString(),
            'size'      => $existing?->size ?? null,
            'mau'       => $existing?->mau ?? null,
            'so_luong'  => $request->so_luong,
            'ma_nv'     => $request->ma_nv ?? '',
            'lenh_sx'   => $lenhSx,
            'note'      => 'Nhập kho qua QR scan',
        ]);

        return redirect()->route('lenh-sx.scan', $lenhSx)
            ->with('success', "Đã nhập kho {$request->so_luong} cho mã {$maHh}.");
    }
}
