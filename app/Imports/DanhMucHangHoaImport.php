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
        $fillable = (new DanhMucHangHoa)->getFillable();
        $data = collect($row)->only($fillable)->toArray();
        $data['active'] = $data['active'] ?? true;
        
        // Remove ma_hh from data since it's used in the search condition
        unset($data['ma_hh']);

        return DanhMucHangHoa::updateOrCreate(
            ['ma_hh' => $row['ma_hh']],
            $data
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
