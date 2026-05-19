<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CostOverhead extends Model
{
    protected $fillable = [
        'month',
        'year',
        'labor_cost_vnd',
        'factory_overhead_vnd',
        'other_cost_vnd',
        'allocation_basis',
        'note',
    ];

    protected $casts = [
        'labor_cost_vnd' => 'decimal:2',
        'factory_overhead_vnd' => 'decimal:2',
        'other_cost_vnd' => 'decimal:2',
    ];

    public function getTotalCostVndAttribute(): float
    {
        return (float) $this->labor_cost_vnd
            + (float) $this->factory_overhead_vnd
            + (float) $this->other_cost_vnd;
    }
}
