<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DinhMucNvl extends Model
{
    use HasFactory;

    protected $table = 'dinh_muc_nvl';

    protected $fillable = [
        'san_pham_id',
        'nguyen_lieu_id',
        'so_luong',
        'ti_le_hao_hut',
        'cong_doan',
        'ghi_chu',
    ];

    /**
     * Lấy thông tin thành phẩm
     */
    public function sanPham()
    {
        return $this->belongsTo(DanhMucHangHoa::class, 'san_pham_id');
    }

    /**
     * Lấy thông tin nguyên liệu
     */
    public function nguyenLieu()
    {
        return $this->belongsTo(DanhMucHangHoa::class, 'nguyen_lieu_id');
    }
}
