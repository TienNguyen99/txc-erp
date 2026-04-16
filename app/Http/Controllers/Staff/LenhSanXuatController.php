<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\OrderTracking;
use App\Models\ProductionReport;
use App\Models\WarehouseTransaction;
use App\Models\DanhMucHangHoa;
use Illuminate\Http\Request;

class LenhSanXuatController extends Controller
{
    /**
     * Danh sách lệnh con trong 1 lot — công nhân quét QR dẫn đến đây.
     * URL: /lenh-sx/{trackingNumber}
     */
    public function index(string $trackingNumber)
    {
        // Lấy tất cả tracking thuộc lot này
        $trackings = OrderTracking::with('order')
            ->where('tracking_number', $trackingNumber)
            ->get();

        if ($trackings->isEmpty()) {
            return view('staff.lenh-sx.not-found', compact('trackingNumber'));
        }

        // Gom theo tracking_number_child (mỗi tracking_number_child = 1 lệnh con)
        $children = $trackings->groupBy('tracking_number_child')->map(function ($group, $childCode) use ($trackingNumber) {
            $maHhList = $group->map(fn($t) => $t->order->ma_hh ?? $t->size)->unique()->values();
            $maHh = $maHhList->first();
            $hangHoa = $maHh ? DanhMucHangHoa::where('ma_hh', $maHh)->first() : null;

            // Tổng SL đơn hàng
            $totalSlDonHang = $group->sum('sl_don_hang');

            // Lấy lịch sử SX cho lệnh con này
            $totalSlDat = ProductionReport::where('lenh_sx', $childCode)->sum('sl_dat');

            // Tồn kho
            $nhap = $maHh ? WarehouseTransaction::where('ma_hh', $maHh)->nhapKho()->sum('so_luong') : 0;
            $xuat = $maHh ? WarehouseTransaction::where('ma_hh', $maHh)->xuatKho()->sum('so_luong') : 0;
            $tonKho = $nhap - $xuat;

            // Progress
            $progress = $totalSlDonHang > 0 ? min(100, round($totalSlDat / $totalSlDonHang * 100)) : 0;

            // Công đoạn hiện tại
            $congDoan = $group->pluck('cong_doan')->unique()->first();

            return (object) [
                'tracking_number_child' => $childCode,
                'ma_hh' => $maHh,
                'ten_hh' => $hangHoa?->ten_hh ?? '',
                'hinh_anh' => $hangHoa?->hinh_anh ?? '',
                'mau' => $group->pluck('mau')->unique()->filter()->implode(', '),
                'so_po' => $group->count(),
                'tong_sl' => $totalSlDonHang,
                'sl_da_sx' => $totalSlDat,
                'ton_kho' => $tonKho,
                'progress' => $progress,
                'cong_doan' => $congDoan,
                'da_tao_lenh_sx' => $group->first()->da_tao_lenh_sx,
            ];
        })->filter(fn($item) => !empty($item->tracking_number_child))->values();

        return view('staff.lenh-sx.index', compact('trackingNumber', 'children'));
    }

    /**
     * Chi tiết lệnh con — form nhập công đoạn + số lượng.
     * URL: /lenh-sx/{trackingNumber}/{stt}
     */
    public function scan(string $trackingNumber, int $stt)
    {
        $lenhSx = $trackingNumber . '/' . $stt;

        // Lấy các tracking thuộc lệnh con này
        $trackings = OrderTracking::with('order')
            ->where('tracking_number_child', $lenhSx)
            ->get();

        $maHh = null;
        $mau = null;

        if ($trackings->isNotEmpty()) {
            $maHh = $trackings->first()->order->ma_hh ?? $trackings->first()->size;
            $mau = $trackings->pluck('mau')->unique()->filter()->implode(', ');
        }

        // Lấy thông tin hàng hóa
        $hangHoa = $maHh ? DanhMucHangHoa::where('ma_hh', $maHh)->first() : null;

        // Tìm production report theo lenh_sx
        $reports = ProductionReport::where('lenh_sx', $lenhSx)->get();

        // Lịch sử báo cáo SX cho lệnh này
        $history = ProductionReport::where('lenh_sx', $lenhSx)
            ->orderByDesc('created_at')
            ->get();

        // Lịch sử nhập kho cho lệnh này
        $warehouseHistory = WarehouseTransaction::where('lenh_sx', 'like', "%{$lenhSx}%")
            ->orderByDesc('created_at')
            ->get();

        // Tổng SL
        $totalSlDat = $history->sum('sl_dat');
        $totalSlHu = $history->sum('sl_hu');
        $totalNhapKho = $warehouseHistory->where('cong_doan', 'NHAPKHO')->sum('so_luong');
        $totalSlDonHang = $trackings->sum('sl_don_hang');

        // Tồn kho hiện tại
        $tonKho = 0;
        if ($maHh) {
            $nhap = WarehouseTransaction::where('ma_hh', $maHh)->nhapKho()->sum('so_luong');
            $xuat = WarehouseTransaction::where('ma_hh', $maHh)->xuatKho()->sum('so_luong');
            $tonKho = $nhap - $xuat;
        }

        return view('staff.lenh-sx.scan', compact(
            'lenhSx',
            'trackingNumber',
            'stt',
            'reports',
            'hangHoa',
            'maHh',
            'mau',
            'history',
            'warehouseHistory',
            'totalSlDat',
            'totalSlHu',
            'totalNhapKho',
            'totalSlDonHang',
            'tonKho',
            'trackings'
        ));
    }

    /**
     * Ghi báo cáo sản xuất từ QR scan.
     */
    public function report(Request $request, string $trackingNumber, int $stt)
    {
        $lenhSx = $trackingNumber . '/' . $stt;

        $request->validate([
            'cong_doan' => 'required|string',
            'sl_dat'    => 'required|numeric|min:0',
            'sl_hu'     => 'nullable|numeric|min:0',
            'ca'        => 'nullable|string',
            'ma_nv'     => 'nullable|string|max:100',
        ]);

        // Lấy ma_hh từ tracking_number_child
        $tracking = OrderTracking::with('order')
            ->where('tracking_number_child', $lenhSx)->first();
        $maHh = $tracking?->order?->ma_hh ?? $tracking?->size ?? $request->input('ma_hh', '');
        $mau = $tracking?->mau ?? '';

        ProductionReport::create([
            'cong_doan'  => $request->cong_doan,
            'ngay_sx'    => now()->toDateString(),
            'ca'         => $request->ca ?? '1',
            'ma_nv'      => $request->ma_nv ?? '',
            'lenh_sx'    => $lenhSx,
            'mau'        => $mau,
            'size'       => $maHh,
            'sl_dat'     => $request->sl_dat,
            'sl_hu'      => $request->sl_hu ?? 0,
        ]);

        return redirect()->route('lenh-sx.scan', [$trackingNumber, $stt])
            ->with('success', "Đã ghi báo cáo SX: SL đạt {$request->sl_dat}" .
                ($request->sl_hu > 0 ? ", SL hư {$request->sl_hu}" : ''));
    }

    /**
     * Ghi nhập kho từ QR scan.
     */
    public function nhapKho(Request $request, string $trackingNumber, int $stt)
    {
        $lenhSx = $trackingNumber . '/' . $stt;

        $request->validate([
            'so_luong' => 'required|numeric|min:0.01',
            'ma_nv'    => 'nullable|string|max:100',
        ]);

        // Lấy ma_hh từ tracking_number_child
        $tracking = OrderTracking::with('order')
            ->where('tracking_number_child', $lenhSx)->first();
        $maHh = $tracking?->order?->ma_hh ?? $tracking?->size ?? $request->input('ma_hh', '');

        if (empty($maHh)) {
            return redirect()->back()->with('error', 'Không tìm thấy mã hàng hóa cho lệnh này.');
        }

        WarehouseTransaction::create([
            'cong_doan' => 'NHAPKHO',
            'ma_hh'     => $maHh,
            'ngay'      => now()->toDateString(),
            'size'      => $tracking?->size ?? null,
            'mau'       => $tracking?->mau ?? null,
            'so_luong'  => $request->so_luong,
            'ma_nv'     => $request->ma_nv ?? '',
            'lenh_sx'   => $lenhSx,
            'note'      => 'Nhập kho qua QR scan',
        ]);

        return redirect()->route('lenh-sx.scan', [$trackingNumber, $stt])
            ->with('success', "Đã nhập kho {$request->so_luong} cho mã {$maHh}.");
    }
}
