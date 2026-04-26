<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DepartmentMessage extends Model
{
    protected $fillable = [
        'tenant_id', 'from_user_id', 'from_department', 'to_department',
        'body', 'type', 'voice_path', 'voice_duration', 'read_at',
    ];

    protected $casts = ['read_at' => 'datetime'];

    public function sender(): BelongsTo { return $this->belongsTo(User::class, 'from_user_id'); }
}
