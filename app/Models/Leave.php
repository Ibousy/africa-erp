<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Leave extends Model
{
    protected $fillable = [
        'tenant_id', 'employee_id', 'type', 'start_date', 'end_date',
        'days', 'status', 'reason', 'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    public const TYPES = [
        'conge_annuel' => 'Congé annuel',
        'maladie'      => 'Maladie',
        'maternite'    => 'Maternité',
        'paternite'    => 'Paternité',
        'sans_solde'   => 'Sans solde',
        'autre'        => 'Autre',
    ];

    public const STATUSES = [
        'en_attente' => 'En attente',
        'approuve'   => 'Approuvé',
        'refuse'     => 'Refusé',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
