<?php
// app/Models/WarehouseTransaction.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class WarehouseTransaction extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['cong_doan', 'ma_hh', 'ngay', 'so_luong', 'lenh_sx'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    protected $fillable = [
        'cong_doan', 'ma_hh', 'hang_hoa_id', 'ngay', 'size', 'mau',
        'so_luong', 'price_usd', 'exchange_rate', 'ma_nv', 'lenh_sx', 'note'
    ];

    protected $casts = [
        'ngay'          => 'date',
        'so_luong'      => 'decimal:2',
        'price_usd'     => 'decimal:4',
        'exchange_rate' => 'decimal:2',
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

    public function hangHoa()
    {
        return $this->belongsTo(DanhMucHangHoa::class, 'hang_hoa_id');
    }
}