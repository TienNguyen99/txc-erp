<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\WarehouseTransaction;
use App\Services\WarehouseDocumentService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class WarehouseEntryController extends Controller
{
    /**
     * Staff warehouse entry screen.
     */
    public function index(Request $request)
    {
        $lenhSx = $request->lenh_sx;
        $items = collect();
        $summary = (object) [
            'total_items' => 0,
            'total_order_qty' => 0,
            'total_received_for_order' => 0,
            'total_remaining' => 0,
            'shortage_items' => 0,
        ];

        if ($lenhSx) {
            $orders = Order::with('tracking')
                ->where('lenh_sanxuat', $lenhSx)
                ->get();

            $maHhList = $orders->pluck('ma_hh')->filter()->unique()->values();
            $stockIn = WarehouseTransaction::nhapKho()
                ->whereIn('ma_hh', $maHhList)
                ->selectRaw('ma_hh, SUM(so_luong) as total')
                ->groupBy('ma_hh')
                ->pluck('total', 'ma_hh');
            $stockOut = WarehouseTransaction::xuatKho()
                ->whereIn('ma_hh', $maHhList)
                ->selectRaw('ma_hh, SUM(so_luong) as total')
                ->groupBy('ma_hh')
                ->pluck('total', 'ma_hh');
            $receivedByOrder = WarehouseTransaction::nhapKho()
                ->where('lenh_sx', $lenhSx)
                ->whereIn('ma_hh', $maHhList)
                ->selectRaw('ma_hh, SUM(so_luong) as total')
                ->groupBy('ma_hh')
                ->pluck('total', 'ma_hh');

            $items = $orders->map(function (Order $order) use ($stockIn, $stockOut, $receivedByOrder) {
                $orderedQty = (float) ($order->yrd ?? 0);
                $receivedForOrder = (float) ($receivedByOrder[$order->ma_hh] ?? 0);
                $remainingQty = max(0, $orderedQty - $receivedForOrder);

                return (object) [
                    'order_id' => $order->id,
                    'ma_hang' => $order->ma_hh,
                    'mau' => $order->color,
                    'size' => $order->tracking->first()?->kich,
                    'sl_don' => $orderedQty,
                    'da_nhap_lenh' => $receivedForOrder,
                    'con_lai' => $remainingQty,
                    'ton_kho' => (float) (($stockIn[$order->ma_hh] ?? 0) - ($stockOut[$order->ma_hh] ?? 0)),
                    'job_no' => $order->job_no,
                ];
            });

            $summary = (object) [
                'total_items' => $items->count(),
                'total_order_qty' => $items->sum('sl_don'),
                'total_received_for_order' => $items->sum('da_nhap_lenh'),
                'total_remaining' => $items->sum('con_lai'),
                'shortage_items' => $items->filter(fn ($item) => $item->con_lai > 0)->count(),
            ];
        }

        $danhSachLenh = Order::whereNotNull('lenh_sanxuat')
            ->where('lenh_sanxuat', '!=', '')
            ->where('status', '!=', 'shipped')
            ->distinct()
            ->pluck('lenh_sanxuat');

        return view('staff.warehouse.index', compact('items', 'lenhSx', 'danhSachLenh', 'summary'));
    }

    /**
     * Store a staff warehouse receipt and create a printable warehouse document.
     */
    public function store(Request $request, WarehouseDocumentService $documentService)
    {
        $validated = $request->validate([
            'lenh_sx' => 'required|string|max:100',
            'ngay' => 'required|date',
            'rows' => 'required|array|min:1',
            'rows.*.ma_hh' => 'required|string',
            'rows.*.mau' => 'nullable|string',
            'rows.*.size' => 'nullable|string',
            'rows.*.so_luong' => 'nullable|numeric|min:0',
        ]);

        $maNv = $request->user()->name;
        $rows = collect($validated['rows'])
            ->map(function (array $row) use ($maNv, $validated) {
                return [
                    'ma_hh' => $row['ma_hh'],
                    'mau' => $row['mau'] ?? null,
                    'size' => $row['size'] ?? null,
                    'so_luong' => (float) ($row['so_luong'] ?? 0),
                    'ma_nv' => $maNv,
                    'lenh_sx' => $validated['lenh_sx'],
                    'note' => 'Nhap kho theo lenh SX tu staff portal',
                ];
            })
            ->filter(fn (array $row) => $row['so_luong'] > 0)
            ->values();

        if ($rows->isEmpty()) {
            throw ValidationException::withMessages([
                'rows' => 'Vui long nhap it nhat mot dong co so luong lon hon 0.',
            ]);
        }

        $document = $documentService->create(
            'NHAPKHO',
            $validated['ngay'],
            $rows->all(),
            $request->user(),
            "Nhap kho theo lenh {$validated['lenh_sx']} tu staff portal"
        );

        return redirect()
            ->route('staff.warehouse.index', ['lenh_sx' => $validated['lenh_sx']])
            ->with('success', "Da nhap kho {$rows->count()} muc. Phieu: {$document->document_no}.");
    }

    /**
     * Current staff member receipt history.
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
