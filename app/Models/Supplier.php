<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    protected $fillable = [
        'tenant_id', 'name', 'contact_name', 'email', 'phone',
        'country', 'address', 'notes', 'reliability_score', 'payment_terms', 'balance_due',
    ];

    protected $casts = [
        'reliability_score' => 'integer',
        'balance_due'       => 'decimal:2',
    ];

    public function purchaseOrders(): HasMany { return $this->hasMany(PurchaseOrder::class); }
    public function payments(): HasMany       { return $this->hasMany(SupplierPayment::class); }
}
