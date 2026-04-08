<?php

namespace App\Exports;

use App\Models\Order;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class OrderExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Order::with('khachHang')->orderBy('id', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'job_no',
            'fty_po',
            'khach_hang',
            'im_number',
            'color',
            'unit',
            'ma_hh',
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

    public function map($row): array
    {
        return [
            $row->job_no,
            $row->fty_po,
            $row->khachHang?->ten_kh,
            $row->im_number,
            $row->color,
            $row->unit,
            $row->ma_hh,
            $row->yrd,
            $row->can_giao_1,
            $row->can_giao_2,
            $row->pl_number,
            $row->tagtime_etc?->format('Y-m-d'),
            $row->sig_need_date?->format('Y-m-d'),
            $row->chart,
            $row->price_usd_auto,
            $row->price_usd,
            $row->to_khai,
            $row->status,
        ];
    }
}
