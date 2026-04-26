<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimeLog extends Model
{
    protected $fillable = [
        'tenant_id', 'employee_id', 'date', 'check_in',
        'check_out', 'hours_worked', 'status', 'notes',
    ];

    protected $casts = ['date' => 'date'];

    public const STATUSES = [
        'present' => 'Présent',
        'absent'  => 'Absent',
        'retard'  => 'Retard',
        'conge'   => 'Congé',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
