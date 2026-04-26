<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalaryPayment extends Model
{
    protected $fillable = [
        'tenant_id', 'employee_id', 'period', 'base_salary',
        'bonuses', 'deductions', 'status', 'paid_at', 'notes',
    ];

    protected $casts = [
        'base_salary' => 'decimal:2',
        'bonuses'     => 'decimal:2',
        'deductions'  => 'decimal:2',
        'net_salary'  => 'decimal:2',
        'paid_at'     => 'date',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
