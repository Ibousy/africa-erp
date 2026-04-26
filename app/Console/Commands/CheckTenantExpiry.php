<?php

namespace App\Console\Commands;

use App\Models\ErpNotification;
use App\Models\RenewalRequest;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Console\Command;

class CheckTenantExpiry extends Command
{
    protected $signature   = 'tenants:check-expiry';
    protected $description = 'Expire les abonnements dépassés et envoie des rappels avant expiration';

    public function handle(): void
    {
        $this->expireActive();
        $this->expireTrial();
        $this->sendReminders();
        $this->info('Vérification des expirations terminée.');
    }

    // Abonnements actifs dont la date est dépassée → expired + modules désactivés
    private function expireActive(): void
    {
        $expired = Tenant::where('status', 'active')
            ->whereNotNull('subscription_ends_at')
            ->where('subscription_ends_at', '<', now())
            ->get();

        foreach ($expired as $tenant) {
            $tenant->update(['status' => 'expired']);
            $tenant->modules()->update(['enabled' => false]);

            $this->notifyAdmin($tenant,
                'subscription_expired',
                '⚠ Abonnement expiré',
                'Votre abonnement a expiré. Veuillez renouveler pour retrouver l\'accès.',
                '/subscription/renew'
            );

            $this->line("  Expiré : {$tenant->company_name}");
        }
    }

    // Périodes d'essai dépassées → expired + modules désactivés
    private function expireTrial(): void
    {
        $expired = Tenant::where('status', 'trial')
            ->whereNotNull('trial_ends_at')
            ->where('trial_ends_at', '<', now())
            ->get();

        foreach ($expired as $tenant) {
            $tenant->update(['status' => 'expired']);
            $tenant->modules()->update(['enabled' => false]);

            $this->notifyAdmin($tenant,
                'trial_expired',
                '⏰ Période d\'essai terminée',
                'Votre période d\'essai a expiré. Choisissez un plan pour continuer.',
                '/subscription/renew'
            );

            $this->line("  Essai expiré : {$tenant->company_name}");
        }
    }

    // Rappels avant expiration : 7 jours, 3 jours, 1 jour
    private function sendReminders(): void
    {
        foreach ([7, 3, 1] as $days) {
            $start = now()->addDays($days)->startOfDay();
            $end   = now()->addDays($days)->endOfDay();

            $tenants = Tenant::whereIn('status', ['active', 'trial'])
                ->where(function ($q) use ($start, $end) {
                    $q->whereBetween('subscription_ends_at', [$start, $end])
                      ->orWhereBetween('trial_ends_at', [$start, $end]);
                })
                ->get();

            foreach ($tenants as $tenant) {
                // Ne pas envoyer si une demande de renouvellement est déjà en attente
                if ($tenant->pendingRenewal()) continue;

                $icon = $days === 1 ? 'alert' : 'clock';
                $this->notifyAdmin($tenant,
                    'subscription_expiring',
                    "⚡ Abonnement expire dans {$days} jour(s)",
                    "Votre abonnement expire le {$tenant->expiryDate()->format('d/m/Y')}. Renouvelez maintenant pour éviter toute interruption.",
                    '/subscription/renew',
                    $icon
                );

                $this->line("  Rappel {$days}j : {$tenant->company_name}");
            }
        }
    }

    private function notifyAdmin(Tenant $tenant, string $type, string $title, string $body, string $link, string $icon = 'bell'): void
    {
        $admin = $tenant->users()->where('role', 'admin')->first();
        if (!$admin) return;

        // Éviter les doublons : ne pas notifier si notif identique dans les dernières 24h
        $exists = ErpNotification::where('user_id', $admin->id)
            ->where('type', $type)
            ->where('created_at', '>=', now()->subDay())
            ->exists();

        if ($exists) return;

        ErpNotification::create([
            'user_id'   => $admin->id,
            'tenant_id' => $tenant->id,
            'type'      => $type,
            'title'     => $title,
            'body'      => $body,
            'link'      => $link,
            'icon'      => $icon,
        ]);
    }
}
