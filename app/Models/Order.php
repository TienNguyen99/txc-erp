<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Order extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['job_no', 'fty_po', 'color', 'qty', 'status'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    protected $fillable = [
        'khach_hang_id',
        'job_no', 'fty_po', 'im_number', 'color', 'qty', 'unit', 'ma_hh', 'yrd',
        'can_giao_1', 'can_giao_2', 'pl_number', 'tagtime_etc', 'sig_need_date',
        'chart', 'price_usd_auto', 'price_usd', 'to_khai', 'lenh_sanxuat', 'status',
    ];

    protected $casts = [
        'tagtime_etc'    => 'date',
        'sig_need_date'  => 'date',
        'qty'            => 'decimal:2',
        'yrd'            => 'decimal:2',
        'price_usd'      => 'decimal:4',
        'price_usd_auto' => 'decimal:4',
    ];

    public function tracking()
    {
        return $this->hasMany(OrderTracking::class);
    }

    public function khachHang()
    {
        return $this->belongsTo(DanhMucKhachHang::class, 'khach_hang_id');
    }

    /**
     * Tự động cập nhật status dựa trên trạng thái tracking.
     */
    public function updateStatusFromTracking(): void
    {
        $trackings = $this->tracking()->get();

        if ($trackings->isEmpty()) {
            return;
        }

        $allShipped = $trackings->every(fn($t) => $t->cong_doan === 'Đã giao');
        $allDone    = $trackings->every(fn($t) => in_array($t->cong_doan, ['Đã nhập kho', 'Đã giao']));

        if ($allShipped) {
            $this->update(['status' => 'shipped']);
        } elseif ($allDone) {
            $this->update(['status' => 'done']);
        } elseif ($this->status === 'pending') {
            $this->update(['status' => 'in_production']);
        }
    }
}
