<?php

namespace App\Imports;

use App\Models\DanhMucHangHoa;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class DanhMucHangHoaImport implements ToModel, WithHeadingRow, WithValidation
{
    public function model(array $row)
    {
        return DanhMucHangHoa::updateOrCreate(
            ['ma_hh' => $row['ma_hh']],
            [
                'ten_hh'  => $row['ten_hh'] ?? '',
                'mau'     => $row['mau'] ?? null,
                'kich_co' => $row['kich_co'] ?? null,
                'nhom_hh' => $row['nhom_hh'] ?? null,
                'don_vi'  => $row['don_vi'] ?? null,
                'don_gia'       => $row['don_gia'] ?? 0,
                'dinh_muc_thung'=> $row['dinh_muc_thung'] ?? null,
                'net_weight'    => $row['net_weight'] ?? null,
                'gross_weight'  => $row['gross_weight'] ?? null,
                'mo_ta'         => $row['mo_ta'] ?? null,
                'active'        => true,
            ]
        );
    }

    public function rules(): array
    {
        return [
            'ma_hh'  => 'required|string',
            'ten_hh' => 'required|string',
        ];
    }
}
