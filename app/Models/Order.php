<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'khach_hang_id',
        'job_no', 'fty_po', 'im_number', 'color', 'qty', 'unit', 'size', 'yrd',
        'can_giao_1', 'can_giao_2', 'pl_number', 'tagtime_etc', 'sig_need_date',
        'chart', 'price_usd_auto', 'price_usd', 'to_khai', 'status',
    ];

    protected $casts = [
        'tagtime_etc'    => 'date',
        'sig_need_date'  => 'date',
        'qty'            => 'decimal:2',
        'yrd'            => 'decimal:2',
        'price_usd'      => 'decimal:4',
        'price_usd_auto' => 'decimal:4',
    ];

    public function tracking()
    {
        return $this->hasMany(OrderTracking::class);
    }

    public function khachHang()
    {
        return $this->belongsTo(DanhMucKhachHang::class, 'khach_hang_id');
    }
}
