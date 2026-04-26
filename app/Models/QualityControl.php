<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QualityControl extends Model
{
    protected $fillable = [
        'tenant_id', 'production_order_id', 'product_id', 'checked_by', 'check_date',
        'quantity_checked', 'quantity_defective', 'status', 'notes',
    ];

    protected $casts = [
        'check_date' => 'date',
    ];

    public function productionOrder(): BelongsTo
    {
        return $this->belongsTo(ProductionOrder::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function checker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_by');
    }

    public function defects(): HasMany
    {
        return $this->hasMany(Defect::class);
    }

    public function getTauxDefautAttribute(): float
    {
        if ($this->quantity_checked == 0) return 0;
        return round(($this->quantity_defective / $this->quantity_checked) * 100, 1);
    }
}
