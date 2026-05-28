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
            'chart',
            'nhan_vien_id',
            'khach_hang_id',
            'ma_hh',
            'quy_cach',
            'ten_hh',
            'kich_co',
            'color',
            'unit',
            'yrd',
            'tagtime_etc',
            'sig_need_date',
            'noi_giao',
            'job_no',
            'fty_po',
            'im_number',
            'qty',
            'can_giao_1',
            'can_giao_2',
            'pl_number',
            'price_usd_auto',
            'price_usd',
            'to_khai',
            'lenh_sanxuat',
            'status',
        ];
    }

    public function array(): array
    {
        return [
            [
                '310613-AW25',
                '',
                1,
                '9810030133',
                'Quan cuon',
                'DAY RAI SILICONE 2 DUONG (SOI RECYCLE)',
                '30MM',
                'DKT-N07A BLACK',
                'MET',
                3980,
                '04/03/2025',
                '04/05/2025',
                'PLPC',
                'PB1-SAMDANG-31517',
                'PB1-SAMDANG-31517',
                '9810030133',
                3980,
                '',
                '',
                'PB1-SAMDANG-31517',
                '',
                '',
                '',
                '',
                'pending',
            ],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $lastCol = 'Y';
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
        $sheet->getStyle('K2:L2')->getNumberFormat()->setFormatCode('dd/mm/yyyy');
        $sheet->getStyle('Q2:S2')->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('U2:V2')->getNumberFormat()->setFormatCode('#,##0.0000');

        return [];
    }
}
