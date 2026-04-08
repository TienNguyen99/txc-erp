<?php

namespace App\Imports;

use App\Models\Order;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class OrderImport implements ToModel, WithHeadingRow, WithValidation
{
    public function model(array $row)
    {
        return Order::updateOrCreate(
            ['job_no' => $row['job_no']],
            [
                'fty_po'         => $row['fty_po'] ?? null,
                'im_number'      => $row['im_number'] ?? null,
                'color'          => $row['color'] ?? null,
                'qty'            => $row['qty'] ?? null,
                'unit'           => $row['unit'] ?? null,
                'size'           => $row['size'] ?? null,
                'yrd'            => $row['yrd'] ?? null,
                'can_giao_1'     => $row['can_giao_1'] ?? null,
                'can_giao_2'     => $row['can_giao_2'] ?? null,
                'pl_number'      => $row['pl_number'] ?? null,
                'tagtime_etc'    => $row['tagtime_etc'] ?? null,
                'sig_need_date'  => $row['sig_need_date'] ?? null,
                'chart'          => $row['chart'] ?? null,
                'price_usd_auto' => $row['price_usd_auto'] ?? null,
                'price_usd'      => $row['price_usd'] ?? null,
                'to_khai'        => $row['to_khai'] ?? null,
                'status'         => $row['status'] ?? 'pending',
            ]
        );
    }

    public function rules(): array
    {
        return [
            'job_no' => 'required|string',
            'status' => 'nullable|in:pending,in_production,done,shipped',
        ];
    }
}
