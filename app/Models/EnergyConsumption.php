<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EnergyConsumption extends Model
{
    protected $fillable = [
        'tenant_id', 'machine_id', 'date', 'kwh_consumed', 'cost_per_kwh', 'total_cost', 'hours_used', 'notes',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class);
    }
}
