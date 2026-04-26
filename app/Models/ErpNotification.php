<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ErpNotification extends Model
{
    protected $table = 'erp_notifications';

    protected $fillable = [
        'tenant_id', 'user_id', 'type', 'title', 'body', 'link', 'icon', 'read_at',
    ];

    protected $casts = ['read_at' => 'datetime'];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }

    public function isRead(): bool { return $this->read_at !== null; }

    public static function send(int $userId, int $tenantId, string $type, string $title, string $body = '', string $link = '', string $icon = 'bell'): self
    {
        return self::create(compact('userId', 'tenantId', 'type', 'title', 'body', 'link', 'icon') + [
            'user_id' => $userId, 'tenant_id' => $tenantId,
        ]);
    }

    public static function notify(int $userId, int $tenantId, string $type, string $title, string $body = '', string $link = '', string $icon = 'bell'): self
    {
        return self::create([
            'user_id'   => $userId,
            'tenant_id' => $tenantId,
            'type'      => $type,
            'title'     => $title,
            'body'      => $body,
            'link'      => $link,
            'icon'      => $icon,
        ]);
    }
}
