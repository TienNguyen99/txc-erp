<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class ProductionReport extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['cong_doan', 'ngay_sx', 'ca', 'ma_nv', 'lenh_sx', 'sl_dat', 'sl_hu'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    protected $fillable = [
        'cong_doan', 'ngay_sx', 'ca', 'ma_nv', 'lenh_sx', 'mau', 'size',
        'dinh_muc', 'so_band', 'ns_8h_1may', 'ns_gio_may',
        'sl_dat', 'sl_hu', 'so_may', 'gio_sx', 'sl_yard_met', 'van_de',
    ];

    protected $casts = [
        'ngay_sx'     => 'date',
        'dinh_muc'    => 'decimal:4',
        'ns_8h_1may'  => 'decimal:2',
        'ns_gio_may'  => 'decimal:2',
        'sl_dat'      => 'decimal:2',
        'sl_hu'       => 'decimal:2',
        'gio_sx'      => 'decimal:2',
        'sl_yard_met' => 'decimal:2',
    ];
}
