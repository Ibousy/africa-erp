@extends('layouts.superadmin')
@section('title', 'Nouvelle entreprise')

@section('content')
<div class="max-w-3xl mx-auto space-y-5">

    {{-- Header --}}
    <div class="flex items-center gap-4">
        <a href="{{ route('superadmin.index') }}" class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Retour
        </a>
        <h1 class="text-xl font-bold text-gray-900">Nouvelle entreprise cliente</h1>
    </div>

    <form method="POST" action="{{ route('superadmin.store') }}" class="space-y-5">
        @csrf

        {{-- Infos entreprise --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-4">
            <h2 class="font-semibold text-gray-800 text-sm border-b border-gray-100 pb-2">Informations de l'entreprise</h2>
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="form-label">Nom de l'entreprise <span class="text-red-500">*</span></label>
                    <input type="text" name="company_name" value="{{ old('company_name') }}" required class="form-input" placeholder="Ex : Dakar Distribution SARL">
                </div>
                <div>
                    <label class="form-label">Email de l'entreprise</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="form-input" placeholder="contact@entreprise.com">
                </div>
                <div>
                    <label class="form-label">Téléphone</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" class="form-input" placeholder="+221 77 000 00 00">
                </div>
                <div class="col-span-2">
                    <label class="form-label">Pays</label>
                    <select name="country" class="form-input">
                        <option value="">Sélectionner un pays</option>
                        @foreach(['Sénégal','Côte d\'Ivoire','Mali','Burkina Faso','Cameroun','Guinée','Togo','Bénin','Niger','Tchad','Madagascar','Maroc','Tunisie','Algérie','RDC','Congo','Gabon','Mauritanie','Autre'] as $c)
                        <option value="{{ $c }}" {{ old('country') === $c ? 'selected' : '' }}>{{ $c }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- Plan & abonnement --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-4">
            <h2 class="font-semibold text-gray-800 text-sm border-b border-gray-100 pb-2">Plan & abonnement</h2>

            {{-- Plan cards --}}
            <div class="grid grid-cols-3 gap-3" id="planCards">
                @foreach(['starter' => ['label' => 'Starter', 'price' => 29, 'users' => 5, 'color' => 'blue', 'desc' => 'Idéal pour les PME'], 'pro' => ['label' => 'Pro', 'price' => 79, 'users' => 20, 'color' => 'purple', 'desc' => 'Pour les entreprises en croissance'], 'enterprise' => ['label' => 'Enterprise', 'price' => 199, 'users' => '∞', 'color' => 'orange', 'desc' => 'Pour les grandes structures']] as $key => $p)
                <label class="cursor-pointer">
                    <input type="radio" name="plan" value="{{ $key }}" {{ old('plan', 'pro') === $key ? 'checked' : '' }} class="sr-only peer" onchange="selectPlan('{{ $key }}', {{ $p['price'] }})">
                    <div class="border-2 rounded-xl p-4 peer-checked:border-{{ $p['color'] }}-500 peer-checked:bg-{{ $p['color'] }}-50 border-gray-200 hover:border-gray-300 transition-all">
                        <p class="font-bold text-gray-800 text-sm">{{ $p['label'] }}</p>
                        <p class="text-xl font-bold text-{{ $p['color'] }}-600 mt-1">{{ $p['price'] }}€<span class="text-xs font-normal text-gray-400">/mois</span></p>
                        <p class="text-xs text-gray-500 mt-1">{{ $p['users'] }} utilisateurs</p>
                        <p class="text-xs text-gray-400 mt-0.5">{{ $p['desc'] }}</p>
                    </div>
                </label>
                @endforeach
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Statut initial <span class="text-red-500">*</span></label>
                    <select name="status" required class="form-input" id="statusSelect" onchange="toggleDates(this.value)">
                        <option value="trial" {{ old('status', 'trial') === 'trial' ? 'selected' : '' }}>Période d'essai (14 jours)</option>
                        <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Actif (payé)</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Prix mensuel (€)</label>
                    <input type="number" name="monthly_price" id="monthlyPrice" value="{{ old('monthly_price', 79) }}" step="0.01" min="0" class="form-input">
                </div>
                <div id="trialDateField">
                    <label class="form-label">Fin de la période d'essai</label>
                    <input type="date" name="trial_ends_at" value="{{ old('trial_ends_at', now()->addDays(14)->format('Y-m-d')) }}" class="form-input">
                </div>
                <div id="subDateField" class="hidden">
                    <label class="form-label">Fin d'abonnement</label>
                    <input type="date" name="subscription_ends_at" value="{{ old('subscription_ends_at', now()->addYear()->format('Y-m-d')) }}" class="form-input">
                </div>
            </div>
        </div>

        {{-- Compte administrateur --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-4">
            <h2 class="font-semibold text-gray-800 text-sm border-b border-gray-100 pb-2">Compte administrateur</h2>
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="form-label">Nom complet <span class="text-red-500">*</span></label>
                    <input type="text" name="admin_name" value="{{ old('admin_name') }}" required class="form-input" placeholder="Ex : Moussa Diallo">
                </div>
                <div>
                    <label class="form-label">Email de connexion <span class="text-red-500">*</span></label>
                    <input type="email" name="admin_email" value="{{ old('admin_email') }}" required class="form-input" placeholder="admin@entreprise.com">
                </div>
                <div>
                    <label class="form-label">Mot de passe provisoire <span class="text-red-500">*</span></label>
                    <input type="text" name="admin_password" value="{{ old('admin_password') }}" required minlength="8" class="form-input font-mono" placeholder="Min. 8 caractères">
                </div>
            </div>
        </div>

        {{-- Options --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-3">
            <h2 class="font-semibold text-gray-800 text-sm border-b border-gray-100 pb-2">Options</h2>
            <label class="flex items-start gap-3 cursor-pointer">
                <input type="checkbox" name="provision_db" value="1" {{ old('provision_db') ? 'checked' : '' }} class="mt-0.5 w-4 h-4 text-indigo-600 rounded">
                <div>
                    <p class="text-sm font-medium text-gray-800">Provisionner une base de données isolée</p>
                    <p class="text-xs text-gray-500">Crée une base de données dédiée <code class="bg-gray-100 px-1 rounded text-xs">erp_t{id}</code> et y exécute toutes les migrations. Recommandé pour les entreprises Enterprise.</p>
                </div>
            </label>
        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-end gap-3 pb-6">
            <a href="{{ route('superadmin.index') }}" class="btn-secondary">Annuler</a>
            <button type="submit" class="btn-primary flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Créer l'entreprise
            </button>
        </div>
    </form>
</div>

<script>
const planPrices = @json($planPrices);

function selectPlan(plan, price) {
    document.getElementById('monthlyPrice').value = price;
}

function toggleDates(status) {
    const trial = document.getElementById('trialDateField');
    const sub   = document.getElementById('subDateField');
    if (status === 'active') {
        trial.classList.add('hidden');
        sub.classList.remove('hidden');
    } else {
        trial.classList.remove('hidden');
        sub.classList.add('hidden');
    }
}

// Init
toggleDates(document.getElementById('statusSelect').value);
</script>
@endsection
