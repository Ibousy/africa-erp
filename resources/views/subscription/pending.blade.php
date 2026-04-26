@extends('layouts.public')
@section('title', 'Demande envoyée — AfricaERP')

@section('content')
<div class="min-h-screen bg-gray-50 flex items-center justify-center p-6">
    <div class="w-full max-w-md text-center">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-10">

            {{-- Icône succès --}}
            <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-10 h-10 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>

            <h1 class="text-2xl font-bold text-gray-900 mb-2">Demande envoyée !</h1>
            <p class="text-gray-500 text-sm mb-6">
                Votre demande de renouvellement a été transmise à notre équipe. Nous la traitons sous <strong>24h</strong>.
            </p>

            {{-- Récapitulatif --}}
            @php
                $r = $renewal ?? $pending ?? null;
            @endphp
            @if($r)
            <div class="bg-gray-50 rounded-xl p-5 text-left space-y-2 mb-6">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Entreprise</span>
                    <span class="font-semibold text-gray-800">{{ $tenant->company_name }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Plan demandé</span>
                    <span class="font-semibold text-gray-800">{{ \App\Models\RenewalRequest::PLAN_LABELS[$r->plan] ?? $r->plan }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Durée</span>
                    <span class="font-semibold text-gray-800">{{ $r->duration_months }} mois</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Montant</span>
                    <span class="font-bold text-orange-600">{{ money($r->amount) }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Mode de paiement</span>
                    <span class="font-semibold text-gray-800">{{ \App\Models\RenewalRequest::METHOD_LABELS[$r->payment_method] ?? $r->payment_method }}</span>
                </div>
                @if($r->payment_reference)
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Référence</span>
                    <span class="font-mono text-gray-700">{{ $r->payment_reference }}</span>
                </div>
                @endif
                <div class="flex justify-between text-sm pt-2 border-t border-gray-200">
                    <span class="text-gray-500">Statut</span>
                    <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-amber-100 text-amber-700">En attente de validation</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Soumis le</span>
                    <span class="text-gray-600">{{ $r->created_at->format('d/m/Y à H:i') }}</span>
                </div>
            </div>
            @endif

            {{-- Étapes du traitement --}}
            <div class="text-left space-y-3 mb-8">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Prochaines étapes</p>
                @foreach([
                    ['✅', 'Demande soumise', true],
                    ['⏳', 'Vérification du paiement par notre équipe', false],
                    ['🔓', 'Activation de votre accès', false],
                    ['📧', 'Notification de confirmation', false],
                ] as [$icon, $step, $done])
                <div class="flex items-center gap-3">
                    <span class="text-base">{{ $icon }}</span>
                    <span class="text-sm {{ $done ? 'text-green-700 font-medium' : 'text-gray-500' }}">{{ $step }}</span>
                </div>
                @endforeach
            </div>

            <a href="mailto:contact@africaerp.com" class="btn-secondary w-full py-3 block mb-3 text-sm">
                Contacter le support
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="text-xs text-gray-400 hover:text-gray-600">Se déconnecter</button>
            </form>
        </div>
    </div>
</div>
@endsection
