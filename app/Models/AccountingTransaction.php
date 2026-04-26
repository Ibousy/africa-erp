<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountingTransaction extends Model
{
    protected $fillable = [
        'tenant_id', 'type', 'category', 'amount', 'date',
        'description', 'reference', 'payment_method', 'notes',
    ];

    protected $casts = [
        'date'   => 'date',
        'amount' => 'decimal:2',
    ];
}
