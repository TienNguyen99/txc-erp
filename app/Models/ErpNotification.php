<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ErpNotification extends Model
{
    protected $table = 'erp_notifications';
    private const MISSING_BOM_TITLE = 'Thiếu định mức BOM';
    private const MISSING_ORDER_PRICE_TITLE = 'Thiếu đơn giá order';
    private const MISSING_CATALOG_PRICE_TITLE = 'Danh mục thiếu đơn giá';
    private const MISSING_CATALOG_CARTON_NORM_TITLE = 'Danh mục thiếu định mức thùng';
    public const CATEGORY_DATA = 'data';
    public const CATEGORY_WAREHOUSE = 'warehouse';
    public const CATEGORY_DELIVERY = 'delivery';
    public const CATEGORY_SYSTEM = 'system';
    public const STATUS_OPEN = 'open';
    public const STATUS_DONE = 'done';
    public const STATUS_IGNORED = 'ignored';

    protected $fillable = [
        'type', 'category', 'icon', 'title', 'message', 'link', 'is_read', 'status', 'resolved_at', 'user_id',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'resolved_at' => 'datetime',
    ];

    /** Tạo thông báo nhanh */
    public static function categoryLabels(): array
    {
        return [
            self::CATEGORY_DATA => 'Dữ liệu thiếu',
            self::CATEGORY_WAREHOUSE => 'Kho & sản xuất',
            self::CATEGORY_DELIVERY => 'Giao hàng & VAT',
            self::CATEGORY_SYSTEM => 'Hệ thống',
        ];
    }

    public static function statusLabels(): array
    {
        return [
            self::STATUS_OPEN => 'Cần xử lý',
            self::STATUS_DONE => 'Đã xử lý',
            self::STATUS_IGNORED => 'Bỏ qua',
        ];
    }

    /** Tạo thông báo nhanh */
    public static function send(string $title, string $message = '', string $type = 'info', string $link = '', string $icon = '', string $category = self::CATEGORY_SYSTEM): static
    {
        $icons = ['info' => 'fa-circle-info', 'warning' => 'fa-triangle-exclamation', 'danger' => 'fa-circle-xmark', 'success' => 'fa-circle-check'];
        return static::create([
            'type'    => $type,
            'category' => $category,
            'icon'    => $icon ?: ($icons[$type] ?? 'fa-bell'),
            'title'   => $title,
            'message' => $message,
            'link'    => $link,
            'is_read' => false,
            'status' => self::STATUS_OPEN,
        ]);
    }

    /** Kiểm tra tồn kho thấp và tạo cảnh báo */
    public static function checkLowStock(): int
    {
        $count = 0;
        $items = DanhMucHangHoa::whereRaw('ton_toi_thieu > 0')->get();

        $maHhList = $items->pluck('ma_hh')->filter()->values();
        $stockIn = WarehouseTransaction::nhapKho()
            ->whereIn('ma_hh', $maHhList)
            ->selectRaw('ma_hh, SUM(so_luong) as total')
            ->groupBy('ma_hh')
            ->pluck('total', 'ma_hh');
        $stockOut = WarehouseTransaction::xuatKho()
            ->whereIn('ma_hh', $maHhList)
            ->selectRaw('ma_hh, SUM(so_luong) as total')
            ->groupBy('ma_hh')
            ->pluck('total', 'ma_hh');

        foreach ($items as $hh) {
            $tonKho = (float) (($stockIn[$hh->ma_hh] ?? 0) - ($stockOut[$hh->ma_hh] ?? 0));
            if ($tonKho < $hh->ton_toi_thieu) {
                // Tránh tạo duplicate cùng ngày
                $exists = static::where('title', 'like', "%{$hh->ma_hh}%")
                    ->whereDate('created_at', today())->first();
                if ($exists) {
                    $exists->update([
                        'category' => self::CATEGORY_WAREHOUSE,
                        'status' => $exists->status === self::STATUS_IGNORED ? self::STATUS_IGNORED : self::STATUS_OPEN,
                        'resolved_at' => $exists->status === self::STATUS_IGNORED ? $exists->resolved_at : null,
                    ]);
                } else {
                    static::send(
                        "Tồn kho thấp: {$hh->ma_hh}",
                        "Tồn kho hiện tại: " . number_format($tonKho) . " | Tối thiểu: " . number_format($hh->ton_toi_thieu),
                        'warning',
                        '/admin/warehouse-transactions/ton-kho',
                        'fa-box-open',
                        self::CATEGORY_WAREHOUSE
                    );
                    $count++;
                }
            }
        }
        return $count;
    }

    public static function syncOperationalChecks(): int
    {
        return static::checkLowStock()
            + static::syncMissingBomNotification()
            + static::syncMissingOrderPriceNotification()
            + static::syncMissingCatalogPriceNotification()
            + static::syncMissingCatalogCartonNormNotification();
    }

    private static function syncMissingBomNotification(): int
    {
        $orderItemCodes = Order::query()
            ->whereNotNull('ma_hh')
            ->where('ma_hh', '!=', '')
            ->distinct()
            ->pluck('ma_hh');

        $missingCodes = DanhMucHangHoa::query()
            ->whereIn('ma_hh', $orderItemCodes)
            ->whereDoesntHave('dinhMucNvl')
            ->orderBy('ma_hh')
            ->pluck('ma_hh');

        return static::syncSummaryNotification(
            self::MISSING_BOM_TITLE,
            $missingCodes->count(),
            'mã hàng hóa trong order chưa có định mức BOM',
            $missingCodes->take(20)->all(),
            route('admin.dinh-muc-nvl.index', ['missing' => 'bom'], false),
            'warning',
            'fa-list-check',
            self::CATEGORY_DATA
        );
    }

    private static function syncMissingOrderPriceNotification(): int
    {
        $missingOrders = Order::query()
            ->where(function ($q) {
                $q->whereNull('price_usd')
                    ->orWhere('price_usd', '<=', 0);
            })
            ->where(function ($q) {
                $q->whereNull('price_usd_auto')
                    ->orWhere('price_usd_auto', '<=', 0);
            })
            ->orderByDesc('created_at')
            ->get(['job_no', 'ma_hh']);

        $examples = $missingOrders
            ->map(fn($order) => trim(($order->job_no ?: 'NO-JOB') . ' / ' . ($order->ma_hh ?: 'NO-MA-HH')))
            ->unique()
            ->take(12)
            ->values()
            ->all();

        return static::syncSummaryNotification(
            self::MISSING_ORDER_PRICE_TITLE,
            $missingOrders->count(),
            'order chưa có price_usd hoặc price_usd_auto',
            $examples,
            route('admin.orders.index', [], false),
            'warning',
            'fa-dollar-sign',
            self::CATEGORY_DATA
        );
    }

    private static function syncMissingCatalogPriceNotification(): int
    {
        $missingCodes = DanhMucHangHoa::query()
            ->where(function ($q) {
                $q->whereNull('don_gia')
                    ->orWhere('don_gia', '<=', 0);
            })
            ->orderBy('ma_hh')
            ->pluck('ma_hh');

        return static::syncSummaryNotification(
            self::MISSING_CATALOG_PRICE_TITLE,
            $missingCodes->count(),
            'mã hàng hóa trong danh mục chưa có đơn giá',
            $missingCodes->take(20)->all(),
            route('admin.hang-hoa.index', ['missing' => 'price'], false),
            'warning',
            'fa-tags',
            self::CATEGORY_DATA
        );
    }

    private static function syncMissingCatalogCartonNormNotification(): int
    {
        $missingCodes = DanhMucHangHoa::query()
            ->where(function ($q) {
                $q->whereNull('dinh_muc_thung')
                    ->orWhere('dinh_muc_thung', '<=', 0);
            })
            ->orderBy('ma_hh')
            ->pluck('ma_hh');

        return static::syncSummaryNotification(
            self::MISSING_CATALOG_CARTON_NORM_TITLE,
            $missingCodes->count(),
            'mã hàng hóa trong danh mục chưa có định mức thùng',
            $missingCodes->take(20)->all(),
            route('admin.hang-hoa.index', ['missing' => 'carton_norm'], false),
            'warning',
            'fa-boxes-packing',
            self::CATEGORY_DATA
        );
    }

    private static function syncSummaryNotification(
        string $title,
        int $count,
        string $label,
        array $examples,
        string $link,
        string $type,
        string $icon,
        string $category
    ): int {
        $notification = static::where('title', 'like', "{$title}:%")->latest()->first();

        if ($count <= 0) {
            $notification?->delete();
            return 0;
        }

        $message = number_format($count) . " {$label}";
        if (!empty($examples)) {
            $message .= '. Ví dụ: ' . implode(', ', $examples);
        }

        $nextTitle = "{$title}: " . number_format($count);

        if ($notification) {
            $changed = $notification->title !== $nextTitle || $notification->message !== $message;
            $status = (!$changed && $notification->status === self::STATUS_IGNORED)
                ? self::STATUS_IGNORED
                : self::STATUS_OPEN;

            $notification->update([
                'title' => $nextTitle,
                'message' => $message,
                'link' => $link,
                'type' => $type,
                'category' => $category,
                'icon' => $icon,
                'is_read' => ($changed || $status === self::STATUS_OPEN) ? false : $notification->is_read,
                'status' => $status,
                'resolved_at' => $status === self::STATUS_OPEN ? null : $notification->resolved_at,
            ]);

            return 0;
        }

        static::send($nextTitle, $message, $type, $link, $icon, $category);

        return 1;
    }

    public function markStatus(string $status): void
    {
        $this->update([
            'status' => $status,
            'is_read' => true,
            'resolved_at' => $status === self::STATUS_OPEN ? null : now(),
        ]);
    }

    public function scopeUnread($q) { return $q->where('is_read', false); }
    public function scopeOpen($q) { return $q->where('status', self::STATUS_OPEN); }
}
