<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Machine extends Model
{
    protected $fillable = [
        'tenant_id', 'code', 'name', 'type', 'description', 'status',
        'location', 'purchase_date', 'power_kw',
    ];

    protected $casts = [
        'purchase_date' => 'date',
    ];

    public function maintenances(): HasMany
    {
        return $this->hasMany(Maintenance::class);
    }

    public function energyConsumptions(): HasMany
    {
        return $this->hasMany(EnergyConsumption::class);
    }
}
