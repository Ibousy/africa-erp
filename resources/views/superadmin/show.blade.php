@extends('layouts.superadmin')
@section('title', $tenant->company_name . ' — Gestion')

@section('content')
<div class="space-y-5">

    {{-- Header --}}
    <div class="flex items-center gap-4">
        <a href="{{ route('superadmin.index') }}" class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Retour
        </a>
        <div class="w-10 h-10 rounded-xl flex items-center justify-center text-lg font-bold text-white shrink-0" style="background-color:#f97316">
            {{ strtoupper(substr($tenant->company_name, 0, 1)) }}
        </div>
        <div>
            <h2 class="text-xl font-bold text-gray-900">{{ $tenant->company_name }}</h2>
            <p class="text-xs text-gray-500">{{ $tenant->email ?? $tenant->country ?? 'Créé le ' . $tenant->created_at->format('d/m/Y') }}</p>
        </div>
        <span class="ml-2 px-3 py-1 text-xs font-bold rounded-full
            {{ $tenant->status === 'active' ? 'bg-green-100 text-green-700' :
               ($tenant->status === 'trial' ? 'bg-blue-100 text-blue-700' :
               ($tenant->status === 'suspended' ? 'bg-orange-100 text-orange-700' : 'bg-red-100 text-red-700')) }}">
            {{ ['active' => 'Actif', 'trial' => 'Essai', 'expired' => 'Expiré', 'suspended' => 'Suspendu'][$tenant->status] ?? $tenant->status }}
        </span>

        {{-- Boutons rapides suspendre / réactiver --}}
        <div class="ml-auto flex items-center gap-2">
            @if($tenant->status !== 'suspended')
            <form method="POST" action="{{ route('superadmin.suspend', $tenant) }}"
                onsubmit="return confirm('Suspendre « {{ $tenant->company_name }} » ? Tous les modules seront désactivés et l\'accès bloqué.')">
                @csrf
                <button type="submit" class="px-4 py-2 text-sm font-semibold text-orange-700 border border-orange-300 rounded-lg hover:bg-orange-50 transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                    Suspendre
                </button>
            </form>
            @else
            <form method="POST" action="{{ route('superadmin.activate', $tenant) }}">
                @csrf
                <button type="submit" class="px-4 py-2 text-sm font-semibold text-green-700 border border-green-300 rounded-lg hover:bg-green-50 transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Réactiver (tout activer)
                </button>
            </form>
            @endif
        </div>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 rounded-xl px-5 py-3 text-sm text-green-800 flex items-center gap-2">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="bg-red-50 border border-red-200 rounded-xl px-5 py-3 text-sm text-red-800 flex items-center gap-2">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        {{ session('error') }}
    </div>
    @endif
    @if(session('warning'))
    <div class="bg-amber-50 border border-amber-200 rounded-xl px-5 py-3 text-sm text-amber-800">{{ session('warning') }}</div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- COLONNE GAUCHE --}}
        <div class="space-y-5">

            {{-- Infos --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h3 class="font-semibold text-gray-800 text-sm border-b border-gray-100 pb-2 mb-3">Informations</h3>
                <dl class="space-y-2 text-sm">
                    <div class="flex gap-2"><dt class="text-gray-500 w-28 shrink-0">Email</dt><dd class="text-gray-700 break-all">{{ $tenant->email ?? '—' }}</dd></div>
                    <div class="flex gap-2"><dt class="text-gray-500 w-28 shrink-0">Téléphone</dt><dd class="text-gray-700">{{ $tenant->phone ?? '—' }}</dd></div>
                    <div class="flex gap-2"><dt class="text-gray-500 w-28 shrink-0">Secteur</dt><dd class="text-gray-700">{{ $tenant->industry ?? '—' }}</dd></div>
                    <div class="flex gap-2"><dt class="text-gray-500 w-28 shrink-0">Pays</dt><dd class="text-gray-700">{{ $tenant->country ?? '—' }}</dd></div>
                    <div class="flex gap-2"><dt class="text-gray-500 w-28 shrink-0">Inscription</dt><dd class="text-gray-700">{{ $tenant->created_at->format('d/m/Y') }}</dd></div>
                    <div class="flex gap-2"><dt class="text-gray-500 w-28 shrink-0">Utilisateurs</dt><dd class="font-bold text-gray-800">{{ $userCount }}@if($tenant->max_users) / {{ $tenant->max_users }}@endif</dd></div>
                    <div class="flex gap-2"><dt class="text-gray-500 w-28 shrink-0">Base données</dt>
                        <dd>@if($tenant->db_name)
                            <span class="text-xs text-green-700 bg-green-50 px-2 py-0.5 rounded-full font-medium">{{ $tenant->db_name }}</span>
                            @else<span class="text-xs text-gray-400">Partagée</span>@endif
                        </dd>
                    </div>
                </dl>
            </div>

            {{-- Modules avec toggles --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <div class="flex items-center justify-between border-b border-gray-100 pb-2 mb-4">
                    <h3 class="font-semibold text-gray-800 text-sm">Modules</h3>
                    <div class="flex gap-2">
                        <form method="POST" action="{{ route('superadmin.modules.enable-all', $tenant) }}">
                            @csrf
                            <button type="submit" class="text-xs text-green-600 hover:text-green-800 font-medium">Tout activer</button>
                        </form>
                        <span class="text-gray-300">|</span>
                        <form method="POST" action="{{ route('superadmin.modules.disable-all', $tenant) }}"
                            onsubmit="return confirm('Désactiver tous les modules de ce compte ?')">
                            @csrf
                            <button type="submit" class="text-xs text-red-500 hover:text-red-700 font-medium">Tout désactiver</button>
                        </form>
                    </div>
                </div>

                @php $moduleMap = $tenant->modules->keyBy('module'); @endphp
                <div class="space-y-2">
                    @foreach($allModules as $mod)
                    @php $m = $moduleMap[$mod] ?? null; $enabled = $m?->enabled ?? false; @endphp
                    <div class="flex items-center justify-between py-1.5 px-3 rounded-lg {{ $enabled ? 'bg-green-50' : 'bg-gray-50' }} transition-colors">
                        <span class="text-sm {{ $enabled ? 'text-gray-800 font-medium' : 'text-gray-400' }}">
                            {{ $moduleLabels[$mod] ?? $mod }}
                        </span>
                        <form method="POST" action="{{ route('superadmin.modules.toggle', [$tenant, $mod]) }}">
                            @csrf
                            <button type="submit" class="relative inline-flex h-5 w-9 items-center rounded-full transition-colors focus:outline-none
                                {{ $enabled ? 'bg-green-500' : 'bg-gray-300' }}">
                                <span class="inline-block h-3.5 w-3.5 transform rounded-full bg-white shadow transition-transform
                                    {{ $enabled ? 'translate-x-4' : 'translate-x-1' }}"></span>
                            </button>
                        </form>
                    </div>
                    @endforeach
                </div>

                @if($tenant->status === 'suspended')
                <div class="mt-3 p-3 bg-orange-50 rounded-lg">
                    <p class="text-xs text-orange-700 font-medium">⚠ Compte suspendu — les modules sont bloqués même si activés ici.</p>
                    <p class="text-xs text-orange-600 mt-0.5">Utilisez "Réactiver" pour restaurer l'accès complet.</p>
                </div>
                @endif
            </div>

            {{-- Base de données isolée --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h3 class="font-semibold text-gray-800 text-sm border-b border-gray-100 pb-2 mb-3">Base de données isolée</h3>
                @if($tenant->db_name)
                <div class="flex items-center gap-2 p-3 bg-green-50 rounded-lg">
                    <svg class="w-5 h-5 text-green-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                    <div>
                        <p class="text-sm font-semibold text-green-800">Base isolée active</p>
                        <p class="text-xs text-green-600">{{ $tenant->db_name }}</p>
                    </div>
                </div>
                @else
                <p class="text-xs text-gray-500 mb-3">Base partagée. Provisionnez une base dédiée pour une isolation complète.</p>
                <form method="POST" action="{{ route('superadmin.provision', $tenant) }}"
                    onsubmit="return confirm('Créer une base isolée pour {{ $tenant->company_name }} ?')">
                    @csrf
                    <button type="submit" class="w-full py-2.5 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition-colors">
                        Provisionner la base de données
                    </button>
                </form>
                @endif
            </div>

        </div>

        {{-- COLONNE DROITE --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- Statut paiement rapide --}}
            <div class="grid grid-cols-3 gap-4">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 text-center">
                    <p class="text-xs text-gray-500 uppercase font-semibold">Plan</p>
                    <p class="text-lg font-bold text-gray-800 mt-1">{{ strtoupper($tenant->plan ?? 'trial') }}</p>
                    <p class="text-xs text-gray-400">{{ $tenant->monthly_price ? number_format($tenant->monthly_price, 0) . '€/mois' : '—' }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 text-center">
                    <p class="text-xs text-gray-500 uppercase font-semibold">Fin abonnement</p>
                    @php $ends = $tenant->subscription_ends_at ?? $tenant->trial_ends_at; @endphp
                    <p class="text-lg font-bold {{ $ends && $ends->isPast() ? 'text-red-600' : ($ends && $ends->diffInDays() < 15 ? 'text-amber-600' : 'text-gray-800') }} mt-1">
                        {{ $ends ? $ends->format('d/m/Y') : '—' }}
                    </p>
                    <p class="text-xs text-gray-400">{{ $ends && $ends->isFuture() ? $ends->diffInDays() . ' jours restants' : ($ends && $ends->isPast() ? 'Expiré' : '') }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 text-center">
                    <p class="text-xs text-gray-500 uppercase font-semibold">Modules actifs</p>
                    <p class="text-lg font-bold text-gray-800 mt-1">
                        {{ $tenant->modules->where('enabled', true)->count() }} / {{ count($allModules) }}
                    </p>
                    <p class="text-xs text-gray-400">modules activés</p>
                </div>
            </div>

            {{-- Renouveler abonnement (paiement reçu) --}}
            <div class="bg-white rounded-xl shadow-sm  border-green-100 border-2 p-5">
                <div class="flex items-center gap-2 mb-4">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <h3 class="font-semibold text-gray-800 text-sm">Renouveler l'abonnement (paiement reçu)</h3>
                </div>
                <p class="text-xs text-gray-500 mb-4">Enregistre le paiement, prolonge l'accès et <strong>réactive automatiquement tous les modules</strong>.</p>
                <form method="POST" action="{{ route('superadmin.extend', $tenant) }}" class="flex flex-wrap gap-3 items-end">
                    @csrf
                    <div class="flex-1 min-w-40">
                        <label class="form-label text-xs">Nouvelle date de fin <span class="text-red-500">*</span></label>
                        <input type="date" name="subscription_ends_at" required class="form-input"
                            value="{{ $tenant->subscription_ends_at ? $tenant->subscription_ends_at->addMonth()->format('Y-m-d') : now()->addMonths(1)->format('Y-m-d') }}">
                    </div>
                    <div class="w-32">
                        <label class="form-label text-xs">Montant (€)</label>
                        <input type="number" name="monthly_price" value="{{ $tenant->monthly_price ?? '' }}" step="0.01" min="0" class="form-input">
                    </div>
                    <div class="w-36">
                        <label class="form-label text-xs">Plan</label>
                        <select name="plan" class="form-input text-sm">
                            <option value="">Inchangé</option>
                            <option value="starter" {{ $tenant->plan === 'starter' ? 'selected' : '' }}>Starter</option>
                            <option value="pro"     {{ $tenant->plan === 'pro'     ? 'selected' : '' }}>Pro</option>
                            <option value="enterprise" {{ $tenant->plan === 'enterprise' ? 'selected' : '' }}>Enterprise</option>
                        </select>
                    </div>
                    <button type="submit" class="py-2 px-5 text-sm font-semibold text-white bg-green-600 hover:bg-green-700 rounded-lg transition-colors flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        Renouveler & Activer
                    </button>
                </form>
            </div>

            {{-- Modifier abonnement --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h3 class="font-semibold text-gray-800 text-sm border-b border-gray-100 pb-2 mb-4">Modifier l'abonnement</h3>
                <form method="POST" action="{{ route('superadmin.update', $tenant) }}" class="space-y-4">
                    @csrf @method('PATCH')
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="form-label text-xs">Statut</label>
                            <select name="status" required class="form-input">
                                <option value="trial"     {{ $tenant->status === 'trial'     ? 'selected' : '' }}>Essai (Trial)</option>
                                <option value="active"    {{ $tenant->status === 'active'    ? 'selected' : '' }}>Actif (Payé)</option>
                                <option value="expired"   {{ $tenant->status === 'expired'   ? 'selected' : '' }}>Expiré</option>
                                <option value="suspended" {{ $tenant->status === 'suspended' ? 'selected' : '' }}>Suspendu</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label text-xs">Plan tarifaire</label>
                            <select name="plan" class="form-input" onchange="updatePrice(this.value)">
                                <option value="starter"    {{ $tenant->plan === 'starter'    ? 'selected' : '' }}>Starter — 29€/mois</option>
                                <option value="pro"        {{ $tenant->plan === 'pro'        ? 'selected' : '' }}>Pro — 79€/mois</option>
                                <option value="enterprise" {{ $tenant->plan === 'enterprise' ? 'selected' : '' }}>Enterprise — 199€/mois</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label text-xs">Prix mensuel (€)</label>
                            <input type="number" name="monthly_price" id="priceInput" value="{{ $tenant->monthly_price ?? '' }}" step="0.01" min="0" class="form-input">
                        </div>
                        <div>
                            <label class="form-label text-xs">Utilisateurs max</label>
                            <input type="number" name="max_users" value="{{ $tenant->max_users ?? '' }}" min="1" class="form-input">
                        </div>
                        <div>
                            <label class="form-label text-xs">Fin période d'essai</label>
                            <input type="date" name="trial_ends_at" value="{{ $tenant->trial_ends_at?->format('Y-m-d') }}" class="form-input">
                        </div>
                        <div>
                            <label class="form-label text-xs">Fin abonnement</label>
                            <input type="date" name="subscription_ends_at" value="{{ $tenant->subscription_ends_at?->format('Y-m-d') }}" class="form-input">
                        </div>
                    </div>
                    <button type="submit" class="btn-primary w-full">Enregistrer</button>
                </form>
            </div>

            {{-- Actions --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h3 class="font-semibold text-gray-800 text-sm border-b border-gray-100 pb-2 mb-4">Actions</h3>
                <div class="flex flex-wrap gap-3">
                    <form method="POST" action="{{ route('superadmin.impersonate', $tenant) }}">
                        @csrf
                        <button type="submit" class="btn-secondary flex items-center gap-2 text-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            Accéder au compte
                        </button>
                    </form>
                    <form method="POST" action="{{ route('superadmin.destroy', $tenant) }}"
                        onsubmit="return confirm('ATTENTION : Supprimer définitivement « {{ $tenant->company_name }} » et TOUS ses utilisateurs et données ? Irréversible.')">
                        @csrf @method('DELETE')
                        <button type="submit" class="py-2 px-4 text-sm font-medium text-red-600 border border-red-200 rounded-lg hover:bg-red-50 transition-colors">
                            Supprimer ce compte
                        </button>
                    </form>
                </div>
            </div>

            {{-- Utilisateurs --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-800 text-sm">Utilisateurs ({{ $userCount }})</h3>
                </div>
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50">
                            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Nom</th>
                            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Email</th>
                            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Rôle</th>
                            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Département</th>
                            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Statut</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($tenant->users as $u)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-3 font-medium text-gray-800">{{ $u->name }}</td>
                            <td class="px-5 py-3 text-gray-600 text-xs">{{ $u->email }}</td>
                            <td class="px-5 py-3">
                                <span class="px-2 py-0.5 text-xs rounded-full {{ $u->role === 'admin' ? 'bg-purple-100 text-purple-700' : 'bg-gray-100 text-gray-600' }}">
                                    {{ ucfirst($u->role) }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-gray-500 text-xs">{{ $u->department ? (\App\Models\User::DEPARTMENTS[$u->department] ?? $u->department) : '—' }}</td>
                            <td class="px-5 py-3">
                                <span class="px-2 py-0.5 text-xs rounded-full {{ $u->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' }}">
                                    {{ $u->is_active ? 'Actif' : 'Inactif' }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="px-5 py-8 text-center text-gray-400">Aucun utilisateur</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
const planPrices = @json($planPrices);
function updatePrice(plan) {
    const inp = document.getElementById('priceInput');
    if (planPrices[plan]) inp.value = planPrices[plan];
}
</script>
@endsection
