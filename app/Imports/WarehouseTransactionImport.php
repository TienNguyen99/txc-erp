<?php

namespace App\Imports;

use App\Models\WarehouseTransaction;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class WarehouseTransactionImport implements ToModel, WithHeadingRow, WithValidation
{
    protected int $count = 0;

    public function model(array $row)
    {
        if (empty($row['cong_doan']) || empty($row['so_luong'])) {
            return null;
        }

        $this->count++;

        return new WarehouseTransaction([
            'cong_doan' => strtoupper(trim($row['cong_doan'])),
            'ma_hh'     => $row['ma_hh'] ?? null,
            'ngay'      => $this->toDate($row['ngay'] ?? null) ?? now()->format('Y-m-d'),
            'size'      => $row['size'] ?? null,
            'mau'       => $row['mau'] ?? null,
            'so_luong'  => $this->toNumeric($row['so_luong']),
            'ma_nv'     => $row['ma_nv'] ?? null,
            'lenh_sx'   => $row['lenh_sx'] ?? null,
            'note'      => $row['note'] ?? null,
        ]);
    }

    public function rules(): array
    {
        return [
            'cong_doan' => 'required|in:NHAPKHO,XUATKHO,nhapkho,xuatkho',
            'so_luong'  => 'required|numeric|min:0.01',
        ];
    }

    public function getCount(): int
    {
        return $this->count;
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
}
