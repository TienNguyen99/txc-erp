<?php

namespace App\Models;

use App\Support\ItemCode;
use Illuminate\Database\Eloquent\Model;

class DanhMucHangHoa extends Model
{
    protected $table = 'danh_muc_hang_hoa';

    protected $fillable = [
        'ma_hh', 'ten_hh', 'mau', 'kich_co', 'nhom_hh', 'don_vi',
        'base_uom_id', 'purchase_uom_id', 'purchase_to_base_factor',
        'don_gia', 'quy_cach', 'yards_per_roll', 'rolls_per_carton', 'dinh_muc_thung', 'net_weight', 'gross_weight',
        'hinh_anh', 'mo_ta', 'active',
        'nha_cung_cap_id', 'gia_nvl', 'ton_toi_thieu',
    ];

    protected $casts = [
        'don_gia'          => 'decimal:4',
        'yards_per_roll'   => 'decimal:2',
        'rolls_per_carton' => 'integer',
        'dinh_muc_thung'   => 'integer',
        'net_weight'       => 'decimal:2',
        'gross_weight'     => 'decimal:2',
        'active'           => 'boolean',
        'gia_nvl'          => 'decimal:4',
        'ton_toi_thieu'    => 'integer',
        'purchase_to_base_factor' => 'decimal:6',
    ];

    public function setMaHhAttribute($value): void
    {
        $this->attributes['ma_hh'] = ItemCode::normalize($value);
    }

    public function warehouseTransactions()
    {
        return $this->hasMany(WarehouseTransaction::class, 'hang_hoa_id');
    }

    public function nhaCungCap()
    {
        return $this->belongsTo(NhaCungCap::class, 'nha_cung_cap_id');
    }

    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    /**
     * Định mức cấu thành nên sản phẩm này (BOM)
     * Lấy các nguyên vật liệu cần thiết để tạo ra sản phẩm.
     */
    public function dinhMucNvl()
    {
        return $this->hasMany(DinhMucNvl::class, 'san_pham_id');
    }

    /**
     * Sản phẩm này được dùng làm nguyên liệu cho các BOM nào
     */
    public function duocDungChoBom()
    {
        return $this->hasMany(DinhMucNvl::class, 'nguyen_lieu_id');
    }

    public function baseUom()
    {
        return $this->belongsTo(UnitOfMeasure::class, 'base_uom_id');
    }

    public function purchaseUom()
    {
        return $this->belongsTo(UnitOfMeasure::class, 'purchase_uom_id');
    }

    public function getBaseUnitCostVndAttribute(): float
    {
        $factor = (float) ($this->purchase_to_base_factor ?: 1);

        return (float) ($this->gia_nvl ?: 0) / max($factor, 0.000001);
    }

    public function standardCostSheets()
    {
        return $this->hasMany(StandardCostSheet::class, 'product_id');
    }
}
