<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobApplication extends Model
{
    protected $fillable = [
        'tenant_id', 'job_posting_id', 'applicant_name', 'email',
        'phone', 'status', 'notes', 'applied_at',
    ];

    protected $casts = ['applied_at' => 'date'];

    public const STATUSES = [
        'nouveau'  => 'Nouveau',
        'examine'  => 'Examiné',
        'convoque' => 'Convoqué',
        'embauche' => 'Embauché',
        'refuse'   => 'Refusé',
    ];

    public function jobPosting(): BelongsTo
    {
        return $this->belongsTo(JobPosting::class);
    }
}
