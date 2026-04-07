<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DanhMucHangHoa extends Model
{
    protected $table = 'danh_muc_hang_hoa';

    protected $fillable = [
        'ma_hh', 'ten_hh', 'mau', 'kich_co', 'nhom_hh', 'don_vi',
        'don_gia', 'hinh_anh', 'mo_ta', 'active',
    ];

    protected $casts = [
        'don_gia' => 'decimal:4',
        'active'  => 'boolean',
    ];

    public function warehouseTransactions()
    {
        return $this->hasMany(WarehouseTransaction::class, 'hang_hoa_id');
    }
}
