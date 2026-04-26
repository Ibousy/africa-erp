<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomerOrder extends Model
{
    protected $fillable = [
        'tenant_id', 'client_id', 'quote_id', 'reference', 'status',
        'order_date', 'delivery_date', 'total_amount', 'delivery_address', 'notes',
    ];

    protected $casts = [
        'order_date'    => 'date',
        'delivery_date' => 'date',
        'total_amount'  => 'decimal:2',
    ];

    public function client(): BelongsTo { return $this->belongsTo(Client::class); }
    public function quote(): BelongsTo  { return $this->belongsTo(Quote::class); }
    public function items(): HasMany    { return $this->hasMany(CustomerOrderItem::class); }
}
