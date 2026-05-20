<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ErpNotification extends Model
{
    protected $table = 'erp_notifications';

    protected $fillable = [
        'type', 'icon', 'title', 'message', 'link', 'is_read', 'user_id',
    ];

    protected $casts = ['is_read' => 'boolean'];

    /** Tạo thông báo nhanh */
    public static function send(string $title, string $message = '', string $type = 'info', string $link = '', string $icon = ''): static
    {
        $icons = ['info' => 'fa-circle-info', 'warning' => 'fa-triangle-exclamation', 'danger' => 'fa-circle-xmark', 'success' => 'fa-circle-check'];
        return static::create([
            'type'    => $type,
            'icon'    => $icon ?: ($icons[$type] ?? 'fa-bell'),
            'title'   => $title,
            'message' => $message,
            'link'    => $link,
            'is_read' => false,
        ]);
    }

    /** Kiểm tra tồn kho thấp và tạo cảnh báo */
    public static function checkLowStock(): int
    {
        $count = 0;
        $items = DanhMucHangHoa::whereRaw('ton_toi_thieu > 0')->get();

        $maHhList = $items->pluck('ma_hh')->filter()->values();
        $stockIn = WarehouseTransaction::nhapKho()
            ->whereIn('ma_hh', $maHhList)
            ->selectRaw('ma_hh, SUM(so_luong) as total')
            ->groupBy('ma_hh')
            ->pluck('total', 'ma_hh');
        $stockOut = WarehouseTransaction::xuatKho()
            ->whereIn('ma_hh', $maHhList)
            ->selectRaw('ma_hh, SUM(so_luong) as total')
            ->groupBy('ma_hh')
            ->pluck('total', 'ma_hh');

        foreach ($items as $hh) {
            $tonKho = (float) (($stockIn[$hh->ma_hh] ?? 0) - ($stockOut[$hh->ma_hh] ?? 0));
            if ($tonKho < $hh->ton_toi_thieu) {
                // Tránh tạo duplicate cùng ngày
                $exists = static::where('title', 'like', "%{$hh->ma_hh}%")
                    ->whereDate('created_at', today())->exists();
                if (!$exists) {
                    static::send(
                        "Tồn kho thấp: {$hh->ma_hh}",
                        "Tồn kho hiện tại: " . number_format($tonKho) . " | Tối thiểu: " . number_format($hh->ton_toi_thieu),
                        'warning',
                        '/admin/warehouse-transactions/ton-kho',
                        'fa-box-open'
                    );
                    $count++;
                }
            }
        }
        return $count;
    }

    public function scopeUnread($q) { return $q->where('is_read', false); }
}
