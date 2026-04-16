<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LenhSanXuat extends Model
{
    protected $table = 'lenh_san_xuat';

    protected $fillable = [
        'lenh_so', 'chart', 'nhom_hh', 'pct_hao_hut', 'ghi_chu',
    ];

    protected $casts = [
        'pct_hao_hut' => 'float',
    ];

    public function items()
    {
        return $this->hasMany(LenhSanXuatItem::class)->orderBy('stt');
    }

    /**
     * Sinh mã lệnh: {nhom_hh}-YYYYMMDD-XXX
     */
    public static function generateLenhSo(string $nhomHh): string
    {
        $prefix = $nhomHh . '-' . now()->format('Ymd') . '-';
        $last = static::where('lenh_so', 'like', $prefix . '%')
            ->orderByDesc('lenh_so')
            ->value('lenh_so');

        $seq = $last ? ((int) substr($last, -3)) + 1 : 1;

        return $prefix . str_pad($seq, 3, '0', STR_PAD_LEFT);
    }
}
