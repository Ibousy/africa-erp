<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
    protected $fillable = [
        'tenant_id', 'reference', 'product_id', 'quantity', 'type', 'carrier', 'contact_name',
        'origin_destination', 'status', 'departure_date',
        'arrival_date', 'weight_kg', 'notes', 'stock_processed',
    ];

    protected $casts = [
        'departure_date'  => 'date',
        'arrival_date'    => 'date',
        'weight_kg'       => 'decimal:2',
        'quantity'        => 'decimal:2',
        'stock_processed' => 'boolean',
    ];

    public function product(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Product::class);
    }
}
