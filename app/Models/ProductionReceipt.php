<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductionReceipt extends Model
{
    protected $fillable = [
        'receipt_no',
        'receipt_date',
        'cong_doan',
        'status',
        'approved_by_id',
        'posted_by_id',
        'posted_at',
        'printed_at',
        'note',
    ];

    protected $casts = [
        'receipt_date' => 'date',
        'posted_at' => 'datetime',
        'printed_at' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(ProductionReceiptItem::class);
    }

    public function reports()
    {
        return $this->hasMany(ProductionReport::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by_id');
    }

    public function postedBy()
    {
        return $this->belongsTo(User::class, 'posted_by_id');
    }
}
