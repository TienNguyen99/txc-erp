<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderTracking;
use App\Models\ProductionReport;
use App\Models\WarehouseTransaction;
use Illuminate\Http\Request;

class OrderTrackingController extends Controller
{
    public function index(Request $request)
    {
        // Lấy danh sách PL Number và Chart để filter
        $plNumbers = Order::whereNotNull('pl_number')->where('pl_number', '!=', '')
                         ->distinct()->pluck('pl_number');
        $charts    = Order::whereNotNull('chart')->where('chart', '!=', '')
                         ->distinct()->pluck('chart');

        // Lọc orders theo PL Number hoặc Chart
        $plFilter = array_filter((array) $request->input('pl_number', []));
        $hasFilter = !empty($plFilter) || $request->filled('chart');

        // Dashboard chỉ hiện khi có filter PL/Chart (= chọn lô cần đi)
        $summary = collect();
        $stats = (object) [
            'total_mahh' => 0, 'du_hang' => 0, 'thieu_hang' => 0, 'dang_sx' => 0,
            'tong_can_giao' => 0, 'tong_ton_kho' => 0, 'tong_dang_sx' => 0, 'tong_thieu' => 0,
        ];

        if ($hasFilter) {
            $orders = Order::query()
                ->when(!empty($plFilter), fn($q) => $q->whereIn('pl_number', $plFilter))
                ->when($request->chart, fn($q, $v) => $q->where('chart', $v))
                ->get();

            // ═══ DASHBOARD: Tổng hợp theo ma_hh ═══
            $summary = $orders->groupBy('ma_hh')->map(function ($group, $maHh) {
                $totalQty = $group->sum('yrd');
                $orderIds = $group->pluck('id');

                $trackings = OrderTracking::whereIn('order_id', $orderIds)->get();
                $stageBreakdown = collect(OrderTracking::STAGES)->mapWithKeys(function ($info, $stage) use ($trackings) {
                    return [$stage => $trackings->where('cong_doan', $stage)->sum('sl_don_hang')];
                });

                $slProduction = ProductionReport::where('size', $maHh)
                    ->where('cong_doan', '!=', 'Đã nhập kho')
                    ->sum('sl_dat');

                $slProducedDone = ProductionReport::where('size', $maHh)
                    ->where('cong_doan', 'Đã nhập kho')
                    ->sum('sl_dat');

                $nhap = WarehouseTransaction::where('ma_hh', $maHh)->nhapKho()->sum('so_luong');
                $xuat = WarehouseTransaction::where('ma_hh', $maHh)->xuatKho()->sum('so_luong');
                $tonKho = $nhap - $xuat;
                $thieu = max(0, $totalQty - $tonKho);

                $totalProgress = $totalQty > 0
                    ? min(100, round(($tonKho + $slProduction) / $totalQty * 100))
                    : 0;

                return (object) [
                    'ma_hh'           => $maHh,
                    'so_don'          => $group->count(),
                    'tong_qty'        => $totalQty,
                    'sl_production'   => $slProduction,
                    'sl_produced_done'=> $slProducedDone,
                    'sl_warehouse'    => $nhap,
                    'ton_kho'         => $tonKho,
                    'thieu'           => $thieu,
                    'du_hang'         => $tonKho >= $totalQty,
                    'stage_breakdown' => $stageBreakdown,
                    'order_ids'       => $orderIds->toArray(),
                    'total_progress'  => $totalProgress,
                ];
            })->values();

            $stats = (object) [
                'total_mahh'   => $summary->count(),
                'du_hang'      => $summary->where('du_hang', true)->count(),
                'thieu_hang'   => $summary->where('du_hang', false)->count(),
                'dang_sx'      => $summary->where('sl_production', '>', 0)->count(),
                'tong_can_giao'=> $summary->sum('tong_qty'),
                'tong_ton_kho' => $summary->sum('ton_kho'),
                'tong_dang_sx' => $summary->sum('sl_production'),
                'tong_thieu'   => $summary->sum('thieu'),
            ];
        }

        // Danh sách tracking (phân trang)
        $data = OrderTracking::with('order')
                    ->when(!empty($plFilter), fn($q) => $q->whereHas('order', fn($oq) => $oq->whereIn('pl_number', $plFilter)))
                    ->when($request->chart, fn($q, $v) => $q->whereHas('order', fn($oq) => $oq->where('chart', $v)))
                    ->when($request->search, fn($q, $s) => $q->where(function ($sub) use ($s) {
                        $sub->where('pl_number', 'like', "%$s%")
                            ->orWhere('mau', 'like', "%$s%")
                            ->orWhere('size', 'like', "%$s%");
                    }))
                    ->latest()->paginate(15)->withQueryString();

        $allOrders = Order::pluck('job_no', 'id');
        $stages = OrderTracking::STAGES;

        return view('admin.order-tracking.index', compact(
            'data', 'allOrders', 'plNumbers', 'charts',
            'summary', 'hasFilter', 'stages', 'stats'
        ));
    }

    public function create()
    {
        $orders = Order::pluck('job_no', 'id');
        return view('admin.order-tracking.form', compact('orders'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_id'     => 'required|exists:orders,id',
            'pl_number'    => 'nullable|string',
            'size'         => 'nullable|string',
            'mau'          => 'nullable|string',
            'kich'         => 'nullable|string',
            'cong_doan'    => 'nullable|string',
            'sl_don_hang'  => 'nullable|numeric',
            'sl_san_xuat'  => 'nullable|numeric',
        ]);
        $tracking = OrderTracking::create($validated);
        $tracking->order->updateStatusFromTracking();
        return redirect()->route('admin.order-tracking.index')->with('success', 'Thêm tracking thành công.');
    }

    public function edit(OrderTracking $orderTracking)
    {
        $orders = Order::pluck('job_no', 'id');
        return view('admin.order-tracking.form', compact('orderTracking', 'orders'));
    }

    public function update(Request $request, OrderTracking $orderTracking)
    {
        $validated = $request->validate([
            'order_id'     => 'required|exists:orders,id',
            'pl_number'    => 'nullable|string',
            'size'         => 'nullable|string',
            'mau'          => 'nullable|string',
            'kich'         => 'nullable|string',
            'cong_doan'    => 'nullable|string',
            'sl_don_hang'  => 'nullable|numeric',
            'sl_san_xuat'  => 'nullable|numeric',
        ]);
        $orderTracking->update($validated);
        $orderTracking->order->updateStatusFromTracking();
        return redirect()->route('admin.order-tracking.index')->with('success', 'Cập nhật tracking thành công.');
    }

    public function destroy(OrderTracking $orderTracking)
    {
        $orderTracking->delete();
        return redirect()->route('admin.order-tracking.index')->with('success', 'Xóa tracking thành công.');
    }

    /**
     * Xóa hàng loạt tracking.
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'tracking_ids'   => 'required|array',
            'tracking_ids.*' => 'exists:order_tracking,id',
        ]);

        $count = OrderTracking::whereIn('id', $request->tracking_ids)->delete();

        return redirect()->back()->with('success', "Đã xóa {$count} tracking.");
    }

    /**
     * Tự động tạo tracking rows từ orders đã filter.
     * Mỗi order tạo 1 tracking row với thông tin từ order.
     */
    public function generate(Request $request)
    {
        $request->validate([
            'order_ids'   => 'required|array',
            'order_ids.*' => 'exists:orders,id',
        ]);

        $count = 0;
        foreach ($request->order_ids as $orderId) {
            $order = Order::find($orderId);
            if (!$order) continue;

            // Kiểm tra đã có tracking cho order này chưa
            $exists = OrderTracking::where('order_id', $orderId)->exists();
            if ($exists) continue;

            OrderTracking::create([
                'order_id'    => $order->id,
                'pl_number'   => $order->pl_number,
                'size'        => $order->ma_hh,
                'mau'         => $order->color,
                'cong_doan'   => 'Chờ sản xuất',
                'sl_don_hang' => $order->yrd,
                'sl_san_xuat' => 0,
            ]);

            // Tự động cập nhật status đơn hàng
            $order->updateStatusFromTracking();
            $count++;
        }

        return redirect()->back()->with('success', "Đã tạo {$count} tracking mới từ đơn hàng.");
    }

    /**
     * Chuyển tracking sang Production Report — gộp theo mã HH.
     */
    public function pushToProduction(Request $request)
    {
        $request->validate([
            'tracking_ids'   => 'required|array',
            'tracking_ids.*' => 'exists:order_tracking,id',
        ]);

        $trackings = OrderTracking::with('order')
            ->whereIn('id', $request->tracking_ids)
            ->get();

        // Gộp theo ma_hh (từ order)
        $grouped = $trackings->groupBy(fn($t) => $t->order->ma_hh ?? $t->size);

        $countGroup = 0;
        foreach ($grouped as $maHh => $group) {
            $totalSlSanXuat = $group->sum('sl_san_xuat');
            $totalSlDonHang = $group->sum('sl_don_hang');

            $mauList = $group->pluck('mau')->unique()->filter()->implode(', ');
            $lenhSxList = $group->map(fn($t) => $t->order->lenh_sanxuat ?? $t->order->job_no)
                                ->unique()->filter()->implode(', ');

            ProductionReport::create([
                'cong_doan'   => $group->first()->cong_doan,
                'ngay_sx'     => now()->toDateString(),
                'ca'          => '1',
                'lenh_sx'     => $lenhSxList,
                'mau'         => $mauList,
                'size'        => $maHh,
                'sl_dat'      => $totalSlSanXuat > 0 ? $totalSlSanXuat : $totalSlDonHang,
                'sl_hu'       => 0,
            ]);

            // Cập nhật tất cả tracking trong nhóm
            foreach ($group as $tracking) {
                $tracking->update(['cong_doan' => 'Đã chuyển SX']);
                $tracking->order->updateStatusFromTracking();
            }
            $countGroup++;
        }

        return redirect()->back()->with('success',
            "Đã gộp {$trackings->count()} tracking thành {$countGroup} lệnh SX theo mã HH.");
    }

    /**
     * Chuyển từ Production sang Warehouse (nhập kho).
     */
    public function pushToWarehouse(Request $request)
    {
        $request->validate([
            'tracking_ids'   => 'required|array',
            'tracking_ids.*' => 'exists:order_tracking,id',
        ]);

        $trackings = OrderTracking::with('order')
            ->whereIn('id', $request->tracking_ids)
            ->where('sl_san_xuat', '>', 0)
            ->get();

        // Gộp theo ma_hh
        $grouped = $trackings->groupBy(fn($t) => $t->order->ma_hh ?? $t->size);

        $countGroup = 0;
        foreach ($grouped as $maHh => $group) {
            $totalSl = $group->sum('sl_san_xuat');
            $mauList = $group->pluck('mau')->unique()->filter()->implode(', ');
            $lenhSxList = $group->map(fn($t) => $t->order->lenh_sanxuat ?? $t->order->job_no)
                                ->unique()->filter()->implode(', ');
            $jobNos = $group->map(fn($t) => $t->order->job_no)->unique()->filter()->implode(', ');

            WarehouseTransaction::create([
                'cong_doan' => 'NHAPKHO',
                'ma_hh'     => $maHh,
                'ngay'      => now()->toDateString(),
                'size'      => $group->first()->kich ?? $group->first()->size,
                'mau'       => $mauList,
                'so_luong'  => $totalSl,
                'lenh_sx'   => $lenhSxList,
                'note'      => "Gộp {$group->count()} tracking - Orders: {$jobNos}",
            ]);

            // Cập nhật tất cả tracking trong nhóm
            foreach ($group as $tracking) {
                $tracking->update(['cong_doan' => 'Đã nhập kho']);
                $tracking->order->updateStatusFromTracking();
            }
            $countGroup++;
        }

        return redirect()->back()->with('success',
            "Đã gộp {$trackings->count()} tracking thành {$countGroup} phiếu nhập kho theo mã HH.");
    }

    /**
     * Xuất kho giao hàng — dùng khi ma_hh đã đủ tồn kho.
     * Tạo XUATKHO transaction + cập nhật order → shipped.
     */
    public function shipFromStock(Request $request)
    {
        $request->validate([
            'order_ids'   => 'required|array',
            'order_ids.*' => 'exists:orders,id',
        ]);

        $shipped = 0;
        $errors  = [];

        foreach ($request->order_ids as $orderId) {
            $order = Order::find($orderId);
            if (!$order || !$order->ma_hh || $order->status === 'shipped') continue;

            // Tính tồn kho hiện tại cho ma_hh này
            $nhap   = WarehouseTransaction::where('ma_hh', $order->ma_hh)->nhapKho()->sum('so_luong');
            $xuat   = WarehouseTransaction::where('ma_hh', $order->ma_hh)->xuatKho()->sum('so_luong');
            $tonKho = $nhap - $xuat;
            $canXuat = $order->yrd ?? 0;

            if ($tonKho < $canXuat) {
                $errors[] = "{$order->job_no}: tồn kho ({$tonKho}) < cần ({$canXuat})";
                continue;
            }

            // Tạo phiếu xuất kho
            WarehouseTransaction::create([
                'cong_doan' => 'XUATKHO',
                'ma_hh'     => $order->ma_hh,
                'ngay'      => now()->toDateString(),
                'size'      => $order->tracking()->first()->kich ?? null,
                'mau'       => $order->color,
                'so_luong'  => $canXuat,
                'lenh_sx'   => $order->lenh_sanxuat ?? $order->job_no,
                'note'      => "Xuất giao hàng - Order {$order->job_no} / FTY PO: {$order->fty_po}",
            ]);

            // Cập nhật order → shipped
            $order->update(['status' => 'shipped']);

            // Cập nhật tracking (nếu có) → Đã giao
            $order->tracking()->update(['cong_doan' => 'Đã giao']);

            $shipped++;
        }

        $msg = "Đã xuất kho giao hàng {$shipped} đơn.";
        if (count($errors) > 0) {
            $msg .= ' Lỗi: ' . implode('; ', array_slice($errors, 0, 5));
            return redirect()->back()->with('warning', $msg);
        }
        return redirect()->back()->with('success', $msg);
    }
}
