<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Defect extends Model
{
    protected $fillable = ['quality_control_id', 'type', 'description', 'quantity', 'severity'];

    public function qualityControl(): BelongsTo
    {
        return $this->belongsTo(QualityControl::class);
    }
}
