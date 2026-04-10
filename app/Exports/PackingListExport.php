<?php

namespace App\Exports;

use App\Models\DanhMucHangHoa;
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

                // Load tracking items with order & customer
                $trackings = OrderTracking::with('order.khachHang')
                    ->where('tracking_number', $this->trackingNumber)
                    ->get()
                    ->sortBy(fn($t) => $t->order->ma_hh ?? '');

                if ($trackings->isEmpty()) {
                    $sheet->setCellValue('A1', 'Không có dữ liệu cho OT: ' . $this->trackingNumber);
                    return;
                }

                // Preload carton specs per ma_hh
                $allMaHh = $trackings->pluck('order.ma_hh')->unique()->filter()->values();
                $cartonSpecs = DanhMucHangHoa::whereIn('ma_hh', $allMaHh)
                    ->whereNotNull('dinh_muc_thung')
                    ->get()
                    ->keyBy('ma_hh');

                // Derive header info
                $plNumbers  = $trackings->pluck('pl_number')->unique()->filter()->implode(', ');
                $firstOrder = $trackings->first()->order;
                $khachHang  = $firstOrder?->khachHang;
                $shipDate   = $firstOrder?->sig_need_date?->format('d/m/Y') ?? now()->format('d/m/Y');

                // ═══ HEADER SECTION ═══
                $row = 1;
                $this->setVal($sheet, "A{$row}", 'PACKING LIST  (PHIẾU XUẤT KHO CHIA THEO THÙNG)', true);
                $sheet->mergeCells("A{$row}:K{$row}");
                $sheet->getStyle("A{$row}")->getFont()->setSize(14)->setBold(true);
                $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $row++;
                $this->setVal($sheet, "A{$row}", 'PL NUMBER:', true);
                $this->setVal($sheet, "B{$row}", $plNumbers, true);

                $row++;
                $this->setVal($sheet, "A{$row}", 'SHIP DATE');
                $this->setVal($sheet, "B{$row}", $shipDate);

                $row++;
                $this->setVal($sheet, "A{$row}", 'SHIP TO:');
                $this->setVal($sheet, "B{$row}", $khachHang->ten_kh ?? '');

                $row++;
                $this->setVal($sheet, "A{$row}", 'Address:');
                $this->setVal($sheet, "B{$row}", $khachHang->dia_chi ?? '');

                $row += 2;
                $this->setVal($sheet, "A{$row}", 'SUPPLIER:', true);
                $this->setVal($sheet, "B{$row}", 'TEXENCO CORPORATION', true);

                $row++;
                $this->setVal($sheet, "A{$row}", 'Address:');
                $this->setVal($sheet, "B{$row}", '219 Le Van Chi, Linh Xuan Ward, Ho Chi Minh City, Vietnam');

                $row++;
                $this->setVal($sheet, "B{$row}", 'Ho Chi Minh City, Vietnam');

                $row++;
                $this->setVal($sheet, "A{$row}", 'Tel:');
                $this->setVal($sheet, "B{$row}", ' 028. 39003333');

                $row += 2;
                $this->setVal($sheet, "A{$row}", 'FACTORY:', true);
                $this->setVal($sheet, "B{$row}", 'VIETTIEN GARMENT CORPORATION', true);

                $row++;
                $this->setVal($sheet, "A{$row}", 'Address:');
                $this->setVal($sheet, "B{$row}", '7 Le Minh Xuan Street, Tân Sơn Nhất ward, HCMC - VN');

                $row++;
                $this->setVal($sheet, "A{$row}", 'Tel:');
                $this->setVal($sheet, "B{$row}", '028-38 640 800');

                $row++;
                $this->setVal($sheet, "A{$row}", 'Fax:');
                $this->setVal($sheet, "B{$row}", '84-028.38 645 085');

                $row += 2;
                $this->setVal($sheet, "A{$row}", 'CUSTOMER:', true);
                $this->setVal($sheet, "B{$row}", $khachHang->ten_kh ?? '', true);

                $row++;
                $this->setVal($sheet, "A{$row}", 'Address:');
                $this->setVal($sheet, "B{$row}", $khachHang->dia_chi ?? '');

                // CHI TIẾT GIAO HÀNG
                $row += 2;
                $this->setVal($sheet, "A{$row}", 'CHI TIẾT GIAO HÀNG', true);
                $sheet->mergeCells("A{$row}:K{$row}");
                $sheet->getStyle("A{$row}")->getFont()->setSize(11)->setBold(true);

                // ═══ TABLE HEADER ═══
                $row++;
                $headers = [
                    'JOB NO.', 'PO', 'Description', 'Màu',
                    'Số lượng (Grs)', 'Số lượng (yard)', 'Số lượng/ Carton No',
                    'SIZE', 'NET WEIGHT (KGS)', 'GROSS WEIGHT (KGS)', 'Carton No.',
                ];
                $cols = ['A','B','C','D','E','F','G','H','I','J','K'];
                foreach ($headers as $i => $h) {
                    $sheet->setCellValue("{$cols[$i]}{$row}", $h);
                }
                $sheet->getStyle("A{$row}:K{$row}")->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F2937']],
                    'font' => ['color' => ['rgb' => 'FFFFFF'], 'bold' => true, 'size' => 9],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]],
                ]);
                $sheet->getRowDimension($row)->setRowHeight(35);

                // ═══ DATA ROWS — per carton ═══
                $row++;
                $dataStartRow = $row;
                $cartonNo = 0;

                $grouped = $trackings->groupBy(fn($t) => $t->order->ma_hh ?? 'UNKNOWN');

                $grandTotalGrs = 0;
                $grandTotalYrd = 0;
                $grandTotalCartonQty = 0;
                $grandTotalNet = 0;
                $grandTotalGross = 0;

                foreach ($grouped as $maHh => $groupTrackings) {
                    $spec = $cartonSpecs[$maHh] ?? null;
                    $cap  = $spec->dinh_muc_thung ?? null;
                    $nwFull = $spec ? (float) $spec->net_weight : 0;
                    $gwFull = $spec ? (float) $spec->gross_weight : 0;

                    $description = $groupTrackings->first()->order->im_number ?? '';
                    $sizeName = $spec->ten_hh ?? $maHh;

                    $subGrs = 0; $subYrd = 0; $subCQ = 0; $subNW = 0; $subGW = 0;

                    // Group by fty_po within this product
                    $byPo = $groupTrackings->groupBy(fn($t) => $t->order->fty_po ?? '');

                    foreach ($byPo as $ftyPo => $poTrackings) {
                        $jobNos = $poTrackings->pluck('order.job_no')->unique()->filter()->implode("\n");
                        $color  = $poTrackings->first()->mau ?? $poTrackings->first()->order->color ?? '';
                        $tGrs   = $poTrackings->sum(fn($t) => $t->order->qty ?? 0);
                        $tYrd   = $poTrackings->sum(fn($t) => $t->sl_don_hang ?? $t->order->yrd ?? 0);

                        $subGrs += $tGrs;
                        $subYrd += $tYrd;

                        if ($cap && $cap > 0) {
                            $remaining = $tYrd;
                            $first = true;

                            while ($remaining > 0) {
                                $cartonNo++;
                                $cQty = min($remaining, $cap);
                                $remaining -= $cQty;

                                $ratio = $cQty / $cap;
                                $nw = round($nwFull * $ratio, 1);
                                $gw = round($gwFull * $ratio, 3);

                                $sheet->setCellValue("A{$row}", $jobNos);
                                $sheet->getStyle("A{$row}")->getAlignment()->setWrapText(true);
                                $sheet->setCellValue("B{$row}", $ftyPo);
                                $sheet->setCellValue("C{$row}", $description);
                                $sheet->setCellValue("D{$row}", $color);

                                if ($first) {
                                    $sheet->setCellValue("E{$row}", $tGrs);
                                    $sheet->setCellValue("F{$row}", $tYrd);
                                    $first = false;
                                }

                                $sheet->setCellValue("G{$row}", $cQty);
                                $sheet->setCellValue("H{$row}", $sizeName);
                                $sheet->setCellValue("I{$row}", $nw);
                                $sheet->setCellValue("J{$row}", $gw);
                                $sheet->setCellValue("K{$row}", $cartonNo);

                                $subCQ += $cQty;
                                $subNW += $nw;
                                $subGW += $gw;

                                $sheet->getStyle("E{$row}:F{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
                                $sheet->getStyle("G{$row}")->getNumberFormat()->setFormatCode('#,##0');
                                $sheet->getStyle("I{$row}:J{$row}")->getNumberFormat()->setFormatCode('#,##0.0##');

                                $row++;
                            }
                        } else {
                            // No carton spec → single row
                            $cartonNo++;
                            $sheet->setCellValue("A{$row}", $jobNos);
                            $sheet->getStyle("A{$row}")->getAlignment()->setWrapText(true);
                            $sheet->setCellValue("B{$row}", $ftyPo);
                            $sheet->setCellValue("C{$row}", $description);
                            $sheet->setCellValue("D{$row}", $color);
                            $sheet->setCellValue("E{$row}", $tGrs);
                            $sheet->setCellValue("F{$row}", $tYrd);
                            $sheet->setCellValue("G{$row}", $tYrd);
                            $sheet->setCellValue("H{$row}", $sizeName);
                            $sheet->setCellValue("K{$row}", $cartonNo);

                            $subCQ += $tYrd;
                            $sheet->getStyle("E{$row}:F{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
                            $sheet->getStyle("G{$row}")->getNumberFormat()->setFormatCode('#,##0');
                            $row++;
                        }
                    }

                    // SUBTOTAL per product type
                    $sheet->setCellValue("A{$row}", "TOTAL {$description}");
                    $sheet->mergeCells("A{$row}:D{$row}");
                    $sheet->setCellValue("E{$row}", $subGrs);
                    $sheet->setCellValue("F{$row}", $subYrd);
                    $sheet->setCellValue("G{$row}", $subCQ);
                    $sheet->setCellValue("I{$row}", round($subNW, 1));
                    $sheet->setCellValue("J{$row}", round($subGW, 3));

                    $sheet->getStyle("A{$row}:K{$row}")->applyFromArray([
                        'font' => ['bold' => true],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F3F4F6']],
                    ]);
                    $sheet->getStyle("E{$row}:F{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
                    $sheet->getStyle("G{$row}")->getNumberFormat()->setFormatCode('#,##0');
                    $sheet->getStyle("I{$row}:J{$row}")->getNumberFormat()->setFormatCode('#,##0.0##');

                    $grandTotalGrs += $subGrs;
                    $grandTotalYrd += $subYrd;
                    $grandTotalCartonQty += $subCQ;
                    $grandTotalNet += $subNW;
                    $grandTotalGross += $subGW;

                    $row++;
                }

                // ═══ GRAND TOTAL ═══
                $sheet->setCellValue("A{$row}", 'TOTAL');
                $sheet->mergeCells("A{$row}:D{$row}");
                $sheet->setCellValue("E{$row}", $grandTotalGrs);
                $sheet->setCellValue("F{$row}", $grandTotalYrd);
                $sheet->setCellValue("G{$row}", $grandTotalCartonQty);
                $sheet->setCellValue("I{$row}", round($grandTotalNet, 1));
                $sheet->setCellValue("J{$row}", round($grandTotalGross, 0));

                $sheet->getStyle("A{$row}:K{$row}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 10],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D1D5DB']],
                    'borders' => ['top' => ['borderStyle' => Border::BORDER_MEDIUM]],
                ]);
                $sheet->getStyle("E{$row}:G{$row}")->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle("I{$row}:J{$row}")->getNumberFormat()->setFormatCode('#,##0');

                // Data borders
                $sheet->getStyle("A{$dataStartRow}:K{$row}")->applyFromArray([
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                    'font' => ['size' => 9],
                    'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                ]);

                // ═══ SIGNATURE BLOCK ═══
                $row += 4;
                $sigMap = ['A' => 'KHÁCH HÀNG', 'C' => 'KINH DOANH', 'E' => 'THỦ KHO', 'G' => 'KẾ TOÁN', 'I' => 'GIÁM ĐỐC'];
                foreach ($sigMap as $col => $title) {
                    $this->setVal($sheet, "{$col}{$row}", $title, true);
                    $sheet->getStyle("{$col}{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }

                // ═══ COLUMN WIDTHS ═══
                $sheet->getColumnDimension('A')->setWidth(22);
                $sheet->getColumnDimension('B')->setWidth(20);
                $sheet->getColumnDimension('C')->setWidth(50);
                $sheet->getColumnDimension('D')->setWidth(22);
                $sheet->getColumnDimension('E')->setWidth(14);
                $sheet->getColumnDimension('F')->setWidth(14);
                $sheet->getColumnDimension('G')->setWidth(16);
                $sheet->getColumnDimension('H')->setWidth(28);
                $sheet->getColumnDimension('I')->setWidth(14);
                $sheet->getColumnDimension('J')->setWidth(16);
                $sheet->getColumnDimension('K')->setWidth(12);

                // Print settings
                $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
                $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
                $sheet->getPageSetup()->setFitToWidth(1);
                $sheet->getPageSetup()->setFitToHeight(0);
                $sheet->getPageMargins()->setTop(0.4);
                $sheet->getPageMargins()->setBottom(0.4);
                $sheet->getPageMargins()->setLeft(0.3);
                $sheet->getPageMargins()->setRight(0.3);

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
