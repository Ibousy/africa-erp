<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    protected $fillable = [
        'tenant_id', 'name', 'company', 'email', 'phone',
        'source', 'status', 'estimated_value', 'notes',
    ];

    protected $casts = [
        'estimated_value' => 'decimal:2',
    ];
}
