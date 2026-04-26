<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    protected $connection = 'mysql';

    protected $fillable = [
        'company_name', 'slug', 'db_name', 'logo_path', 'industry', 'country', 'city',
        'address', 'phone', 'email', 'website', 'theme', 'plan', 'status',
        'trial_ends_at', 'subscribed_at', 'subscription_ends_at',
        'onboarding_complete', 'max_users', 'monthly_price',
    ];

    protected $casts = [
        'trial_ends_at'        => 'datetime',
        'subscribed_at'        => 'datetime',
        'subscription_ends_at' => 'datetime', // BUG FIX: était 'date', empêchait isPast() précis
        'onboarding_complete'  => 'boolean',
        'monthly_price'        => 'decimal:2',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function modules(): HasMany
    {
        return $this->hasMany(TenantModule::class);
    }

    public function renewalRequests(): HasMany
    {
        return $this->hasMany(RenewalRequest::class);
    }

    public function hasModule(string $module): bool
    {
        return $this->modules->contains(fn($m) => $m->module === $module && $m->enabled);
    }

    // BUG FIX : l'ancienne version retournait true pour status='active' même si
    // subscription_ends_at était dépassée (abonnement expiré mais statut non mis à jour).
    public function isActive(): bool
    {
        if (in_array($this->status, ['suspended', 'expired'])) return false;

        if ($this->status === 'active') {
            if ($this->subscription_ends_at && $this->subscription_ends_at->isPast()) {
                return false;
            }
            return true;
        }

        if ($this->status === 'trial') {
            return $this->trial_ends_at?->isFuture() ?? false;
        }

        return false;
    }

    public function expiryDate(): ?\Illuminate\Support\Carbon
    {
        return $this->status === 'trial'
            ? $this->trial_ends_at
            : $this->subscription_ends_at;
    }

    public function daysUntilExpiry(): int
    {
        $date = $this->expiryDate();
        if (!$date) return PHP_INT_MAX;
        return max(0, (int) now()->diffInDays($date, false));
    }

    public function isExpiringSoon(int $withinDays = 7): bool
    {
        $date = $this->expiryDate();
        if (!$date || !$this->isActive()) return false;
        $days = (int) now()->diffInDays($date, false);
        return $days >= 0 && $days <= $withinDays;
    }

    public function pendingRenewal(): ?RenewalRequest
    {
        return $this->renewalRequests()->where('status', 'pending')->latest()->first();
    }

    public function trialDaysLeft(): int
    {
        if (!$this->trial_ends_at || $this->status !== 'trial') return 0;
        return max(0, (int) now()->diffInDays($this->trial_ends_at, false));
    }

    public function logoUrl(): ?string
    {
        return $this->logo_path ? asset('storage/' . $this->logo_path) : null;
    }

    public function themeColors(): array
    {
        return match ($this->theme) {
            'blue'   => ['hex' => '#3b82f6', 'dark' => '#2563eb', 'name' => 'Océan'],
            'green'  => ['hex' => '#10b981', 'dark' => '#059669', 'name' => 'Forêt'],
            'purple' => ['hex' => '#8b5cf6', 'dark' => '#7c3aed', 'name' => 'Violet'],
            'red'    => ['hex' => '#ef4444', 'dark' => '#dc2626', 'name' => 'Rouge'],
            default  => ['hex' => '#f97316', 'dark' => '#ea580c', 'name' => 'Corail'],
        };
    }
}
