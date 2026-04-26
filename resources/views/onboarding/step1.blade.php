@extends('layouts.public')
@section('title', 'Créer un compte — AfricaERP')

@section('content')
<div class="min-h-screen bg-gray-50 flex">
    <!-- Panneau gauche -->
    <div class="hidden lg:flex lg:w-1/2 bg-linear-to-br from-gray-900 to-gray-950 text-white flex-col justify-between p-12">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-orange-500 rounded-xl flex items-center justify-center font-bold text-xl">A</div>
            <span class="font-bold text-xl">AfricaERP</span>
        </div>
        <div>
            <h2 class="text-3xl font-bold mb-4">L'ERP qui s'adapte<br>à votre entreprise</h2>
            <p class="text-gray-400 text-lg leading-relaxed">Choisissez vos modules, personnalisez votre thème et gérez votre usine depuis un seul tableau de bord.</p>
            <div class="mt-8 space-y-3">
                @foreach(['Essai gratuit 14 jours sans carte', 'Modules à la carte selon vos besoins', 'Votre logo et vos couleurs', 'Support en français'] as $f)
                <div class="flex items-center gap-3 text-gray-300">
                    <span class="w-5 h-5 bg-orange-500 rounded-full flex items-center justify-center text-xs font-bold">✓</span>
                    {{ $f }}
                </div>
                @endforeach
            </div>
        </div>
        <p class="text-gray-600 text-sm">© {{ date('Y') }} AfricaERP — PME Industrielles Africaines</p>
    </div>

    <!-- Panneau droit -->
    <div class="flex-1 flex items-center justify-center p-8">
        <div class="w-full max-w-md">
            <!-- Progress -->
            <div class="mb-8">
                <div class="flex items-center gap-2 mb-3">
                    @foreach(['Compte', 'Entreprise', 'Modules', 'Thème'] as $i => $label)
                    <div class="flex items-center gap-2 {{ $i > 0 ? 'flex-1' : '' }}">
                        @if($i > 0)<div class="flex-1 h-px {{ $i === 0 ? 'bg-orange-400' : 'bg-gray-200' }}"></div>@endif
                        <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold {{ $i === 0 ? 'bg-orange-500 text-white' : 'bg-gray-200 text-gray-400' }}">{{ $i + 1 }}</div>
                    </div>
                    @endforeach
                </div>
                <div class="flex justify-between text-xs text-gray-500 px-0">
                    <span class="text-orange-600 font-semibold">Compte</span>
                    <span>Entreprise</span>
                    <span>Modules</span>
                    <span>Thème</span>
                </div>
            </div>

            <h1 class="text-2xl font-bold text-gray-900 mb-1">Créez votre compte</h1>
            <p class="text-gray-500 text-sm mb-8">Commencez votre essai gratuit de 14 jours.</p>

            @if($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6 text-sm">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                </ul>
            </div>
            @endif

            <form method="POST" action="{{ route('register') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="form-label">Nom complet *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required placeholder="Ibrahima Diallo" class="form-input">
                </div>
                <div>
                    <label class="form-label">Adresse email *</label>
                    <input type="email" name="email" value="{{ old('email') }}" required placeholder="vous@entreprise.sn" class="form-input">
                </div>
                <div>
                    <label class="form-label">Mot de passe *</label>
                    <input type="password" name="password" required placeholder="Minimum 8 caractères" class="form-input">
                </div>
                <div>
                    <label class="form-label">Confirmer le mot de passe *</label>
                    <input type="password" name="password_confirmation" required placeholder="Répétez le mot de passe" class="form-input">
                </div>
                <div>
                    <label class="form-label">Téléphone</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" placeholder="+221 77 000 00 00" class="form-input">
                </div>
                <button type="submit" class="btn-primary w-full py-3 mt-2">Continuer →</button>
            </form>

            <p class="text-center text-sm text-gray-500 mt-6">
                Déjà un compte ?
                <a href="{{ route('login') }}" class="text-orange-600 font-medium hover:underline">Se connecter</a>
            </p>
        </div>
    </div>
</div>
@endsection
