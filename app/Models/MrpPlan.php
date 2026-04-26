<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MrpPlan extends Model
{
    protected $fillable = [
        'tenant_id', 'product_id', 'quantity_needed', 'quantity_available',
        'shortage', 'planned_date', 'status', 'notes',
    ];

    protected $casts = [
        'planned_date'       => 'date',
        'quantity_needed'    => 'decimal:2',
        'quantity_available' => 'decimal:2',
        'shortage'           => 'decimal:2',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
