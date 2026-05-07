<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuyTrinhSanXuat extends Model
{
    protected $table = 'quy_trinh_san_xuat';

    protected $fillable = [
        'ma_quy_trinh',
        'ten_quy_trinh',
        'san_pham_ap_dung',
        'ngay_hieu_luc',
        'trang_thai',
        'flow_data',
        'ghi_chu',
    ];

    protected $casts = [
        'san_pham_ap_dung' => 'array',
        'flow_data' => 'array',
        'ngay_hieu_luc' => 'date',
    ];
}
