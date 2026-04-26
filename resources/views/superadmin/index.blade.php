@extends('layouts.superadmin')
@section('title', 'AfricaERP — Administration SaaS')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Tableau de bord SaaS</h1>
            <p class="text-sm text-gray-500 mt-0.5">Gestion des entreprises clientes AfricaERP</p>
        </div>
        <a href="{{ route('superadmin.create') }}" class="btn-primary flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Nouvelle entreprise
        </a>
    </div>

    {{-- Alertes --}}
    @if($stats['pending_renewals'] > 0)
    <div class="bg-blue-50 border border-blue-200 rounded-xl px-5 py-3 flex items-center gap-3">
        <svg class="w-5 h-5 text-blue-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
        <p class="text-sm font-medium text-blue-800">
            <strong>{{ $stats['pending_renewals'] }} demande(s) de renouvellement</strong> en attente de traitement.
            <a href="{{ route('superadmin.renewals') }}" class="underline ml-1 font-semibold">Traiter maintenant →</a>
        </p>
    </div>
    @endif

    @if($stats['expiring_soon'] > 0)
    <div class="bg-amber-50 border border-amber-200 rounded-xl px-5 py-3 flex items-center gap-3">
        <svg class="w-5 h-5 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
        <p class="text-sm font-medium text-amber-800">
            <strong>{{ $stats['expiring_soon'] }} abonnement(s)</strong> expire(nt) dans les 30 prochains jours.
            <a href="{{ route('superadmin.index', ['status' => 'active']) }}" class="underline ml-1">Voir</a>
        </p>
    </div>
    @endif

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 rounded-xl px-5 py-3 text-sm text-green-800">{{ session('success') }}</div>
    @endif
    @if(session('warning'))
    <div class="bg-amber-50 border border-amber-200 rounded-xl px-5 py-3 text-sm text-amber-800">{{ session('warning') }}</div>
    @endif

    {{-- KPI Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 xl:grid-cols-8 gap-4">
        <div class="col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs text-gray-500 uppercase font-semibold tracking-wide">MRR estimé</p>
            <p class="text-2xl font-bold text-emerald-600 mt-1">{{ number_format($stats['revenue'], 0, ',', ' ') }} €<span class="text-sm font-normal text-gray-400">/mois</span></p>
            <p class="text-xs text-gray-400 mt-0.5">{{ $stats['active'] }} entreprise(s) active(s)</p>
        </div>
        <div class="col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs text-gray-500 uppercase font-semibold tracking-wide">Total entreprises</p>
            <p class="text-2xl font-bold text-gray-800 mt-1">{{ $stats['total'] }}</p>
            <p class="text-xs text-gray-400 mt-0.5">{{ $stats['provisioned'] }} BD isolées</p>
        </div>
        <div class="col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs text-gray-500 uppercase font-semibold tracking-wide">Abonnés actifs</p>
            <p class="text-2xl font-bold text-green-600 mt-1">{{ $stats['active'] }}</p>
            <p class="text-xs text-gray-400 mt-0.5">{{ $stats['trial'] }} en période d'essai</p>
        </div>
        <div class="col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs text-gray-500 uppercase font-semibold tracking-wide">Expirés / Suspendus</p>
            <p class="text-2xl font-bold text-red-500 mt-1">{{ $stats['expired'] }}</p>
            <p class="text-xs text-gray-400 mt-0.5">{{ $stats['users'] }} utilisateurs total</p>
        </div>
    </div>

    {{-- Plans distribution --}}
    <div class="grid grid-cols-3 gap-4">
        @php
            $planCounts = \App\Models\Tenant::selectRaw('plan, count(*) as cnt')->groupBy('plan')->pluck('cnt', 'plan');
            $plans = ['starter' => ['label' => 'Starter', 'price' => '29€', 'color' => 'blue'], 'pro' => ['label' => 'Pro', 'price' => '79€', 'color' => 'purple'], 'enterprise' => ['label' => 'Enterprise', 'price' => '199€', 'color' => 'orange']];
        @endphp
        @foreach($plans as $key => $plan)
        <a href="{{ route('superadmin.index', ['plan' => $key]) }}" class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 hover:border-gray-300 transition-colors">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wide text-{{ $plan['color'] }}-600 bg-{{ $plan['color'] }}-50 px-2 py-0.5 rounded-full">{{ $plan['label'] }}</span>
                <span class="text-lg font-bold text-gray-800">{{ $planCounts[$key] ?? 0 }}</span>
            </div>
            <p class="text-xs text-gray-400 mt-2">{{ $plan['price'] }}/mois · {{ $key === 'enterprise' ? 'illimité' : (['starter' => 5, 'pro' => 20][$key]) . ' users max' }}</p>
        </a>
        @endforeach
    </div>

    {{-- Filtres --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-48">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher entreprise ou email..." class="form-input text-sm w-full">
            </div>
            <select name="status" class="form-input w-36 text-sm">
                <option value="">Tous statuts</option>
                <option value="trial"     {{ request('status') === 'trial'     ? 'selected' : '' }}>Essai</option>
                <option value="active"    {{ request('status') === 'active'    ? 'selected' : '' }}>Actif</option>
                <option value="expired"   {{ request('status') === 'expired'   ? 'selected' : '' }}>Expiré</option>
                <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspendu</option>
            </select>
            <select name="plan" class="form-input w-36 text-sm">
                <option value="">Tous plans</option>
                <option value="starter"    {{ request('plan') === 'starter'    ? 'selected' : '' }}>Starter</option>
                <option value="pro"        {{ request('plan') === 'pro'        ? 'selected' : '' }}>Pro</option>
                <option value="enterprise" {{ request('plan') === 'enterprise' ? 'selected' : '' }}>Enterprise</option>
            </select>
            <button type="submit" class="btn-primary text-sm">Filtrer</button>
            <a href="{{ route('superadmin.index') }}" class="btn-secondary text-sm">Reset</a>
        </form>
    </div>

    {{-- Table entreprises --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-semibold text-gray-800 text-sm">Entreprises ({{ $tenants->total() }})</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50">
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Entreprise</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Plan</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Statut</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Échéance</th>
                        <th class="text-center px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Users</th>
                        <th class="text-center px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Base isolée</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Créé le</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($tenants as $t)
                    @php
                        $isExpiringSoon = $t->status === 'active' && $t->subscription_ends_at && $t->subscription_ends_at->between(now(), now()->addDays(30));
                        $echance = $t->status === 'trial' ? $t->trial_ends_at : $t->subscription_ends_at;
                    @endphp
                    <tr class="hover:bg-gray-50 {{ $isExpiringSoon ? 'bg-amber-50/30' : '' }}">
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center text-sm font-bold text-white shrink-0" style="background-color:#f97316">
                                    {{ strtoupper(substr($t->company_name, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-800">{{ $t->company_name }}</p>
                                    <p class="text-xs text-gray-400">{{ $t->email ?? $t->country ?? '—' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3">
                            @php $planKey = $t->plan ?? 'trial'; @endphp
                            <div>
                                <span class="px-2 py-0.5 text-xs rounded-full font-semibold
                                    {{ $planKey === 'enterprise' ? 'bg-orange-100 text-orange-700' : ($planKey === 'pro' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700') }}">
                                    {{ strtoupper($planKey) }}
                                </span>
                                @if($t->monthly_price)
                                <p class="text-xs text-gray-400 mt-0.5">{{ number_format($t->monthly_price, 0) }}€/mois</p>
                                @endif
                            </div>
                        </td>
                        <td class="px-5 py-3">
                            <span class="px-2 py-0.5 text-xs font-semibold rounded-full
                                {{ $t->status === 'active' ? 'bg-green-100 text-green-700' :
                                   ($t->status === 'trial' ? 'bg-blue-100 text-blue-700' :
                                   ($t->status === 'suspended' ? 'bg-orange-100 text-orange-700' : 'bg-red-100 text-red-700')) }}">
                                {{ ['active' => 'Actif', 'trial' => 'Essai', 'expired' => 'Expiré', 'suspended' => 'Suspendu'][$t->status] ?? $t->status }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-xs {{ $isExpiringSoon ? 'text-amber-600 font-semibold' : 'text-gray-500' }}">
                            @if($echance)
                                {{ $echance->format('d/m/Y') }}
                                @if($isExpiringSoon)
                                <span class="block text-amber-500">⚠ expire bientôt</span>
                                @endif
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-5 py-3 text-center text-gray-700">
                            {{ $t->users_count }}@if($t->max_users)<span class="text-gray-400">/{{ $t->max_users }}</span>@endif
                        </td>
                        <td class="px-5 py-3 text-center">
                            @if($t->db_name)
                            <span class="inline-flex items-center gap-1 text-xs text-green-700 bg-green-50 px-2 py-0.5 rounded-full font-medium">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                Isolée
                            </span>
                            @else
                            <span class="text-xs text-gray-400">Partagée</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-gray-500 text-xs">{{ $t->created_at->format('d/m/Y') }}</td>
                        <td class="px-5 py-3">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('superadmin.show', $t) }}" class="text-xs text-blue-500 hover:text-blue-700 font-medium whitespace-nowrap">Gérer</a>
                                <form method="POST" action="{{ route('superadmin.impersonate', $t) }}">
                                    @csrf
                                    <button class="text-xs text-purple-500 hover:text-purple-700 font-medium whitespace-nowrap">Accéder</button>
                                </form>
                                <form method="POST" action="{{ route('superadmin.destroy', $t) }}"
                                    onsubmit="return confirm('Supprimer « {{ $t->company_name }} » et tous ses utilisateurs ? Cette action est irréversible.')">
                                    @csrf @method('DELETE')
                                    <button class="text-xs text-red-400 hover:text-red-600">Suppr.</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="px-5 py-12 text-center text-gray-400">Aucune entreprise trouvée</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-5 py-3 border-t border-gray-100">{{ $tenants->links() }}</div>
    </div>
</div>
@endsection
