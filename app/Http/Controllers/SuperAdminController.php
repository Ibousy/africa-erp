<?php

namespace App\Http\Controllers;

use App\Models\ErpNotification;
use App\Models\RenewalRequest;
use App\Models\Tenant;
use App\Models\TenantModule;
use App\Models\User;
use App\Services\TenantDatabaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SuperAdminController extends Controller
{
    private const PLAN_PRICES  = ['starter' => 29, 'pro' => 79, 'enterprise' => 199];
    private const PLAN_LIMITS  = ['starter' => 5,  'pro' => 20, 'enterprise' => null];
    private const ALL_MODULES  = ['stock', 'production', 'mrp', 'quality', 'maintenance',
                                   'logistics', 'purchases', 'sales', 'crm', 'accounting', 'hr', 'energy', 'users'];
    private const MODULE_LABELS = [
        'stock'       => '📦 Stock',
        'production'  => '🏭 Production',
        'mrp'         => '📋 MRP',
        'quality'     => '✅ Qualité',
        'maintenance' => '🔧 Maintenance',
        'logistics'   => '🚚 Logistique',
        'purchases'   => '🛒 Achats',
        'sales'       => '💼 Commercial',
        'crm'         => '🎯 CRM',
        'accounting'  => '💰 Comptabilité',
        'hr'          => '👥 RH',
        'energy'      => '⚡ Énergie',
        'users'       => '🔐 Utilisateurs',
    ];

    private function guard(): void
    {
        if (!auth()->check() || !auth()->user()->isSuperAdmin()) {
            abort(redirect()->route('login')->with('error', 'Accès réservé au super administrateur.'));
        }
    }

    public function index(Request $request)
    {
        $this->guard();

        $query = Tenant::withCount('users');
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('company_name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }
        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('plan'))   $query->where('plan', $request->plan);

        $tenants = $query->latest()->paginate(20)->withQueryString();

        $stats = [
            'total'            => Tenant::count(),
            'active'           => Tenant::where('status', 'active')->count(),
            'trial'            => Tenant::where('status', 'trial')->count(),
            'expired'          => Tenant::whereIn('status', ['expired', 'suspended'])->count(),
            'users'            => User::where('role', '!=', 'super_admin')->count(),
            'revenue'          => (float) Tenant::where('status', 'active')->sum('monthly_price'),
            'expiring_soon'    => Tenant::where('status', 'active')
                                        ->whereNotNull('subscription_ends_at')
                                        ->whereBetween('subscription_ends_at', [now(), now()->addDays(30)])
                                        ->count(),
            'provisioned'      => Tenant::whereNotNull('db_name')->count(),
            'pending_renewals' => RenewalRequest::where('status', 'pending')->count(),
        ];

        return view('superadmin.index', compact('tenants', 'stats'));
    }

    public function create()
    {
        $this->guard();
        return view('superadmin.create', [
            'planPrices'   => self::PLAN_PRICES,
            'planLimits'   => self::PLAN_LIMITS,
            'allModules'   => self::ALL_MODULES,
            'moduleLabels' => self::MODULE_LABELS,
        ]);
    }

    public function store(Request $request)
    {
        $this->guard();

        $data = $request->validate([
            'company_name'         => 'required|string|max:191',
            'email'                => 'nullable|email|max:191',
            'phone'                => 'nullable|string|max:30',
            'country'              => 'nullable|string|max:100',
            'plan'                 => 'required|in:starter,pro,enterprise',
            'status'               => 'required|in:trial,active',
            'trial_ends_at'        => 'nullable|date',
            'subscription_ends_at' => 'nullable|date',
            'monthly_price'        => 'nullable|numeric|min:0',
            'admin_name'           => 'required|string|max:191',
            'admin_email'          => 'required|email|unique:users,email|max:191',
            'admin_password'       => 'required|min:8',
            'modules'              => 'nullable|array',
        ]);

        $tenant = Tenant::create([
            'company_name'         => $data['company_name'],
            'slug'                 => Str::slug($data['company_name']) . '-' . Str::random(6),
            'email'                => $data['email'] ?? null,
            'phone'                => $data['phone'] ?? null,
            'country'              => $data['country'] ?? null,
            'plan'                 => $data['plan'],
            'status'               => $data['status'],
            'trial_ends_at'        => $data['trial_ends_at'] ?? ($data['status'] === 'trial' ? now()->addDays(14) : null),
            'subscription_ends_at' => $data['subscription_ends_at'] ?? null,
            'monthly_price'        => $data['monthly_price'] ?? self::PLAN_PRICES[$data['plan']],
            'max_users'            => self::PLAN_LIMITS[$data['plan']],
            'theme'                => 'orange',
            'onboarding_complete'  => true,
        ]);

        User::create([
            'name'      => $data['admin_name'],
            'email'     => $data['admin_email'],
            'password'  => Hash::make($data['admin_password']),
            'role'      => 'admin',
            'tenant_id' => $tenant->id,
            'is_active' => true,
        ]);

        $enabledModules = $data['modules'] ?? self::ALL_MODULES;
        foreach (self::ALL_MODULES as $module) {
            TenantModule::create([
                'tenant_id' => $tenant->id,
                'module'    => $module,
                'enabled'   => in_array($module, $enabledModules),
            ]);
        }

        if ($request->boolean('provision_db')) {
            try {
                app(TenantDatabaseService::class)->provision($tenant);
            } catch (\Throwable $e) {
                return redirect()->route('superadmin.show', $tenant)
                    ->with('warning', 'Entreprise créée mais la base de données n\'a pas pu être provisionnée : ' . $e->getMessage());
            }
        }

        return redirect()->route('superadmin.show', $tenant)
            ->with('success', 'Entreprise "' . $tenant->company_name . '" créée avec succès.');
    }

    public function show(Tenant $tenant)
    {
        $this->guard();
        $tenant->load(['users', 'modules']);
        $userCount    = $tenant->users()->count();
        $planPrices   = self::PLAN_PRICES;
        $planLimits   = self::PLAN_LIMITS;
        $allModules   = self::ALL_MODULES;
        $moduleLabels = self::MODULE_LABELS;
        return view('superadmin.show', compact('tenant', 'userCount', 'planPrices', 'planLimits', 'allModules', 'moduleLabels'));
    }

    public function update(Request $request, Tenant $tenant)
    {
        $this->guard();

        $data = $request->validate([
            'status'               => 'required|in:trial,active,expired,suspended',
            'plan'                 => 'nullable|in:starter,pro,enterprise',
            'trial_ends_at'        => 'nullable|date',
            'subscription_ends_at' => 'nullable|date',
            'monthly_price'        => 'nullable|numeric|min:0',
            'max_users'            => 'nullable|integer|min:1',
        ]);

        $tenant->update([
            'status'               => $data['status'],
            'plan'                 => $data['plan'] ?: $tenant->plan,
            'monthly_price'        => $data['monthly_price'] !== null && $data['monthly_price'] !== '' ? $data['monthly_price'] : $tenant->monthly_price,
            'max_users'            => $data['max_users'] ?: $tenant->max_users,
            'trial_ends_at'        => $data['trial_ends_at'] ?: null,
            'subscription_ends_at' => $data['subscription_ends_at'] ?: null,
        ]);

        return back()->with('success', $tenant->company_name . ' mis à jour.');
    }

    public function extend(Request $request, Tenant $tenant)
    {
        $this->guard();

        $data = $request->validate([
            'subscription_ends_at' => 'required|date',
            'monthly_price'        => 'nullable|numeric|min:0',
            'plan'                 => 'nullable|in:starter,pro,enterprise',
        ]);

        $tenant->update([
            'status'               => 'active',
            'subscription_ends_at' => $data['subscription_ends_at'],
            'monthly_price'        => $data['monthly_price'] ?? $tenant->monthly_price,
            'plan'                 => $data['plan'] ?? $tenant->plan,
        ]);

        // Réactiver tous les modules lors du renouvellement
        $tenant->modules()->update(['enabled' => true]);

        return back()->with('success',
            'Abonnement renouvelé jusqu\'au ' . \Carbon\Carbon::parse($data['subscription_ends_at'])->format('d/m/Y') .
            '. Tous les modules sont réactivés.'
        );
    }

    // Suspendre : désactive tous les modules + bloque l'accès
    public function suspend(Tenant $tenant)
    {
        $this->guard();

        $tenant->update(['status' => 'suspended']);
        $tenant->modules()->update(['enabled' => false]);

        return back()->with('success', '"' . $tenant->company_name . '" suspendu — tous les modules désactivés.');
    }

    // Réactiver : réactive tous les modules + remet active
    public function activate(Tenant $tenant)
    {
        $this->guard();

        $tenant->update(['status' => 'active']);
        $tenant->modules()->update(['enabled' => true]);

        return back()->with('success', '"' . $tenant->company_name . '" réactivé — tous les modules activés.');
    }

    public function enableAllModules(Tenant $tenant)
    {
        $this->guard();
        $tenant->modules()->update(['enabled' => true]);
        return back()->with('success', 'Tous les modules activés.');
    }

    public function disableAllModules(Tenant $tenant)
    {
        $this->guard();
        $tenant->modules()->update(['enabled' => false]);
        return back()->with('success', 'Tous les modules désactivés.');
    }

    // Toggle un module individuel
    public function toggleModule(Tenant $tenant, string $module)
    {
        $this->guard();

        if (!in_array($module, self::ALL_MODULES)) abort(404);

        $mod = $tenant->modules()->firstOrCreate(
            ['module' => $module],
            ['enabled' => false]
        );
        $mod->update(['enabled' => !$mod->enabled]);

        return back()->with('success',
            'Module ' . (self::MODULE_LABELS[$module] ?? $module) .
            ($mod->enabled ? ' activé.' : ' désactivé.')
        );
    }

    public function provision(Tenant $tenant)
    {
        $this->guard();
        try {
            $db = app(TenantDatabaseService::class)->provision($tenant);
            return back()->with('success', 'Base de données isolée créée : ' . $db);
        } catch (\Throwable $e) {
            return back()->with('error', 'Erreur de provisionnement : ' . $e->getMessage());
        }
    }

    public function destroy(Tenant $tenant)
    {
        $this->guard();

        if ($tenant->db_name) {
            try { app(TenantDatabaseService::class)->drop($tenant); } catch (\Throwable) {}
        }

        $name = $tenant->company_name;
        $tenant->users()->delete();
        $tenant->modules()->delete();
        $tenant->delete();

        return redirect()->route('superadmin.index')->with('success', 'Entreprise "' . $name . '" supprimée définitivement.');
    }

    public function impersonate(Tenant $tenant)
    {
        $this->guard();
        $admin = $tenant->users()->where('role', 'admin')->first();
        if (!$admin) return back()->with('error', 'Aucun administrateur dans cette entreprise.');
        session(['impersonating' => auth()->id()]);
        auth()->login($admin);
        return redirect()->route('dashboard')->with('success', 'Connecté en tant que ' . $admin->name . ' (' . $tenant->company_name . ')');
    }

    public function stopImpersonate()
    {
        $superAdminId = session()->pull('impersonating');
        if (!$superAdminId) return redirect()->route('dashboard');
        $superAdmin = User::find($superAdminId);
        if (!$superAdmin || !$superAdmin->isSuperAdmin()) return redirect()->route('dashboard');
        auth()->login($superAdmin);
        return redirect()->route('superadmin.index')->with('success', 'Retour au compte super admin.');
    }

    // ─── Renouvellements ─────────────────────────────────────────────────────

    public function renewals(Request $request)
    {
        $this->guard();

        $query = RenewalRequest::with('tenant', 'processor')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $renewals = $query->paginate(20)->withQueryString();

        $stats = [
            'pending'  => RenewalRequest::where('status', 'pending')->count(),
            'approved' => RenewalRequest::where('status', 'approved')
                            ->where('created_at', '>=', now()->subDays(30))->count(),
            'revenue'  => (float) RenewalRequest::where('status', 'approved')
                            ->where('created_at', '>=', now()->subDays(30))->sum('amount'),
        ];

        return view('superadmin.renewals', compact('renewals', 'stats'));
    }

    public function approveRenewal(RenewalRequest $renewal)
    {
        $this->guard();

        if (!$renewal->isPending()) {
            return back()->with('error', 'Cette demande a déjà été traitée.');
        }

        $tenant = $renewal->tenant;

        // Calculer la nouvelle date de fin à partir d'aujourd'hui (ou de la date actuelle si encore active)
        $base = ($tenant->subscription_ends_at && $tenant->subscription_ends_at->isFuture())
            ? $tenant->subscription_ends_at
            : now();

        $newEnd = $base->addMonths($renewal->duration_months);

        $tenant->update([
            'status'               => 'active',
            'plan'                 => $renewal->plan,
            'subscription_ends_at' => $newEnd,
        ]);

        // Réactiver tous les modules
        $tenant->modules()->update(['enabled' => true]);

        $renewal->update([
            'status'       => 'approved',
            'processed_by' => auth()->id(),
            'processed_at' => now(),
        ]);

        // Notifier l'admin de l'entreprise
        $admin = $tenant->users()->where('role', 'admin')->first();
        if ($admin) {
            ErpNotification::create([
                'user_id'   => $admin->id,
                'tenant_id' => $tenant->id,
                'type'      => 'renewal_approved',
                'title'     => '✅ Abonnement renouvelé !',
                'body'      => "Votre abonnement {$renewal->plan} est actif jusqu'au " . $newEnd->format('d/m/Y') . '. Tous vos modules sont réactivés.',
                'link'      => '/dashboard',
                'icon'      => 'check',
            ]);
        }

        return back()->with('success',
            "Renouvellement approuvé pour {$tenant->company_name}. Accès actif jusqu'au " . $newEnd->format('d/m/Y') . '.'
        );
    }

    public function rejectRenewal(Request $request, RenewalRequest $renewal)
    {
        $this->guard();

        if (!$renewal->isPending()) {
            return back()->with('error', 'Cette demande a déjà été traitée.');
        }

        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $renewal->update([
            'status'           => 'rejected',
            'rejection_reason' => $request->rejection_reason,
            'processed_by'     => auth()->id(),
            'processed_at'     => now(),
        ]);

        // Notifier l'admin de l'entreprise
        $admin = $renewal->tenant->users()->where('role', 'admin')->first();
        if ($admin) {
            ErpNotification::create([
                'user_id'   => $admin->id,
                'tenant_id' => $renewal->tenant_id,
                'type'      => 'renewal_rejected',
                'title'     => '❌ Demande de renouvellement refusée',
                'body'      => 'Motif : ' . $request->rejection_reason . '. Contactez le support pour plus d\'informations.',
                'link'      => '/subscription/renew',
                'icon'      => 'alert',
            ]);
        }

        return back()->with('success', 'Demande refusée et entreprise notifiée.');
    }
}
