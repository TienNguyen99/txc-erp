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

class PackingListSummarySheet implements WithEvents, WithTitle
{
    protected string $trackingNumber;

    public function __construct(string $trackingNumber)
    {
        $this->trackingNumber = $trackingNumber;
    }

    public function title(): string
    {
        return 'Summary';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $trackings = OrderTracking::with('order.khachHang')
                    ->where('tracking_number', $this->trackingNumber)
                    ->get()
                    ->sortBy(fn($t) => $t->order->ma_hh ?? '');

                if ($trackings->isEmpty()) {
                    $sheet->setCellValue('A1', 'Không có dữ liệu cho OT: ' . $this->trackingNumber);
                    return;
                }

                $allMaHh = $trackings->pluck('order.ma_hh')->unique()->filter()->values();
                $cartonSpecs = DanhMucHangHoa::whereIn('ma_hh', $allMaHh)
                    ->whereNotNull('dinh_muc_thung')
                    ->get()
                    ->keyBy('ma_hh');

                $row = 1;
                $dataStartRow = $row;
                $grandTotalPackages = 0;

                // Group by Product Code + Color
                $grouped = $trackings->groupBy(function($t) {
                    return ($t->order->ma_hh ?? 'UNKNOWN') . '|' . ($t->mau ?? $t->order->color ?? '');
                });

                $isYellowBg = true;

                foreach ($grouped as $key => $groupTrackings) {
                    $maHh = $groupTrackings->first()->order->ma_hh ?? 'UNKNOWN';
                    $color = $groupTrackings->first()->mau ?? $groupTrackings->first()->order->color ?? '';
                    $spec = $cartonSpecs[$maHh] ?? null;

                    $cap  = $spec->dinh_muc_thung ?? null;
                    $isRoll = false;
                    $rollsPerCarton = 0;
                    $yardsPerRoll = 0;

                    if ($spec && $spec->quy_cach === 'Quấn cuộn' && $spec->yards_per_roll > 0) {
                        $isRoll = true;
                        $yardsPerRoll = $spec->yards_per_roll;
                        $rollsPerCarton = $cap > 0 ? floor($cap / $yardsPerRoll) : 0;
                    }

                    $sizeName = $spec->ten_hh ?? $maHh;
                    $headerText = strtoupper($sizeName . ' ' . $color);

                    // Determine background color for this group
                    $bgColor = $isYellowBg ? 'FFFFFF00' : 'FFFFFFFF'; // Yellow or White
                    $isYellowBg = !$isYellowBg; // Alternate

                    // HEADER ROW
                    $sheet->setCellValue("A{$row}", $headerText);
                    $sheet->mergeCells("A{$row}:C{$row}");
                    $sheet->getStyle("A{$row}:C{$row}")->applyFromArray([
                        'font' => ['bold' => true, 'color' => ['rgb' => '0000FF'], 'size' => 10],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $bgColor]],
                    ]);
                    $row++;

                    // Calculate packages
                    $packages = [];
                    $byPo = $groupTrackings->groupBy(fn($t) => $t->order->fty_po ?? '');

                    foreach ($byPo as $ftyPo => $poTrackings) {
                        $tYrd   = $poTrackings->sum(fn($t) => $t->sl_don_hang ?? $t->order->yrd ?? 0);
                        if ($cap && $cap > 0) {
                            $remaining = $tYrd;
                            while ($remaining > 0) {
                                $cQty = min($remaining, $cap);
                                $remaining -= $cQty;
                                $qtyKey = (string)$cQty;
                                if (!isset($packages[$qtyKey])) $packages[$qtyKey] = 0;
                                $packages[$qtyKey]++;
                            }
                        } else {
                            $qtyKey = (string)$tYrd;
                            if (!isset($packages[$qtyKey])) $packages[$qtyKey] = 0;
                            $packages[$qtyKey]++;
                        }
                    }

                    // Sort packages so full cartons appear first (highest quantity first)
                    krsort($packages, SORT_NUMERIC);

                    foreach ($packages as $qty => $count) {
                        $packageText = $count . ' PACKAGE';
                        
                        $qtyFloat = (float)$qty;
                        $descText = number_format($qtyFloat, 0, ',', '.') . ' YARD';
                        
                        // Add roll info if full carton
                        if ($isRoll && abs($qtyFloat - $cap) < 0.01 && $rollsPerCarton > 0) {
                            $descText .= "( " . $rollsPerCarton . " ROLL*" . $yardsPerRoll . " YARD)";
                        }

                        $totalYards = $count * $qtyFloat;

                        $sheet->setCellValue("A{$row}", $packageText);
                        $sheet->setCellValue("B{$row}", $descText);
                        $sheet->setCellValue("C{$row}", $totalYards);
                        
                        $sheet->getStyle("A{$row}:C{$row}")->applyFromArray([
                            'font' => ['bold' => true, 'color' => ['rgb' => '0000FF'], 'size' => 10],
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $bgColor]],
                        ]);
                        
                        $sheet->getStyle("C{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
                        
                        $grandTotalPackages += $count;
                        $row++;
                    }
                }

                // GRAND TOTAL
                $sheet->setCellValue("A{$row}", 'TOTAL');
                $sheet->setCellValue("B{$row}", $grandTotalPackages . ' PACKAGE');
                $sheet->getStyle("A{$row}:C{$row}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => '0000FF'], 'size' => 10],
                    'borders' => [
                        'top' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '0000FF']],
                        'bottom' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '0000FF']],
                    ],
                ]);

                // Set column widths
                $sheet->getColumnDimension('A')->setWidth(20);
                $sheet->getColumnDimension('B')->setWidth(40);
                $sheet->getColumnDimension('C')->setWidth(20);

                // Add grid lines (thin blue lines)
                $sheet->getStyle("A{$dataStartRow}:C{$row}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_HAIR,
                            'color' => ['rgb' => '0000FF'],
                        ],
                    ],
                ]);
            },
        ];
    }
}
