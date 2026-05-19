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
        'sl_dat', 'sl_hu', 'so_may', 'gio_sx', 'sl_yard_met', 'van_de', 'status',
        'approved_by_id', 'approved_at', 'production_receipt_id', 'posted_at',
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
        'approved_at' => 'datetime',
        'posted_at'   => 'datetime',
    ];

    public function receipt()
    {
        return $this->belongsTo(ProductionReceipt::class, 'production_receipt_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by_id');
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isPosted(): bool
    {
        return $this->status === 'posted' || $this->production_receipt_id !== null;
    }
}
