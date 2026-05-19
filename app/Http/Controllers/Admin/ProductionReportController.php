<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ProductionReportExport;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ProductionReport;
use App\Services\ProductionReceiptService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ProductionReportController extends Controller
{
    public function index(Request $request)
    {
        $thang = $request->thang ?? now()->month;
        $nam = $request->nam ?? now()->year;

        $data = ProductionReport::with('receipt')
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('lenh_sx', 'like', "%{$search}%")
                        ->orWhere('ma_nv', 'like', "%{$search}%")
                        ->orWhere('size', 'like', "%{$search}%");
                });
            })
            ->when($request->ngay_sx, fn ($query, $date) => $query->whereDate('ngay_sx', $date))
            ->when($request->status, fn ($query, $status) => $query->where('status', $status))
            ->when($request->thang && ! $request->ngay_sx, fn ($query, $month) => $query->whereMonth('ngay_sx', $month))
            ->when($request->nam && ! $request->ngay_sx, fn ($query, $year) => $query->whereYear('ngay_sx', $year))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.production-reports.index', compact('data', 'thang', 'nam'));
    }

    public function create()
    {
        return view('admin.production-reports.form');
    }

    public function store(Request $request)
    {
        $validated = $this->validatedReportData($request);
        ProductionReport::create($validated);
        $this->syncOrderProductionNumber($validated, $request->input('pl_number'));

        return redirect()->route('admin.production-reports.index')->with('success', 'Thêm báo cáo thành công.');
    }

    public function edit(ProductionReport $productionReport)
    {
        return view('admin.production-reports.form', compact('productionReport'));
    }

    public function update(Request $request, ProductionReport $productionReport)
    {
        if ($productionReport->isPosted()) {
            return redirect()->back()->with('error', 'Báo cáo đã tạo phiếu nhập, không thể sửa.');
        }

        $validated = $this->validatedReportData($request);
        $productionReport->update($validated);
        $this->syncOrderProductionNumber($validated, $request->input('pl_number'));

        return redirect()->route('admin.production-reports.index')->with('success', 'Cập nhật báo cáo thành công.');
    }

    public function destroy(ProductionReport $productionReport)
    {
        if ($productionReport->isPosted()) {
            return redirect()->back()->with('error', 'Báo cáo đã tạo phiếu nhập, không thể xóa.');
        }

        Order::where('ma_hh', $productionReport->size)
            ->where('lenh_sanxuat', $productionReport->lenh_sx)
            ->when($productionReport->mau, fn ($query) => $query->where('color', $productionReport->mau))
            ->update(['lenh_sanxuat' => null]);

        $productionReport->delete();

        return redirect()->route('admin.production-reports.index')->with('success', 'Xóa báo cáo thành công.');
    }

    public function approve(ProductionReport $productionReport)
    {
        if ($productionReport->isPosted()) {
            return redirect()->back()->with('error', 'Báo cáo đã tạo phiếu nhập, không thể duyệt lại.');
        }

        $productionReport->update([
            'status' => 'approved',
            'approved_by_id' => auth()->id(),
            'approved_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Đã duyệt báo cáo thành công.');
    }

    public function approveSelected(Request $request)
    {
        $request->validate([
            'report_ids' => ['required', 'array'],
            'report_ids.*' => ['exists:production_reports,id'],
        ]);

        $count = ProductionReport::whereIn('id', $request->report_ids)
            ->where('status', 'pending')
            ->whereNull('production_receipt_id')
            ->update([
                'status' => 'approved',
                'approved_by_id' => $request->user()?->id,
                'approved_at' => now(),
            ]);

        return redirect()->back()->with('success', "Đã duyệt {$count} báo cáo sản xuất.");
    }

    public function pushToWarehouse(Request $request, ProductionReceiptService $receiptService)
    {
        $request->validate([
            'report_ids' => ['required', 'array'],
            'report_ids.*' => ['exists:production_reports,id'],
        ]);

        $receipt = $receiptService->createFromReports($request->report_ids, $request->user());

        return redirect()
            ->route('admin.production-receipts.show', $receipt)
            ->with('success', "Đã tạo phiếu nhập kho {$receipt->receipt_no}.");
    }

    public function export(Request $request)
    {
        $thang = $request->thang ?? now()->month;
        $nam = $request->nam ?? now()->year;
        $fileName = "Bao_Cao_SX_{$thang}_{$nam}.xlsx";

        return Excel::download(new ProductionReportExport($thang, $nam), $fileName);
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedReportData(Request $request): array
    {
        return $request->validate([
            'cong_doan' => ['nullable', 'string'],
            'ngay_sx' => ['required', 'date'],
            'ca' => ['nullable', 'string'],
            'ma_nv' => ['nullable', 'string'],
            'lenh_sx' => ['nullable', 'string'],
            'mau' => ['nullable', 'string'],
            'size' => ['nullable', 'string'],
            'dinh_muc' => ['nullable', 'numeric'],
            'so_band' => ['nullable', 'integer'],
            'ns_8h_1may' => ['nullable', 'numeric'],
            'ns_gio_may' => ['nullable', 'numeric'],
            'sl_dat' => ['nullable', 'numeric'],
            'sl_hu' => ['nullable', 'numeric'],
            'so_may' => ['nullable', 'integer'],
            'gio_sx' => ['nullable', 'numeric'],
            'sl_yard_met' => ['nullable', 'numeric'],
            'van_de' => ['nullable', 'string'],
        ]);
    }

    /**
     * @param array<string, mixed> $validated
     * @param array<int, string>|string|null $plNumbers
     */
    private function syncOrderProductionNumber(array $validated, array|string|null $plNumbers): void
    {
        if (empty($validated['lenh_sx']) || empty($validated['size'])) {
            return;
        }

        $query = Order::where('ma_hh', $validated['size']);
        if (! empty($validated['mau'])) {
            $query->where('color', $validated['mau']);
        }

        if (! empty($plNumbers)) {
            is_array($plNumbers)
                ? $query->whereIn('pl_number', $plNumbers)
                : $query->where('pl_number', $plNumbers);
        }

        $query->update(['lenh_sanxuat' => $validated['lenh_sx']]);
    }
}
