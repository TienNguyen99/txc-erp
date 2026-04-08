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
        $orders = collect();
        $plFilter = array_filter((array) $request->input('pl_number', []));
        $hasFilter = !empty($plFilter) || $request->filled('chart');

        if ($hasFilter) {
            $orders = Order::query()
                ->when(!empty($plFilter), fn($q) => $q->whereIn('pl_number', $plFilter))
                ->when($request->chart, fn($q, $v) => $q->where('chart', $v))
                ->get();
        }

        // Tổng hợp theo ma_hh từ các orders đã lọc
        $summary = $orders->groupBy('ma_hh')->map(function ($group, $maHh) {
            $totalQty = $group->sum('yrd');
            $orderIds = $group->pluck('id');

            // Đếm SL theo từng công đoạn
            $trackings = OrderTracking::whereIn('order_id', $orderIds)->get();
            $stageBreakdown = collect(OrderTracking::STAGES)->mapWithKeys(function ($info, $stage) use ($trackings) {
                return [$stage => $trackings->where('cong_doan', $stage)->sum('sl_don_hang')];
            });

            $slTracking   = $trackings->sum('sl_san_xuat');
            $slProduction = ProductionReport::where('lenh_sx', 'like', "%{$maHh}%")->sum('sl_dat');
            $slWarehouse  = WarehouseTransaction::where('ma_hh', $maHh)->nhapKho()->sum('so_luong');

            // Kiểm tra tồn kho
            $nhap = WarehouseTransaction::where('ma_hh', $maHh)->nhapKho()->sum('so_luong');
            $xuat = WarehouseTransaction::where('ma_hh', $maHh)->xuatKho()->sum('so_luong');
            $tonKho = $nhap - $xuat;
            $thieu = max(0, $totalQty - $tonKho);

            return (object) [
                'ma_hh'           => $maHh,
                'so_don'          => $group->count(),
                'tong_qty'        => $totalQty,
                'sl_tracking'     => $slTracking,
                'sl_production'   => $slProduction,
                'sl_warehouse'    => $slWarehouse,
                'ton_kho'         => $tonKho,
                'thieu'           => $thieu,
                'du_hang'         => $tonKho >= $totalQty,
                'stage_breakdown' => $stageBreakdown,
                'order_ids'       => $orderIds->toArray(),
            ];
        })->values();

        // Danh sách tracking hiện tại (vẫn giữ phân trang)
        $data = OrderTracking::with('order')
                    ->when(!empty($plFilter), fn($q) => $q->whereHas('order', fn($oq) => $oq->whereIn('pl_number', $plFilter)))
                    ->when($request->chart, fn($q, $v) => $q->whereHas('order', fn($oq) => $oq->where('chart', $v)))
                    ->when($request->search, fn($q, $s) => $q->where('pl_number', 'like', "%$s%")->orWhere('mau', 'like', "%$s%"))
                    ->latest()->paginate(15)->withQueryString();

        $allOrders = Order::pluck('job_no', 'id');
        $stages = OrderTracking::STAGES;

        return view('admin.order-tracking.index', compact('data', 'allOrders', 'plNumbers', 'charts', 'summary', 'hasFilter', 'stages'));
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
     * Chuyển tracking sang Production Report.
     */
    public function pushToProduction(Request $request)
    {
        $request->validate([
            'tracking_ids'   => 'required|array',
            'tracking_ids.*' => 'exists:order_tracking,id',
        ]);

        $count = 0;
        foreach ($request->tracking_ids as $trackingId) {
            $tracking = OrderTracking::with('order')->find($trackingId);
            if (!$tracking) continue;

            ProductionReport::create([
                'cong_doan'   => $tracking->cong_doan,
                'ngay_sx'     => now()->toDateString(),
                'ca'          => '1',
                'lenh_sx'     => $tracking->order->lenh_sanxuat ?? $tracking->order->job_no,
                'mau'         => $tracking->mau,
                'size'        => $tracking->size,
                'sl_dat'      => $tracking->sl_san_xuat,
                'sl_hu'       => 0,
            ]);

            // Cập nhật công đoạn tracking
            $tracking->update(['cong_doan' => 'Đã chuyển SX']);

            // Tự động cập nhật status đơn hàng
            $tracking->order->updateStatusFromTracking();
            $count++;
        }

        return redirect()->back()->with('success', "Đã chuyển {$count} mục sang sản xuất.");
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

        $count = 0;
        foreach ($request->tracking_ids as $trackingId) {
            $tracking = OrderTracking::with('order')->find($trackingId);
            if (!$tracking || $tracking->sl_san_xuat <= 0) continue;

            WarehouseTransaction::create([
                'cong_doan' => 'NHAPKHO',
                'ma_hh'     => $tracking->order->ma_hh,
                'ngay'      => now()->toDateString(),
                'size'      => $tracking->kich ?? $tracking->size,
                'mau'       => $tracking->mau,
                'so_luong'  => $tracking->sl_san_xuat,
                'lenh_sx'   => $tracking->order->lenh_sanxuat ?? $tracking->order->job_no,
                'note'      => "Từ tracking #{$tracking->id} - Order {$tracking->order->job_no}",
            ]);

            $tracking->update(['cong_doan' => 'Đã nhập kho']);

            // Tự động cập nhật status đơn hàng
            $tracking->order->updateStatusFromTracking();
            $count++;
        }

        return redirect()->back()->with('success', "Đã nhập kho {$count} mục.");
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
