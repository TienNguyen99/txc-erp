<?php

namespace App\Exports;

use App\Models\DanhMucHangHoa;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class DanhMucHangHoaExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return DanhMucHangHoa::orderBy('ma_hh')->get();
    }

    public function headings(): array
    {
        return [
            'ma_hh',
            'ten_hh',
            'mau',
            'kich_co',
            'nhom_hh',
            'don_vi',
            'don_gia',
            'active',
        ];
    }

    public function map($row): array
    {
        return [
            $row->ma_hh,
            $row->ten_hh,
            $row->mau,
            $row->kich_co,
            $row->nhom_hh,
            $row->don_vi,
            $row->don_gia,
            $row->active ? 'Yes' : 'No',
        ];
    }
}
