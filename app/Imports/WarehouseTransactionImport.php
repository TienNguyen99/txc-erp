<?php

namespace App\Imports;

use App\Models\WarehouseTransaction;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Row;

class WarehouseTransactionImport implements OnEachRow, WithHeadingRow, WithValidation
{
    protected int $processedRows = 0;
    protected int $createdCount = 0;
    protected int $updatedCount = 0;
    protected int $skippedCount = 0;
    protected array $seenKeys = [];
    protected array $duplicateRows = [];

    public function onRow(Row $excelRow): void
    {
        $row = $excelRow->toArray();
        $this->processedRows++;

        $soLuong = $this->toNumeric($row['so_luong'] ?? null);
        $congDoan = strtoupper(trim((string) ($row['cong_doan'] ?? '')));

        if ($congDoan === '' || $soLuong === null || $soLuong <= 0) {
            $this->skippedCount++;
            return;
        }

        $ngay = $this->toDate($row['ngay'] ?? null) ?? now()->format('Y-m-d');
        $maHh = $this->toNullableString($row['ma_hh'] ?? null);
        $size = $this->toNullableString($row['size'] ?? null);
        $mau = $this->toNullableString($row['mau'] ?? null);
        $maNv = $this->toNullableString($row['ma_nv'] ?? null);
        $lenhSx = $this->toNullableString($row['lenh_sx'] ?? null);

        $rowKey = mb_strtolower(implode('|', [
            $congDoan,
            $maHh ?? '',
            $ngay,
            $size ?? '',
            $mau ?? '',
            (string) $soLuong,
            $maNv ?? '',
            $lenhSx ?? '',
        ]));

        if (isset($this->seenKeys[$rowKey])) {
            $this->duplicateRows[] = [
                'row' => $excelRow->getIndex(),
                'first_row' => $this->seenKeys[$rowKey],
                'key' => $rowKey,
            ];
            $this->skippedCount++;
            return;
        }
        $this->seenKeys[$rowKey] = $excelRow->getIndex();

        $transaction = WarehouseTransaction::firstOrNew([
            'cong_doan' => $congDoan,
            'ma_hh' => $maHh,
            'ngay' => $ngay,
            'size' => $size,
            'mau' => $mau,
            'ma_nv' => $maNv,
            'lenh_sx' => $lenhSx,
        ]);
        $wasExisting = $transaction->exists;

        $transaction->so_luong = $soLuong;
        $transaction->note = $this->toNullableString($row['note'] ?? null);
        $transaction->save();

        if ($wasExisting) {
            $this->updatedCount++;
        } else {
            $this->createdCount++;
        }
    }

    public function rules(): array
    {
        return [
            'cong_doan' => 'nullable|in:NHAPKHO,XUATKHO,nhapkho,xuatkho',
            'so_luong' => 'nullable|numeric',
        ];
    }

    public function getProcessedRows(): int
    {
        return $this->processedRows;
    }

    public function getCreatedCount(): int
    {
        return $this->createdCount;
    }

    public function getUpdatedCount(): int
    {
        return $this->updatedCount;
    }

    public function getSkippedCount(): int
    {
        return $this->skippedCount;
    }

    public function getDuplicateRows(): array
    {
        return $this->duplicateRows;
    }

    private function toNumeric($value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }
        $clean = str_replace([',', ' '], ['', ''], trim((string) $value));
        return is_numeric($clean) ? (float) $clean : null;
    }

    private function toDate($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        if (is_numeric($value) && (int) $value > 30000) {
            try {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((int) $value)->format('Y-m-d');
            } catch (\Exception $e) {
                return null;
            }
        }

        $parsed = date_create((string) $value);
        if ($parsed && $parsed->format('Y') >= 2000) {
            return $parsed->format('Y-m-d');
        }

        return null;
    }

    private function toNullableString($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $clean = trim((string) $value);
        return $clean === '' ? null : $clean;
    }
}
