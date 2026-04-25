<?php

namespace App\Imports;

use App\Models\Order;
use App\Models\DanhMucHangHoa;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class OrderImport implements ToModel, WithHeadingRow, WithValidation
{
    protected array $importedMaHh = [];

    public function model(array $row)
    {
        $maHh     = trim($row['ma_hh'] ?? '');
        $priceUsd = $this->toNumeric($row['price_usd'] ?? null);

        // Tự động tạo/cập nhật danh mục hàng hóa nếu có ma_hh
        if ($maHh !== '') {
            $this->importedMaHh[] = $maHh;

            DanhMucHangHoa::updateOrCreate(
                ['ma_hh' => $maHh],
                array_filter([
                    'ten_hh'  => $row['ten_hh'] ?? $maHh,
                    'mau'     => $row['color'] ?? null,
                    'don_vi'  => $row['unit'] ?? null,
                    'don_gia' => $priceUsd,
                    'active'  => true,
                ], fn($v) => $v !== null)
            );
        }

        return Order::updateOrCreate(
            [
                'job_no' => $row['job_no'],
                'ma_hh'  => $row['ma_hh'] ?? null,
                'color'  => $row['color'] ?? null,
            ],
            [
                'ten_hh'         => $row['ten_hh'] ?? null,
                'fty_po'         => $row['fty_po'] ?? null,
                'im_number'      => $row['im_number'] ?? null,
                'unit'           => $row['unit'] ?? null,
                'yrd'            => $this->toNumeric($row['yrd'] ?? null),
                'can_giao_1'     => $this->toNumeric($row['can_giao_1'] ?? null),
                'can_giao_2'     => $this->toNumeric($row['can_giao_2'] ?? null),
                'pl_number'      => $row['pl_number'] ?? null,
                'tagtime_etc'    => $this->toDate($row['tagtime_etc'] ?? null),
                'sig_need_date'  => $this->toDate($row['sig_need_date'] ?? null),
                'chart'          => $row['chart'] ?? null,
                'price_usd_auto' => $this->toNumeric($row['price_usd_auto'] ?? null),
                'price_usd'      => $this->toNumeric($row['price_usd'] ?? null),
                'to_khai'        => $row['to_khai'] ?? null,
                'lenh_sanxuat'   => $row['lenh_sanxuat'] ?? null,
                'status'         => $row['status'] ?? 'pending',
            ]
        );
    }

    /**
     * Chuyển giá trị sang số, trả về null nếu không phải số (VD: "đã giao").
     */
    private function toNumeric($value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }
        $clean = str_replace([',', ' '], ['', ''], trim((string) $value));
        return is_numeric($clean) ? (float) $clean : null;
    }

    /**
     * Chuyển giá trị sang date (Y-m-d), hỗ trợ Excel serial number.
     */
    private function toDate($value): ?string
    {
        if (empty($value)) {
            return null;
        }
        if (is_numeric($value) && (int) $value > 30000) {
            try {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((int) $value)->format('Y-m-d');
            } catch (\Exception $e) {
                return null;
            }
        }
        // Nếu là chuỗi ngày hợp lệ
        $parsed = date_create((string) $value);
        if ($parsed && $parsed->format('Y') >= 2000) {
            return $parsed->format('Y-m-d');
        }
        return null;
    }

    public function getImportedMaHh(): array
    {
        return array_values(array_unique($this->importedMaHh));
    }

    public function rules(): array
    {
        return [
            'job_no' => 'required|string',
            'status' => 'nullable|in:pending,in_production,done,shipped',
        ];
    }
}
