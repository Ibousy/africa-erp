@extends('layouts.app')
@section('title', 'Mon Entreprise')
@section('page-title', 'Mon Entreprise')

@section('content')
<div class="mt-2 max-w-3xl space-y-6">

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg flex items-center gap-2 text-sm">
        <span class="text-green-500">✓</span> {{ session('success') }}
    </div>
    @endif

    <!-- Statut abonnement -->
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
        <h3 class="font-semibold text-gray-800 mb-4">Abonnement</h3>
        <div class="flex items-center justify-between">
            <div>
                @if($tenant->status === 'trial')
                <span class="px-3 py-1 bg-orange-100 text-orange-700 text-sm font-semibold rounded-full">Essai gratuit</span>
                <p class="text-sm text-gray-500 mt-2">{{ $tenant->trialDaysLeft() }} jour(s) restant(s) · Expire le {{ $tenant->trial_ends_at?->format('d/m/Y') }}</p>
                @elseif($tenant->status === 'active')
                <span class="px-3 py-1 bg-green-100 text-green-700 text-sm font-semibold rounded-full">Actif — Plan {{ ucfirst($tenant->plan) }}</span>
                @else
                <span class="px-3 py-1 bg-red-100 text-red-700 text-sm font-semibold rounded-full">{{ ucfirst($tenant->status) }}</span>
                @endif
            </div>
            <a href="#" class="btn-secondary text-xs">Gérer l'abonnement</a>
        </div>
    </div>

    <form method="POST" action="{{ route('settings.company') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf @method('PUT')

        <!-- Logo & Thème -->
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
            <h3 class="font-semibold text-gray-800 mb-5">Identité visuelle</h3>

            <div class="flex items-start gap-6 mb-6">
                <div>
                    @if($tenant->logoUrl())
                    <img src="{{ $tenant->logoUrl() }}" alt="Logo" id="logo-preview" class="w-20 h-20 rounded-xl object-cover border border-gray-200">
                    @else
                    <div id="logo-preview" class="w-20 h-20 rounded-xl flex items-center justify-center text-white font-bold text-3xl" style="background-color: var(--clr-p)">
                        {{ strtoupper(substr($tenant->company_name, 0, 1)) }}
                    </div>
                    @endif
                </div>
                <div>
                    <label class="form-label">Logo de l'entreprise</label>
                    <label for="logo-input" class="cursor-pointer btn-secondary text-xs">Changer le logo</label>
                    <input id="logo-input" type="file" name="logo" accept="image/*" class="hidden" onchange="previewLogo(this)">
                    <p class="text-xs text-gray-400 mt-1">PNG, JPG — max 2 Mo</p>
                </div>
            </div>

            <div>
                <label class="form-label">Thème couleur</label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-2">
                    @foreach([
                        ['key' => 'orange', 'hex' => '#f97316', 'name' => 'Corail'],
                        ['key' => 'blue',   'hex' => '#3b82f6', 'name' => 'Océan'],
                        ['key' => 'green',  'hex' => '#10b981', 'name' => 'Forêt'],
                        ['key' => 'purple', 'hex' => '#8b5cf6', 'name' => 'Violet'],
                        ['key' => 'red',    'hex' => '#ef4444', 'name' => 'Grenat'],
                    ] as $t)
                    <label class="flex items-center gap-3 p-3 border-2 rounded-xl cursor-pointer transition-all {{ $tenant->theme === $t['key'] ? 'border-gray-800 bg-gray-50' : 'border-gray-100 hover:border-gray-300' }}">
                        <input type="radio" name="theme" value="{{ $t['key'] }}" {{ $tenant->theme === $t['key'] ? 'checked' : '' }} class="sr-only">
                        <div class="w-7 h-7 rounded-full shrink-0" style="background-color: {{ $t['hex'] }}"></div>
                        <span class="text-sm font-medium text-gray-700">{{ $t['name'] }}</span>
                        @if($tenant->theme === $t['key'])
                        <span class="ml-auto text-xs text-gray-500">Actuel</span>
                        @endif
                    </label>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Infos entreprise -->
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
            <h3 class="font-semibold text-gray-800 mb-5">Informations de l'entreprise</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="form-label">Nom de l'entreprise *</label>
                    <input type="text" name="company_name" value="{{ old('company_name', $tenant->company_name) }}" required class="form-input">
                </div>
                <div>
                    <label class="form-label">Secteur d'activité</label>
                    <input type="text" name="industry" value="{{ old('industry', $tenant->industry) }}" placeholder="Parfumerie, Agroalimentaire..." class="form-input">
                </div>
                <div>
                    <label class="form-label">Pays</label>
                    <input type="text" name="country" value="{{ old('country', $tenant->country) }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">Ville</label>
                    <input type="text" name="city" value="{{ old('city', $tenant->city) }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">Téléphone</label>
                    <input type="text" name="phone" value="{{ old('phone', $tenant->phone) }}" class="form-input">
                </div>
                <div class="md:col-span-2">
                    <label class="form-label">Adresse</label>
                    <input type="text" name="address" value="{{ old('address', $tenant->address) }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">Email</label>
                    <input type="email" name="email" value="{{ old('email', $tenant->email) }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">Site web</label>
                    <input type="text" name="website" value="{{ old('website', $tenant->website) }}" class="form-input">
                </div>
            </div>
        </div>

        <!-- Modules -->
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
            <h3 class="font-semibold text-gray-800 mb-5">Modules actifs</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                @foreach([
                    ['key' => 'stock',       'emoji' => '📦', 'name' => 'Stock & Inventaire'],
                    ['key' => 'production',  'emoji' => '🏭', 'name' => 'Production & Fabrication'],
                    ['key' => 'mrp',         'emoji' => '📋', 'name' => 'MRP — Planification'],
                    ['key' => 'quality',     'emoji' => '✅', 'name' => 'Contrôle Qualité'],
                    ['key' => 'maintenance', 'emoji' => '🔧', 'name' => 'Maintenance'],
                    ['key' => 'logistics',   'emoji' => '🚛', 'name' => 'Logistique & Transport'],
                    ['key' => 'purchases',   'emoji' => '🛒', 'name' => 'Achats & Fournisseurs'],
                    ['key' => 'sales',       'emoji' => '💼', 'name' => 'Commercial & Facturation'],
                    ['key' => 'crm',         'emoji' => '🎯', 'name' => 'CRM & Prospects'],
                    ['key' => 'accounting',  'emoji' => '💰', 'name' => 'Comptabilité & Finance'],
                    ['key' => 'hr',          'emoji' => '👤', 'name' => 'Ressources Humaines'],
                    ['key' => 'energy',      'emoji' => '⚡', 'name' => 'Énergie'],
                    ['key' => 'users',       'emoji' => '👥', 'name' => 'Gestion Utilisateurs'],
                ] as $mod)
                <label class="flex items-center gap-3 p-3 border border-gray-100 rounded-xl cursor-pointer hover:bg-gray-50 transition-colors">
                    <input type="checkbox" name="modules[]" value="{{ $mod['key'] }}"
                        {{ $tenant->hasModule($mod['key']) ? 'checked' : '' }}
                        class="accent-orange-500 w-4 h-4">
                    <span class="text-xl">{{ $mod['emoji'] }}</span>
                    <span class="text-sm font-medium text-gray-700">{{ $mod['name'] }}</span>
                </label>
                @endforeach
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="btn-primary px-8 py-3">Enregistrer les modifications</button>
        </div>
    </form>
</div>

@push('scripts')
<script>
function previewLogo(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            const el = document.getElementById('logo-preview');
            if (el.tagName === 'IMG') {
                el.src = e.target.result;
            } else {
                el.outerHTML = `<img src="${e.target.result}" id="logo-preview" class="w-20 h-20 rounded-xl object-cover border border-gray-200">`;
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Highlight radio on click
document.querySelectorAll('input[name="theme"]').forEach(radio => {
    radio.addEventListener('change', () => {
        document.querySelectorAll('label:has(input[name="theme"])').forEach(l => {
            l.classList.remove('border-gray-800', 'bg-gray-50');
            l.classList.add('border-gray-100');
        });
        radio.closest('label').classList.add('border-gray-800', 'bg-gray-50');
        radio.closest('label').classList.remove('border-gray-100');
    });
});
</script>
@endpush
@endsection
