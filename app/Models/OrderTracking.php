<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class OrderTracking extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['tracking_number', 'tracking_number_child', 'cong_doan', 'ngay_xe_lay_hang', 'sl_don_hang', 'sl_san_xuat'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    protected $table = 'order_tracking';

    // Stage chuẩn hóa theo nghiệp vụ, có map alias để tương thích dữ liệu cũ.
    public const STAGE_IN_PENDING = 'IN_PENDING';
    public const STAGE_DET = 'DET';
    public const STAGE_DINH_HINH = 'DINH_HINH';
    public const STAGE_NHAP_KHO = 'NHAPKHO';
    public const STAGE_XUAT_KHO = 'XUATKHO';

    public const STAGE_ALIASES = [
        self::STAGE_IN_PENDING => ['IN_PENDING', 'Chờ sản xuất'],
        self::STAGE_DET => ['DET', 'Dệt'],
        self::STAGE_DINH_HINH => ['DINH_HINH', 'Định hình'],
        self::STAGE_NHAP_KHO => ['NHAPKHO', 'Đã nhập kho'],
        self::STAGE_XUAT_KHO => ['XUATKHO', 'shipped', 'Đã giao', 'Ðã giao'],
    ];

    const STAGES = [
        'Chờ sản xuất' => ['icon' => 'fa-clock', 'color' => 'warning', 'order' => 0],
        'Dệt' => ['icon' => 'fa-industry', 'color' => 'info', 'order' => 1],
        'Định hình' => ['icon' => 'fa-shapes', 'color' => 'primary', 'order' => 2],
        'Đã nhập kho' => ['icon' => 'fa-warehouse', 'color' => 'success', 'order' => 3],
        'Đã giao' => ['icon' => 'fa-truck-loading', 'color' => 'dark', 'order' => 4],
    ];

    protected $fillable = [
        'order_id',
        'tracking_number',
        'tracking_number_child',
        'pl_number',
        'size',
        'mau',
        'kich',
        'cong_doan',
        'ngay_xe_lay_hang',
        'sl_don_hang',
        'sl_san_xuat',
        'da_tao_lenh_sx',
        'invoice_no',
        'invoice_issued_at',
        'invoice_exchange_rate',
    ];

    protected $casts = [
        'sl_don_hang' => 'decimal:2',
        'sl_san_xuat' => 'decimal:2',
        'ngay_xe_lay_hang' => 'date',
        'invoice_issued_at' => 'datetime',
        'invoice_exchange_rate' => 'decimal:2',
    ];

    /**
     * Sinh tracking number tự động: OT-YYYYMMDD-XXX
     */
    public static function generateTrackingNumber(): string
    {
        $prefix = 'OT-' . now()->format('Ymd') . '-';
        $last = static::where('tracking_number', 'like', $prefix . '%')
            ->orderByDesc('tracking_number')
            ->value('tracking_number');

        $seq = $last ? ((int) substr($last, -3)) + 1 : 1;

        return $prefix . str_pad($seq, 3, '0', STR_PAD_LEFT);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public static function normalizeStage(?string $stage): ?string
    {
        if ($stage === null || $stage === '') {
            return null;
        }

        foreach (self::STAGE_ALIASES as $canonical => $aliases) {
            if (in_array($stage, $aliases, true)) {
                return $canonical;
            }
        }

        return $stage;
    }

    public static function deliveredStages(): array
    {
        return self::STAGE_ALIASES[self::STAGE_XUAT_KHO];
    }

    public static function warehouseDoneStages(): array
    {
        return self::STAGE_ALIASES[self::STAGE_NHAP_KHO];
    }

    public function isDelivered(): bool
    {
        return self::normalizeStage($this->cong_doan) === self::STAGE_XUAT_KHO;
    }

    public function isWarehouseDone(): bool
    {
        $canonical = self::normalizeStage($this->cong_doan);
        return in_array($canonical, [self::STAGE_NHAP_KHO, self::STAGE_XUAT_KHO], true);
    }
}
