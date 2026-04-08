<?php

namespace App\Exports;

use App\Models\WarehouseTransaction;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class WarehouseTransactionExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return WarehouseTransaction::orderBy('ngay', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'cong_doan',
            'ma_hh',
            'ngay',
            'size',
            'mau',
            'so_luong',
            'ma_nv',
            'lenh_sx',
            'note',
        ];
    }

    public function map($row): array
    {
        return [
            $row->cong_doan,
            $row->ma_hh,
            $row->ngay?->format('Y-m-d'),
            $row->size,
            $row->mau,
            $row->so_luong,
            $row->ma_nv,
            $row->lenh_sx,
            $row->note,
        ];
    }
}
