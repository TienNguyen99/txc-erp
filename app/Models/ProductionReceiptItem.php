<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductionReceiptItem extends Model
{
    protected $fillable = [
        'production_receipt_id',
        'production_report_id',
        'ten_san_pham',
        'ma_hh',
        'mau',
        'size',
        'so_luong',
        'don_vi',
        'lenh_sx',
        'ghi_chu',
    ];

    protected $casts = [
        'so_luong' => 'decimal:2',
    ];

    public function receipt()
    {
        return $this->belongsTo(ProductionReceipt::class, 'production_receipt_id');
    }

    public function report()
    {
        return $this->belongsTo(ProductionReport::class, 'production_report_id');
    }
}
