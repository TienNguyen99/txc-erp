<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DanhMucHangHoa;
use App\Models\Order;
use App\Models\OrderTracking;
use App\Models\Setting;
use App\Models\ProductionReport;
use App\Models\WarehouseTransaction;
use App\Exports\WarehouseTransactionExport;
use App\Exports\WarehouseInventoryDashboardExport;
use App\Exports\WarehouseTransactionTemplateExport;
use App\Exports\PackingListExport;
use App\Imports\WarehouseTransactionImport;
use App\Services\WarehouseDocumentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class WarehouseTransactionController extends Controller
{
    public function index(Request $request)
    {
        $data = WarehouseTransaction::with('warehouseDocument')
            ->when($request->search, fn($q, $s) => $q->where('lenh_sx', 'like', "%$s%")->orWhere('ma_nv', 'like', "%$s%"))
            ->when($request->cong_doan, fn($q, $cd) => $q->where('cong_doan', $cd))
            ->latest()->paginate(15)->withQueryString();

        // ═══ SOẠN HÀNG: Phân theo tracking_number (lô giao) ═══
        // Danh sách tracking_number có sẵn (chưa giao hết)
        $availableTrackings = OrderTracking::select('tracking_number')
            ->where('cong_doan', '!=', 'shipped')
            ->whereHas('order', fn($q) => $q->where('status', '!=', 'shipped')
                ->whereNotNull('ma_hh')->where('ma_hh', '!=', ''))
            ->distinct()
            ->pluck('tracking_number')
            ->sort()
            ->values();

        $selectedTracking = $request->input('tracking_filter', '');

        // Lấy tracking theo lô đã chọn (hoặc tất cả nếu không filter)
        $trackings = OrderTracking::with('order')
            ->where('cong_doan', '!=', 'shipped')
            ->whereHas('order', fn($q) => $q->where('status', '!=', 'shipped')
                ->whereNotNull('ma_hh')->where('ma_hh', '!=', ''))
            ->when($selectedTracking, fn($q) => $q->where('tracking_number', $selectedTracking))
            ->get();

        // Tính tồn kho 1 lần cho mỗi ma_hh (tránh N+1)
        $allMaHh = $trackings->map(fn($t) => $t->order->ma_hh)->unique();
        $tonKhoMap = [];
        foreach ($allMaHh as $maHh) {
            $nhap = WarehouseTransaction::where('ma_hh', $maHh)->nhapKho()->sum('so_luong');
            $xuat = WarehouseTransaction::where('ma_hh', $maHh)->xuatKho()->sum('so_luong');
            $tonKhoMap[$maHh] = $nhap - $xuat;
        }

        // Tính đang SX 1 lần
        $dangSxMap = [];
        foreach ($allMaHh as $maHh) {
            $dangSxMap[$maHh] = ProductionReport::where('size', $maHh)
                ->where('cong_doan', '!=', 'Đã nhập kho')
                ->sum('sl_dat');
        }

        // ═══ Sắp xếp theo ma_hh rồi fty_po (giống Packing List) ═══
        $soanHangRaw = $trackings->sortBy([
            fn($a, $b) => strcmp($a->order->ma_hh ?? '', $b->order->ma_hh ?? ''),
            fn($a, $b) => strcmp($a->order->fty_po ?? '', $b->order->fty_po ?? ''),
        ])->map(function ($tracking) use ($tonKhoMap, $dangSxMap) {
            $order = $tracking->order;
            $maHh = $order->ma_hh;
            $canXuat = $tracking->sl_don_hang ?? $order->yrd ?? 0;
            $hangHoa = DanhMucHangHoa::where('ma_hh', $maHh)->first();

            return (object) [
                'tracking_id' => $tracking->id,
                'ma_hh' => $maHh,
                'ten_hh' => $hangHoa?->ten_hh ?? '',
                'nhom_hh' => $hangHoa?->nhom_hh ?? '',
                'dinh_muc_thung' => $hangHoa?->dinh_muc_thung ?? null,
                'pl_number' => $tracking->pl_number ?? $order->pl_number,
                'chart' => $order->chart,
                'job_no' => $order->job_no,
                'fty_po' => $order->fty_po,
                'im_number' => $order->im_number ?? '',
                'mau' => $tracking->mau ?? $order->color,
                'size' => $tracking->size,
                'cong_doan' => $tracking->cong_doan,
                'can_xuat' => $canXuat,
                'ton_kho_tong' => $tonKhoMap[$maHh] ?? 0,
                'dang_sx' => $dangSxMap[$maHh] ?? 0,
                'sig_need_date' => $order->sig_need_date,
            ];
        })->values();

        // Bước 2: Trừ tồn tuần tự theo ma_hh từ trên xuống
        $tonConLaiMap = []; // Track remaining per ma_hh
        $soanHang = $soanHangRaw->map(function ($row) use (&$tonConLaiMap) {
            $maHh = $row->ma_hh;

            // Khởi tạo tồn còn lại lần đầu cho ma_hh này
            if (!isset($tonConLaiMap[$maHh])) {
                $tonConLaiMap[$maHh] = $row->ton_kho_tong;
            }

            $tonConLai = $tonConLaiMap[$maHh];
            $canXuat = $row->can_xuat;
            $capDuoc = min($canXuat, max(0, $tonConLai));
            $thieu = max(0, $canXuat - $capDuoc);

            // Trừ tồn cho PO tiếp theo cùng ma_hh
            $tonConLaiMap[$maHh] -= $capDuoc;

            // Xác định trạng thái
            if ($capDuoc >= $canXuat) {
                $trangThai = 'du';
            } elseif ($capDuoc > 0) {
                $trangThai = 'thieu_1_phan';
            } elseif ($row->dang_sx > 0) {
                $trangThai = 'dang_sx';
            } else {
                $trangThai = 'thieu';
            }

            $row->ton_con_lai = $tonConLai;
            $row->cap_duoc = $capDuoc;
            $row->thieu = $thieu;
            $row->trang_thai = $trangThai;

            return $row;
        })->values();

        $soanStats = (object) [
            'tong_phieu' => $soanHang->count(),
            'du_hang' => $soanHang->where('trang_thai', 'du')->count(),
            'thieu_1_phan' => $soanHang->where('trang_thai', 'thieu_1_phan')->count(),
            'dang_sx' => $soanHang->where('trang_thai', 'dang_sx')->count(),
            'thieu_hang' => $soanHang->where('trang_thai', 'thieu')->count(),
        ];

        // ═══ TỒN KHO ═══
        $thang = $request->thang ?? now()->month;
        $nam = $request->nam ?? now()->year;

        $startOfMonth = Carbon::create($nam, $thang, 1)->startOfMonth();

        $makeKey = fn($r) => ($r->ma_hh ?? '') . '|' . ($r->size ?? '') . '|' . ($r->mau ?? '');

        $tonDau = WarehouseTransaction::select(
            'ma_hh',
            'size',
            'mau',
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
                'ma_hh' => $maHh,
                'size' => $size,
                'mau' => $mau,
                'ton_dau' => $tonDauVal,
                'nhap_days' => $nhapRows,
                'tong_nhap' => $tongNhap,
                'xuat_days' => $xuatRows,
                'tong_xuat' => $tongXuat,
                'ton_cuoi' => $tonCuoi,
                'can_di' => $canDi[$maHh] ?? 0,
            ];
        })->sortBy(['ma_hh', 'mau'])->values();

        return view('admin.warehouse-transactions.index', compact(
            'data',
            'tonKho',
            'thang',
            'nam',
            'nhapDates',
            'xuatDates',
            'soanHang',
            'soanStats',
            'availableTrackings',
            'selectedTracking'
        ));
    }

    public function dashboard(Request $request)
    {
        $thang = (int) ($request->thang ?? now()->month);
        $nam = (int) ($request->nam ?? now()->year);

        $startOfMonth = Carbon::create($nam, $thang, 1)->startOfMonth();
        $makeKey = fn($r) => ($r->ma_hh ?? '') . '|' . ($r->size ?? '') . '|' . ($r->mau ?? '');

        $tonDau = WarehouseTransaction::select(
            'ma_hh',
            'size',
            'mau',
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
                'ma_hh' => $maHh,
                'size' => $size,
                'mau' => $mau,
                'ton_dau' => $tonDauVal,
                'nhap_days' => $nhapRows,
                'tong_nhap' => $tongNhap,
                'xuat_days' => $xuatRows,
                'tong_xuat' => $tongXuat,
                'ton_cuoi' => $tonCuoi,
                'can_di' => $canDi[$maHh] ?? 0,
            ];
        })->sortBy(['ma_hh', 'mau'])->values();

        $stats = (object) [
            'tong_ma' => $tonKho->count(),
            'tong_ton' => $tonKho->sum('ton_cuoi'),
            'tong_nhap' => $tonKho->sum('tong_nhap'),
            'tong_xuat' => $tonKho->sum('tong_xuat'),
        ];

        return view('admin.warehouse-dashboard.index', compact(
            'tonKho',
            'thang',
            'nam',
            'nhapDates',
            'xuatDates',
            'stats'
        ));
    }

    public function create()
    {
        $hangHoas = DanhMucHangHoa::where('active', true)->pluck('ten_hh', 'id');
        return view('admin.warehouse-transactions.form', compact('hangHoas'));
    }

    public function store(Request $request, WarehouseDocumentService $documentService)
    {
        $validated = $request->validate([
            'cong_doan' => 'required|in:NHAPKHO,XUATKHO',
            'ma_hh' => 'nullable|string',
            'hang_hoa_id' => 'nullable|exists:danh_muc_hang_hoa,id',
            'ngay' => 'required|date',
            'size' => 'nullable|string',
            'mau' => 'nullable|string',
            'so_luong' => 'required|numeric|min:0.01',
            'ma_nv' => 'nullable|string',
            'lenh_sx' => 'nullable|string',
            'note' => 'nullable|string',
        ]);
        $document = $documentService->create(
            $validated['cong_doan'],
            $validated['ngay'],
            [$validated],
            $request->user(),
            $validated['note'] ?? null
        );

        return redirect()
            ->route('admin.warehouse-documents.show', $document)
            ->with('success', "Đã tạo phiếu kho {$document->document_no}.");
    }

    public function edit(WarehouseTransaction $warehouseTransaction)
    {
        $hangHoas = DanhMucHangHoa::where('active', true)->pluck('ten_hh', 'id');
        return view('admin.warehouse-transactions.form', compact('warehouseTransaction', 'hangHoas'));
    }

    public function update(Request $request, WarehouseTransaction $warehouseTransaction)
    {
        if ($warehouseTransaction->warehouse_document_id) {
            return redirect()->back()->with('error', 'Giao dịch đã thuộc phiếu kho, không thể sửa trực tiếp.');
        }

        $validated = $request->validate([
            'cong_doan' => 'required|in:NHAPKHO,XUATKHO',
            'ma_hh' => 'nullable|string',
            'hang_hoa_id' => 'nullable|exists:danh_muc_hang_hoa,id',
            'ngay' => 'required|date',
            'size' => 'nullable|string',
            'mau' => 'nullable|string',
            'so_luong' => 'required|numeric|min:0.01',
            'ma_nv' => 'nullable|string',
            'lenh_sx' => 'nullable|string',
            'note' => 'nullable|string',
        ]);
        $warehouseTransaction->update($validated);
        return redirect()->route('admin.warehouse-transactions.index')->with('success', 'Cập nhật giao dịch kho thành công.');
    }

    public function destroy(WarehouseTransaction $warehouseTransaction)
    {
        if ($warehouseTransaction->warehouse_document_id) {
            return redirect()->back()->with('error', 'Giao dịch đã thuộc phiếu kho, không thể xóa trực tiếp.');
        }

        $warehouseTransaction->delete();
        return redirect()->route('admin.warehouse-transactions.index')->with('success', 'Xóa giao dịch kho thành công.');
    }

    /**
     * Xuất kho hàng loạt — mỗi tracking = 1 phiếu xuất kho.
     */
    public function xuatHangLoat(Request $request)
    {
        $request->validate([
            'ngay' => 'required|date',
            'ma_nv' => 'nullable|string',
            'items' => 'required|array',
            'items.*.selected' => 'required',
            'items.*.tracking_id' => 'required|exists:order_tracking,id',
            'items.*.ma_hh' => 'required|string',
            'items.*.so_luong' => 'required|numeric|min:0.01',
        ]);

        $count = 0;
        $errors = [];

        // Lấy tỷ giá mặc định từ cấu hình hệ thống
        $exchangeRate = Setting::where('key', 'usd_to_vnd')->value('value') ?? 25400;

        foreach ($request->items as $item) {
            if (empty($item['selected']) || floatval($item['so_luong'] ?? 0) <= 0) {
                continue;
            }

            $tracking = OrderTracking::with('order')->find($item['tracking_id']);
            if (!$tracking)
                continue;

            $maHh = $item['ma_hh'];
            $slXuat = floatval($item['so_luong']);

            // Kiểm tra tồn kho đủ không
            $nhap = WarehouseTransaction::where('ma_hh', $maHh)->nhapKho()->sum('so_luong');
            $xuat = WarehouseTransaction::where('ma_hh', $maHh)->xuatKho()->sum('so_luong');
            $tonKho = $nhap - $xuat;

            if ($tonKho < $slXuat) {
                $errors[] = "{$maHh}: tồn ({$tonKho}) < xuất ({$slXuat})";
                continue;
            }

            $order = $tracking->order;

            WarehouseTransaction::create([
                'cong_doan' => 'XUATKHO',
                'ma_hh' => $maHh,
                'ngay' => $request->ngay,
                'size' => $item['size'] ?? null,
                'mau' => $item['mau'] ?? null,
                'so_luong' => $slXuat,
                'price_usd' => $order->price_usd ?? $order->price_usd_auto ?? 0,
                'exchange_rate' => $exchangeRate,
                'ma_nv' => $request->ma_nv,
                'lenh_sx' => $order->lenh_sanxuat ?? $order->job_no,
                'note' => "Phiếu XK - Tracking #{$tracking->id} - Job: {$order->job_no}",
            ]);

            // Cập nhật tracking → shipped
            $tracking->update(['cong_doan' => 'shipped']);
            $order->updateStatusFromTracking();

            $count++;
        }

        $msg = "Đã tạo {$count} phiếu xuất kho.";
        if (count($errors) > 0) {
            $msg .= ' Lỗi: ' . implode('; ', array_slice($errors, 0, 5));
            return redirect()->route('admin.warehouse-transactions.index')->with('warning', $msg);
        }

        return redirect()->route('admin.warehouse-transactions.index')
            ->with('success', $msg);
    }

    /**
     * View tổng quan tồn kho hiện tại của từng mã
     */
    public function tonKho(Request $request)
    {
        $search = $request->input('search');

        // Lấy tồn kho hiện tại gom nhóm theo mã hàng, size, màu
        $inventoryQuery = WarehouseTransaction::select(
                'ma_hh', 'size', 'mau',
                DB::raw("SUM(CASE WHEN cong_doan='NHAPKHO' THEN so_luong ELSE 0 END) as tong_nhap"),
                DB::raw("SUM(CASE WHEN cong_doan='XUATKHO' THEN so_luong ELSE 0 END) as tong_xuat"),
                DB::raw("SUM(CASE WHEN cong_doan='NHAPKHO' THEN so_luong ELSE -so_luong END) as ton_hien_tai")
            )
            ->groupBy('ma_hh', 'size', 'mau')
            ->havingRaw('ton_hien_tai != 0 OR tong_nhap > 0') // Hiện cả những mã có nhập, kể cả khi tồn = 0 để theo dõi
            ->orderBy('ma_hh');
            
        if ($search) {
            $inventoryQuery->where('ma_hh', 'LIKE', "%{$search}%");
        }

        $inventory = $inventoryQuery->paginate(20)->withQueryString();

        // Get order quantities needed for these items (can_di)
        $maHhList = $inventory->pluck('ma_hh')->unique();
        $canDi = Order::where('status', '!=', 'shipped')
            ->whereIn('ma_hh', $maHhList)
            ->groupBy('ma_hh')
            ->select('ma_hh', DB::raw('SUM(yrd) as tong_yrd'))
            ->pluck('tong_yrd', 'ma_hh');
        
        // Get in-production quantities
        $dangSx = ProductionReport::where('cong_doan', '!=', 'Đã nhập kho')
            ->whereIn('size', $maHhList)
            ->groupBy('size')
            ->select('size', DB::raw('SUM(sl_dat) as tong_dang_sx'))
            ->pluck('tong_dang_sx', 'size');

        // Lấy danh mục hàng hóa để thêm thông tin Tên sản phẩm
        $danhMuc = DanhMucHangHoa::whereIn('ma_hh', $maHhList)
            ->get()
            ->keyBy('ma_hh');

        // Enrich the data
        foreach ($inventory as $item) {
            $item->can_di = $canDi[$item->ma_hh] ?? 0;
            $item->dang_sx = $dangSx[$item->ma_hh] ?? 0;
            $item->ten_hh = $danhMuc[$item->ma_hh]->ten_hh ?? 'N/A';
            $item->nhom_hh = $danhMuc[$item->ma_hh]->nhom_hh ?? 'N/A';
        }

        return view('admin.warehouse-transactions.ton-kho', compact('inventory', 'search'));
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
                    'ma_hang' => $order->ma_hh,
                    'mau' => $order->color,
                    'size' => $order->tracking()->first()->kich ?? null,
                    'sl_don' => $order->yrd,
                    'ton_kho' => $nhap - $xuat,
                    'job_no' => $order->job_no,
                    'fty_po' => $order->fty_po,
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
            'lenh_sx' => 'required|string',
            'ngay' => 'required|date',
            'ma_nv' => 'nullable|string',
            'rows' => 'required|array|min:1',
            'rows.*.ma_hh' => 'required|string',
            'rows.*.mau' => 'nullable|string',
            'rows.*.size' => 'nullable|string',
            'rows.*.so_luong' => 'nullable|numeric|min:0',
        ]);

        $count = 0;
        foreach ($request->rows as $row) {
            $sl = floatval($row['so_luong'] ?? 0);
            if ($sl <= 0)
                continue;

            WarehouseTransaction::create([
                'cong_doan' => 'NHAPKHO',
                'ma_hh' => $row['ma_hh'],
                'ngay' => $request->ngay,
                'size' => $row['size'] ?? null,
                'mau' => $row['mau'] ?? null,
                'so_luong' => $sl,
                'ma_nv' => $request->ma_nv,
                'lenh_sx' => $request->lenh_sx,
                'note' => "Nhập theo lệnh SX",
            ]);

            // Cập nhật ProductionReport liên quan thành NHAPKHO
            \App\Models\ProductionReport::where('size', $row['ma_hh'])
                ->where('lenh_sx', $request->lenh_sx)
                ->when($row['mau'] ?? null, function ($q) use ($row) {
                    $q->where('mau', $row['mau']);
                })
                ->update(['cong_doan' => 'NHAPKHO']);

            $count++;
        }

        return redirect()
            ->route('admin.warehouse-transactions.nhap-theo-lenh', ['lenh_sx' => $request->lenh_sx])
            ->with('success', "Đã nhập kho {$count} mục theo lệnh {$request->lenh_sx}.");
    }

    /**
     * Yêu cầu xuất vật tư cho Lệnh Sản Xuất dựa trên BOM
     */
    public function lenhXuatVatTu(\App\Models\LenhSanXuat $lenhSanXuat)
    {
        $items = $lenhSanXuat->items()->where('da_len_lenh', true)->get();
        $materials = [];
        
        foreach ($items as $item) {
            $hangHoa = DanhMucHangHoa::where('ma_hh', $item->ma_hh)->first();
            if (!$hangHoa) continue;
            
            $dinhMucs = \App\Models\DinhMucNvl::with('nguyenLieu')->where('san_pham_id', $hangHoa->id)->get();
            foreach ($dinhMucs as $dm) {
                $nl = $dm->nguyenLieu;
                if (!$nl) continue;
                
                $haoHut = 1 + ($dm->ti_le_hao_hut / 100);
                $reqQty = $item->sl_can_sx * $dm->so_luong * $haoHut;
                
                $key = $nl->id;
                if (!isset($materials[$key])) {
                    // Tính tồn kho hiện tại
                    $nhap = WarehouseTransaction::where('ma_hh', $nl->ma_hh)->nhapKho()->sum('so_luong');
                    $xuat = WarehouseTransaction::where('ma_hh', $nl->ma_hh)->xuatKho()->sum('so_luong');
                    $tonKho = $nhap - $xuat;

                    // Đã xuất bao nhiêu cho lệnh này?
                    $daXuatChoLenh = WarehouseTransaction::where('ma_hh', $nl->ma_hh)
                        ->xuatKho()
                        ->where('lenh_sx', $lenhSanXuat->lenh_so)
                        ->sum('so_luong');

                    $materials[$key] = [
                        'ma_hh' => $nl->ma_hh,
                        'ten_hh' => $nl->ten_hh,
                        'dvt' => $nl->don_vi,
                        'tong_can' => 0,
                        'ton_kho' => $tonKho,
                        'da_xuat' => $daXuatChoLenh,
                    ];
                }
                $materials[$key]['tong_can'] += $reqQty;
            }
        }

        return view('admin.warehouse-transactions.xuat-vat-tu', compact('lenhSanXuat', 'materials'));
    }

    public function storeLenhXuatVatTu(Request $request)
    {
        $request->validate([
            'lenh_san_xuat_id' => 'required|exists:lenh_san_xuat,id',
            'ngay' => 'required|date',
            'ma_nv' => 'nullable|string',
            'rows' => 'required|array',
            'rows.*.ma_hh' => 'required|string',
            'rows.*.so_luong_xuat' => 'nullable|numeric|min:0',
        ]);

        $lenh = \App\Models\LenhSanXuat::findOrFail($request->lenh_san_xuat_id);
        $count = 0;

        foreach ($request->rows as $row) {
            $sl = floatval($row['so_luong_xuat'] ?? 0);
            if ($sl <= 0) continue;

            WarehouseTransaction::create([
                'cong_doan' => 'XUATKHO',
                'ma_hh' => $row['ma_hh'],
                'ngay' => $request->ngay,
                'so_luong' => $sl,
                'ma_nv' => $request->ma_nv,
                'lenh_sx' => $lenh->lenh_so,
                'note' => "Xuất vật tư cho lệnh SX {$lenh->lenh_so}",
            ]);
            $count++;
        }

        return redirect()->route('admin.lenh-san-xuat.show', $lenh->id)
            ->with('success', "Đã xuất {$count} loại vật tư cho lệnh {$lenh->lenh_so}.");
    }

    public function export()
    {
        return Excel::download(new WarehouseTransactionExport, 'kho_nhap_xuat_' . now()->format('Ymd') . '.xlsx');
    }

    public function exportInventoryDashboard(Request $request)
    {
        $month = (int) $request->input('thang', now()->month);
        $year = (int) $request->input('nam', now()->year);

        return Excel::download(
            new WarehouseInventoryDashboardExport($month, $year),
            'dashboard_ton_kho_' . str_pad((string) $month, 2, '0', STR_PAD_LEFT) . '_' . $year . '.xlsx'
        );
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

        try {
            $import = new WarehouseTransactionImport();
            Excel::import($import, $request->file('file'));

            $msg = "Import OK: total {$import->getProcessedRows()} rows | created {$import->getCreatedCount()} | updated {$import->getUpdatedCount()} | skipped {$import->getSkippedCount()}";
            $dupes = $import->getDuplicateRows();
            if (!empty($dupes)) {
                $preview = collect($dupes)
                    ->take(5)
                    ->map(fn($d) => "row {$d['row']} duplicated key {$d['key']} (first seen at row {$d['first_row']})")
                    ->implode('; ');
                return redirect()->route('admin.warehouse-transactions.index')->with('warning', $msg . ". Duplicates: {$preview}");
            }

            return redirect()->route('admin.warehouse-transactions.index')->with('success', $msg);
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = collect($e->failures())->map(fn($f) => "Row {$f->row()}: {$f->attribute()} - " . implode(', ', $f->errors()));
            return redirect()->route('admin.warehouse-transactions.index')->with('error', 'Import validation error: ' . $failures->take(5)->implode(' | '));
        } catch (\Exception $e) {
            return redirect()->route('admin.warehouse-transactions.index')->with('error', 'Import error: ' . $e->getMessage());
        }
    }

    public function exportPackingList(Request $request)
    {
        $request->validate([
            'tracking_number' => 'required|string',
        ]);

        $tn = $request->tracking_number;
        $filename = 'PackingList_' . preg_replace('/[^A-Za-z0-9\-]/', '_', $tn) . '_' . now()->format('Ymd') . '.xlsx';

        return Excel::download(new PackingListExport($tn), $filename);
    }

    public function printLabels(Request $request)
    {
        $request->validate([
            'tracking_number' => 'required|string',
        ]);

        $tn = $request->tracking_number;
        $trackings = OrderTracking::with('order.khachHang')
            ->where('tracking_number', $tn)
            ->get()
            ->sortBy(fn($t) => $t->order->ma_hh ?? '');

        if ($trackings->isEmpty()) {
            return redirect()->back()->with('error', 'Không tìm thấy dữ liệu cho Tracking Number này.');
        }

        $allMaHh = $trackings->pluck('order.ma_hh')->unique()->filter()->values();
        $cartonSpecs = DanhMucHangHoa::whereIn('ma_hh', $allMaHh)
            ->whereNotNull('dinh_muc_thung')
            ->get()
            ->keyBy('ma_hh');

        $plNumbers  = $trackings->pluck('pl_number')->unique()->filter()->implode(', ');
        $firstOrder = $trackings->first()->order;
        $khachHang  = $firstOrder?->khachHang;
        $shipDate   = $firstOrder?->sig_need_date?->format('d/m/Y') ?? now()->format('d/m/Y');

        $grouped = $trackings->groupBy(fn($t) => $t->order->ma_hh ?? 'UNKNOWN');

        $cartonsData = [];

        foreach ($grouped as $maHh => $groupTrackings) {
            $spec = $cartonSpecs[$maHh] ?? null;
            $cap  = $spec->dinh_muc_thung ?? null;
            $nwFull = $spec ? (float) $spec->net_weight : 0;
            $gwFull = $spec ? (float) $spec->gross_weight : 0;
            $sizeName = $spec->ten_hh ?? $maHh;
            
            $byPo = $groupTrackings->groupBy(fn($t) => $t->order->fty_po ?? '');
            
            foreach ($byPo as $ftyPo => $poTrackings) {
                $jobNosArr = $poTrackings->pluck('order.job_no')->unique()->filter()->values();
                $jobNoStr = $jobNosArr->implode(", ");
                $color  = $poTrackings->first()->mau ?? $poTrackings->first()->order->color ?? '';
                $tYrd   = $poTrackings->sum(fn($t) => $t->sl_don_hang ?? $t->order->yrd ?? 0);
                
                $description = $poTrackings->first()->order->im_number ?? '';
                $itemCode = trim($sizeName . ' ' . $description);

                if ($cap && $cap > 0) {
                    $remaining = $tYrd;
                    while ($remaining > 0) {
                        $cQty = min($remaining, $cap);
                        $remaining -= $cQty;
                        $ratio = $cQty / $cap;
                        $nw = round($nwFull * $ratio, 1);
                        $gw = round($gwFull * $ratio, 3);
                        
                        $cartonsData[] = [
                            'date' => $shipDate,
                            'customer' => $khachHang->ten_kh ?? '',
                            'pkl' => $plNumbers,
                            'item_code' => $itemCode,
                            'color' => $color,
                            'nw' => $nw,
                            'gw' => $gw,
                            'job' => $jobNoStr,
                            'po' => $ftyPo,
                            'qty' => $cQty
                        ];
                    }
                } else {
                    $cartonsData[] = [
                        'date' => $shipDate,
                        'customer' => $khachHang->ten_kh ?? '',
                        'pkl' => $plNumbers,
                        'item_code' => $itemCode,
                        'color' => $color,
                        'nw' => 0,
                        'gw' => 0,
                        'job' => $jobNoStr,
                        'po' => $ftyPo,
                        'qty' => $tYrd
                    ];
                }
            }
        }

        $totalCartons = count($cartonsData);
        foreach ($cartonsData as $idx => &$c) {
            $c['carton_no'] = $idx + 1;
            $c['total_cartons'] = $totalCartons;
        }

        return view('admin.warehouse-transactions.print-labels', compact('tn', 'cartonsData', 'totalCartons'));
    }

    public function renderLabels(Request $request)
    {
        $allLabels = $request->input('labels', []);
        $selectedLabels = collect($allLabels)
            ->filter(fn($l) => !empty($l['selected']))
            ->map(function($l) {
                $data = json_decode($l['json'] ?? '{}', true);
                $data['nw'] = $l['nw'] ?? 0;
                $data['gw'] = $l['gw'] ?? 0;
                return $data;
            })
            ->values();

        if ($selectedLabels->isEmpty()) {
            return redirect()->back()->with('error', 'Vui lòng chọn ít nhất 1 tem để in.');
        }

        return view('admin.warehouse-transactions.render-labels', compact('selectedLabels'));
    }
}

