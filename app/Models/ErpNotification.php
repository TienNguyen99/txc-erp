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
    public static function push(string $title, string $message = '', string $type = 'info', string $link = '', string $icon = ''): static
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
        foreach ($items as $hh) {
            $tonKho = WarehouseTransaction::where('ma_hh', $hh->ma_hh)->nhapKho()->sum('so_luong')
                    - WarehouseTransaction::where('ma_hh', $hh->ma_hh)->xuatKho()->sum('so_luong');
            if ($tonKho < $hh->ton_toi_thieu) {
                // Tránh tạo duplicate cùng ngày
                $exists = static::where('title', 'like', "%{$hh->ma_hh}%")
                    ->whereDate('created_at', today())->exists();
                if (!$exists) {
                    static::push(
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
