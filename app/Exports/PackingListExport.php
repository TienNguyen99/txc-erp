<?php

namespace App\Exports;

use App\Models\OrderTracking;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PackingListExport implements WithEvents
{
    protected string $trackingNumber;

    public function __construct(string $trackingNumber)
    {
        $this->trackingNumber = $trackingNumber;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Load tracking items with order for this OT number
                $trackings = OrderTracking::with('order.khachHang')
                    ->where('tracking_number', $this->trackingNumber)
                    ->get()
                    ->sortBy([fn($a, $b) => ($a->order->ma_hh ?? '') <=> ($b->order->ma_hh ?? ''), fn($a, $b) => ($a->order->job_no ?? '') <=> ($b->order->job_no ?? '')]);

                if ($trackings->isEmpty()) {
                    $sheet->setCellValue('A1', 'Không có dữ liệu cho OT: ' . $this->trackingNumber);
                    return;
                }

                // Derive PL number & customer from tracking data
                $plNumbers  = $trackings->pluck('pl_number')->unique()->filter()->implode(', ');
                $firstOrder = $trackings->first()->order;
                $khachHang  = $firstOrder?->khachHang;
                $shipDate   = $firstOrder?->sig_need_date?->format('d/m/Y') ?? now()->format('d/m/Y');

                // ═══ HEADER ═══
                $row = 1;
                $this->setVal($sheet, "A{$row}", 'PACKING LIST  (PHIẾU XUẤT KHO)', true);
                $sheet->mergeCells("A{$row}:H{$row}");
                $sheet->getStyle("A{$row}")->getFont()->setSize(14)->setBold(true);
                $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $row += 1;
                $this->setVal($sheet, "A{$row}", 'OT NUMBER:');
                $this->setVal($sheet, "B{$row}", $this->trackingNumber, true);

                $row += 1;
                $this->setVal($sheet, "A{$row}", 'PL NUMBER:');
                $this->setVal($sheet, "B{$row}", $plNumbers, true);

                $row += 1;
                $this->setVal($sheet, "A{$row}", 'SHIP DATE:');
                $this->setVal($sheet, "B{$row}", $shipDate);

                $row += 1;
                $this->setVal($sheet, "A{$row}", 'SHIP TO:');
                $this->setVal($sheet, "B{$row}", $khachHang->ten_kh ?? '');

                $row += 1;
                $this->setVal($sheet, "A{$row}", 'Address:');
                $this->setVal($sheet, "B{$row}", $khachHang->dia_chi ?? '');

                $row += 2;
                $this->setVal($sheet, "A{$row}", 'SUPPLIER:');
                $this->setVal($sheet, "B{$row}", 'TEXENCO CORPORATION', true);

                $row += 1;
                $this->setVal($sheet, "A{$row}", 'Address:');
                $this->setVal($sheet, "B{$row}", '219 Le Van Chi, Linh Xuan Ward, Ho Chi Minh City, Vietnam');

                $row += 1;
                $this->setVal($sheet, "A{$row}", 'Tel:');
                $this->setVal($sheet, "B{$row}", '84-028. 39003333');

                // ═══ TABLE HEADER ═══
                $row += 2;
                $headerRow = $row;
                $headers = ['JOB REF', 'P/O No', 'Description', 'Colour', 'ORDER Qty (GRS)', 'ORDER Qty (YARD)', 'DELIVERY Qty (YARD)', 'NOTE'];
                foreach ($headers as $i => $h) {
                    $col = chr(65 + $i); // A-H
                    $this->setVal($sheet, "{$col}{$row}", $h, true);
                }
                $sheet->getStyle("A{$row}:H{$row}")->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F2937']],
                    'font' => ['color' => ['rgb' => 'FFFFFF'], 'bold' => true, 'size' => 9],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);
                $sheet->getRowDimension($row)->setRowHeight(30);

                // ═══ DATA ROWS — grouped by ma_hh (NOTE column) ═══
                $row += 1;
                $dataStartRow = $row;
                $grouped = $trackings->groupBy(fn($t) => $t->order->ma_hh ?? '');
                $groupTotals = []; // ma_hh => [grs, yrd]

                foreach ($grouped as $maHh => $group) {
                    foreach ($group as $tracking) {
                        $order = $tracking->order;
                        $grs = $order->qty ?? 0;
                        $yrd = $tracking->sl_don_hang ?? $order->yrd ?? 0;
                        $note = $maHh ?: '';

                        $sheet->setCellValue("A{$row}", $order->job_no ?? '');
                        $sheet->setCellValue("B{$row}", $order->fty_po ?? '');
                        $sheet->setCellValue("C{$row}", $order->im_number ?? '');
                        $sheet->setCellValue("D{$row}", $tracking->mau ?? $order->color ?? '');
                        $sheet->setCellValue("E{$row}", $grs);
                        $sheet->setCellValue("F{$row}", $yrd);
                        $sheet->setCellValue("G{$row}", $yrd);
                        $sheet->setCellValue("H{$row}", $note);

                        $sheet->getStyle("E{$row}:G{$row}")->getNumberFormat()->setFormatCode('#,##0.00');

                        $row++;
                    }

                    // SUBTOTAL row per group
                    $totalGrs = $group->sum(fn($t) => $t->order->qty ?? 0);
                    $totalYrd = $group->sum(fn($t) => $t->sl_don_hang ?? $t->order->yrd ?? 0);
                    $groupTotals[$maHh] = ['grs' => $totalGrs, 'yrd' => $totalYrd, 'count' => $group->count()];

                    $sheet->setCellValue("A{$row}", "TOTAL {$maHh}");
                    $sheet->mergeCells("A{$row}:D{$row}");
                    $sheet->setCellValue("E{$row}", $totalGrs);
                    $sheet->setCellValue("F{$row}", $totalYrd);
                    $sheet->setCellValue("G{$row}", $totalYrd);

                    $sheet->getStyle("A{$row}:H{$row}")->applyFromArray([
                        'font' => ['bold' => true],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F3F4F6']],
                    ]);
                    $sheet->getStyle("E{$row}:G{$row}")->getNumberFormat()->setFormatCode('#,##0.00');

                    $row++;
                }

                // ═══ GRAND TOTAL ═══
                $grandGrs   = $trackings->sum(fn($t) => $t->order->qty ?? 0);
                $grandYrd   = $trackings->sum(fn($t) => $t->sl_don_hang ?? $t->order->yrd ?? 0);
                $grandCount = $trackings->count();

                $sheet->setCellValue("A{$row}", 'TOTAL');
                $sheet->mergeCells("A{$row}:D{$row}");
                $sheet->setCellValue("E{$row}", $grandGrs);
                $sheet->setCellValue("F{$row}", $grandYrd);
                $sheet->setCellValue("G{$row}", $grandYrd);
                $sheet->setCellValue("H{$row}", "{$grandCount} items");

                $sheet->getStyle("A{$row}:H{$row}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 10],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D1D5DB']],
                    'borders' => ['top' => ['borderStyle' => Border::BORDER_MEDIUM]],
                ]);
                $sheet->getStyle("E{$row}:G{$row}")->getNumberFormat()->setFormatCode('#,##0.00');

                // Data borders
                $sheet->getStyle("A{$dataStartRow}:H{$row}")->applyFromArray([
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                    'font' => ['size' => 9],
                ]);

                // ═══ PACKAGE SUMMARY ═══
                $row += 2;
                $this->setVal($sheet, "A{$row}", 'ĐÓNG GÓI / PACKAGE SUMMARY', true);
                $sheet->mergeCells("A{$row}:H{$row}");
                $sheet->getStyle("A{$row}")->getFont()->setSize(11)->setBold(true);
                $sheet->getStyle("A{$row}")->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E5E7EB']],
                ]);

                $row += 1;
                $totalPackages = 0;
                foreach ($groupTotals as $maHh => $info) {
                    $this->setVal($sheet, "B{$row}", $maHh, true);
                    $sheet->setCellValue("C{$row}", number_format($info['yrd'], 0, '.', '.') . ' YARD');
                    $sheet->setCellValue("D{$row}", $info['count'] . ' items');
                    $totalPackages += $info['count'];
                    $row++;
                }

                $row++;
                $this->setVal($sheet, "A{$row}", "TOTAL: {$totalPackages} PACKAGE", true);
                $sheet->mergeCells("A{$row}:D{$row}");
                $sheet->getStyle("A{$row}")->getFont()->setSize(10)->setBold(true);

                // ═══ SIGNATURE BLOCK ═══
                $row += 3;
                $sigTitles = ['KHÁCH HÀNG', 'KINH DOANH', 'THỦ KHO', 'KẾ TOÁN', 'GIÁM ĐỐC'];
                $sigCols   = ['A', 'B', 'C', 'E', 'G'];
                foreach ($sigTitles as $idx => $title) {
                    $col = $sigCols[$idx];
                    $this->setVal($sheet, "{$col}{$row}", $title, true);
                    $sheet->getStyle("{$col}{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }

                // ═══ COLUMN WIDTHS ═══
                $sheet->getColumnDimension('A')->setWidth(18);
                $sheet->getColumnDimension('B')->setWidth(20);
                $sheet->getColumnDimension('C')->setWidth(50);
                $sheet->getColumnDimension('D')->setWidth(25);
                $sheet->getColumnDimension('E')->setWidth(14);
                $sheet->getColumnDimension('F')->setWidth(14);
                $sheet->getColumnDimension('G')->setWidth(14);
                $sheet->getColumnDimension('H')->setWidth(30);

                // Print settings
                $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
                $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
                $sheet->getPageSetup()->setFitToWidth(1);
                $sheet->getPageSetup()->setFitToHeight(0);
                $sheet->getPageMargins()->setTop(0.4);
                $sheet->getPageMargins()->setBottom(0.4);
                $sheet->getPageMargins()->setLeft(0.4);
                $sheet->getPageMargins()->setRight(0.4);

                $sheet->setTitle('Packing List');
            },
        ];
    }

    private function setVal(Worksheet $sheet, string $cell, string $value, bool $bold = false): void
    {
        $sheet->setCellValue($cell, $value);
        if ($bold) {
            $sheet->getStyle($cell)->getFont()->setBold(true);
        }
    }
}
