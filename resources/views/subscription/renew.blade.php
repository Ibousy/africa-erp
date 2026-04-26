@extends('layouts.public')
@section('title', 'Renouveler votre abonnement — AfricaERP')

@section('content')
<div class="min-h-screen bg-gray-50 flex items-center justify-center p-6">
    <div class="w-full max-w-2xl">

        {{-- En-tête --}}
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Renouveler votre abonnement</h1>
            <p class="text-gray-500 mt-1 text-sm">
                {{ $tenant->company_name }}
                @if($tenant->expiryDate())
                    · Expiration : <span class="{{ $tenant->expiryDate()->isPast() ? 'text-red-500 font-semibold' : 'text-gray-600' }}">{{ $tenant->expiryDate()->format('d/m/Y') }}</span>
                @endif
            </p>
        </div>

        @if(session('error'))
        <div class="mb-4 bg-red-50 border border-red-200 rounded-xl px-5 py-3 text-sm text-red-700">{{ session('error') }}</div>
        @endif

        @if($lastRequest && $lastRequest->isRejected())
        <div class="mb-4 bg-amber-50 border border-amber-200 rounded-xl px-5 py-3 text-sm text-amber-800">
            <p class="font-semibold">Dernière demande refusée</p>
            <p class="mt-0.5">Motif : {{ $lastRequest->rejection_reason ?? 'Non précisé' }}</p>
        </div>
        @endif

        <form method="POST" action="{{ route('subscription.renew.store') }}" class="space-y-6">
            @csrf

            {{-- Choix du plan --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h2 class="font-semibold text-gray-800 mb-4">1. Choisir votre plan</h2>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    @php
                        $plans = [
                            'starter'    => ['label' => 'Starter',     'price' => 15000,  'desc' => '3 modules · 5 utilisateurs'],
                            'pro'        => ['label' => 'Pro',          'price' => 35000,  'desc' => 'Tous modules · 20 utilisateurs'],
                            'enterprise' => ['label' => 'Enterprise',   'price' => 75000,  'desc' => 'Illimité · support dédié'],
                        ];
                        $currentPlan = old('plan', $tenant->plan ?? 'pro');
                    @endphp
                    @foreach($plans as $key => $plan)
                    <label class="relative cursor-pointer">
                        <input type="radio" name="plan" value="{{ $key }}" class="sr-only peer" {{ $currentPlan === $key ? 'checked' : '' }}>
                        <div class="border-2 rounded-xl p-4 text-center transition-all peer-checked:border-orange-500 peer-checked:bg-orange-50 border-gray-200 hover:border-gray-300">
                            <p class="font-bold text-gray-800">{{ $plan['label'] }}</p>
                            <p class="text-lg font-extrabold text-orange-600 mt-1">{{ money($plan['price'], false) }}<span class="text-xs font-normal text-gray-400"> FCFA/mois</span></p>
                            <p class="text-xs text-gray-400 mt-1">{{ $plan['desc'] }}</p>
                        </div>
                    </label>
                    @endforeach
                </div>
                @error('plan')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>

            {{-- Durée --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h2 class="font-semibold text-gray-800 mb-4">2. Durée de l'abonnement</h2>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    @php
                        $durations = [1 => '1 mois', 3 => '3 mois', 6 => '6 mois', 12 => '12 mois'];
                        $discounts = [1 => null, 3 => '-5%', 6 => '-10%', 12 => '-15%'];
                    @endphp
                    @foreach($durations as $months => $label)
                    <label class="relative cursor-pointer">
                        <input type="radio" name="duration_months" value="{{ $months }}" class="sr-only peer" {{ old('duration_months', 1) == $months ? 'checked' : '' }}>
                        <div class="border-2 rounded-xl p-3 text-center transition-all peer-checked:border-orange-500 peer-checked:bg-orange-50 border-gray-200 hover:border-gray-300">
                            <p class="font-semibold text-gray-800">{{ $label }}</p>
                            @if($discounts[$months])
                            <p class="text-xs text-green-600 font-semibold mt-0.5">{{ $discounts[$months] }}</p>
                            @endif
                        </div>
                    </label>
                    @endforeach
                </div>
                @error('duration_months')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>

            {{-- Paiement --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
                <h2 class="font-semibold text-gray-800">3. Informations de paiement</h2>
                <div>
                    <label class="form-label text-xs">Mode de paiement *</label>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 mt-1">
                        @foreach(['especes' => 'Espèces', 'virement' => 'Virement', 'mobile' => 'Mobile Money', 'cheque' => 'Chèque'] as $v => $l)
                        <label class="cursor-pointer">
                            <input type="radio" name="payment_method" value="{{ $v }}" class="sr-only peer" {{ old('payment_method') === $v ? 'checked' : '' }}>
                            <div class="border rounded-lg px-3 py-2 text-xs text-center transition-all peer-checked:border-orange-500 peer-checked:bg-orange-50 border-gray-200 hover:border-gray-300 font-medium text-gray-700">
                                {{ $l }}
                            </div>
                        </label>
                        @endforeach
                    </div>
                    @error('payment_method')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label text-xs">Référence de paiement <span class="text-gray-400">(N° reçu, transaction...)</span></label>
                    <input type="text" name="payment_reference" value="{{ old('payment_reference') }}"
                        placeholder="Ex: TXN-20240426-001"
                        class="form-input text-sm @error('payment_reference') border-red-300 @enderror">
                    @error('payment_reference')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label text-xs">Notes additionnelles</label>
                    <textarea name="notes" rows="2" class="form-input text-sm" placeholder="Informations complémentaires...">{{ old('notes') }}</textarea>
                </div>
            </div>

            {{-- Récapitulatif dynamique + bouton --}}
            <div class="bg-orange-50 border border-orange-200 rounded-2xl p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Montant estimé</p>
                        <p id="totalDisplay" class="text-2xl font-extrabold text-orange-600">—</p>
                        <p class="text-xs text-gray-400 mt-0.5">Le montant final sera confirmé par notre équipe</p>
                    </div>
                    <button type="submit" class="btn-primary px-8 py-3 text-base">
                        Envoyer la demande →
                    </button>
                </div>
            </div>
        </form>

        <div class="text-center mt-4">
            <form method="POST" action="{{ route('logout') }}" class="inline">
                @csrf
                <button class="text-xs text-gray-400 hover:text-gray-600">Se déconnecter</button>
            </form>
        </div>
    </div>
</div>

<script>
const prices = { starter: 15000, pro: 35000, enterprise: 75000 };
const discounts = { 1: 0, 3: 0.05, 6: 0.10, 12: 0.15 };

function updateTotal() {
    const plan     = document.querySelector('input[name="plan"]:checked')?.value;
    const duration = parseInt(document.querySelector('input[name="duration_months"]:checked')?.value || 1);
    if (!plan) { document.getElementById('totalDisplay').textContent = '—'; return; }
    const base     = prices[plan] * duration;
    const discount = discounts[duration] || 0;
    const total    = Math.round(base * (1 - discount));
    document.getElementById('totalDisplay').textContent = total.toLocaleString('fr-FR') + ' FCFA';
}
document.querySelectorAll('input[name="plan"], input[name="duration_months"]')
    .forEach(el => el.addEventListener('change', updateTotal));
updateTotal();
</script>
@endsection
