<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrderItem extends Model
{
    protected $table = 'purchase_order_items';

    protected $fillable = [
        'purchase_order_id', 'ma_hh', 'ten_hh',
        'don_vi', 'base_uom_id', 'purchase_uom_id', 'purchase_to_base_factor',
        'so_luong', 'don_gia', 'da_nhan', 'ghi_chu',
    ];

    protected $casts = [
        'purchase_to_base_factor' => 'decimal:6',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

    public function hangHoa()
    {
        return $this->belongsTo(DanhMucHangHoa::class, 'ma_hh', 'ma_hh');
    }

    public function baseUom()
    {
        return $this->belongsTo(UnitOfMeasure::class, 'base_uom_id');
    }

    public function purchaseUom()
    {
        return $this->belongsTo(UnitOfMeasure::class, 'purchase_uom_id');
    }

    public function getBaseQuantityAttribute(): float
    {
        return (float) $this->so_luong * (float) ($this->purchase_to_base_factor ?: 1);
    }

    public function getThanhTienAttribute(): float
    {
        return $this->so_luong * $this->don_gia;
    }

    public function getChuaNhanAttribute(): float
    {
        return max(0, $this->so_luong - $this->da_nhan);
    }
}
