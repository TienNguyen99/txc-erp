<?php

namespace App\Imports;

use App\Models\Order;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

/**
 * Import đơn hàng từ file Excel của khách hàng (South Island / TEXENCO format).
 *
 * Cấu trúc file khách:
 * - Dòng 1-11 : metadata (PROG ID, Prod Category, Order Date, ...)
 * - Dòng 12   : header cột (Buy, PO Place Date, SN CODE, ...)
 * - Dòng 13-14: section title (TEXENCO - MAR END BUY, ...)
 * - Dòng 15+  : dữ liệu
 *
 * Mapping cột (0-indexed):
 *  0 (A) Buy              → (bỏ qua)
 *  1 (B) PO Place Date    → sig_need_date
 *  2 (C) SN CODE          → (bỏ qua)
 *  3 (D) Pty              → (bỏ qua)
 *  4 (E) KeySty           → (bỏ qua)
 *  5 (F) Style#           → chart
 *  6 (G) Clw              → (bỏ qua)
 *  7 (H) PTL              → fty_po
 *  8 (I) JOB NO           → job_no *** KEY ***
 *  9 (J) Fty POR          → (mô tả sản phẩm, lưu vào size tạm)
 * 10 (K) IMe              → im_number
 * 11 (L) Clr              → color
 * 12 (M) Odr Q            → qty
 * 13 (N) Unit             → unit
 * 14 (O) Pl Num           → yrd (số lượng pieces)
 * 15 (P) RMDS             → (bỏ qua)
 * 16 (Q) Actual           → (bỏ qua)
 * 17 (R) RMDS OETC        → tagtime_etc
 */
class CustomerOrderImport implements ToModel, WithStartRow
{
    protected int $imported = 0;
    protected int $skipped = 0;

    public function startRow(): int
    {
        return 13; // Bắt đầu sau header (dòng 12), sẽ lọc section title
    }

    public function model(array $row)
    {
        $jobNo = trim($row[8] ?? '');

        // Bỏ qua dòng trống, section title, hoặc dòng header phụ
        if (empty($jobNo) || !preg_match('/[A-Z]{2}\d+/i', $jobNo)) {
            $this->skipped++;
            return null;
        }

        $this->imported++;

        return Order::updateOrCreate(
            ['job_no' => $jobNo],
            [
                'fty_po'        => $this->cleanString($row[7] ?? null),
                'im_number'     => $this->cleanString($row[10] ?? null),
                'color'         => $this->cleanString($row[11] ?? null),
                'qty'           => $this->toNumber($row[12] ?? null),
                'unit'          => $this->cleanString($row[13] ?? null),
                'size'          => $this->cleanString($row[9] ?? null),
                'yrd'           => $this->toNumber($row[14] ?? null),
                'chart'         => $this->cleanString($row[5] ?? null),
                'sig_need_date' => $this->parseDate($row[1] ?? null),
                'tagtime_etc'   => $this->parseDate($row[17] ?? null),
                'status'        => 'pending',
            ]
        );
    }

    public function getImportedCount(): int
    {
        return $this->imported;
    }

    public function getSkippedCount(): int
    {
        return $this->skipped;
    }

    private function cleanString($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        return trim((string) $value);
    }

    private function toNumber($value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }
        $clean = str_replace([',', ' '], ['', ''], (string) $value);
        return is_numeric($clean) ? (float) $clean : null;
    }

    private function parseDate($value)
    {
        if (empty($value)) {
            return null;
        }

        // Excel serial number (numeric date)
        if (is_numeric($value)) {
            try {
                return ExcelDate::excelToDateTimeObject((int) $value)->format('Y-m-d');
            } catch (\Exception $e) {
                return null;
            }
        }

        // String date dd/mm/yyyy
        try {
            return \Carbon\Carbon::createFromFormat('d/m/Y', trim($value))->format('Y-m-d');
        } catch (\Exception $e) {
            // Try Y-m-d
            try {
                return \Carbon\Carbon::parse(trim($value))->format('Y-m-d');
            } catch (\Exception $e) {
                return null;
            }
        }
    }
}
