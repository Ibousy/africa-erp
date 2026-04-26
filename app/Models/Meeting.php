<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Meeting extends Model
{
    protected $fillable = [
        'tenant_id', 'organized_by', 'title', 'agenda', 'minutes',
        'scheduled_at', 'duration_minutes', 'departments', 'location', 'status',
        'meet_url',
        'zoom_meeting_id', 'zoom_join_url', 'zoom_start_url', 'zoom_password',
    ];

    protected $casts = [
        'departments'  => 'array',
        'scheduled_at' => 'datetime',
    ];

    public function organizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'organized_by');
    }

    public function isPast(): bool
    {
        return $this->scheduled_at->isPast();
    }
}
