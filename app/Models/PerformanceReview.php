<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerformanceReview extends Model
{
    protected $fillable = [
        'tenant_id', 'employee_id', 'period', 'punctuality_score',
        'productivity_score', 'quality_score', 'teamwork_score',
        'initiative_score', 'strengths', 'improvements', 'notes', 'reviewed_at',
    ];

    protected $casts = ['reviewed_at' => 'date'];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function averageScore(): float
    {
        return round(($this->punctuality_score + $this->productivity_score +
            $this->quality_score + $this->teamwork_score + $this->initiative_score) / 5, 1);
    }
}
