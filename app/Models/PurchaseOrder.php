<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    protected $table = 'purchase_orders';

    protected $fillable = [
        'so_po', 'nha_cung_cap_id', 'ngay_dat',
        'ngay_giao_du_kien', 'ngay_nhan_thuc_te',
        'trang_thai', 'ghi_chu', 'created_by',
    ];

    protected $casts = [
        'ngay_dat'           => 'date',
        'ngay_giao_du_kien'  => 'date',
        'ngay_nhan_thuc_te'  => 'date',
    ];

    public static $trangThaiLabels = [
        'draft'     => ['label' => 'Nháp',        'color' => 'secondary'],
        'sent'      => ['label' => 'Đã gửi NCC',  'color' => 'info'],
        'confirmed' => ['label' => 'NCC xác nhận','color' => 'primary'],
        'received'  => ['label' => 'Đã nhận hàng','color' => 'success'],
        'cancelled' => ['label' => 'Huỷ',         'color' => 'danger'],
    ];

    public function nhaCungCap()
    {
        return $this->belongsTo(NhaCungCap::class, 'nha_cung_cap_id');
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class, 'purchase_order_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function getTongTienAttribute(): float
    {
        return $this->items->sum(fn($i) => $i->so_luong * $i->don_gia);
    }

    /** Tự sinh mã PO: PO-YYYYMM-XXX */
    public static function generateSoPo(): string
    {
        $prefix = 'PO-' . now()->format('Ym') . '-';
        $last = static::where('so_po', 'like', $prefix . '%')
            ->orderByDesc('id')->first();
        $seq = $last ? ((int) substr($last->so_po, -3)) + 1 : 1;
        return $prefix . str_pad($seq, 3, '0', STR_PAD_LEFT);
    }
}
