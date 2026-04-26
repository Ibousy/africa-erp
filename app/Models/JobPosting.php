<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobPosting extends Model
{
    protected $fillable = [
        'tenant_id', 'title', 'department', 'type', 'description',
        'requirements', 'salary_min', 'salary_max', 'status', 'closes_at',
    ];

    protected $casts = ['closes_at' => 'date'];

    public const TYPES = ['cdi' => 'CDI', 'cdd' => 'CDD', 'stage' => 'Stage', 'freelance' => 'Freelance'];

    public function applications(): HasMany
    {
        return $this->hasMany(JobApplication::class);
    }
}
