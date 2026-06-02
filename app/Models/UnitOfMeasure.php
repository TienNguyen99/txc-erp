<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UnitOfMeasure extends Model
{
    protected $table = 'units_of_measure';

    protected $fillable = [
        'code',
        'name',
        'dimension',
        'factor_to_base',
        'is_base',
        'active',
    ];

    protected $casts = [
        'factor_to_base' => 'decimal:6',
        'is_base' => 'boolean',
        'active' => 'boolean',
    ];
}
