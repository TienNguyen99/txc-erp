<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LenhSanXuatItem extends Model
{
    protected $table = 'lenh_san_xuat_items';

    protected $fillable = [
        'lenh_san_xuat_id', 'lenh_child', 'ma_hh', 'ten_hh', 'mau',
        'tong_yrd', 'sl_can_sx', 'da_len_lenh', 'stt',
    ];

    protected $casts = [
        'tong_yrd'    => 'decimal:2',
        'sl_can_sx'   => 'decimal:2',
        'da_len_lenh' => 'boolean',
    ];

    public function lenhSanXuat()
    {
        return $this->belongsTo(LenhSanXuat::class);
    }
}
