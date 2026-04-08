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
        'order_id', 'pl_number', 'size', 'mau', 'kich',
        'cong_doan', 'sl_don_hang', 'sl_san_xuat',
    ];

    protected $casts = [
        'sl_don_hang'  => 'decimal:2',
        'sl_san_xuat'  => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
