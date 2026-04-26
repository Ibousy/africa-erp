<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    protected $fillable = [
        'tenant_id', 'code', 'name', 'contact_person', 'phone', 'email',
        'address', 'city', 'country', 'segment', 'loyalty_points', 'credit_limit', 'balance_due',
    ];

    protected $casts = [
        'loyalty_points' => 'integer',
        'credit_limit'   => 'decimal:2',
        'balance_due'    => 'decimal:2',
    ];

    public const SEGMENTS = [
        'nouveau'  => 'Nouveau',
        'regulier' => 'Régulier',
        'vip'      => 'VIP',
    ];

    public function invoices(): HasMany        { return $this->hasMany(Invoice::class); }
    public function payments(): HasMany        { return $this->hasMany(ClientPayment::class); }
    public function customerOrders(): HasMany  { return $this->hasMany(CustomerOrder::class); }
    public function quotes(): HasMany          { return $this->hasMany(Quote::class); }
}
