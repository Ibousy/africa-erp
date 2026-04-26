@extends('layouts.public')
@section('title', 'Bienvenue — AfricaERP')

@section('content')
<div class="min-h-screen bg-linear-to-br from-gray-900 to-gray-950 flex items-center justify-center p-6">
    <div class="w-full max-w-lg text-center">
        <div class="bg-white rounded-3xl shadow-2xl p-10">
            <!-- Icône succès -->
            <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-10 h-10 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>

            <h1 class="text-2xl font-bold text-gray-900 mb-2">Votre ERP est prêt !</h1>
            <p class="text-gray-500 mb-8">
                Bienvenue dans <span class="font-semibold text-gray-800">{{ $tenant->company_name }}</span>.<br>
                Votre essai gratuit de 14 jours est maintenant actif.
            </p>

            <!-- Infos essai -->
            <div class="bg-orange-50 border border-orange-200 rounded-xl p-5 mb-6 text-left">
                <div class="flex items-center gap-3 mb-3">
                    <span class="text-2xl">🎁</span>
                    <div>
                        <div class="font-semibold text-gray-900">Essai gratuit — 14 jours</div>
                        <div class="text-sm text-gray-500">Expire le {{ $tenant->trial_ends_at->format('d/m/Y') }}</div>
                    </div>
                </div>
                <div class="text-sm text-gray-600 space-y-1">
                    <div class="flex items-center gap-2"><span class="text-green-500">✓</span> Tous vos modules sont activés</div>
                    <div class="flex items-center gap-2"><span class="text-green-500">✓</span> Aucune carte bancaire requise</div>
                    <div class="flex items-center gap-2"><span class="text-green-500">✓</span> Vos données sont conservées après l'essai</div>
                </div>
            </div>

            <!-- Modules activés -->
            @if($tenant->modules->where('enabled', true)->count() > 0)
            <div class="mb-8">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Modules activés</p>
                <div class="flex flex-wrap justify-center gap-2">
                    @foreach($tenant->modules->where('enabled', true) as $mod)
                    @php $names = ['stock'=>'Stock','production'=>'Production','quality'=>'Qualité','maintenance'=>'Maintenance','sales'=>'Commercial','energy'=>'Énergie','users'=>'Utilisateurs']; @endphp
                    <span class="px-3 py-1 bg-green-50 border border-green-200 text-green-700 text-xs font-medium rounded-full">{{ $names[$mod->module] ?? $mod->module }}</span>
                    @endforeach
                </div>
            </div>
            @endif

            <a href="{{ route('dashboard') }}" class="btn-primary w-full py-4 text-base rounded-xl block">
                Accéder à mon tableau de bord →
            </a>

            <p class="text-xs text-gray-400 mt-4">
                Vous pouvez modifier vos modules et votre thème à tout moment dans<br>
                <strong>Paramètres → Mon Entreprise</strong>
            </p>
        </div>
    </div>
</div>
@endsection
