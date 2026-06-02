<?php

namespace App\Imports;

use App\Models\DanhMucHangHoa;
use App\Models\DanhMucKhachHang;
use App\Models\Order;
use App\Models\User;
use App\Support\ItemCode;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Row;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class OrderImport implements OnEachRow, WithHeadingRow, WithValidation
{
    protected array $importedMaHh = [];
    protected int $processedRows = 0;
    protected int $createdCount = 0;
    protected int $updatedCount = 0;
    protected int $skippedCount = 0;
    protected array $seenKeys = [];
    protected array $duplicateRows = [];

    public function onRow(Row $excelRow): void
    {
        $row = $excelRow->toArray();
        $this->processedRows++;

        $jobNo = $this->cleanString($this->value($row, 'job_no', 'po', 'so_po'));
        if ($jobNo === '') {
            $this->skippedCount++;
            return;
        }

        $maHh = ItemCode::normalize($this->value($row, 'ma_hh', 'item_code', 'ma_hang'));
        if ($maHh !== '' && ! ItemCode::isValid($maHh)) {
            throw new \InvalidArgumentException("ma_hh '{$maHh}' khong hop le. Chi cho phep chu, so, dau gach ngang (-) va gach duoi (_).");
        }
        $color = $this->cleanString($this->value($row, 'color', 'mau'));
        $priceUsd = $this->toNumeric($this->value($row, 'price_usd', 'don_gia'));
        $rowKey = mb_strtolower($jobNo . '|' . $maHh . '|' . $color);

        if (isset($this->seenKeys[$rowKey])) {
            $this->duplicateRows[] = [
                'row' => $excelRow->getIndex(),
                'first_row' => $this->seenKeys[$rowKey],
                'key' => "{$jobNo}|{$maHh}|{$color}",
            ];
            $this->skippedCount++;
            return;
        }
        $this->seenKeys[$rowKey] = $excelRow->getIndex();

        $customerValue = $this->cleanString($this->value($row, 'ma_kh', 'khach_hang', 'customer', 'khach_hang_id'));
        $khachHangId = $customerValue !== '' ? $this->resolveCustomerId($customerValue) : null;
        $staffValue = $this->cleanString($this->value($row, 'nhan_vien_id', 'nhan_vien_theo'));
        $nhanVienId = $staffValue !== '' ? $this->resolveUserId($staffValue) : null;

        $tenHh = $this->cleanString($this->value($row, 'ten_hh', 'description', 'ten_hang'));
        $unit = $this->cleanString($this->value($row, 'unit', 'don_vi'));
        $kichCo = $this->cleanString($this->value($row, 'kich_co', 'size'));
        $quyCach = $this->cleanString($this->value($row, 'quy_cach'));
        $noiGiao = $this->cleanString($this->value($row, 'noi_giao'));
        $receivingDate = $this->toDate($this->value($row, 'order_receiving_date', 'tagtime_etc', 'ngay_nhan_order'));
        $deliveryDate = $this->toDate($this->value($row, 'delivery_date', 'ngay_giao'));
        $customerNeedDate = $this->toDate($this->value($row, 'customer_need_date', 'sig_need_date', 'ngay_can'));

        if ($maHh !== '') {
            $this->importedMaHh[] = $maHh;

            DanhMucHangHoa::updateOrCreate(
                ['ma_hh' => $maHh],
                array_filter([
                    'ten_hh' => $tenHh !== '' ? $tenHh : $maHh,
                    'mau' => $color !== '' ? $color : null,
                    'kich_co' => $kichCo !== '' ? $kichCo : null,
                    'quy_cach' => $quyCach !== '' ? $quyCach : null,
                    'don_vi' => $unit !== '' ? $unit : null,
                    'don_gia' => $priceUsd,
                    'active' => true,
                ], fn ($value) => $value !== null)
            );
        }

        $order = Order::firstOrNew([
            'job_no' => $jobNo,
            'ma_hh' => $maHh ?: null,
            'color' => $color ?: null,
        ]);
        $wasExisting = $order->exists;

        $order->khach_hang_id = $khachHangId;
        $order->nhan_vien_id = $nhanVienId;
        $order->ten_hh = $tenHh ?: null;
        $order->quy_cach = $quyCach ?: null;
        $order->kich_co = $kichCo ?: null;
        $order->noi_giao = $noiGiao ?: null;
        $order->fty_po = $this->cleanString($this->value($row, 'fty_po', 'po', 'job_no')) ?: null;
        $order->im_number = $this->cleanString($this->value($row, 'im_number', 'im', 'ma_hang')) ?: null;
        $order->unit = $unit ?: null;
        $order->qty = $this->toNumeric($this->value($row, 'qty', 'quantity', 'so_luong', 'yrd'));
        $order->yrd = $this->toNumeric($this->value($row, 'yrd', 'quantity', 'so_luong', 'qty'));
        $order->can_giao_1 = $this->toNumeric($this->value($row, 'can_giao_1'));
        $order->can_giao_2 = $this->toNumeric($this->value($row, 'can_giao_2'));
        $order->pl_number = $this->cleanString($this->value($row, 'pl_number')) ?: null;
        $order->tagtime_etc = $receivingDate ?: $this->toDate($this->value($row, 'tagtime_etc'));
        $order->sig_need_date = $customerNeedDate ?: $deliveryDate;
        $order->chart = $this->cleanString($this->value($row, 'chart', 'style')) ?: null;
        $order->price_usd_auto = $this->toNumeric($this->value($row, 'price_usd_auto'));
        $order->price_usd = $priceUsd;

        if (isset($row['to_khai']) && $row['to_khai'] !== '') {
            $order->to_khai = $row['to_khai'];
        } else {
            $importNote = $this->buildImportNote(
                $this->cleanString($this->value($row, 'nhan_vien_theo')),
                $this->cleanString($this->value($row, 'vi_tri')),
                $this->cleanString($this->value($row, 'delivery_date', 'customer_need_date'))
            );

            if ($importNote !== null) {
                $order->to_khai = $importNote;
            }
        }

        if (isset($row['lenh_sanxuat']) && $row['lenh_sanxuat'] !== '') {
            $order->lenh_sanxuat = $row['lenh_sanxuat'];
        }

        if (!$order->exists) {
            $order->status = $row['status'] ?? 'pending';
        } elseif (isset($row['status']) && $row['status'] !== '') {
            $order->status = $row['status'];
        }

        $order->save();
        if ($wasExisting) {
            $this->updatedCount++;
        } else {
            $this->createdCount++;
        }
    }

    private function toNumeric($value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        $clean = str_replace([',', ' '], ['', ''], trim((string) $value));

        return is_numeric($clean) ? (float) $clean : null;
    }

    private function toDate($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        if (is_numeric($value) && (float) $value > 30000) {
            try {
                return ExcelDate::excelToDateTimeObject((float) $value)->format('Y-m-d');
            } catch (\Throwable) {
                return null;
            }
        }

        $value = trim((string) $value);
        foreach (['d/m/Y', 'd-m-Y', 'Y-m-d', 'm/d/Y'] as $format) {
            try {
                return Carbon::createFromFormat($format, $value)->format('Y-m-d');
            } catch (\Throwable) {
                // Try next format.
            }
        }

        try {
            $parsed = Carbon::parse($value);

            return (int) $parsed->format('Y') >= 2000 ? $parsed->format('Y-m-d') : null;
        } catch (\Throwable) {
            return null;
        }
    }

    private function value(array $row, string ...$keys)
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $row) && $row[$key] !== null && $row[$key] !== '') {
                return $row[$key];
            }
        }

        return null;
    }

    private function cleanString($value): string
    {
        return $value === null ? '' : trim((string) $value);
    }

    private function resolveCustomerId(string $value): ?int
    {
        $query = DanhMucKhachHang::query()
            ->where('ma_kh', $value)
            ->orWhere('ten_kh', $value);

        if (is_numeric($value)) {
            $query->orWhere('id', $value);
        }

        $id = $query->value('id');
        if ($id) {
            return $id;
        }

        return DanhMucKhachHang::create([
            'ma_kh' => Str::upper(Str::slug($value, '')),
            'ten_kh' => $value,
            'active' => true,
        ])->id;
    }

    private function resolveUserId(string $value): ?int
    {
        if (is_numeric($value)) {
            return User::whereKey((int) $value)->value('id');
        }

        return User::query()
            ->where('name', $value)
            ->orWhere('email', $value)
            ->value('id');
    }

    private function buildImportNote(
        string $staff,
        string $location,
        string $legacyDates
    ): ?string {
        $parts = array_filter([
            $staff !== '' ? "nhan_vien_theo={$staff}" : null,
            $location !== '' ? "vi_tri={$location}" : null,
            $legacyDates !== '' ? "legacy_dates={$legacyDates}" : null,
        ]);

        return $parts ? 'IMPORT_ORDER | ' . implode(' | ', $parts) : null;
    }

    public function getImportedMaHh(): array
    {
        return array_values(array_unique($this->importedMaHh));
    }

    public function getProcessedRows(): int
    {
        return $this->processedRows;
    }

    public function getCreatedCount(): int
    {
        return $this->createdCount;
    }

    public function getUpdatedCount(): int
    {
        return $this->updatedCount;
    }

    public function getSkippedCount(): int
    {
        return $this->skippedCount;
    }

    public function getDuplicateRows(): array
    {
        return $this->duplicateRows;
    }

    public function rules(): array
    {
        return [
            'job_no' => 'nullable|string',
            'status' => 'nullable|in:pending,in_production,done,shipped',
        ];
    }
}
