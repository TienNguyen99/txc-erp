<?php
// app/Models/WarehouseTransaction.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WarehouseTransaction extends Model
{
    protected $fillable = [
        'cong_doan', 'ngay', 'size', 'mau',
        'so_luong', 'ma_nv', 'lenh_sx', 'note'
    ];

    protected $casts = [
        'ngay'     => 'date',
        'so_luong' => 'decimal:2',
    ];

    // Scope lọc nhanh
    public function scopeNhapKho($query)
    {
        return $query->where('cong_doan', 'NHAPKHO');
    }

    public function scopeXuatKho($query)
    {
        return $query->where('cong_doan', 'XUATKHO');
    }

    public function scopeThangNay($query)
    {
        return $query->whereMonth('ngay', now()->month)
                     ->whereYear('ngay', now()->year);
    }

    public function scopeThangTruoc($query)
    {
        return $query->whereMonth('ngay', now()->subMonth()->month)
                     ->whereYear('ngay', now()->subMonth()->year);
    }
}