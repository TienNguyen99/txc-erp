<?php

namespace App\Services;

use App\Models\DanhMucHangHoa;
use App\Models\DinhMucNvl;
use App\Models\ProductionReceipt;
use App\Models\ProductionReport;
use App\Models\User;
use App\Models\WarehouseTransaction;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProductionReceiptService
{
    /**
     * @param array<int, int|string> $reportIds
     */
    public function createFromReports(array $reportIds, ?User $user): ProductionReceipt
    {
        return DB::transaction(function () use ($reportIds, $user) {
            $reports = ProductionReport::query()
                ->whereIn('id', $reportIds)
                ->lockForUpdate()
                ->get();

            $this->validateReports($reports, count($reportIds));

            $receipt = ProductionReceipt::create([
                'receipt_no' => $this->nextReceiptNo(),
                'receipt_date' => now()->toDateString(),
                'cong_doan' => $reports->pluck('cong_doan')->filter()->unique()->values()->implode(', '),
                'approved_by_id' => $user?->id,
                'posted_by_id' => $user?->id,
                'posted_at' => now(),
                'note' => 'Tạo từ ' . $reports->count() . ' báo cáo sản xuất đã duyệt.',
            ]);

            $grouped = $this->groupReports($reports);

            foreach ($grouped as $group) {
                $first = $group->first();
                $maHh = (string) $first->size;
                $hangHoa = DanhMucHangHoa::where('ma_hh', $maHh)->first();
                $totalPassed = (float) $group->sum('sl_dat');
                $totalFailed = (float) $group->sum('sl_hu');
                $quantityToReceive = $totalPassed - $totalFailed;

                if ($quantityToReceive <= 0) {
                    continue;
                }

                $item = $receipt->items()->create([
                    'production_report_id' => $first->id,
                    'ten_san_pham' => $hangHoa?->ten_hh,
                    'ma_hh' => $maHh,
                    'mau' => $first->mau,
                    'size' => $this->displaySize($first->size, $hangHoa?->ma_hh),
                    'so_luong' => $quantityToReceive,
                    'don_vi' => $hangHoa?->don_vi ?: 'PCS',
                    'lenh_sx' => $group->pluck('lenh_sx')->filter()->unique()->values()->implode(', '),
                    'ghi_chu' => $totalFailed > 0 ? "SL đạt {$totalPassed}, SL hư {$totalFailed}" : null,
                ]);

                WarehouseTransaction::create([
                    'production_receipt_id' => $receipt->id,
                    'cong_doan' => 'NHAPKHO',
                    'ma_hh' => $maHh,
                    'ngay' => $receipt->receipt_date,
                    'size' => $item->size ?: $maHh,
                    'mau' => $item->mau,
                    'so_luong' => $quantityToReceive,
                    'ma_nv' => $user?->email,
                    'lenh_sx' => $item->lenh_sx,
                    'note' => "Phiếu nhập {$receipt->receipt_no}",
                ]);

                $this->backflushMaterials($receipt, $maHh, $totalPassed, $item->lenh_sx);
            }

            ProductionReport::whereIn('id', $reports->pluck('id'))->update([
                'status' => 'posted',
                'production_receipt_id' => $receipt->id,
                'posted_at' => now(),
            ]);

            return $receipt->load('items', 'postedBy');
        });
    }

    /**
     * @param Collection<int, ProductionReport> $reports
     */
    private function validateReports(Collection $reports, int $requestedCount): void
    {
        if ($reports->count() !== $requestedCount || $reports->isEmpty()) {
            throw ValidationException::withMessages([
                'report_ids' => 'Danh sách báo cáo không hợp lệ.',
            ]);
        }

        $notApproved = $reports->reject(fn (ProductionReport $report) => $report->isApproved());
        if ($notApproved->isNotEmpty()) {
            throw ValidationException::withMessages([
                'report_ids' => 'Chỉ được tạo phiếu nhập từ báo cáo đã duyệt.',
            ]);
        }

        $posted = $reports->filter(fn (ProductionReport $report) => $report->isPosted());
        if ($posted->isNotEmpty()) {
            throw ValidationException::withMessages([
                'report_ids' => 'Một số báo cáo đã có phiếu nhập, không thể nhập kho lần hai.',
            ]);
        }

        $receivableQuantity = $reports->sum(fn (ProductionReport $report) => (float) $report->sl_dat - (float) $report->sl_hu);
        if ($receivableQuantity <= 0) {
            throw ValidationException::withMessages([
                'report_ids' => 'Tổng số lượng nhập kho phải lớn hơn 0.',
            ]);
        }
    }

    /**
     * @param Collection<int, ProductionReport> $reports
     * @return Collection<string, Collection<int, ProductionReport>>
     */
    private function groupReports(Collection $reports): Collection
    {
        return $reports->groupBy(fn (ProductionReport $report) => implode('|', [
            $report->size,
            $report->mau ?: '',
            $report->lenh_sx ?: '',
        ]));
    }

    private function nextReceiptNo(): string
    {
        $prefix = 'PNK-SX-' . now()->format('Ymd') . '-';
        $next = ProductionReceipt::where('receipt_no', 'like', $prefix . '%')->count() + 1;

        return $prefix . str_pad((string) $next, 3, '0', STR_PAD_LEFT);
    }

    private function displaySize(?string $reportSize, ?string $maHh): ?string
    {
        if ($reportSize && $reportSize !== $maHh) {
            return $reportSize;
        }

        return null;
    }

    private function backflushMaterials(ProductionReceipt $receipt, string $maHh, float $totalPassed, ?string $lenhSx): void
    {
        $hangHoa = DanhMucHangHoa::where('ma_hh', $maHh)->first();
        if (! $hangHoa) {
            return;
        }

        $dinhMucs = DinhMucNvl::with('nguyenLieu')->where('san_pham_id', $hangHoa->id)->get();
        foreach ($dinhMucs as $dm) {
            if (! $dm->nguyenLieu) {
                continue;
            }

            $quantity = $totalPassed * (float) $dm->so_luong * (1 + ((float) $dm->ti_le_hao_hut / 100));
            if ($quantity <= 0) {
                continue;
            }

            WarehouseTransaction::create([
                'production_receipt_id' => $receipt->id,
                'cong_doan' => 'XUATKHO',
                'ma_hh' => $dm->nguyenLieu->ma_hh,
                'ngay' => $receipt->receipt_date,
                'so_luong' => $quantity,
                'lenh_sx' => $lenhSx,
                'note' => "Auto trừ kho BOM từ phiếu {$receipt->receipt_no} cho SX {$maHh}",
            ]);
        }
    }
}
