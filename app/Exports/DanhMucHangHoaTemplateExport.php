<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DanhMucHangHoaTemplateExport implements WithHeadings, WithStyles
{
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
            'dinh_muc_thung',
            'net_weight',
            'gross_weight',
            'mo_ta',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:K1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1F4E79'],
            ],
        ]);

        foreach (range('A', 'K') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return [];
    }
}
