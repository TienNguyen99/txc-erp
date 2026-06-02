<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StandardCostSheet extends Model
{
    public const STATUSES = [
        'draft' => 'Nháp',
        'active' => 'Đang áp dụng',
        'archived' => 'Lưu trữ',
    ];

    public const BASES = [
        'production_cost' => 'Giá thành sản xuất',
        'sale_price' => 'Giá bán',
        'subtotal' => 'Tạm tính trước khoản này',
    ];

    protected $fillable = [
        'product_id', 'version', 'effective_date', 'status', 'standard_output_qty',
        'sale_price_vnd', 'bank_interest_pct', 'bank_interest_basis',
        'commission_pct', 'commission_basis', 'management_pct', 'management_basis',
        'transport_cost_vnd', 'note', 'created_by_id',
    ];

    protected $casts = [
        'effective_date' => 'date',
        'standard_output_qty' => 'decimal:4',
        'sale_price_vnd' => 'decimal:4',
        'bank_interest_pct' => 'decimal:4',
        'commission_pct' => 'decimal:4',
        'management_pct' => 'decimal:4',
        'transport_cost_vnd' => 'decimal:4',
    ];

    public function product()
    {
        return $this->belongsTo(DanhMucHangHoa::class, 'product_id');
    }

    public function lines()
    {
        return $this->hasMany(StandardCostLine::class)->orderBy('sort_order')->orderBy('id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }
}
