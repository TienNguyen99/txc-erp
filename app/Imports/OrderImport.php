<?php

namespace App\Imports;

use App\Models\Order;
use App\Models\DanhMucHangHoa;
use App\Models\DanhMucKhachHang;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Row;

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

        $jobNo = trim((string) ($row['job_no'] ?? ''));
        if ($jobNo === '') {
            $this->skippedCount++;
            return;
        }

        $maHh     = trim($row['ma_hh'] ?? '');
        $color    = trim($row['color'] ?? '');
        $priceUsd = $this->toNumeric($row['price_usd'] ?? null);
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

        $khachHangId = null;
        if (!empty($row['khach_hang_id'])) {
            $val = trim($row['khach_hang_id']);
            $q = DanhMucKhachHang::where('ma_kh', $val);
            if (is_numeric($val)) {
                $q->orWhere('id', $val);
            }
            $khachHangId = $q->value('id');
        }

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

        $order = Order::firstOrNew([
            'job_no' => $jobNo,
            'ma_hh'  => $maHh ?: null,
            'color'  => $color ?: null,
        ]);
        $wasExisting = $order->exists;

        $order->khach_hang_id  = $khachHangId;
        $order->ten_hh         = $row['ten_hh'] ?? null;
        $order->fty_po         = $row['fty_po'] ?? null;
        $order->im_number      = $row['im_number'] ?? null;
        $order->unit           = $row['unit'] ?? null;
        $order->yrd            = $this->toNumeric($row['yrd'] ?? null);
        $order->can_giao_1     = $this->toNumeric($row['can_giao_1'] ?? null);
        $order->can_giao_2     = $this->toNumeric($row['can_giao_2'] ?? null);
        $order->pl_number      = $row['pl_number'] ?? null;
        $order->tagtime_etc    = $this->toDate($row['tagtime_etc'] ?? null);
        $order->sig_need_date  = $this->toDate($row['sig_need_date'] ?? null);
        $order->chart          = $row['chart'] ?? null;
        $order->price_usd_auto = $this->toNumeric($row['price_usd_auto'] ?? null);
        $order->price_usd      = $this->toNumeric($row['price_usd'] ?? null);

        // Chỉ cập nhật các trường nhạy cảm nếu file Excel thực sự có dữ liệu
        // Tránh tình trạng file Excel để trống làm mất dữ liệu đã nhập trên phần mềm
        if (isset($row['to_khai']) && $row['to_khai'] !== '') {
            $order->to_khai = $row['to_khai'];
        }
        if (isset($row['lenh_sanxuat']) && $row['lenh_sanxuat'] !== '') {
            $order->lenh_sanxuat = $row['lenh_sanxuat'];
        }

        // Bảo vệ trạng thái (status): Chỉ set về pending nếu là đơn mới
        // Không hạ cấp status nếu đơn hàng đã vào sản xuất hoặc đã giao
        if (!$order->exists) {
            $order->status = $row['status'] ?? 'pending';
        } else if (isset($row['status']) && $row['status'] !== '') {
            // Nếu file Excel có ép status và không rỗng, thì có thể cho phép cập nhật
            // (Tuỳ logic doanh nghiệp, có thể bỏ dòng này nếu không muốn Excel đè status)
            $order->status = $row['status'];
        }

        $order->save();
        if ($wasExisting) {
            $this->updatedCount++;
        } else {
            $this->createdCount++;
        }
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
            'job_no' => 'required|string',
            'status' => 'nullable|in:pending,in_production,done,shipped',
        ];
    }
}
