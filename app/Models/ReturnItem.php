<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReturnItem extends Model
{
    protected $fillable = ['return_id', 'product_id', 'quantity', 'condition', 'notes'];

    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
}
