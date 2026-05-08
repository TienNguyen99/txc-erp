<?php

namespace App\Exports\Sheets;

use App\Models\DanhMucHangHoa;
use App\Models\OrderTracking;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\RichText\RichText;

class PackingListLabelSheet implements WithEvents, WithTitle
{
    protected string $trackingNumber;

    public function __construct(string $trackingNumber)
    {
        $this->trackingNumber = $trackingNumber;
    }

    public function title(): string
    {
        return 'Labels';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Load tracking items
                $trackings = OrderTracking::with('order.khachHang')
                    ->where('tracking_number', $this->trackingNumber)
                    ->get()
                    ->sortBy(fn($t) => $t->order->ma_hh ?? '');

                if ($trackings->isEmpty()) return;

                $allMaHh = $trackings->pluck('order.ma_hh')->unique()->filter()->values();
                $cartonSpecs = DanhMucHangHoa::whereIn('ma_hh', $allMaHh)
                    ->whereNotNull('dinh_muc_thung')
                    ->get()
                    ->keyBy('ma_hh');

                $plNumbers  = $trackings->pluck('pl_number')->unique()->filter()->implode(', ');
                $firstOrder = $trackings->first()->order;
                $khachHang  = $firstOrder?->khachHang;
                $shipDate   = $firstOrder?->sig_need_date?->format('d/m/Y') ?? now()->format('d/m/Y');

                $grouped = $trackings->groupBy(fn($t) => $t->order->ma_hh ?? 'UNKNOWN');

                $cartonsData = [];

                foreach ($grouped as $maHh => $groupTrackings) {
                    $spec = $cartonSpecs[$maHh] ?? null;
                    $cap  = $spec->dinh_muc_thung ?? null;
                    $nwFull = $spec ? (float) $spec->net_weight : 0;
                    $gwFull = $spec ? (float) $spec->gross_weight : 0;
                    $sizeName = $spec->ten_hh ?? $maHh;
                    
                    $byPo = $groupTrackings->groupBy(fn($t) => $t->order->fty_po ?? '');
                    
                    foreach ($byPo as $ftyPo => $poTrackings) {
                        $jobNosArr = $poTrackings->pluck('order.job_no')->unique()->filter()->values();
                        $jobNoStr = $jobNosArr->implode("\n");
                        $color  = $poTrackings->first()->mau ?? $poTrackings->first()->order->color ?? '';
                        $tYrd   = $poTrackings->sum(fn($t) => $t->sl_don_hang ?? $t->order->yrd ?? 0);
                        
                        $description = $poTrackings->first()->order->im_number ?? '';
                        $itemCode = $sizeName . ' ' . $description;

                        if ($cap && $cap > 0) {
                            $remaining = $tYrd;
                            while ($remaining > 0) {
                                $cQty = min($remaining, $cap);
                                $remaining -= $cQty;
                                $ratio = $cQty / $cap;
                                $nw = round($nwFull * $ratio, 1);
                                $gw = round($gwFull * $ratio, 3);
                                
                                $cartonsData[] = [
                                    'date' => $shipDate,
                                    'customer' => $khachHang->ten_kh ?? '',
                                    'pkl' => $plNumbers,
                                    'item_code' => $itemCode,
                                    'color' => $color,
                                    'nw' => $nw,
                                    'gw' => $gw,
                                    'job' => $jobNoStr,
                                    'po' => $ftyPo,
                                    'qty' => $cQty
                                ];
                            }
                        } else {
                            $cartonsData[] = [
                                'date' => $shipDate,
                                'customer' => $khachHang->ten_kh ?? '',
                                'pkl' => $plNumbers,
                                'item_code' => $itemCode,
                                'color' => $color,
                                'nw' => 0,
                                'gw' => 0,
                                'job' => $jobNoStr,
                                'po' => $ftyPo,
                                'qty' => $tYrd
                            ];
                        }
                    }
                }

                $totalCartons = count($cartonsData);
                
                // Draw grid
                $sheet->getColumnDimension('A')->setWidth(15);
                $sheet->getColumnDimension('B')->setWidth(35);
                $sheet->getColumnDimension('C')->setWidth(3); // spacer
                $sheet->getColumnDimension('D')->setWidth(15);
                $sheet->getColumnDimension('E')->setWidth(35);

                $r = 1;
                $colIndex = 0; // 0 for left (A,B), 1 for right (D,E)
                
                foreach ($cartonsData as $idx => $carton) {
                    $carton['carton'] = $idx + 1;
                    $carton['total'] = $totalCartons;
                    
                    if ($colIndex == 0) {
                        $this->drawLabel($sheet, $r, 'A', 'B', $carton);
                        $colIndex = 1;
                    } else {
                        $this->drawLabel($sheet, $r, 'D', 'E', $carton);
                        $colIndex = 0;
                        $r += 14; // move down for next row of labels
                    }
                }

                // Page setup for printing
                $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_PORTRAIT);
                $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
                $sheet->getPageSetup()->setFitToWidth(1);
                $sheet->getPageSetup()->setFitToHeight(0);
                $sheet->getPageMargins()->setTop(0.4);
                $sheet->getPageMargins()->setBottom(0.4);
                $sheet->getPageMargins()->setLeft(0.4);
                $sheet->getPageMargins()->setRight(0.4);
            }
        ];
    }

    private function drawLabel(Worksheet $sheet, int $startRow, string $colA, string $colB, array $data)
    {
        $r = $startRow;
        
        // Header
        $sheet->mergeCells("{$colA}{$r}:{$colB}{$r}");
        $sheet->setCellValue("{$colA}{$r}", 'TEXENCO CORPORATION');
        $sheet->getStyle("{$colA}{$r}")->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle("{$colA}{$r}")->getFont()->getColor()->setRGB('0000FF');
        $sheet->getStyle("{$colA}{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("{$colA}{$r}:{$colB}{$r}")->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);
        $r++;

        $sheet->mergeCells("{$colA}{$r}:{$colB}{$r}");
        $sheet->setCellValue("{$colA}{$r}", 'PRODUCT INFORMATION');
        $sheet->getStyle("{$colA}{$r}")->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle("{$colA}{$r}")->getFont()->getColor()->setRGB('0000FF');
        $sheet->getStyle("{$colA}{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("{$colA}{$r}:{$colB}{$r}")->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);
        $r++;

        $fields = [
            'DATE:' => $data['date'],
            'CUSTOMER:' => $data['customer'],
            'PKL No:' => $data['pkl'],
            'Item code' => $data['item_code'],
            'Color' => $data['color'],
            'N/WEIGHT' => $data['nw'] . ' kgs',
            'G/WEIGHT' => $data['gw'] . ' kgs',
            'JOB No.' => $data['job'],
            'PO' => $data['po'],
            'QUANTITY:' => $data['qty'] . ' YARD',
            'Carton No:' => $data['carton'] . ' / ' . $data['total'],
        ];

        foreach ($fields as $label => $val) {
            $sheet->setCellValue("{$colA}{$r}", $label);
            
            if ($label === 'Carton No:') {
                $richText = new RichText();
                $richText->createText($val . "     ");
                $redText = $richText->createTextRun("MADE IN VIET NAM");
                $redText->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED));
                $redText->getFont()->setBold(true)->setSize(10);
                
                $sheet->setCellValue("{$colB}{$r}", $richText);
            } else {
                $sheet->setCellValue("{$colB}{$r}", $val);
            }

            $sheet->getStyle("{$colA}{$r}")->getFont()->setBold(true)->setItalic(true);
            $sheet->getStyle("{$colB}{$r}")->getFont()->setBold(true);
            
            if ($label === 'Item code') {
                $sheet->getStyle("{$colB}{$r}")->getAlignment()->setWrapText(true);
                $sheet->getRowDimension($r)->setRowHeight(30);
            }
            if ($label === 'JOB No.') {
                $sheet->getStyle("{$colB}{$r}")->getAlignment()->setWrapText(true);
                $sheet->getRowDimension($r)->setRowHeight(30);
            }
            
            $sheet->getStyle("{$colB}{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle("{$colA}{$r}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

            if ($label === 'JOB No.' || $label === 'PO') {
                $sheet->getStyle("{$colA}{$r}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFFF00');
            }
            if ($label === 'QUANTITY:') {
                $sheet->getStyle("{$colB}{$r}")->getFont()->setSize(18);
                $sheet->getRowDimension($r)->setRowHeight(25);
            }
            if ($label === 'Carton No:') {
                $sheet->getStyle("{$colB}{$r}")->getFont()->setSize(18);
                $sheet->getRowDimension($r)->setRowHeight(25);
            }

            $sheet->getStyle("{$colA}{$r}:{$colB}{$r}")->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
            ]);
            $r++;
        }

        // Outer border
        $sheet->getStyle("{$colA}{$startRow}:{$colB}" . ($r - 1))->applyFromArray([
            'borders' => ['outline' => ['borderStyle' => Border::BORDER_MEDIUM]]
        ]);
    }
}
