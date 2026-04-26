<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RenewalRequest extends Model
{
    protected $connection = 'mysql';

    protected $fillable = [
        'tenant_id', 'plan', 'duration_months', 'amount',
        'status', 'payment_method', 'payment_reference',
        'notes', 'rejection_reason', 'processed_by', 'processed_at',
    ];

    protected $casts = [
        'processed_at'   => 'datetime',
        'amount'         => 'decimal:2',
        'duration_months'=> 'integer',
    ];

    // Prix FCFA par plan par mois (tarifs publics)
    public const PLAN_PRICES_FCFA = [
        'starter'    => 15000,
        'pro'        => 35000,
        'enterprise' => 75000,
    ];

    public const PLAN_LABELS = [
        'starter'    => 'Starter',
        'pro'        => 'Pro',
        'enterprise' => 'Enterprise',
    ];

    public const METHOD_LABELS = [
        'especes' => 'Espèces',
        'virement'=> 'Virement bancaire',
        'mobile'  => 'Mobile Money',
        'cheque'  => 'Chèque',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function isPending(): bool  { return $this->status === 'pending';  }
    public function isApproved(): bool { return $this->status === 'approved'; }
    public function isRejected(): bool { return $this->status === 'rejected'; }

    public static function computeAmount(string $plan, int $months): float
    {
        return (self::PLAN_PRICES_FCFA[$plan] ?? 15000) * $months;
    }
}
