<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StandardCostLine extends Model
{
    public const CATEGORIES = [
        'material' => 'Nguyên vật liệu',
        'labor' => 'Nhân công',
        'depreciation' => 'Khấu hao và công cụ',
        'test' => 'Kiểm tra chất lượng',
        'other' => 'Chi phí trực tiếp khác',
    ];

    protected $fillable = [
        'standard_cost_sheet_id', 'category', 'item_id', 'code', 'name', 'stage',
        'unit', 'quantity', 'waste_pct', 'unit_price_vnd', 'allocation_qty',
        'note', 'sort_order',
    ];

    protected $casts = [
        'quantity' => 'decimal:6',
        'waste_pct' => 'decimal:4',
        'unit_price_vnd' => 'decimal:4',
        'allocation_qty' => 'decimal:4',
    ];

    public function sheet()
    {
        return $this->belongsTo(StandardCostSheet::class, 'standard_cost_sheet_id');
    }

    public function item()
    {
        return $this->belongsTo(DanhMucHangHoa::class, 'item_id');
    }
}
