<?php

namespace App\Exports;

use App\Models\DanhMucHangHoa;
use App\Models\DanhMucKhachHang;
use App\Models\Order;
use App\Models\OrderTracking;
use App\Models\ProductionReport;
use App\Models\WarehouseTransaction;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LenhSanXuatExport implements FromArray, WithTitle, WithColumnWidths, WithStyles, WithEvents
{
    protected string $trackingNumber;
    protected float $pctHaoHut;

    public function __construct(string $trackingNumber, float $pctHaoHut = 10)
    {
        $this->trackingNumber = $trackingNumber;
        $this->pctHaoHut = $pctHaoHut;
    }

    public function title(): string
    {
        return 'LỆNH SẢN XUẤT';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,   // STT
            'B' => 18,  // MÃ LỆNH SX
            'C' => 35,  // MÃ HÀNG
            'D' => 35,  // TÊN SẢN PHẨM
            'E' => 18,  // MÀU
            'F' => 15,  // QUY CÁCH
            'G' => 10,  // SIZE
            'H' => 12,  // SỐ LƯỢNG
            'I' => 12,  // SL TỒN KHO
            'J' => 14,  // SL + %HH
            'K' => 8,   // ĐVT
        ];
    }

    public function array(): array
    {
        return []; // We build content in AfterSheet
    }

    public function styles(Worksheet $sheet): array
    {
        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // --- Lấy dữ liệu ---
                $lenh = \App\Models\LenhSanXuat::where('lenh_so', $this->trackingNumber)->first();
                $items = $lenh ? $lenh->items()->where('da_len_lenh', true)->get() : collect();

                // Lấy orders thuộc Chart này
                $orders = $lenh ? Order::with('khachHang')->where('chart', $lenh->chart)->get() : collect();

                // Thông tin chung
                $firstOrder = $orders->first();
                $khachHang = $firstOrder?->khachHang;
                $plNumbers = $orders->pluck('pl_number')->unique()->filter()->implode(', ');
                $ftyPos = $orders->pluck('fty_po')->unique()->filter()->implode(', ');
                $charts = $orders->pluck('chart')->unique()->filter()->implode(', ');
                $sigDate = $orders->pluck('sig_need_date')->filter()->min();
                $sigDateFormatted = $sigDate ? \Carbon\Carbon::parse($sigDate)->format('d/m/Y') : '';

                // --- HEADER ---
                // Row 1-2: LỆNH SẢN XUẤT
                $sheet->mergeCells('A1:G2');
                $sheet->setCellValue('A1', "LỆNH SẢN XUẤT\n( TRUY CẬP ZALO QUÉT MÃ QR SAU KHI KẾT THÚC CA SẢN XUẤT )");
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => '003366']],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ],
                ]);

                // QR Code placeholder (text link — actual QR can be generated separately)
                $sheet->mergeCells('H1:I2');
                $qrUrl = url('/lenh-sx/' . $this->trackingNumber);
                $sheet->setCellValue('H1', "QR: {$qrUrl}");
                $sheet->getStyle('H1')->applyFromArray([
                    'font' => ['size' => 8],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                ]);

                // LỆNH SỐ
                $sheet->mergeCells('J1:K1');
                $sheet->setCellValue('J1', 'LỆNH SỐ:');
                $sheet->getStyle('J1')->getFont()->setBold(true);
                $sheet->mergeCells('J2:K2');
                $sheet->setCellValue('J2', $this->trackingNumber);
                $sheet->getStyle('J2')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'CC0000']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // Row 3: Khách hàng / Nơi giao / Ngày nhận
                $sheet->setCellValue('A3', 'KHÁCH HÀNG:');
                $sheet->getStyle('A3')->getFont()->setBold(true);
                $sheet->mergeCells('B3:D3');
                $sheet->setCellValue('B3', $khachHang?->ten_kh ?? '');

                $sheet->setCellValue('E3', 'NƠI GIAO:');
                $sheet->getStyle('E3')->getFont()->setBold(true);
                $sheet->mergeCells('F3:H3');
                $sheet->setCellValue('F3', $khachHang?->dia_chi ?? '');

                $sheet->setCellValue('I3', 'NGÀY NHẬN:');
                $sheet->getStyle('I3')->getFont()->setBold(true);
                $sheet->mergeCells('J3:K3');
                $sheet->setCellValue('J3', now()->format('d/m/Y'));

                // Row 4: PO / SIV
                $sheet->setCellValue('A4', 'PO:');
                $sheet->getStyle('A4')->getFont()->setBold(true);
                $sheet->mergeCells('B4:D4');
                $sheet->setCellValue('B4', $ftyPos);

                $sheet->setCellValue('E4', 'Chart:');
                $sheet->getStyle('E4')->getFont()->setBold(true);
                $sheet->mergeCells('F4:H4');
                $sheet->setCellValue('F4', $charts);

                $sheet->setCellValue('I4', 'NGÀY GIAO:');
                $sheet->getStyle('I4')->getFont()->setBold(true);
                $sheet->mergeCells('J4:K4');
                $sheet->setCellValue('J4', $sigDateFormatted);

                // --- TABLE HEADER (Row 5) ---
                $headers = ['STT', 'MÃ LỆNH SX/HH', 'MÃ HÀNG', 'TÊN SẢN PHẨM', 'MÀU', 'ART/QUY CÁCH', 'SIZE', 'SỐ LƯỢNG', 'SL TỒN KHO', 'SỐ LƯỢNG +%HH', 'ĐVT'];
                $cols = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K'];
                foreach ($headers as $i => $h) {
                    $sheet->setCellValue($cols[$i] . '5', $h);
                }
                $sheet->getStyle('A5:K5')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 9, 'color' => ['rgb' => '003366']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFF00']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);

                $row = 6;
                foreach ($items as $item) {
                    $hangHoa = DanhMucHangHoa::where('ma_hh', $item->ma_hh)->first();
                    $nhap = WarehouseTransaction::where('ma_hh', $item->ma_hh)->nhapKho()->sum('so_luong');
                    $xuat = WarehouseTransaction::where('ma_hh', $item->ma_hh)->xuatKho()->sum('so_luong');
                    $tonKho = $nhap - $xuat;
                    $slPlusHh = $item->sl_can_sx;

                    $sheet->setCellValue('A' . $row, $item->stt);
                    $sheet->setCellValue('B' . $row, $item->lenh_child);
                    $sheet->setCellValue('C' . $row, $item->ma_hh);
                    $sheet->setCellValue('D' . $row, $hangHoa?->ten_hh ?? $item->ten_hh);
                    $sheet->setCellValue('E' . $row, $item->mau);
                    $sheet->setCellValue('F' . $row, $hangHoa?->nhom_hh ?? '');
                    $sheet->setCellValue('G' . $row, $hangHoa?->kich_co ?? '');
                    $sheet->setCellValue('H' . $row, $item->tong_yrd);
                    $sheet->setCellValue('I' . $row, $tonKho);
                    $sheet->setCellValue('J' . $row, round($slPlusHh, 2));
                    $sheet->setCellValue('K' . $row, $hangHoa?->don_vi ?? 'YRD');

                    $sheet->getStyle("A{$row}:K{$row}")->applyFromArray([
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                        'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                    ]);
                    $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("H{$row}:J{$row}")->getNumberFormat()->setFormatCode('#,##0.00');

                    $sheet->getRowDimension($row)->setRowHeight(25);
                    $row++;
                }

                // Borders for header area
                $sheet->getStyle('A1:K4')->applyFromArray([
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);

                // Row heights
                $sheet->getRowDimension(1)->setRowHeight(22);
                $sheet->getRowDimension(2)->setRowHeight(22);
                $sheet->getRowDimension(5)->setRowHeight(30);

                // Print settings
                $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
                $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
                $sheet->getPageSetup()->setFitToWidth(1);
                $sheet->getPageSetup()->setFitToHeight(0);
            },
        ];
    }
}
