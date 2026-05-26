<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class OtherCustomerOrderTemplateExport implements FromArray, WithHeadings, WithStyles
{
    public function headings(): array
    {
        return [
            'job_no',
            'nhan_vien_theo',
            'khach_hang',
            'chart',
            'ma_hh',
            'ten_hh',
            'size',
            'color',
            'unit',
            'yrd',
            'vi_tri',
            'order_receiving_date',
            'delivery_date',
            'customer_need_date',
            'noi_giao',
        ];
    }

    public function array(): array
    {
        return [
            [
                'PB1-SAMDANG-31517',
                'NGAN',
                'PLPC',
                '310613-AW25',
                '9810030133',
                'DAY RAI SILICONE 2 DUONG (SOI RECYCLE)',
                '30MM',
                'DKT-N07A BLACK',
                'MET',
                3980,
                '',
                '04/03/2025',
                '04/05/2025',
                '',
                'PLPC',
            ],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $lastCol = 'O';
        $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1F4E79'],
            ],
        ]);

        foreach (range('A', $lastCol) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $sheet->freezePane('A2');
        $sheet->setAutoFilter("A1:{$lastCol}1");
        $sheet->getStyle('J2')->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('L2:N2')->getNumberFormat()->setFormatCode('dd/mm/yyyy');

        return [];
    }
}
