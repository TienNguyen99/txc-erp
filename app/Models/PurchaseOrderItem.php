<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrderItem extends Model
{
    protected $table = 'purchase_order_items';

    protected $fillable = [
        'purchase_order_id', 'ma_hh', 'ten_hh',
        'don_vi', 'so_luong', 'don_gia', 'da_nhan', 'ghi_chu',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

    public function hangHoa()
    {
        return $this->belongsTo(DanhMucHangHoa::class, 'ma_hh', 'ma_hh');
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
