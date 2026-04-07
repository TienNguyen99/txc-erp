<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderTracking extends Model
{
    protected $table = 'order_tracking';

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
