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
            'job_no',
            'fty_po',
            'im_number',
            'color',
            'qty',
            'unit',
            'size',
            'yrd',
            'can_giao_1',
            'can_giao_2',
            'pl_number',
            'tagtime_etc',
            'sig_need_date',
            'chart',
            'price_usd_auto',
            'price_usd',
            'to_khai',
            'status',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $lastCol = 'R'; // 18 columns A-R
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

        return [];
    }
}
