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
        return Order::with(['khachHang', 'nhanVien'])->orderBy('id', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'chart',
            'nhan_vien_id',
            'khach_hang_id',
            'ma_hh',
            'quy_cach',
            'ten_hh',
            'kich_co',
            'color',
            'unit',
            'yrd',
            'tagtime_etc',
            'sig_need_date',
            'noi_giao',
            'job_no',
            'fty_po',
            'im_number',
            'qty',
            'can_giao_1',
            'can_giao_2',
            'pl_number',
            'price_usd_auto',
            'price_usd',
            'to_khai',
            'lenh_sanxuat',
            'status',
        ];
    }

    public function map($row): array
    {
        return [
            $row->chart,
            $row->nhan_vien_id,
            $row->khach_hang_id,
            $row->ma_hh,
            $row->quy_cach,
            $row->ten_hh,
            $row->kich_co,
            $row->color,
            $row->unit,
            $row->yrd,
            $row->tagtime_etc?->format('d/m/Y'),
            $row->sig_need_date?->format('d/m/Y'),
            $row->noi_giao,
            $row->job_no,
            $row->fty_po,
            $row->im_number,
            $row->qty,
            $row->can_giao_1,
            $row->can_giao_2,
            $row->pl_number,
            $row->price_usd_auto,
            $row->price_usd,
            $row->to_khai,
            $row->lenh_sanxuat,
            $row->status,
        ];
    }
}
