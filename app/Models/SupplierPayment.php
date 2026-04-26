<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierPayment extends Model
{
    protected $fillable = [
        'tenant_id', 'supplier_id', 'purchase_order_id', 'amount',
        'payment_date', 'method', 'reference', 'notes',
    ];

    protected $casts = ['payment_date' => 'date', 'amount' => 'decimal:2'];

    public function supplier(): BelongsTo      { return $this->belongsTo(Supplier::class); }
    public function purchaseOrder(): BelongsTo { return $this->belongsTo(PurchaseOrder::class); }
}
