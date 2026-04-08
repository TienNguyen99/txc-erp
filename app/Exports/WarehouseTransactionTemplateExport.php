<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class WarehouseTransactionTemplateExport implements WithHeadings, WithStyles
{
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

    public function styles(Worksheet $sheet)
    {
        $lastCol = 'I'; // 9 columns A-I
        $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1e3a5f'],
            ],
        ]);
        foreach (range('A', $lastCol) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Sample row
        $sheet->fromArray([
            'NHAPKHO', 'MH001', date('Y-m-d'), '60"', 'Trắng', '100', 'NV01', 'LSX001', 'Ghi chú mẫu'
        ], null, 'A2');

        $sheet->getStyle('A2:I2')->applyFromArray([
            'font' => ['italic' => true, 'color' => ['rgb' => '999999']],
        ]);

        // Dropdown for cong_doan
        $validation = $sheet->getCell('A2')->getDataValidation();
        $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
        $validation->setFormula1('"NHAPKHO,XUATKHO"');
        $validation->setShowDropDown(true);

        return [];
    }
}
