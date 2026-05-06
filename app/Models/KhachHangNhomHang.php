<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KhachHangNhomHang extends Model
{
    use HasFactory;

    protected $table = 'khach_hang_nhom_hang';

    protected $fillable = [
        'khach_hang_id',
        'ma_nhom',
        'ten_nhom'
    ];

    public function khachHang()
    {
        return $this->belongsTo(DanhMucKhachHang::class, 'khach_hang_id');
    }
}
