<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class OrderTemplateExport implements WithHeadings, WithStyles
{
    public function headings(): array
    {
        return [
            'chart',
            'nhan_vien_id',
            'ma_kh',
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
        $sheet->getStyle('K2:L200')->getNumberFormat()->setFormatCode('dd/mm/yyyy');

        return [];
    }
}
