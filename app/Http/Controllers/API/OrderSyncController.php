<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\DanhMucHangHoa;
use App\Models\DanhMucKhachHang;
use App\Models\Setting;
use App\Support\ItemCode;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

/**
 * API endpoint cho Google Apps Script sync orders từ Google Sheets.
 * Auth: Bearer token (lưu trong settings bảng settings, key = 'api_sync_token')
 */
class OrderSyncController extends Controller
{
    // ── Auth middleware đã xử lý qua route, nhưng double-check ở đây
    private function authenticate(Request $request): bool
    {
        $token = Setting::where('key', 'api_sync_token')->value('value');
        if (empty($token)) return false;

        $bearer = $request->bearerToken();
        return $bearer && hash_equals($token, $bearer);
    }

    /**
     * POST /api/orders/sync
     * Body JSON: { "rows": [ { job_no, ma_hh, color, ... } ] }
     */
    public function sync(Request $request): JsonResponse
    {
        if (!$this->authenticate($request)) {
            return response()->json(['error' => 'Unauthorized. Kiểm tra lại API token.'], 401);
        }

        $rows = $request->input('rows', []);
        if (empty($rows) || !is_array($rows)) {
            return response()->json(['error' => 'Không có dữ liệu. Gửi JSON: {"rows":[...]}'], 422);
        }

        $imported = 0;
        $skipped  = 0;
        $errors   = [];

        foreach ($rows as $i => $row) {
            try {
                $jobNo = trim($row['job_no'] ?? '');
                if (empty($jobNo)) { $skipped++; continue; }

                $maHh     = ItemCode::normalize($row['ma_hh'] ?? null);
                if ($maHh !== '' && ! ItemCode::isValid($maHh)) {
                    $errors[] = "Row $i: ma_hh '{$maHh}' khong hop le.";
                    $skipped++;
                    continue;
                }
                $color    = trim($row['color'] ?? '');
                $priceUsd = $this->toNumeric($row['price_usd'] ?? null);

                // Resolve khach_hang_id từ ma_kh
                $khachHangId = null;
                if (!empty($row['khach_hang'])) {
                    $val = trim($row['khach_hang']);
                    $khachHangId = DanhMucKhachHang::where('ma_kh', $val)
                        ->orWhere('ten_kh', 'like', "%$val%")
                        ->value('id');
                }

                // Auto-upsert danh mục hàng hóa
                if ($maHh !== '') {
                    DanhMucHangHoa::updateOrCreate(
                        ['ma_hh' => $maHh],
                        array_filter([
                            'ten_hh'  => $row['ten_hh'] ?? $maHh,
                            'mau'     => $color ?: null,
                            'don_vi'  => $row['unit'] ?? null,
                            'don_gia' => $priceUsd,
                            'active'  => true,
                        ], fn($v) => $v !== null)
                    );
                }

                Order::updateOrCreate(
                    ['job_no' => $jobNo, 'ma_hh' => $maHh ?: null, 'color' => $color ?: null],
                    array_filter([
                        'khach_hang_id'  => $khachHangId,
                        'fty_po'         => $row['fty_po'] ?? null,
                        'im_number'      => $row['im_number'] ?? null,
                        'ten_hh'         => $row['ten_hh'] ?? null,
                        'unit'           => $row['unit'] ?? null,
                        'qty'            => $this->toNumeric($row['qty'] ?? null),
                        'yrd'            => $this->toNumeric($row['yrd'] ?? null),
                        'can_giao_1'     => $this->toNumeric($row['can_giao_1'] ?? null),
                        'can_giao_2'     => $this->toNumeric($row['can_giao_2'] ?? null),
                        'pl_number'      => $row['pl_number'] ?? null,
                        'tagtime_etc'    => $this->toDate($row['tagtime_etc'] ?? null),
                        'sig_need_date'  => $this->toDate($row['sig_need_date'] ?? null),
                        'chart'          => $row['chart'] ?? null,
                        'price_usd'      => $priceUsd,
                        'price_usd_auto' => $this->toNumeric($row['price_usd_auto'] ?? null),
                        'to_khai'        => $row['to_khai'] ?? null,
                        'lenh_sanxuat'   => $row['lenh_sanxuat'] ?? null,
                        'status'         => $row['status'] ?? 'pending',
                    ], fn($v) => $v !== null)
                );

                $imported++;
            } catch (\Exception $e) {
                $errors[] = "Row $i (job_no={$row['job_no']}): " . $e->getMessage();
                Log::error('OrderSync row error', ['row' => $row, 'err' => $e->getMessage()]);
            }
        }

        return response()->json([
            'ok'       => true,
            'imported' => $imported,
            'skipped'  => $skipped,
            'errors'   => $errors,
            'message'  => "✅ Đồng bộ xong: $imported dòng, $skipped bỏ qua." . (count($errors) ? ' ⚠️ ' . count($errors) . ' lỗi.' : ''),
        ]);
    }

    /**
     * GET /api/orders/sync/status  — kiểm tra kết nối từ Apps Script
     */
    public function status(Request $request): JsonResponse
    {
        if (!$this->authenticate($request)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        return response()->json([
            'ok'      => true,
            'server'  => config('app.name'),
            'time'    => now()->toDateTimeString(),
            'orders'  => Order::count(),
        ]);
    }

    private function toNumeric($value): ?float
    {
        if ($value === null || $value === '') return null;
        $clean = str_replace([',', ' '], '', trim((string) $value));
        return is_numeric($clean) ? (float) $clean : null;
    }

    private function toDate($value): ?string
    {
        if (empty($value)) return null;
        $parsed = date_create((string) $value);
        if ($parsed && $parsed->format('Y') >= 2000) return $parsed->format('Y-m-d');
        return null;
    }
}
