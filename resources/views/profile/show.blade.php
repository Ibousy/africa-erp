@extends('layouts.app')
@section('title', 'Mon profil')
@section('page-title', 'Mon profil')

@section('content')
<div class="mt-2 max-w-xl space-y-5">

    <!-- Carte identité -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center gap-4 pb-5 mb-5 border-b border-gray-100">
            <div class="w-14 h-14 rounded-full flex items-center justify-center text-white font-bold text-2xl shrink-0"
                style="background-color: var(--clr-p)">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            <div>
                <p class="font-bold text-gray-900 text-lg">{{ $user->name }}</p>
                <div class="flex items-center gap-2 mt-1">
                    <span class="px-2 py-0.5 text-xs font-semibold rounded-full
                        {{ $user->role === 'admin' ? 'bg-red-100 text-red-700' :
                           ($user->role === 'manager' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600') }}">
                        @label($user->role)
                    </span>
                    @if($tenant)
                    <span class="text-xs text-gray-400">{{ $tenant->company_name }}</span>
                    @endif
                </div>
            </div>
        </div>

        @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-5 text-sm flex items-center gap-2">
            <span class="text-green-500 font-bold">✓</span> {{ session('success') }}
        </div>
        @endif

        @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-5 text-sm">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('profile.update') }}" class="space-y-4">
            @csrf @method('PUT')

            <div>
                <label class="form-label">Nom complet *</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="form-input">
            </div>

            <div>
                <label class="form-label">Email</label>
                <input type="text" value="{{ $user->email }}" disabled class="form-input bg-gray-50 text-gray-400 cursor-not-allowed">
                <p class="text-xs text-gray-400 mt-1">L'email ne peut être modifié que par un administrateur.</p>
            </div>

            <div>
                <label class="form-label">Téléphone</label>
                <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="+221 77 000 00 00" class="form-input">
            </div>

            <div class="border-t border-gray-100 pt-4">
                <p class="text-sm font-semibold text-gray-700 mb-3">Changer le mot de passe</p>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Nouveau mot de passe</label>
                        <input type="password" name="password" placeholder="Min. 6 caractères" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Confirmer</label>
                        <input type="password" name="password_confirmation" class="form-input">
                    </div>
                </div>
                <p class="text-xs text-gray-400 mt-2">Laissez vide pour conserver le mot de passe actuel.</p>
            </div>

            <button type="submit" class="btn-primary w-full py-2.5">Enregistrer les modifications</button>
        </form>
    </div>

    <!-- Infos session -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <h3 class="text-sm font-semibold text-gray-700 mb-3">Informations du compte</h3>
        <dl class="space-y-2 text-sm">
            <div class="flex justify-between">
                <dt class="text-gray-500">Membre depuis</dt>
                <dd class="text-gray-800 font-medium">{{ $user->created_at->format('d/m/Y') }}</dd>
            </div>
            <div class="flex justify-between">
                <dt class="text-gray-500">Statut</dt>
                <dd>
                    <span class="px-2 py-0.5 text-xs font-medium rounded-full {{ $user->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                        {{ $user->is_active ? 'Actif' : 'Désactivé' }}
                    </span>
                </dd>
            </div>
            @if($tenant)
            <div class="flex justify-between">
                <dt class="text-gray-500">Entreprise</dt>
                <dd class="text-gray-800 font-medium">{{ $tenant->company_name }}</dd>
            </div>
            <div class="flex justify-between">
                <dt class="text-gray-500">Abonnement</dt>
                <dd class="text-gray-800 font-medium capitalize">
                    {{ $tenant->status === 'trial' ? 'Essai (' . $tenant->trialDaysLeft() . ' j restants)' : ucfirst($tenant->plan) }}
                </dd>
            </div>
            @endif
        </dl>
    </div>

</div>
@endsection
