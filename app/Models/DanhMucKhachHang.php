<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DanhMucKhachHang extends Model
{
    protected $table = 'danh_muc_khach_hang';

    protected $fillable = [
        'ma_kh', 'ten_kh', 'nguoi_lien_he', 'sdt',
        'email', 'dia_chi', 'ma_so_thue', 'ghi_chu', 'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class, 'khach_hang_id');
    }
}
