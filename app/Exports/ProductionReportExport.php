<?php

namespace App\Exports;

use App\Models\ProductionReport;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProductionReportExport implements FromCollection, WithHeadings, WithMapping
{
    protected $thang;
    protected $nam;

    public function __construct($thang = null, $nam = null)
    {
        $this->thang = $thang;
        $this->nam = $nam;
    }

    public function collection()
    {
        $query = ProductionReport::orderBy('ngay_sx', 'desc');

        if ($this->thang && $this->nam) {
            $query->whereMonth('ngay_sx', $this->thang)
                  ->whereYear('ngay_sx', $this->nam);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'Lệnh SX',
            'Ngày SX',
            'Công Đoạn',
            'Ca',
            'Mã NV',
            'Size (Mã HH)',
            'Màu',
            'SL Đạt',
            'SL Hư',
            'Trạng Thái',
        ];
    }

    public function map($row): array
    {
        return [
            $row->lenh_sx,
            $row->ngay_sx?->format('Y-m-d'),
            $row->cong_doan,
            $row->ca,
            $row->ma_nv,
            $row->size,
            $row->mau,
            $row->sl_dat,
            $row->sl_hu,
            $row->status === 'approved' ? 'Đã duyệt' : 'Chờ duyệt',
        ];
    }
}
