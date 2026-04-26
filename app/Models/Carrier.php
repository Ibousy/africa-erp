<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Carrier extends Model
{
    protected $fillable = [
        'tenant_id', 'name', 'phone', 'email',
        'vehicle_type', 'plate_number', 'status', 'notes',
    ];

    public const VEHICLE_TYPES = [
        'camion'  => 'Camion', 'fourgon' => 'Fourgon', 'moto' => 'Moto',
        'bateau'  => 'Bateau', 'avion'   => 'Avion',   'autre' => 'Autre',
    ];
}
