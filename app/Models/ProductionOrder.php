<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductionOrder extends Model
{
    protected $fillable = [
        'tenant_id', 'reference', 'product_id', 'quantity_planned', 'quantity_produced',
        'status', 'start_date', 'end_date', 'notes', 'user_id',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(ProductionTask::class);
    }

    public function qualityControls(): HasMany
    {
        return $this->hasMany(QualityControl::class);
    }

    public function getRendementAttribute(): float
    {
        if ($this->quantity_planned == 0) return 0;
        return round(($this->quantity_produced / $this->quantity_planned) * 100, 1);
    }
}
