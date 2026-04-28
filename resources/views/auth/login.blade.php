<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion — AfricaERP</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex">

    {{-- ===== CÔTÉ GAUCHE — Présentation du projet ===== --}}
    <div class="hidden lg:flex lg:w-3/5 bg-gray-950 relative overflow-hidden flex-col justify-between p-12">

        {{-- Arrière-plan décoratif --}}
        <div class="absolute inset-0 pointer-events-none">
            <div class="absolute -top-32 -left-32 w-96 h-96 bg-orange-500 rounded-full opacity-10 blur-3xl"></div>
            <div class="absolute top-1/2 right-0 w-80 h-80 bg-blue-600 rounded-full opacity-10 blur-3xl"></div>
            <div class="absolute -bottom-20 left-1/3 w-96 h-64 bg-purple-600 rounded-full opacity-5 blur-3xl"></div>
        </div>

        {{-- Header --}}
        <div class="relative">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 bg-orange-500 rounded-xl flex items-center justify-center font-bold text-white text-xl">A</div>
                <span class="font-bold text-white text-xl">AfricaERP</span>
            </div>
        </div>

        {{-- Contenu principal --}}
        <div class="relative space-y-8">
            <div>
                <div class="inline-flex items-center gap-2 bg-orange-500/15 border border-orange-500/25 rounded-full px-4 py-1.5 text-orange-400 text-sm font-medium mb-6">
                    <span class="w-2 h-2 bg-orange-400 rounded-full"></span>
                    Conçu pour l'industrie africaine
                </div>
                <h1 class="text-4xl font-extrabold text-white leading-tight mb-4">
                    L'ERP des <span class="text-orange-400">PME industrielles</span> africaines
                </h1>
                <p class="text-gray-400 text-lg leading-relaxed max-w-lg">
                    Gérez votre stock, votre production, vos finances et vos équipes depuis un seul tableau de bord pensé pour l'Afrique.
                </p>
            </div>

            {{-- Modules clés --}}
            <div class="grid grid-cols-2 gap-3">
                @foreach([
                    ['icon' => '📦', 'label' => 'Stock & Inventaire'],
                    ['icon' => '🏭', 'label' => 'Production'],
                    ['icon' => '💼', 'label' => 'Commercial & Facturation'],
                    ['icon' => '🔧', 'label' => 'Maintenance'],
                    ['icon' => '👥', 'label' => 'RH & Paie'],
                    ['icon' => '⚡', 'label' => 'Énergie'],
                ] as $m)
                <div class="flex items-center gap-3 bg-white/5 border border-white/10 rounded-xl px-4 py-3">
                    <span class="text-xl">{{ $m['icon'] }}</span>
                    <span class="text-sm text-gray-300 font-medium">{{ $m['label'] }}</span>
                </div>
                @endforeach
            </div>

            {{-- Secteurs --}}
            <div>
                <p class="text-xs text-gray-500 uppercase tracking-widest font-semibold mb-3">Secteurs couverts</p>
                <div class="flex flex-wrap gap-2">
                    @foreach(['Agroalimentaire', 'Parfumeries', 'Textile', 'Métallurgie', 'Menuiserie', 'Pharmacie'] as $s)
                    <span class="px-3 py-1 bg-white/5 border border-white/10 rounded-full text-xs text-gray-400">{{ $s }}</span>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Footer gauche --}}
        <div class="relative">
            <p class="text-gray-600 text-sm">© {{ date('Y') }} AfricaERP — Fait pour les PME africaines</p>
        </div>
    </div>

    {{-- ===== CÔTÉ DROIT — Formulaire de connexion ===== --}}
    <div class="w-full lg:w-2/5 bg-white flex flex-col justify-center px-8 sm:px-12 lg:px-16">

        {{-- Logo mobile uniquement --}}
        <div class="flex items-center gap-3 mb-10 lg:hidden">
            <div class="w-9 h-9 bg-orange-500 rounded-xl flex items-center justify-center font-bold text-white text-lg">A</div>
            <span class="font-bold text-gray-900 text-lg">AfricaERP</span>
        </div>

        <div class="max-w-sm w-full mx-auto">
            <div class="mb-8">
                <h2 class="text-2xl font-bold text-gray-900">Connexion</h2>
                <p class="text-gray-500 text-sm mt-1">Accédez à votre tableau de bord</p>
            </div>

            @if($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-6 text-sm flex items-center gap-2">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ $errors->first() }}
            </div>
            @endif

            @if(session('status'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl mb-6 text-sm">
                {{ session('status') }}
            </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Adresse email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-transparent outline-none text-sm bg-gray-50 focus:bg-white transition-colors"
                        placeholder="vous@entreprise.com">
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-sm font-semibold text-gray-700">Mot de passe</label>
                    </div>
                    <input type="password" name="password" required
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-transparent outline-none text-sm bg-gray-50 focus:bg-white transition-colors"
                        placeholder="••••••••">
                </div>

                <div class="flex items-center">
                    <input type="checkbox" name="remember" id="remember"
                        class="w-4 h-4 rounded border-gray-300 text-orange-500 focus:ring-orange-500">
                    <label for="remember" class="ml-2 text-sm text-gray-600">Se souvenir de moi</label>
                </div>

                <button type="submit"
                    class="w-full bg-orange-500 hover:bg-orange-600 text-white font-semibold py-3 px-4 rounded-xl transition-colors text-sm">
                    Se connecter →
                </button>
            </form>

            <div class="mt-8 pt-6 border-t border-gray-100 text-center">
                <p class="text-sm text-gray-500">
                    Pas encore de compte ?
                    <a href="{{ route('register') }}" class="font-semibold text-orange-500 hover:text-orange-600 transition-colors">
                        Démarrer l'essai gratuit 14 jours →
                    </a>
                </p>
            </div>

            <div class="mt-4 text-center">
                <a href="{{ route('welcome') }}" class="text-xs text-gray-400 hover:text-gray-600 transition-colors">
                    ← Retour à l'accueil
                </a>
            </div>
        </div>
    </div>

</body>
</html>
