<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MaterialRequest extends Model
{
    protected $fillable = [
        'tenant_id', 'production_order_id', 'requested_by', 'handled_by',
        'reference', 'status', 'notes', 'logistics_notes', 'responded_at',
    ];

    protected $casts = ['responded_at' => 'datetime'];

    public function requester(): BelongsTo    { return $this->belongsTo(User::class, 'requested_by'); }
    public function handler(): BelongsTo     { return $this->belongsTo(User::class, 'handled_by'); }
    public function productionOrder(): BelongsTo { return $this->belongsTo(ProductionOrder::class); }
    public function items(): HasMany         { return $this->hasMany(MaterialRequestItem::class); }
}
