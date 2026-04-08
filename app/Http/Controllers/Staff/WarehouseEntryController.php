<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\WarehouseTransaction;
use Illuminate\Http\Request;

class WarehouseEntryController extends Controller
{
    /**
     * Trang chủ nhân viên kho — chọn lệnh SX.
     */
    public function index(Request $request)
    {
        $lenhSx = $request->lenh_sx;
        $items = collect();

        if ($lenhSx) {
            $orders = Order::where('lenh_sanxuat', $lenhSx)->get();

            $items = $orders->map(function ($order) {
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
                ];
            });
        }

        $danhSachLenh = Order::whereNotNull('lenh_sanxuat')
            ->where('lenh_sanxuat', '!=', '')
            ->where('status', '!=', 'shipped')
            ->distinct()->pluck('lenh_sanxuat');

        return view('staff.warehouse.index', compact('items', 'lenhSx', 'danhSachLenh'));
    }

    /**
     * Xử lý nhập kho hàng loạt.
     */
    public function store(Request $request)
    {
        $request->validate([
            'lenh_sx'         => 'required|string|max:100',
            'ngay'            => 'required|date',
            'rows'            => 'required|array|min:1',
            'rows.*.ma_hh'    => 'required|string',
            'rows.*.mau'      => 'nullable|string',
            'rows.*.size'     => 'nullable|string',
            'rows.*.so_luong' => 'nullable|numeric|min:0',
        ]);

        $maNv  = $request->user()->name;
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
                'ma_nv'     => $maNv,
                'lenh_sx'   => $request->lenh_sx,
                'note'      => 'Nhập kho theo lệnh SX',
            ]);
            $count++;
        }

        return redirect()
            ->route('staff.warehouse.index', ['lenh_sx' => $request->lenh_sx])
            ->with('success', "Đã nhập kho {$count} mục.");
    }

    /**
     * Lịch sử nhập kho của nhân viên hiện tại.
     */
    public function history(Request $request)
    {
        $data = WarehouseTransaction::where('ma_nv', $request->user()->name)
            ->nhapKho()
            ->latest()
            ->paginate(20);

        return view('staff.warehouse.history', compact('data'));
    }
}
