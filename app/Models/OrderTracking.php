<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderTracking extends Model
{
    protected $table = 'order_tracking';

    const STAGES = [
        'Chờ sản xuất' => ['icon' => 'fa-clock',         'color' => 'warning',   'order' => 0],
        'Dệt'          => ['icon' => 'fa-industry',       'color' => 'info',      'order' => 1],
        'Định hình'     => ['icon' => 'fa-shapes',         'color' => 'primary',   'order' => 2],
        'Đã nhập kho'   => ['icon' => 'fa-warehouse',      'color' => 'success',   'order' => 3],
        'Đã giao'       => ['icon' => 'fa-truck-loading',   'color' => 'dark',      'order' => 4],
    ];

    protected $fillable = [
        'order_id', 'tracking_number', 'tracking_number_child', 'pl_number', 'size', 'mau', 'kich',
        'cong_doan', 'sl_don_hang', 'sl_san_xuat', 'da_tao_lenh_sx',
    ];

    protected $casts = [
        'sl_don_hang'  => 'decimal:2',
        'sl_san_xuat'  => 'decimal:2',
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
}
