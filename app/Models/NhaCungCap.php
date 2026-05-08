<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NhaCungCap extends Model
{
    protected $table = 'nha_cung_cap';

    protected $fillable = [
        'ma_ncc', 'ten_ncc', 'nguoi_lien_he', 'sdt',
        'email', 'dia_chi', 'ma_so_thue', 'ghi_chu', 'active',
    ];

    protected $casts = ['active' => 'boolean'];

    public function hangHoa()
    {
        return $this->hasMany(DanhMucHangHoa::class, 'nha_cung_cap_id');
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class, 'nha_cung_cap_id');
    }
}
