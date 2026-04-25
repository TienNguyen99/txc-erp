<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DanhMucHangHoa extends Model
{
    protected $table = 'danh_muc_hang_hoa';

    protected $fillable = [
        'ma_hh', 'ten_hh', 'mau', 'kich_co', 'nhom_hh', 'don_vi',
        'don_gia', 'quy_cach', 'yards_per_roll', 'rolls_per_carton', 'dinh_muc_thung', 'net_weight', 'gross_weight',
        'hinh_anh', 'mo_ta', 'active',
    ];

    protected $casts = [
        'don_gia'          => 'decimal:4',
        'yards_per_roll'   => 'decimal:2',
        'rolls_per_carton' => 'integer',
        'dinh_muc_thung'   => 'integer',
        'net_weight'       => 'decimal:2',
        'gross_weight'     => 'decimal:2',
        'active'           => 'boolean',
    ];

    public function warehouseTransactions()
    {
        return $this->hasMany(WarehouseTransaction::class, 'hang_hoa_id');
    }
}
