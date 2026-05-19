<?php

namespace App\Services;

use App\Models\DanhMucHangHoa;
use App\Models\User;
use App\Models\WarehouseDocument;
use App\Models\WarehouseTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WarehouseDocumentService
{
    /**
     * @param array<int, array<string, mixed>> $rows
     */
    public function create(string $type, string $date, array $rows, ?User $user = null, ?string $note = null): WarehouseDocument
    {
        $type = strtoupper($type);
        if (! in_array($type, ['NHAPKHO', 'XUATKHO'], true)) {
            throw ValidationException::withMessages(['type' => 'Loại phiếu kho không hợp lệ.']);
        }

        $rows = collect($rows)
            ->map(fn (array $row) => $this->normalizeRow($row))
            ->filter(fn (array $row) => $row['so_luong'] > 0)
            ->values();

        if ($rows->isEmpty()) {
            throw ValidationException::withMessages(['rows' => 'Phiếu kho phải có ít nhất một dòng số lượng lớn hơn 0.']);
        }

        return DB::transaction(function () use ($type, $date, $rows, $user, $note) {
            $document = WarehouseDocument::create([
                'document_no' => $this->nextDocumentNo($type),
                'type' => $type,
                'document_date' => $date,
                'created_by_id' => $user?->id,
                'posted_at' => now(),
                'note' => $note,
            ]);

            foreach ($rows as $row) {
                $catalog = $this->catalogItem($row);
                $maHh = $row['ma_hh'] ?: (string) $catalog?->ma_hh;

                $transaction = WarehouseTransaction::create([
                    'warehouse_document_id' => $document->id,
                    'cong_doan' => $type,
                    'ma_hh' => $maHh,
                    'hang_hoa_id' => $row['hang_hoa_id'],
                    'ngay' => $date,
                    'size' => $row['size'],
                    'mau' => $row['mau'],
                    'so_luong' => $row['so_luong'],
                    'price_usd' => $row['price_usd'],
                    'exchange_rate' => $row['exchange_rate'],
                    'ma_nv' => $row['ma_nv'],
                    'lenh_sx' => $row['lenh_sx'],
                    'note' => trim(($row['note'] ?: '') . ' ' . "Phiếu {$document->document_no}"),
                ]);

                $document->items()->create([
                    'warehouse_transaction_id' => $transaction->id,
                    'ten_san_pham' => $catalog?->ten_hh,
                    'ma_hh' => $maHh,
                    'mau' => $row['mau'],
                    'size' => $row['size'],
                    'so_luong' => $row['so_luong'],
                    'don_vi' => $catalog?->don_vi ?: 'PCS',
                    'lenh_sx' => $row['lenh_sx'],
                    'ghi_chu' => $row['note'],
                ]);
            }

            return $document->load('items', 'createdBy');
        });
    }

    /**
     * @param array<int, int|string> $transactionIds
     */
    public function createFromTransactions(array $transactionIds, ?User $user = null): WarehouseDocument
    {
        return DB::transaction(function () use ($transactionIds, $user) {
            $transactions = WarehouseTransaction::query()
                ->whereIn('id', $transactionIds)
                ->lockForUpdate()
                ->get();

            if ($transactions->count() !== count($transactionIds) || $transactions->isEmpty()) {
                throw ValidationException::withMessages(['transaction_ids' => 'Danh sách giao dịch kho không hợp lệ.']);
            }

            if ($transactions->whereNotNull('warehouse_document_id')->isNotEmpty()) {
                throw ValidationException::withMessages(['transaction_ids' => 'Một số giao dịch đã có phiếu kho.']);
            }

            if ($transactions->pluck('cong_doan')->unique()->count() > 1) {
                throw ValidationException::withMessages(['transaction_ids' => 'Chỉ được tạo phiếu từ các giao dịch cùng loại nhập hoặc xuất.']);
            }

            $type = (string) $transactions->first()->cong_doan;
            $date = $transactions->min('ngay')?->format('Y-m-d') ?: now()->toDateString();
            $document = WarehouseDocument::create([
                'document_no' => $this->nextDocumentNo($type),
                'type' => $type,
                'document_date' => $date,
                'created_by_id' => $user?->id,
                'posted_at' => now(),
                'note' => 'Tạo từ ' . $transactions->count() . ' giao dịch kho đã có.',
            ]);

            foreach ($transactions as $transaction) {
                $catalog = $transaction->hangHoa ?: ($transaction->ma_hh ? DanhMucHangHoa::where('ma_hh', $transaction->ma_hh)->first() : null);

                $document->items()->create([
                    'warehouse_transaction_id' => $transaction->id,
                    'ten_san_pham' => $catalog?->ten_hh,
                    'ma_hh' => $transaction->ma_hh ?: (string) $catalog?->ma_hh,
                    'mau' => $transaction->mau,
                    'size' => $transaction->size,
                    'so_luong' => $transaction->so_luong,
                    'don_vi' => $catalog?->don_vi ?: 'PCS',
                    'lenh_sx' => $transaction->lenh_sx,
                    'ghi_chu' => $transaction->note,
                ]);

                $transaction->update(['warehouse_document_id' => $document->id]);
            }

            return $document->load('items', 'createdBy');
        });
    }

    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    private function normalizeRow(array $row): array
    {
        return [
            'ma_hh' => trim((string) ($row['ma_hh'] ?? '')),
            'hang_hoa_id' => $row['hang_hoa_id'] ?? null,
            'size' => $row['size'] ?? null,
            'mau' => $row['mau'] ?? null,
            'so_luong' => (float) ($row['so_luong'] ?? 0),
            'price_usd' => $row['price_usd'] ?? null,
            'exchange_rate' => $row['exchange_rate'] ?? null,
            'ma_nv' => $row['ma_nv'] ?? null,
            'lenh_sx' => $row['lenh_sx'] ?? null,
            'note' => $row['note'] ?? null,
        ];
    }

    /**
     * @param array<string, mixed> $row
     */
    private function catalogItem(array $row): ?DanhMucHangHoa
    {
        if (! empty($row['hang_hoa_id'])) {
            return DanhMucHangHoa::find($row['hang_hoa_id']);
        }

        if (! empty($row['ma_hh'])) {
            return DanhMucHangHoa::where('ma_hh', $row['ma_hh'])->first();
        }

        return null;
    }

    private function nextDocumentNo(string $type): string
    {
        $prefix = ($type === 'NHAPKHO' ? 'PNK-' : 'PXK-') . now()->format('Ymd') . '-';
        $next = WarehouseDocument::where('document_no', 'like', $prefix . '%')->count() + 1;

        return $prefix . str_pad((string) $next, 3, '0', STR_PAD_LEFT);
    }
}
