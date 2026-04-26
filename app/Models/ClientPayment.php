<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientPayment extends Model
{
    protected $fillable = [
        'tenant_id', 'client_id', 'invoice_id', 'amount',
        'payment_date', 'method', 'reference', 'notes',
    ];

    protected $casts = ['payment_date' => 'date', 'amount' => 'decimal:2'];

    public const METHODS = [
        'especes' => 'Espèces', 'virement' => 'Virement', 'cheque' => 'Chèque',
        'mobile'  => 'Mobile Money', 'carte' => 'Carte bancaire',
    ];

    public function client(): BelongsTo  { return $this->belongsTo(Client::class); }
    public function invoice(): BelongsTo { return $this->belongsTo(Invoice::class); }
}
