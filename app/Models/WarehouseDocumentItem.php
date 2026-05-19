<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WarehouseDocumentItem extends Model
{
    protected $fillable = [
        'warehouse_document_id',
        'warehouse_transaction_id',
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

    public function document()
    {
        return $this->belongsTo(WarehouseDocument::class, 'warehouse_document_id');
    }

    public function transaction()
    {
        return $this->belongsTo(WarehouseTransaction::class, 'warehouse_transaction_id');
    }
}
