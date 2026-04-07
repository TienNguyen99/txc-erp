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
            ['ma_hh' => $row['ma_hang_hoa']],
            [
                'ten_hh'  => $row['mo_ta'] ?? '',
                'mau'     => $row['mau'] ?? null,
                'kich_co' => $row['kich_de_sort'] ?? null,
                'nhom_hh' => $row['nhom_hh'] ?? null,
                'don_vi'  => $row['don_vi'] ?? null,
                'don_gia' => $row['don_gia'] ?? 0,
                'mo_ta'   => $row['mo_ta_them'] ?? null,
                'active'  => true,
            ]
        );
    }

    public function rules(): array
    {
        return [
            'ma_hang_hoa' => 'required|string',
            'mo_ta'       => 'required|string',
        ];
    }
}
