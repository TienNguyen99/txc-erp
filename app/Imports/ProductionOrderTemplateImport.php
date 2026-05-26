<?php

namespace App\Imports;

use App\Models\DanhMucHangHoa;
use App\Models\DanhMucKhachHang;
use App\Models\Order;
use App\Support\ItemCode;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Row;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class ProductionOrderTemplateImport implements OnEachRow, WithStartRow
{
    private int $processedRows = 0;
    private int $createdCount = 0;
    private int $updatedCount = 0;
    private int $skippedCount = 0;
    private array $importedCharts = [];
    private array $skippedRows = [];

    public function startRow(): int
    {
        return 2;
    }

    public function onRow(Row $excelRow): void
    {
        $row = $excelRow->toArray(null, true, true, false);
        $this->processedRows++;

        $po = $this->clean($row[0] ?? null);
        $staff = $this->clean($row[1] ?? null);
        $customerName = $this->clean($row[2] ?? null);
        $style = $this->clean($row[3] ?? null);
        $itemCode = ItemCode::normalize($row[4] ?? null) ?: null;
        if ($itemCode !== null && ! ItemCode::isValid($itemCode)) {
            $this->skip($excelRow->getIndex(), "Invalid Item code '{$itemCode}'. Only letters, numbers, hyphen, and underscore are allowed.");
            return;
        }
        $description = $this->clean($row[5] ?? null);
        $size = $this->clean($row[6] ?? null);
        $color = $this->clean($row[7] ?? null);
        $unit = $this->clean($row[8] ?? null);
        $quantity = $this->toNumber($row[9] ?? null);
        $location = $this->clean($row[10] ?? null);
        $receivingDate = $this->parseDate($row[11] ?? null);
        $deliveryDate = $this->parseDate($row[12] ?? null);
        $customerNeedDate = $this->parseDate($row[13] ?? null);
        $deliveryPlace = $this->clean($row[14] ?? null);

        if ($po === null && $style === null && $itemCode === null) {
            $this->skippedCount++;
            return;
        }

        if ($po === null || $style === null || $itemCode === null) {
            $this->skip($excelRow->getIndex(), 'Missing PO, Model code/Style, or Item code.');
            return;
        }

        $customerId = $this->resolveCustomerId($customerName);

        DanhMucHangHoa::updateOrCreate(
            ['ma_hh' => $itemCode],
            array_filter([
                'ten_hh' => $description ?: $itemCode,
                'mau' => $color,
                'kich_co' => $size,
                'don_vi' => $unit,
                'active' => true,
            ], fn ($value) => $value !== null && $value !== '')
        );

        $order = Order::firstOrNew([
            'job_no' => $po,
            'ma_hh' => $itemCode,
            'color' => $color,
        ]);
        $wasExisting = $order->exists;

        $order->fill([
            'khach_hang_id' => $customerId,
            'fty_po' => $po,
            'im_number' => $itemCode,
            'unit' => $unit,
            'ten_hh' => $description,
            'yrd' => $quantity,
            'qty' => $quantity,
            'pl_number' => $po,
            'tagtime_etc' => $receivingDate,
            'sig_need_date' => $customerNeedDate ?: $deliveryDate,
            'chart' => $style,
            'to_khai' => $this->buildImportNote($staff, $location, $deliveryPlace, $size, $receivingDate, $deliveryDate, $customerNeedDate),
        ]);

        if (!$order->exists) {
            $order->status = 'pending';
        }

        $order->save();

        $this->importedCharts[] = $style;
        $wasExisting ? $this->updatedCount++ : $this->createdCount++;
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

    public function getImportedCharts(): array
    {
        return array_values(array_unique(array_filter($this->importedCharts)));
    }

    public function getSkippedRows(): array
    {
        return $this->skippedRows;
    }

    private function resolveCustomerId(?string $customerName): ?int
    {
        if ($customerName === null) {
            return null;
        }

        $customer = DanhMucKhachHang::query()
            ->where('ten_kh', $customerName)
            ->orWhere('ma_kh', $customerName)
            ->first();

        if ($customer) {
            return $customer->id;
        }

        return DanhMucKhachHang::create([
            'ma_kh' => Str::upper(Str::slug($customerName, '')),
            'ten_kh' => $customerName,
            'active' => true,
        ])->id;
    }

    private function buildImportNote(
        ?string $staff,
        ?string $location,
        ?string $deliveryPlace,
        ?string $size,
        ?string $receivingDate,
        ?string $deliveryDate,
        ?string $customerNeedDate
    ): ?string {
        $parts = array_filter([
            $staff ? "nhan_vien_theo={$staff}" : null,
            $location ? "vi_tri={$location}" : null,
            $deliveryPlace ? "noi_giao={$deliveryPlace}" : null,
            $size ? "size={$size}" : null,
            $receivingDate ? "order_receiving_date={$receivingDate}" : null,
            $deliveryDate ? "delivery_date={$deliveryDate}" : null,
            $customerNeedDate ? "customer_need_date={$customerNeedDate}" : null,
        ]);

        return $parts ? 'IMPORT_LENH_SX | ' . implode(' | ', $parts) : null;
    }

    private function skip(int $rowNumber, string $reason): void
    {
        $this->skippedRows[] = ['row' => $rowNumber, 'reason' => $reason];
        $this->skippedCount++;
    }

    private function clean($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function toNumber($value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        $clean = str_replace([',', ' '], ['', ''], trim((string) $value));

        return is_numeric($clean) ? (float) $clean : null;
    }

    private function parseDate($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        if (is_numeric($value)) {
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
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }
}
