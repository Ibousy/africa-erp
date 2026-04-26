<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerOrderItem extends Model
{
    protected $fillable = ['customer_order_id', 'product_id', 'description', 'quantity', 'unit_price'];

    public function order(): BelongsTo   { return $this->belongsTo(CustomerOrder::class, 'customer_order_id'); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
}
