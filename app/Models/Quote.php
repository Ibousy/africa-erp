<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quote extends Model
{
    protected $fillable = [
        'tenant_id', 'client_id', 'reference', 'status', 'issue_date',
        'valid_until', 'subtotal', 'tax_rate', 'tax_amount', 'total', 'notes',
    ];

    protected $casts = [
        'issue_date'  => 'date',
        'valid_until' => 'date',
        'subtotal'    => 'decimal:2',
        'tax_rate'    => 'decimal:2',
        'tax_amount'  => 'decimal:2',
        'total'       => 'decimal:2',
    ];

    public function client(): BelongsTo { return $this->belongsTo(Client::class); }
    public function items(): HasMany    { return $this->hasMany(QuoteItem::class); }

    public function recalculate(): void
    {
        $sub = $this->items()->sum(\DB::raw('quantity * unit_price * (1 - discount_pct / 100)'));
        $tax = round($sub * $this->tax_rate / 100, 2);
        $this->update(['subtotal' => $sub, 'tax_amount' => $tax, 'total' => $sub + $tax]);
    }
}
