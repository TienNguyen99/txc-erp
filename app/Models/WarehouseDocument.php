<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WarehouseDocument extends Model
{
    protected $fillable = [
        'document_no',
        'type',
        'document_date',
        'status',
        'created_by_id',
        'posted_at',
        'printed_at',
        'note',
    ];

    protected $casts = [
        'document_date' => 'date',
        'posted_at' => 'datetime',
        'printed_at' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(WarehouseDocumentItem::class);
    }

    public function transactions()
    {
        return $this->hasMany(WarehouseTransaction::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function isInbound(): bool
    {
        return $this->type === 'NHAPKHO';
    }
}
