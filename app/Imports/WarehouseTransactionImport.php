<?php

namespace App\Imports;

use App\Models\WarehouseTransaction;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class WarehouseTransactionImport implements ToModel, WithHeadingRow, WithValidation, SkipsEmptyRows
{
    protected int $count = 0;

    public function prepareForValidation($data, $index)
    {
        if (isset($data['so_luong'])) {
            $data['so_luong'] = $this->toNumeric($data['so_luong']);
        }
        return $data;
    }

    public function model(array $row)
    {
        $so_luong = $this->toNumeric($row['so_luong'] ?? null);

        // Bỏ qua các dòng không có công đoạn hoặc số lượng <= 0
        if (empty($row['cong_doan']) || $so_luong === null || $so_luong <= 0) {
            return null;
        }

        $this->count++;

        return new WarehouseTransaction([
            'cong_doan' => strtoupper(trim($row['cong_doan'])),
            'ma_hh'     => $row['ma_hh'] ?? null,
            'ngay'      => $this->toDate($row['ngay'] ?? null) ?? now()->format('Y-m-d'),
            'size'      => $row['size'] ?? null,
            'mau'       => $row['mau'] ?? null,
            'so_luong'  => $so_luong,
            'ma_nv'     => $row['ma_nv'] ?? null,
            'lenh_sx'   => $row['lenh_sx'] ?? null,
            'note'      => $row['note'] ?? null,
        ]);
    }

    public function rules(): array
    {
        return [
            'cong_doan' => 'nullable|in:NHAPKHO,XUATKHO,nhapkho,xuatkho',
            'so_luong'  => 'nullable|numeric',
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
