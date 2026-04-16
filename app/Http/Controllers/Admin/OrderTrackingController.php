<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Exports\LenhSanXuatExport;
use App\Models\Order;
use App\Models\OrderTracking;
use App\Models\ProductionReport;
use App\Models\WarehouseTransaction;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class OrderTrackingController extends Controller
{
    public function index(Request $request)
    {
        // Lấy danh sách PL Number và Chart để filter
        $plNumbers = Order::whereNotNull('pl_number')->where('pl_number', '!=', '')
            ->distinct()->pluck('pl_number');
        $charts = Order::whereNotNull('chart')->where('chart', '!=', '')
            ->distinct()->pluck('chart');

        // Lọc orders theo PL Number hoặc Chart
        $plFilter = array_filter((array) $request->input('pl_number', []));
        $chartFilter = array_filter((array) $request->input('chart', []));
        $hasFilter = !empty($plFilter) || !empty($chartFilter);

        // Dashboard chỉ hiện khi có filter PL/Chart (= chọn lô cần đi)
        $summary = collect();
        $stats = (object) [
            'total_mahh' => 0,
            'du_hang' => 0,
            'thieu_hang' => 0,
            'dang_sx' => 0,
            'tong_can_giao' => 0,
            'tong_ton_kho' => 0,
            'tong_dang_sx' => 0,
            'tong_thieu' => 0,
        ];

        if ($hasFilter) {
            $orders = Order::query()
                ->when(!empty($plFilter), fn($q) => $q->whereIn('pl_number', $plFilter))
                ->when(!empty($chartFilter), fn($q) => $q->whereIn('chart', $chartFilter))
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
                    'ma_hh' => $maHh,
                    'so_don' => $group->count(),
                    'tong_qty' => $totalQty,
                    'sl_production' => $slProduction,
                    'sl_produced_done' => $slProducedDone,
                    'sl_warehouse' => $nhap,
                    'ton_kho' => $tonKho,
                    'thieu' => $thieu,
                    'du_hang' => $tonKho >= $totalQty,
                    'stage_breakdown' => $stageBreakdown,
                    'order_ids' => $orderIds->toArray(),
                    'total_progress' => $totalProgress,
                ];
            })->values();

            $stats = (object) [
                'total_mahh' => $summary->count(),
                'du_hang' => $summary->where('du_hang', true)->count(),
                'thieu_hang' => $summary->where('du_hang', false)->count(),
                'dang_sx' => $summary->where('sl_production', '>', 0)->count(),
                'tong_can_giao' => $summary->sum('tong_qty'),
                'tong_ton_kho' => $summary->sum('ton_kho'),
                'tong_dang_sx' => $summary->sum('sl_production'),
                'tong_thieu' => $summary->sum('thieu'),
            ];
        }

        // Danh sách tracking (phân trang)
        $dataQuery = OrderTracking::with('order')
            ->when(!empty($plFilter), fn($q) => $q->whereHas('order', fn($oq) => $oq->whereIn('pl_number', $plFilter)))
            ->when(!empty($chartFilter), fn($q) => $q->whereHas('order', fn($oq) => $oq->whereIn('chart', $chartFilter)))
            ->when($request->search, fn($q, $s) => $q->where(function ($sub) use ($s) {
                $sub->where('pl_number', 'like', "%$s%")
                    ->orWhere('mau', 'like', "%$s%")
                    ->orWhere('size', 'like', "%$s%");
            }));

        if (auth()->check() && auth()->user()->isStaff()) {
            $dataQuery->where('da_tao_lenh_sx', true);
        }

        $data = $dataQuery->latest()->paginate(15)->withQueryString();

        $allOrders = Order::pluck('job_no', 'id');
        $stages = OrderTracking::STAGES;

        // Danh sách tracking numbers đã tạo
        $trackingNumbers = OrderTracking::whereNotNull('tracking_number')
            ->select('tracking_number')
            ->selectRaw('MIN(created_at) as created_at')
            ->selectRaw('COUNT(*) as total_items')
            ->groupBy('tracking_number')
            ->orderByDesc('created_at')
            ->get();

        return view('admin.order-tracking.index', compact(
            'data',
            'allOrders',
            'plNumbers',
            'charts',
            'summary',
            'hasFilter',
            'stages',
            'stats',
            'trackingNumbers'
        ));
    }

    public function show(OrderTracking $orderTracking)
    {
        if ($orderTracking->tracking_number) {
            return redirect()->route('admin.order-tracking.lot', $orderTracking->tracking_number);
        }
        return redirect()->route('admin.order-tracking.index');
    }

    /**
     * Tạo Order Tracking Number mới — gom nhiều PL Number thành 1 lô.
     */
    public function createBatch(Request $request)
    {
        $request->validate([
            'pl_numbers' => 'required|array|min:1',
            'pl_numbers.*' => 'required|string',
        ]);

        $plNumbers = $request->pl_numbers;
        $trackingNumber = OrderTracking::generateTrackingNumber();

        // Lấy tất cả orders thuộc các PL này
        $orders = Order::whereIn('pl_number', $plNumbers)->get();

        if ($orders->isEmpty()) {
            return redirect()->back()->with('error', 'Không tìm thấy đơn hàng nào cho các PL đã chọn.');
        }

        $count = 0;
        foreach ($orders as $order) {
            // Kiểm tra đã có tracking cho order này chưa
            $exists = OrderTracking::where('order_id', $order->id)->exists();
            if ($exists)
                continue;

            OrderTracking::create([
                'order_id' => $order->id,
                'tracking_number' => $trackingNumber,
                'pl_number' => $order->pl_number,
                'size' => $order->ma_hh,
                'mau' => $order->color,
                'cong_doan' => 'Chờ sản xuất',
                'sl_don_hang' => $order->yrd,
                'sl_san_xuat' => 0,
            ]);

            $order->updateStatusFromTracking();
            $count++;
        }

        // Nếu có tracking cũ chưa có tracking_number cho các PL này, gán luôn
        OrderTracking::whereNull('tracking_number')
            ->whereIn('pl_number', $plNumbers)
            ->update(['tracking_number' => $trackingNumber]);

        if ($count === 0) {
            // Tất cả order đã có tracking → tìm lot cũ để redirect
            $existingTracking = OrderTracking::whereIn('order_id', $orders->pluck('id'))
                ->whereNotNull('tracking_number')
                ->first();

            if ($existingTracking) {
                return redirect()->route('admin.order-tracking.lot', $existingTracking->tracking_number)
                    ->with('warning', "Tất cả đơn hàng đã thuộc lô: {$existingTracking->tracking_number}");
            }

            return redirect()->back()->with('error', 'Tất cả đơn hàng đã có tracking, không thể tạo lô mới.');
        }

        return redirect()->route('admin.order-tracking.lot', $trackingNumber)
            ->with('success', "Đã tạo {$count} tracking với Order Tracking Number: {$trackingNumber}");
    }

    /**
     * Xem chi tiết một lô theo Tracking Number.
     */
    public function lot(string $trackingNumber)
    {
        $stages = OrderTracking::STAGES;

        // Tất cả tracking của lô này
        $trackings = OrderTracking::with('order')
            ->where('tracking_number', $trackingNumber)
            ->latest()
            ->get();

        if ($trackings->isEmpty()) {
            return redirect()->route('admin.order-tracking.index')
                ->with('error', "Không tìm thấy Order Tracking: {$trackingNumber}");
        }

        // Các PL Numbers trong lô
        $plNumbersInLot = $trackings->pluck('pl_number')->unique()->filter()->values();

        // Tất cả orders thuộc lô
        $orderIds = $trackings->pluck('order_id')->unique();
        $orders = Order::whereIn('id', $orderIds)->get();

        // Tổng hợp theo mã HH
        $summary = $orders->groupBy('ma_hh')->map(function ($group, $maHh) use ($stages, $trackingNumber) {
            $totalQty = $group->sum('yrd');
            $groupOrderIds = $group->pluck('id');

            $trackingsForMa = OrderTracking::where('tracking_number', $trackingNumber)
                ->whereIn('order_id', $groupOrderIds)->get();
            $stageBreakdown = collect($stages)->mapWithKeys(function ($info, $stage) use ($trackingsForMa) {
                return [$stage => $trackingsForMa->where('cong_doan', $stage)->sum('sl_don_hang')];
            });

            $slProduction = ProductionReport::where('size', $maHh)
                ->where('cong_doan', '!=', 'Đã nhập kho')
                ->sum('sl_dat');
            $nhap = WarehouseTransaction::where('ma_hh', $maHh)->nhapKho()->sum('so_luong');
            $xuat = WarehouseTransaction::where('ma_hh', $maHh)->xuatKho()->sum('so_luong');
            $tonKho = $nhap - $xuat;
            $thieu = max(0, $totalQty - $tonKho);

            return (object) [
                'ma_hh' => $maHh,
                'so_don' => $group->count(),
                'tong_qty' => $totalQty,
                'sl_production' => $slProduction,
                'ton_kho' => $tonKho,
                'thieu' => $thieu,
                'du_hang' => $tonKho >= $totalQty,
                'stage_breakdown' => $stageBreakdown,
                'total_progress' => $totalQty > 0 ? min(100, round(($tonKho + $slProduction) / $totalQty * 100)) : 0,
            ];
        })->values()->sortBy('ma_hh')->values();

        // Gán số thứ tự lệnh con theo ma_hh tăng dần + cập nhật tracking_number_child cho từng PO
        $summary = $summary->map(function ($row, $index) use ($trackingNumber, $trackings) {
            $row->stt = $index + 1;
            $row->lenh_sx = $trackingNumber . '/' . ($index + 1);

            // Gán tracking_number_child cho tất cả tracking rows thuộc nhóm ma_hh này
            $trackingsInGroup = $trackings->filter(fn($t) => ($t->order->ma_hh ?? $t->size) === $row->ma_hh);
            foreach ($trackingsInGroup as $t) {
                if ($t->tracking_number_child !== $row->lenh_sx) {
                    $t->update(['tracking_number_child' => $row->lenh_sx]);
                }
            }

            return $row;
        });

        // Thống kê tổng
        $stats = (object) [
            'total_mahh' => $summary->count(),
            'du_hang' => $summary->where('du_hang', true)->count(),
            'thieu_hang' => $summary->where('du_hang', false)->count(),
            'dang_sx' => $summary->where('sl_production', '>', 0)->count(),
            'tong_can_giao' => $summary->sum('tong_qty'),
            'tong_ton_kho' => $summary->sum('ton_kho'),
            'tong_dang_sx' => $summary->sum('sl_production'),
            'tong_thieu' => $summary->sum('thieu'),
        ];

        // Danh sách tất cả Tracking Numbers (để chuyển lô nhanh)
        $allTrackingNumbers = OrderTracking::whereNotNull('tracking_number')
            ->distinct()->orderByDesc('tracking_number')->pluck('tracking_number');

        return view('admin.order-tracking.lot', compact(
            'trackingNumber',
            'plNumbersInLot',
            'orders',
            'trackings',
            'summary',
            'stats',
            'stages',
            'allTrackingNumbers'
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
            'order_id' => 'required|exists:orders,id',
            'pl_number' => 'nullable|string',
            'size' => 'nullable|string',
            'mau' => 'nullable|string',
            'kich' => 'nullable|string',
            'cong_doan' => 'nullable|string',
            'sl_don_hang' => 'nullable|numeric',
            'sl_san_xuat' => 'nullable|numeric',
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
            'order_id' => 'required|exists:orders,id',
            'pl_number' => 'nullable|string',
            'size' => 'nullable|string',
            'mau' => 'nullable|string',
            'kich' => 'nullable|string',
            'cong_doan' => 'nullable|string',
            'sl_don_hang' => 'nullable|numeric',
            'sl_san_xuat' => 'nullable|numeric',
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
            'tracking_ids' => 'required|array',
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
            'order_ids' => 'required|array',
            'order_ids.*' => 'exists:orders,id',
        ]);

        // Tạo tracking number cho batch này
        $trackingNumber = $request->input('tracking_number') ?: OrderTracking::generateTrackingNumber();

        $count = 0;
        foreach ($request->order_ids as $orderId) {
            $order = Order::find($orderId);
            if (!$order)
                continue;

            // Kiểm tra đã có tracking cho order này chưa
            $exists = OrderTracking::where('order_id', $orderId)->exists();
            if ($exists)
                continue;

            OrderTracking::create([
                'order_id' => $order->id,
                'tracking_number' => $trackingNumber,
                'pl_number' => $order->pl_number,
                'size' => $order->ma_hh,
                'mau' => $order->color,
                'cong_doan' => 'Chờ sản xuất',
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
            'tracking_ids' => 'required|array',
            'tracking_ids.*' => 'exists:order_tracking,id',
        ]);

        $trackings = OrderTracking::with('order')
            ->whereIn('id', $request->tracking_ids)
            ->get();

        // Gộp theo ma_hh (từ order)
        $grouped = $trackings->groupBy(fn($t) => $t->order->ma_hh ?? $t->size);


        $countGroup = 0;
        $stt = 1;
        // Lấy tracking_number tổng từ tracking đầu tiên
        $trackingNumber = $trackings->first()->tracking_number ?? null;
        // Sắp xếp theo ma_hh tăng dần trước khi gán số thứ tự
        $grouped = $grouped->sortKeys();
        foreach ($grouped as $maHh => $group) {
            $totalSlSanXuat = $group->sum('sl_san_xuat');
            $totalSlDonHang = $group->sum('sl_don_hang');

            $mauList = $group->pluck('mau')->unique()->filter()->implode(', ');
            // Sinh mã lệnh con: {tracking_number}/{stt} theo ma_hh tăng dần
            $lenhSx = $trackingNumber ? ($trackingNumber . '/' . $stt) : ('LENH/' . $stt);

            // Gán lại cho tất cả order trong nhóm và gán tracking_number_child giống nhau
            foreach ($group as $tracking) {
                $order = $tracking->order;
                if ($order && $order->lenh_sanxuat !== $lenhSx) {
                    $order->update(['lenh_sanxuat' => $lenhSx]);
                }
                // Gán tracking_number_child giống nhau cho tất cả tracking trong nhóm
                $tracking->update(['tracking_number_child' => $lenhSx]);
            }

            ProductionReport::create([
                'cong_doan' => $group->first()->cong_doan,
                'ngay_sx' => now()->toDateString(),
                'ca' => '1',
                'lenh_sx' => $lenhSx,
                'mau' => $mauList,
                'size' => $maHh,
                'sl_dat' => $totalSlSanXuat > 0 ? $totalSlSanXuat : $totalSlDonHang,
                'sl_hu' => 0,
            ]);

            // Cập nhật tất cả tracking trong nhóm
            foreach ($group as $tracking) {
                $tracking->update(['cong_doan' => 'Đã chuyển SX']);
                $tracking->order?->updateStatusFromTracking();
            }
            $countGroup++;
            $stt++;
        }

        return redirect()->back()->with(
            'success',
            "Đã gộp {$trackings->count()} tracking thành {$countGroup} lệnh SX theo mã HH."
        );
    }

    /**
     * Chuyển từ Production sang Warehouse (nhập kho).
     */
    public function pushToWarehouse(Request $request)
    {
        $request->validate([
            'tracking_ids' => 'required|array',
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
                'ma_hh' => $maHh,
                'ngay' => now()->toDateString(),
                'size' => $group->first()->kich ?? $group->first()->size,
                'mau' => $mauList,
                'so_luong' => $totalSl,
                'lenh_sx' => $lenhSxList,
                'note' => "Gộp {$group->count()} tracking - Orders: {$jobNos}",
            ]);

            // Cập nhật tất cả tracking trong nhóm
            foreach ($group as $tracking) {
                $tracking->update(['cong_doan' => 'Đã nhập kho']);
                $tracking->order->updateStatusFromTracking();
            }
            $countGroup++;
        }

        return redirect()->back()->with(
            'success',
            "Đã gộp {$trackings->count()} tracking thành {$countGroup} phiếu nhập kho theo mã HH."
        );
    }

    /**
     * Xuất kho giao hàng — dùng khi ma_hh đã đủ tồn kho.
     * Tạo XUATKHO transaction + cập nhật order → shipped.
     */
    public function shipFromStock(Request $request)
    {
        $request->validate([
            'order_ids' => 'required|array',
            'order_ids.*' => 'exists:orders,id',
        ]);

        $shipped = 0;
        $errors = [];

        foreach ($request->order_ids as $orderId) {
            $order = Order::find($orderId);
            if (!$order || !$order->ma_hh || $order->status === 'shipped')
                continue;

            // Tính tồn kho hiện tại cho ma_hh này
            $nhap = WarehouseTransaction::where('ma_hh', $order->ma_hh)->nhapKho()->sum('so_luong');
            $xuat = WarehouseTransaction::where('ma_hh', $order->ma_hh)->xuatKho()->sum('so_luong');
            $tonKho = $nhap - $xuat;
            $canXuat = $order->yrd ?? 0;

            if ($tonKho < $canXuat) {
                $errors[] = "{$order->job_no}: tồn kho ({$tonKho}) < cần ({$canXuat})";
                continue;
            }

            // Tạo phiếu xuất kho
            WarehouseTransaction::create([
                'cong_doan' => 'XUATKHO',
                'ma_hh' => $order->ma_hh,
                'ngay' => now()->toDateString(),
                'size' => $order->tracking()->first()->kich ?? null,
                'mau' => $order->color,
                'so_luong' => $canXuat,
                'lenh_sx' => $order->lenh_sanxuat ?? $order->job_no,
                'note' => "Xuất giao hàng - Order {$order->job_no} / FTY PO: {$order->fty_po}",
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

    /**
     * Tạo lệnh SX batch — từ modal trên trang lot.
     * Tạo production_reports cho từng ma_hh đã chọn.
     */
    public function createProductionBatch(Request $request)
    {
        $request->validate([
            'tracking_number' => 'required|string',
            'items'           => 'required|array|min:1',
            'items.*.ma_hh'   => 'required|string',
        ]);

        $trackingNumber = $request->tracking_number;
        $count = 0;

        foreach ($request->items as $item) {
            $maHh = $item['ma_hh'];
            if (isset($item['selected'])) {
                // Nếu được chọn, đánh dấu đã lên lệnh SX
                $affected = OrderTracking::where('tracking_number', $trackingNumber)
                    ->where('size', $maHh)
                    ->update(['da_tao_lenh_sx' => true]);
                $count += $affected;
            } else {
                // Nếu không được chọn, bỏ đánh dấu đã lên lệnh SX
                OrderTracking::where('tracking_number', $trackingNumber)
                    ->where('size', $maHh)
                    ->update(['da_tao_lenh_sx' => false]);
            }
        }

        return redirect()->route('admin.order-tracking.lot', $trackingNumber)
            ->with('success', "Đã đánh dấu {$count} lệnh đã tạo lệnh SX.");
    }

    /**
     * Xuất Excel lệnh sản xuất.
     */
    public function exportLenhSx(string $trackingNumber)
    {
        $pctHaoHut = request('pct_hao_hut', 10);
        $filename = 'LENH_SX_' . str_replace(['-', '/'], '_', $trackingNumber) . '.xlsx';

        return Excel::download(new LenhSanXuatExport($trackingNumber, $pctHaoHut), $filename);
    }
}
