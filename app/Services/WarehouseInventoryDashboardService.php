<?php

namespace App\Services;

use App\Models\Order;
use App\Models\WarehouseTransaction;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class WarehouseInventoryDashboardService
{
    public function build(int $month, int $year): array
    {
        $startOfMonth = Carbon::create($year, $month, 1)->startOfMonth();
        $makeKey = fn ($row) => ($row->ma_hh ?? '') . '|' . ($row->size ?? '') . '|' . ($row->mau ?? '');

        $openingStock = WarehouseTransaction::select(
            'ma_hh',
            'size',
            'mau',
            DB::raw("SUM(CASE WHEN cong_doan='NHAPKHO' THEN so_luong ELSE -so_luong END) as ton_dau")
        )
            ->where('ngay', '<', $startOfMonth)
            ->groupBy('ma_hh', 'size', 'mau')
            ->get()
            ->keyBy($makeKey);

        $transactions = WarehouseTransaction::select(
            'ma_hh',
            'size',
            'mau',
            'cong_doan',
            'ngay',
            DB::raw('SUM(so_luong) as so_luong')
        )
            ->whereMonth('ngay', $month)
            ->whereYear('ngay', $year)
            ->groupBy('ma_hh', 'size', 'mau', 'cong_doan', 'ngay')
            ->get();

        $receiptDates = $transactions->where('cong_doan', 'NHAPKHO')->pluck('ngay')->map->format('Y-m-d')->unique()->sort()->values();
        $issueDates = $transactions->where('cong_doan', 'XUATKHO')->pluck('ngay')->map->format('Y-m-d')->unique()->sort()->values();

        $receiptsByDay = [];
        $issuesByDay = [];

        foreach ($transactions as $transaction) {
            $key = $makeKey($transaction);
            $day = $transaction->ngay->format('Y-m-d');
            $quantity = (float) $transaction->so_luong;

            if ($transaction->cong_doan === 'NHAPKHO') {
                $receiptsByDay[$key][$day] = ($receiptsByDay[$key][$day] ?? 0) + $quantity;
            } else {
                $issuesByDay[$key][$day] = ($issuesByDay[$key][$day] ?? 0) + $quantity;
            }
        }

        $needed = Order::where('status', '!=', 'shipped')
            ->select('ma_hh', DB::raw('SUM(yrd) as tong_yrd'))
            ->whereNotNull('ma_hh')
            ->groupBy('ma_hh')
            ->pluck('tong_yrd', 'ma_hh');

        $allKeys = collect($openingStock->keys())
            ->merge(array_keys($receiptsByDay))
            ->merge(array_keys($issuesByDay))
            ->unique()
            ->sort()
            ->values();

        $rows = $allKeys->map(function ($key) use ($openingStock, $receiptsByDay, $issuesByDay, $receiptDates, $issueDates, $needed) {
            [$maHh, $size, $color] = explode('|', $key, 3);
            $opening = (float) ($openingStock[$key]->ton_dau ?? 0);

            $receiptRows = [];
            $totalReceipt = 0;
            foreach ($receiptDates as $date) {
                $value = (float) ($receiptsByDay[$key][$date] ?? 0);
                $receiptRows[$date] = $value;
                $totalReceipt += $value;
            }

            $issueRows = [];
            $totalIssue = 0;
            foreach ($issueDates as $date) {
                $value = (float) ($issuesByDay[$key][$date] ?? 0);
                $issueRows[$date] = $value;
                $totalIssue += $value;
            }

            return [
                'ma_hh' => $maHh,
                'size' => $size,
                'mau' => $color,
                'ton_dau' => $opening,
                'nhap_days' => $receiptRows,
                'tong_nhap' => $totalReceipt,
                'xuat_days' => $issueRows,
                'tong_xuat' => $totalIssue,
                'ton_cuoi' => $opening + $totalReceipt - $totalIssue,
                'can_di' => (float) ($needed[$maHh] ?? 0),
            ];
        })->sortBy(['ma_hh', 'mau', 'size'])->values();

        return [
            'rows' => $rows,
            'nhapDates' => $receiptDates,
            'xuatDates' => $issueDates,
            'stats' => (object) [
                'tong_ma' => $rows->count(),
                'tong_ton' => $rows->sum('ton_cuoi'),
                'tong_nhap' => $rows->sum('tong_nhap'),
                'tong_xuat' => $rows->sum('tong_xuat'),
            ],
        ];
    }

    public function sheetValues(int $month, int $year): array
    {
        $dashboard = $this->build($month, $year);

        return [
            'title' => "Dashboard Kho {$month}/{$year}",
            'nhap_dates' => $dashboard['nhapDates']->all(),
            'xuat_dates' => $dashboard['xuatDates']->all(),
            'headers' => $this->headers($dashboard['nhapDates'], $dashboard['xuatDates']),
            'rows' => $dashboard['rows']->map(fn (array $row) => $this->rowValues($row, $dashboard['nhapDates'], $dashboard['xuatDates']))->all(),
            'stats' => $dashboard['stats'],
        ];
    }

    public function headers(Collection $receiptDates, Collection $issueDates): array
    {
        return collect(['Ma HH', 'Kich', 'Mau', 'Ton dau'])
            ->merge($receiptDates->map(fn ($date) => 'Nhap ' . Carbon::parse($date)->format('d/m')))
            ->push('Tong nhap')
            ->merge($issueDates->map(fn ($date) => 'Xuat ' . Carbon::parse($date)->format('d/m')))
            ->push('Tong xuat')
            ->push('Ton cuoi')
            ->push('Can di')
            ->all();
    }

    public function rowValues(array $row, Collection $receiptDates, Collection $issueDates): array
    {
        return collect([
            $row['ma_hh'],
            $row['size'],
            $row['mau'],
            $row['ton_dau'] ?: null,
        ])
            ->merge($receiptDates->map(fn ($date) => $row['nhap_days'][$date] ?: null))
            ->push($row['tong_nhap'] ?: null)
            ->merge($issueDates->map(fn ($date) => $row['xuat_days'][$date] ?: null))
            ->push($row['tong_xuat'] ?: null)
            ->push($row['ton_cuoi'])
            ->push($row['can_di'] ?: null)
            ->all();
    }
}
