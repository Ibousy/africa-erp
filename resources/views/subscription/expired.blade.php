@extends('layouts.public')
@section('title', 'Abonnement expiré — AfricaERP')

@section('content')
<div class="min-h-screen bg-gray-50 flex items-center justify-center p-6">
    <div class="w-full max-w-md text-center">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-10">

            <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>

            <h1 class="text-2xl font-bold text-gray-900 mb-3">Accès suspendu</h1>
            <p class="text-gray-500 mb-6 text-sm leading-relaxed">
                Votre abonnement ou période d'essai a expiré.<br>
                Renouvelez pour retrouver un accès complet à votre ERP.
            </p>

            {{-- Plans disponibles --}}
            <div class="bg-gray-50 rounded-xl p-4 mb-6 text-left space-y-2">
                @foreach([
                    ['Starter',     '15 000 FCFA/mois', '3 modules · 5 utilisateurs'],
                    ['Pro',         '35 000 FCFA/mois', 'Tous modules · 20 utilisateurs'],
                    ['Enterprise',  '75 000 FCFA/mois', 'Illimité · support dédié'],
                ] as [$plan, $price, $desc])
                <div class="flex items-start gap-3 text-sm">
                    <span class="text-orange-500 font-bold mt-0.5">→</span>
                    <div>
                        <span class="font-semibold text-gray-700">{{ $plan }}</span>
                        <span class="text-gray-500"> — {{ $price }}</span>
                        <p class="text-xs text-gray-400">{{ $desc }}</p>
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Bouton principal : demande de renouvellement --}}
            <a href="{{ route('subscription.renew') }}" class="btn-primary w-full py-3 block mb-3 text-base font-semibold">
                🔄 Demander un renouvellement
            </a>

            <a href="mailto:contact@africaerp.com" class="btn-secondary w-full py-2.5 block mb-3 text-sm">
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
