<?php

namespace App\Exports;

use App\Models\WarehouseTransaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class WarehouseInventoryDashboardExport implements FromArray, ShouldAutoSize, WithEvents, WithTitle
{
    private array $rows = [];
    private array $nhapDates = [];
    private array $xuatDates = [];

    public function __construct(
        private readonly int $month,
        private readonly int $year,
    ) {
        $this->buildRows();
    }

    public function title(): string
    {
        return 'Ton kho T' . $this->month . '-' . $this->year;
    }

    public function array(): array
    {
        $header1 = ['Mã HH', 'Kích', 'Màu', 'Tồn đầu'];
        $header2 = ['', '', '', ''];

        foreach ($this->nhapDates as $index => $date) {
            $header1[] = $index === 0 ? 'Nhập kho' : '';
            $header2[] = Carbon::parse($date)->format('d/m');
        }

        $header1[] = '';
        $header2[] = 'Tổng nhập';

        foreach ($this->xuatDates as $index => $date) {
            $header1[] = $index === 0 ? 'Xuất kho' : '';
            $header2[] = Carbon::parse($date)->format('d/m');
        }

        $header1[] = '';
        $header2[] = 'Tổng xuất';
        $header1[] = 'Tồn cuối';
        $header2[] = '';

        $data = [$header1, $header2];

        foreach ($this->rows as $row) {
            $line = [
                $row['ma_hh'],
                $row['size'],
                $row['mau'],
                $row['ton_dau'],
            ];

            foreach ($this->nhapDates as $date) {
                $line[] = $row['nhap_days'][$date] ?? null;
            }

            $line[] = $row['tong_nhap'];

            foreach ($this->xuatDates as $date) {
                $line[] = $row['xuat_days'][$date] ?? null;
            }

            $line[] = $row['tong_xuat'];
            $line[] = $row['ton_cuoi'];
            $data[] = $line;
        }

        return $data;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = count($this->rows) + 2;
                $lastColumnIndex = 4 + count($this->nhapDates) + 1 + count($this->xuatDates) + 2;
                $lastColumn = Coordinate::stringFromColumnIndex($lastColumnIndex);

                $sheet->freezePane('E3');
                $sheet->getStyle("A1:{$lastColumn}2")->getFont()->setBold(true);
                $sheet->getStyle("A1:{$lastColumn}{$lastRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $sheet->getStyle("A1:{$lastColumn}{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("D3:{$lastColumn}{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                for ($column = 1; $column <= 4; $column++) {
                    $letter = Coordinate::stringFromColumnIndex($column);
                    $sheet->mergeCells("{$letter}1:{$letter}2");
                }

                $nhapStart = 5;
                $nhapEnd = $nhapStart + count($this->nhapDates);
                if ($nhapEnd >= $nhapStart) {
                    $sheet->mergeCells(Coordinate::stringFromColumnIndex($nhapStart) . '1:' . Coordinate::stringFromColumnIndex($nhapEnd) . '1');
                }

                $xuatStart = $nhapEnd + 1;
                $xuatEnd = $xuatStart + count($this->xuatDates);
                if ($xuatEnd >= $xuatStart) {
                    $sheet->mergeCells(Coordinate::stringFromColumnIndex($xuatStart) . '1:' . Coordinate::stringFromColumnIndex($xuatEnd) . '1');
                }

                $tonCuoiColumn = Coordinate::stringFromColumnIndex($lastColumnIndex);
                $sheet->mergeCells("{$tonCuoiColumn}1:{$tonCuoiColumn}2");

                $sheet->getStyle('A1:D2')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFF200');
                $sheet->getStyle(Coordinate::stringFromColumnIndex($nhapStart) . '1:' . Coordinate::stringFromColumnIndex($nhapEnd) . '2')
                    ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('CFE2F3');
                $sheet->getStyle(Coordinate::stringFromColumnIndex($xuatStart) . '1:' . Coordinate::stringFromColumnIndex($xuatEnd) . '2')
                    ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('D9EAD3');
                $sheet->getStyle("{$tonCuoiColumn}1:{$tonCuoiColumn}{$lastRow}")
                    ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFF2CC');

                $sheet->getStyle("D3:{$lastColumn}{$lastRow}")->getNumberFormat()->setFormatCode('#,##0.##');
                $sheet->setAutoFilter("A2:{$lastColumn}{$lastRow}");
            },
        ];
    }

    private function buildRows(): void
    {
        $startOfMonth = Carbon::create($this->year, $this->month, 1)->startOfMonth();
        $makeKey = fn ($row) => ($row->ma_hh ?? '') . '|' . ($row->size ?? '') . '|' . ($row->mau ?? '');

        $tonDau = WarehouseTransaction::select(
            'ma_hh',
            'size',
            'mau',
            DB::raw("SUM(CASE WHEN cong_doan='NHAPKHO' THEN so_luong ELSE -so_luong END) as ton_dau")
        )
            ->where('ngay', '<', $startOfMonth)
            ->groupBy('ma_hh', 'size', 'mau')
            ->get()
            ->keyBy($makeKey);

        $transactions = WarehouseTransaction::select('ma_hh', 'size', 'mau', 'cong_doan', 'ngay', DB::raw('SUM(so_luong) as so_luong'))
            ->whereMonth('ngay', $this->month)
            ->whereYear('ngay', $this->year)
            ->groupBy('ma_hh', 'size', 'mau', 'cong_doan', 'ngay')
            ->get();

        $this->nhapDates = $transactions->where('cong_doan', 'NHAPKHO')->pluck('ngay')->map->format('Y-m-d')->unique()->sort()->values()->all();
        $this->xuatDates = $transactions->where('cong_doan', 'XUATKHO')->pluck('ngay')->map->format('Y-m-d')->unique()->sort()->values()->all();

        $nhapByDay = [];
        $xuatByDay = [];

        foreach ($transactions as $transaction) {
            $key = $makeKey($transaction);
            $day = $transaction->ngay->format('Y-m-d');

            if ($transaction->cong_doan === 'NHAPKHO') {
                $nhapByDay[$key][$day] = ($nhapByDay[$key][$day] ?? 0) + (float) $transaction->so_luong;
            } else {
                $xuatByDay[$key][$day] = ($xuatByDay[$key][$day] ?? 0) + (float) $transaction->so_luong;
            }
        }

        $allKeys = collect($tonDau->keys())
            ->merge(array_keys($nhapByDay))
            ->merge(array_keys($xuatByDay))
            ->unique()
            ->sort()
            ->values();

        $this->rows = $allKeys->map(function ($key) use ($tonDau, $nhapByDay, $xuatByDay) {
            [$maHh, $size, $mau] = explode('|', $key, 3);
            $tonDauVal = (float) ($tonDau[$key]->ton_dau ?? 0);

            $nhapRows = [];
            $tongNhap = 0;
            foreach ($this->nhapDates as $date) {
                $value = $nhapByDay[$key][$date] ?? 0;
                $nhapRows[$date] = $value ?: null;
                $tongNhap += $value;
            }

            $xuatRows = [];
            $tongXuat = 0;
            foreach ($this->xuatDates as $date) {
                $value = $xuatByDay[$key][$date] ?? 0;
                $xuatRows[$date] = $value ?: null;
                $tongXuat += $value;
            }

            return [
                'ma_hh' => $maHh,
                'size' => $size,
                'mau' => $mau,
                'ton_dau' => $tonDauVal ?: null,
                'nhap_days' => $nhapRows,
                'tong_nhap' => $tongNhap ?: null,
                'xuat_days' => $xuatRows,
                'tong_xuat' => $tongXuat ?: null,
                'ton_cuoi' => $tonDauVal + $tongNhap - $tongXuat,
            ];
        })->sortBy(['ma_hh', 'mau', 'size'])->values()->all();
    }
}
