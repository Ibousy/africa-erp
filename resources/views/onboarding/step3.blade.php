@extends('layouts.public')
@section('title', 'Choisissez vos modules — AfricaERP')

@section('content')
<div class="min-h-screen bg-gray-50 flex items-center justify-center p-6">
    <div class="w-full max-w-2xl">
        <!-- Logo -->
        <div class="flex items-center gap-3 justify-center mb-8">
            <div class="w-9 h-9 bg-orange-500 rounded-xl flex items-center justify-center font-bold text-white">A</div>
            <span class="font-bold text-gray-900 text-lg">{{ $tenant->company_name }}</span>
        </div>

        <!-- Progress -->
        <div class="mb-8">
            <div class="flex items-center mb-3">
                @foreach(['Compte', 'Entreprise', 'Modules', 'Thème'] as $i => $label)
                <div class="flex items-center {{ $i < 3 ? 'flex-1' : '' }}">
                    <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold {{ $i === 2 ? 'bg-orange-500 text-white' : ($i < 2 ? 'bg-green-500 text-white' : 'bg-gray-200 text-gray-400') }}">
                        {{ $i < 2 ? '✓' : $i + 1 }}
                    </div>
                    @if($i < 3)<div class="flex-1 h-px mx-2 {{ $i < 2 ? 'bg-green-400' : 'bg-gray-200' }}"></div>@endif
                </div>
                @endforeach
            </div>
            <div class="flex justify-between text-xs text-gray-400">
                <span class="text-green-600 font-semibold">Compte</span>
                <span class="text-green-600 font-semibold">Entreprise</span>
                <span class="text-orange-600 font-semibold">Modules</span>
                <span>Thème</span>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
            <h1 class="text-2xl font-bold text-gray-900 mb-1">Choisissez vos modules</h1>
            <p class="text-gray-500 text-sm mb-7">Sélectionnez les fonctionnalités dont vous avez besoin. Vous pourrez en ajouter ou en retirer plus tard.</p>

            <form method="POST" action="{{ route('onboarding.step3') }}">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-8">
                    @foreach([
                        ['key' => 'stock',       'emoji' => '📦', 'name' => 'Stock & Inventaire',        'desc' => 'Produits, entrées/sorties, alertes stock bas'],
                        ['key' => 'production',  'emoji' => '🏭', 'name' => 'Production & Fabrication',  'desc' => 'Ordres de fab, tâches, rendements'],
                        ['key' => 'mrp',         'emoji' => '📋', 'name' => 'MRP — Planification',       'desc' => 'Besoins en matières, calcul manques, lancements'],
                        ['key' => 'quality',     'emoji' => '✅', 'name' => 'Contrôle Qualité',          'desc' => 'Contrôles, défauts, rapports qualité'],
                        ['key' => 'maintenance', 'emoji' => '🔧', 'name' => 'Maintenance',               'desc' => 'Préventive, corrective, suivi machines'],
                        ['key' => 'logistics',   'emoji' => '🚛', 'name' => 'Logistique & Transport',    'desc' => 'Expéditions, réceptions, transporteurs'],
                        ['key' => 'purchases',   'emoji' => '🛒', 'name' => 'Achats & Fournisseurs',     'desc' => 'Bons de commande, fournisseurs, réceptions'],
                        ['key' => 'sales',       'emoji' => '💼', 'name' => 'Commercial & Facturation',  'desc' => 'Clients, devis, factures FCFA'],
                        ['key' => 'crm',         'emoji' => '🎯', 'name' => 'CRM & Prospects',           'desc' => 'Pipeline commercial, suivi opportunités'],
                        ['key' => 'accounting',  'emoji' => '💰', 'name' => 'Comptabilité & Finance',    'desc' => 'Recettes, dépenses, solde mensuel/annuel'],
                        ['key' => 'hr',          'emoji' => '👤', 'name' => 'Ressources Humaines',       'desc' => 'Employés, postes, salaires, congés'],
                        ['key' => 'energy',      'emoji' => '⚡', 'name' => 'Énergie',                   'desc' => 'Consommation kWh, coûts par machine'],
                        ['key' => 'users',       'emoji' => '👥', 'name' => 'Gestion Utilisateurs',      'desc' => 'Rôles admin, manager, opérateur'],
                    ] as $mod)
                    <label class="module-card cursor-pointer border-2 border-gray-100 rounded-xl p-4 flex items-start gap-3 hover:border-orange-300 transition-colors has-checked:border-orange-500 has-checked:bg-orange-50">
                        <input type="checkbox" name="modules[]" value="{{ $mod['key'] }}" class="mt-1 accent-orange-500" checked>
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-xl">{{ $mod['emoji'] }}</span>
                                <span class="font-semibold text-gray-900 text-sm">{{ $mod['name'] }}</span>
                            </div>
                            <p class="text-xs text-gray-500">{{ $mod['desc'] }}</p>
                        </div>
                    </label>
                    @endforeach
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ route('onboarding.step2') }}" class="btn-secondary px-6 py-3">← Retour</a>
                    <button type="submit" class="btn-primary flex-1 py-3">Continuer →</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
