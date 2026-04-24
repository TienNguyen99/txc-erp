<?php

namespace App\Exports;

use App\Models\DanhMucHangHoa;
use App\Models\Order;
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
            'A' => 20,  // MÃ HÀNG
            'B' => 30,  // TÊN SẢN PHẨM
            'C' => 15,  // MÀU
            'D' => 15,  // ART/QUY CÁCH
            'E' => 10,  // SIZE
            'F' => 12,  // SỐ LƯỢNG
            'G' => 12,  // SL TỒN KHO
            'H' => 14,  // SỐ LƯỢNG + % HH
            'I' => 8,   // ĐVT
            'J' => 20,  // HÌNH ẢNH
        ];
    }

    public function array(): array
    {
        return [];
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

                // --- GIỮ NGUYÊN LOGIC DỮ LIỆU CỦA BẠN ---
                $lenh = \App\Models\LenhSanXuat::where('lenh_so', $this->trackingNumber)->first();
                $items = $lenh ? $lenh->items()->where('da_len_lenh', true)->get() : collect();
                $orders = $lenh ? Order::with('khachHang')->where('chart', $lenh->chart)->get() : collect();
                $firstOrder = $orders->first();
                $khachHang = $firstOrder?->khachHang;
                $ftyPos = $orders->pluck('fty_po')->unique()->filter()->implode(', ');
                $charts = $orders->pluck('chart')->unique()->filter()->implode(', ');
                $sigDateFormatted = $orders->pluck('sig_need_date')->filter()->min() ? \Carbon\Carbon::parse($orders->pluck('sig_need_date')->filter()->min())->format('d/m/Y') : '';

                // --- 1. HEADER (Merge & Style) ---
                $sheet->mergeCells('A1:G2');
                $sheet->setCellValue('A1', "LỆNH SẢN XUẤT\n( TRUY CẬP ZALO QUÉT MÃ QR SAU KHI KẾT THÚC CA SẢN XUẤT )");
                $sheet->getStyle('A1')->getAlignment()->setWrapText(true)->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->setColor(new Color('003366'));

                // QR CODE
                $sheet->mergeCells('H1:H2');
                $appUrl = env('APP_URL', 'http://192.168.1.25:8888');
                $qrLink = $appUrl . '/lenh-sx/' . $this->trackingNumber;
                $sheet->setCellValue('H1', '=_xlfn.IMAGE("https://api.qrserver.com/v1/create-qr-code/?data=' . $qrLink . '")');
                $sheet->getStyle('H1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);

                // LỆNH SỐ
                $sheet->mergeCells('I1:J1');
                $sheet->setCellValue('I1', 'LỆNH SỐ:');
                $sheet->mergeCells('I2:J2');
                $sheet->setCellValue('I2', $this->trackingNumber);
                $sheet->getStyle('I2')->getFont()->setBold(true)->setSize(12)->setColor(new Color('CC0000'));
                $sheet->getStyle('I1:J2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Thông tin khách hàng / PO (Row 3 & 4)
                $sheet->setCellValue('A3', 'KHÁCH HÀNG:')->setCellValue('B3', $khachHang?->ten_kh ?? 'SIG');
                $sheet->mergeCells('B3:D3');
                $sheet->setCellValue('E3', 'NƠI GIAO:')->setCellValue('F3', 'NA');
                $sheet->mergeCells('F3:G3');
                $sheet->setCellValue('H3', 'NGÀY NHẬN:')->setCellValue('I3', now()->format('d/m/Y'));
                $sheet->mergeCells('I3:J3');

                $sheet->setCellValue('A4', 'PO:')->setCellValue('B4', $ftyPos);
                $sheet->mergeCells('B4:D4');
                $sheet->setCellValue('E4', '')->setCellValue('F4', '');
                $sheet->mergeCells('F4:G4');
                $sheet->setCellValue('H4', 'NGÀY GIAO:')->setCellValue('I4', $sigDateFormatted);
                $sheet->mergeCells('I4:J4');
                
                $sheet->getStyle('A3:J4')->getFont()->setBold(true)->setSize(9)->setColor(new Color('FF003366'));

                // --- 2. TABLE HEADER (Row 5) ---
                $headers = ['MÃ HÀNG', 'TÊN SẢN PHẨM', 'MÀU', 'ART/QUY CÁCH', 'SIZE', 'SỐ LƯỢNG', 'SL TỒN KHO', "SỐ LƯỢNG\n+ % HH", 'ĐVT', 'HÌNH ẢNH'];
                $sheet->fromArray([$headers], null, 'A5');
                $sheet->getStyle('A5:J5')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 9, 'color' => ['rgb' => '0000FF']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
                ]);

                // --- 3. DỮ LIỆU ---
                $row = 6;
                $totalQty = 0;
                $totalQtyHh = 0;
                foreach ($items as $item) {
                    $hangHoa = DanhMucHangHoa::where('ma_hh', $item->ma_hh)->first();
                    $tonKho = WarehouseTransaction::where('ma_hh', $item->ma_hh)->nhapKho()->sum('so_luong') - WarehouseTransaction::where('ma_hh', $item->ma_hh)->xuatKho()->sum('so_luong');

                    $sheet->setCellValue('A' . $row, $item->ma_hh);
                    $sheet->setCellValue('B' . $row, $hangHoa?->ten_hh ?? $item->ten_hh);
                    $sheet->setCellValue('C' . $row, $item->mau);
                    $sheet->setCellValue('D' . $row, 'QUẤN CUỘN');
                    $sheet->setCellValue('E' . $row, $hangHoa?->kich_co);
                    $sheet->setCellValue('F' . $row, $item->tong_yrd);
                    $sheet->setCellValue('G' . $row, $tonKho);
                    $sheet->setCellValue('H' . $row, round($item->sl_can_sx, 2));
                    $sheet->setCellValue('I' . $row, $item->dvt ?? 'YRD');

                    $sheet->getStyle('A'.$row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFFF00');
                    $sheet->getStyle('E'.$row)->getFont()->setBold(true)->setColor(new Color('FF0000FF'));
                    $sheet->getStyle('I'.$row)->getFont()->setBold(true)->setColor(new Color('FF0000FF'));

                    $totalQty += $item->tong_yrd;
                    $totalQtyHh += $item->sl_can_sx;

                    $sheet->getStyle("A{$row}:J{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                    $sheet->getStyle("A{$row}:J{$row}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
                    $sheet->getStyle("F{$row}:H{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
                    $row++;
                }

                // Dòng tổng cộng
                $sheet->mergeCells("A{$row}:E{$row}");
                $sheet->setCellValue("A{$row}", "TỔNG SỐ LƯỢNG HÀNG CẦN SẢN XUẤT:");
                $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->setCellValue("F{$row}", $totalQty);
                $sheet->setCellValue("H{$row}", $totalQtyHh);
                $sheet->setCellValue("I{$row}", "YRD");
                $sheet->getStyle("A{$row}:J{$row}")->getFont()->setBold(true)->setColor(new Color('FF0000FF'));
                $sheet->getStyle("A{$row}:J{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $sheet->getStyle("F{$row}:H{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
                $row++;

                // --- 4. LƯU Ý KỸ THUẬT (Màu vàng - Giống mẫu) ---
                $noteRow = $row;
                $sheet->mergeCells("A{$noteRow}:J" . ($noteRow + 6));
                $noteContent = "LƯU Ý : CHẤT LƯỢNG THEO HÀNG THỬ MẪU\n"
                    . "TIÊU CHUẨN TEST:\n"
                    . "1. Shrinkage test -> PHƯƠNG PHÁP TEST (Method): AATCC 135 (Dimensional 2 / Laundering -> Washing Condition : 41°C / 5 Cycles / Tumble Dry Low)\n"
                    . "   a) Pre-shrunk < 2%\n"
                    . "   b) Without pre-shrunk < 5%\n\n"
                    . "2. Elongation test: Tension and Elongation -> PHƯƠNG PHÁP TEST (Method)\n"
                    . "   a) Elastic width < 1 1/2\"( 38mm) : set the specified test load to 1.5 kgf (3.2 lbf, for cuffs and other applications, (Conversion: 2.2 lbf = 14.6 N)\n"
                    . "   b) Elastic width > 1 1/2\"( 38mm) : set the specified test load to 1.25 kgf (3.25 lbf, for waistbands or boot bands, bra straps, etc. (Conversion: 4.45 lbf = 31.6 N)\n\n"
                    . "3. PH Value -> PHƯƠNG PHÁP TEST (Method): -> AATCC 81 Grey Scale\n"
                    . "                       -> Standard: 4.0 ~ 7.5";
                $sheet->setCellValue("A{$noteRow}", $noteContent);
                $sheet->getStyle("A{$noteRow}:J".($noteRow+6))->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFF00']],
                    'alignment' => ['wrapText' => true, 'vertical' => Alignment::VERTICAL_TOP],
                    'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => '0000FF']]
                ]);
                $row += 7;

                // --- 5. PHÂN TÍCH CÔNG ĐOẠN & NĂNG LỰC SX (Grid) ---
                $gridHeaderRow = $row;
                $sheet->mergeCells("A{$gridHeaderRow}:D{$gridHeaderRow}");
                $sheet->setCellValue("A{$gridHeaderRow}", "PHÂN TÍCH CÔNG ĐOẠN");
                $sheet->mergeCells("E{$gridHeaderRow}:J{$gridHeaderRow}");
                $sheet->setCellValue("E{$gridHeaderRow}", "NĂNG LỰC SX");
                
                $sheet->getStyle("A{$gridHeaderRow}:J{$gridHeaderRow}")->getFont()->setBold(true)->setColor(new Color('FF003300'));
                $sheet->getStyle("A{$gridHeaderRow}:J{$gridHeaderRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFCCFFCC');
                $sheet->getStyle("A{$gridHeaderRow}:J{$gridHeaderRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("A{$gridHeaderRow}:J{$gridHeaderRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

                $row++;
                $sheet->setCellValue("A{$row}", "CĐ")->setCellValue("B{$row}", "NGUYÊN LIỆU")->setCellValue("C{$row}", "ĐỊNH MỨC")->setCellValue("D{$row}", "QUY TRÌNH SẢN XUẤT");
                $sheet->setCellValue("E{$row}", "MÁY / THIẾT BỊ")->setCellValue("F{$row}", "MÃ SỐ MÁY")->setCellValue("G{$row}", "THÔNG SỐ KT CƠ BẢN")->setCellValue("H{$row}", "MÁY PHỤ TRỢ")->setCellValue("I{$row}", "SỐ LƯỢNG/CA")->setCellValue("J{$row}", "T G ĐẠT/CA");
                $sheet->getStyle("A{$row}:J{$row}")->getFont()->setBold(true)->setSize(8);
                $sheet->getStyle("A{$row}:J{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("A{$row}:J{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                
                $row++;
                
                // CĐ 1
                $startCd1 = $row;
                $sheet->mergeCells("A{$startCd1}:A".($startCd1+7));
                $sheet->setCellValue("A{$startCd1}", "CĐ 1\n\nDệt Thun Bản");
                $sheet->mergeCells("B{$startCd1}:B".($startCd1+7));
                $sheet->setCellValue("B{$startCd1}", "1. CHỈ NGANG\n- POLY 150D\n2. CHỈ DỌC POLY 150D\n3. Su 37");
                $sheet->mergeCells("C{$startCd1}:C".($startCd1+7));
                $sheet->mergeCells("D{$startCd1}:D".($startCd1+7));
                $sheet->setCellValue("D{$startCd1}", "Quy trình dệt thun bản\nNhóm máy ngâm 15\nNhóm máy trung 22\nNhóm su dưới 24\nNhóm su trên 22\nSố bản thun trên 1 máy\nSố sợi dọc trên 1 bản :34\nSố sợi su trên 1 bản: 24\nSố thông su trên 1 máy 8 thông 30 sợi k\nSố cuộn sợi ngang trên 1 máy: 60");
                $sheet->getStyle("D{$startCd1}")->getFont()->setColor(new Color('FFFF0000'));
                
                for ($i = $startCd1; $i <= $startCd1+7; $i++) {
                    $sheet->getStyle("E{$i}:J{$i}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                }
                $sheet->getStyle("A{$startCd1}:D".($startCd1+7))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $sheet->getStyle("A{$startCd1}:D{$startCd1}")->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);
                
                $row += 8;
                
                // CĐ 2
                $startCd2 = $row;
                $sheet->setCellValue("A{$startCd2}", "CD 2")->setCellValue("B{$startCd2}", "NGUYÊN LIỆU")->setCellValue("C{$startCd2}", "ĐỊNH MỨC")->setCellValue("D{$startCd2}", "QUY TRÌNH SẢN XUẤT");
                $sheet->getStyle("A{$startCd2}:D{$startCd2}")->getFont()->setBold(true);
                for ($i = 0; $i < 5; $i++) {
                    $sheet->getStyle("A" . ($row + $i) . ":J" . ($row + $i))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                }
                $row += 5;

                // CĐ 3
                $startCd3 = $row;
                $sheet->setCellValue("A{$startCd3}", "CD 3")->setCellValue("B{$startCd3}", "NGUYÊN LIỆU")->setCellValue("C{$startCd3}", "ĐỊNH MỨC")->setCellValue("D{$startCd3}", "QUY TRÌNH SẢN XUẤT");
                $sheet->getStyle("A{$startCd3}:D{$startCd3}")->getFont()->setBold(true);
                for ($i = 0; $i < 5; $i++) {
                    $sheet->getStyle("A" . ($row + $i) . ":J" . ($row + $i))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                }
                $row += 5;

                // --- 6. CHUẨN BỊ NGUYÊN LIỆU ---
                $sheet->mergeCells("A{$row}:J{$row}");
                $sheet->setCellValue("A{$row}", "CHUẨN BỊ NGUYÊN LIỆU");
                $sheet->getStyle("A{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFCCFFCC');
                $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setColor(new Color('FF003300'));
                $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("A{$row}:J{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $row++;

                // Headers
                $sheet->setCellValue("A{$row}", "STT");
                $sheet->setCellValue("B{$row}", "SỐ LƯỢNG CHUẨN");
                $sheet->setCellValue("C{$row}", "ĐVT");
                $sheet->setCellValue("D{$row}", "TÊN NGUYÊN LIỆU CHUẨN BỊ");
                $sheet->mergeCells("G{$row}:H{$row}");
                $sheet->setCellValue("G{$row}", "THỜI GIAN CÓ");
                $sheet->setCellValue("I{$row}", "NGƯỜI PHỤ TRÁCH");
                $sheet->setCellValue("J{$row}", "NGÀY NHẬN LỆNH");

                // Subheaders for THỜI GIAN CÓ
                $row++;
                $sheet->mergeCells("A".($row-1).":A{$row}");
                $sheet->mergeCells("B".($row-1).":B{$row}");
                $sheet->mergeCells("C".($row-1).":C{$row}");
                $sheet->mergeCells("D".($row-1).":F{$row}");
                $sheet->setCellValue("G{$row}", "Dự kiến");
                $sheet->setCellValue("H{$row}", "Thực tế");
                $sheet->mergeCells("I".($row-1).":I{$row}");
                $sheet->mergeCells("J".($row-1).":J{$row}");

                $sheet->getStyle("A".($row-1).":J{$row}")->getFont()->setBold(true)->setSize(8);
                $sheet->getStyle("A".($row-1).":J{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyle("A".($row-1).":J{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $sheet->getStyle("A".($row-1).":J{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFCCFFCC');
                $row++;

                // 3 empty rows
                for ($i = 0; $i < 3; $i++) {
                    $sheet->mergeCells("D{$row}:F{$row}");
                    $sheet->getStyle("A{$row}:J{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                    $row++;
                }

                // --- 7. CHỮ KÝ ---
                $row++;
                $sheet->setCellValue("B{$row}", "Giám đốc");
                $sheet->setCellValue("I{$row}", "Người lập phiếu ký");
                $sheet->getStyle("A{$row}:J{$row}")->getFont()->setBold(true);
                $sheet->getStyle("A{$row}:J{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Cài đặt trang in
                $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_PORTRAIT);
                $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
                $sheet->getPageSetup()->setFitToWidth(1);
            },
        ];
    }
}