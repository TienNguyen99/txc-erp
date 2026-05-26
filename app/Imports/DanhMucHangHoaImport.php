<?php

namespace App\Imports;

use App\Models\DanhMucHangHoa;
use App\Support\ItemCode;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class DanhMucHangHoaImport implements ToModel, WithHeadingRow, WithValidation
{
    public function model(array $row)
    {
        $maHh = ItemCode::normalize($row['ma_hh'] ?? null);
        if (! ItemCode::isValid($maHh)) {
            throw new \InvalidArgumentException("ma_hh '{$row['ma_hh']}' khong hop le. Chi cho phep chu, so, dau gach ngang (-) va gach duoi (_).");
        }

        $fillable = (new DanhMucHangHoa)->getFillable();
        $data = collect($row)->only($fillable)->toArray();
        $data['active'] = $data['active'] ?? true;
        
        // Remove ma_hh from data since it's used in the search condition
        unset($data['ma_hh']);

        // Xử lý các cột số không được phép null (khi file Excel để trống)
        $numericFields = [
            'don_gia', 'yards_per_roll', 'rolls_per_carton', 'dinh_muc_thung', 
            'net_weight', 'gross_weight', 'gia_nvl', 'ton_toi_thieu'
        ];
        
        foreach ($numericFields as $field) {
            if (array_key_exists($field, $data) && ($data[$field] === null || trim($data[$field]) === '')) {
                $data[$field] = 0;
            }
        }

        return DanhMucHangHoa::updateOrCreate(
            ['ma_hh' => $maHh],
            $data
        );
    }

    public function rules(): array
    {
        return [
            'ma_hh'  => ['required', 'string'],
            'ten_hh' => 'required|string',
        ];
    }
}
