@extends('layouts.public')
@section('title', 'AfricaERP — L\'ERP des PME industrielles africaines')

@section('content')

<!-- Navbar -->
<nav class="fixed top-0 w-full z-50 bg-white/95 backdrop-blur border-b border-gray-100 shadow-sm">
    <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 bg-orange-500 rounded-xl flex items-center justify-center font-bold text-white text-lg">A</div>
            <span class="font-bold text-gray-900 text-lg">AfricaERP</span>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('login') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900 px-4 py-2">Se connecter</a>
            <a href="{{ route('register') }}" class="btn-primary text-sm px-5 py-2">Essai gratuit 14j →</a>
        </div>
    </div>
</nav>

<!-- Hero -->
<section class="pt-28 pb-24 bg-linear-to-br from-gray-950 via-gray-900 to-gray-950 text-white relative overflow-hidden">
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -left-40 w-96 h-96 bg-orange-500 rounded-full opacity-10 blur-3xl"></div>
        <div class="absolute top-20 right-0 w-80 h-80 bg-blue-600 rounded-full opacity-10 blur-3xl"></div>
        <div class="absolute bottom-0 left-1/2 w-96 h-64 bg-purple-600 rounded-full opacity-5 blur-3xl"></div>
    </div>
    <div class="relative max-w-5xl mx-auto px-6 text-center">
        <div class="inline-flex items-center gap-2 bg-orange-500/15 border border-orange-500/25 rounded-full px-4 py-2 text-orange-400 text-sm font-medium mb-8">
            <span class="w-2 h-2 bg-orange-400 rounded-full"></span>
            Conçu pour l'industrie africaine
        </div>
        <h1 class="text-5xl md:text-6xl font-extrabold leading-tight mb-6 tracking-tight">
            L'ERP des <span class="text-orange-400">PME industrielles</span><br>africaines
        </h1>
        <p class="text-xl text-gray-300 max-w-2xl mx-auto mb-10 leading-relaxed">
            Parfumeries, ateliers de transformation, usines agroalimentaires — gérez tout votre système industriel avec un seul outil pensé pour l'Afrique.
        </p>
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4 mb-6">
            <a href="{{ route('register') }}" class="btn-primary text-base px-8 py-3 rounded-xl">
                Démarrer l'essai gratuit 14 jours →
            </a>
            <a href="{{ route('login') }}" class="bg-white/10 hover:bg-white/20 border border-white/20 text-white text-base px-8 py-3 rounded-xl font-medium transition-colors">
                Accéder à mon compte
            </a>
        </div>
        <p class="text-gray-500 text-sm">Sans carte bancaire · 14 jours offerts · Résiliation à tout moment</p>
    </div>
</section>

<!-- Industries -->
<section class="py-14 bg-gray-50 border-b border-gray-100">
    <div class="max-w-6xl mx-auto px-6">
        <p class="text-center text-xs font-bold text-gray-400 uppercase tracking-widest mb-7">Utilisé dans des secteurs comme</p>
        <div class="flex flex-wrap justify-center gap-3">
            @foreach(['Parfumeries & Cosmétiques', 'Agroalimentaire', 'Textile & Confection', 'Métallurgie', 'Transformation agricole', 'Menuiserie industrielle', 'Plastiques & Emballages', 'Pharmacie & Parapharmacie'] as $ind)
            <span class="px-4 py-2 bg-white border border-gray-200 rounded-full text-sm font-medium text-gray-600 shadow-sm">{{ $ind }}</span>
            @endforeach
        </div>
    </div>
</section>

<!-- Modules -->
<section class="py-24">
    <div class="max-w-6xl mx-auto px-6">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Tous les outils pour piloter votre usine</h2>
            <p class="text-gray-500 max-w-xl mx-auto text-lg">Choisissez uniquement les modules dont vous avez besoin. Activez-en d'autres à mesure que vous grandissez.</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach([
                ['emoji' => '📦', 'name' => 'Stock & Inventaire',       'desc' => 'Gérez vos produits, suivez les entrées/sorties et recevez des alertes de stock bas.'],
                ['emoji' => '🏭', 'name' => 'Production & Fabrication', 'desc' => 'Planifiez vos ordres de fabrication, suivez l\'avancement et calculez vos rendements.'],
                ['emoji' => '✅', 'name' => 'Contrôle Qualité',         'desc' => 'Enregistrez vos contrôles, suivez les défauts et générez des rapports de conformité.'],
                ['emoji' => '🔧', 'name' => 'Maintenance',              'desc' => 'Planifiez maintenances préventives et correctives. Réduisez les pannes machines.'],
                ['emoji' => '💼', 'name' => 'Commercial & Facturation', 'desc' => 'Gérez clients, créez devis et factures en FCFA avec TVA, suivez les paiements.'],
                ['emoji' => '⚡', 'name' => 'Énergie',                  'desc' => 'Suivez la consommation électrique par machine et réduisez vos coûts énergétiques.'],
            ] as $m)
            <div class="bg-white border border-gray-100 rounded-2xl p-7 hover:shadow-md transition-shadow">
                <div class="text-4xl mb-4">{{ $m['emoji'] }}</div>
                <h3 class="font-bold text-gray-900 text-lg mb-2">{{ $m['name'] }}</h3>
                <p class="text-gray-500 text-sm leading-relaxed">{{ $m['desc'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Comment ça marche -->
<section class="py-20 bg-gray-50">
    <div class="max-w-5xl mx-auto px-6">
        <div class="text-center mb-14">
            <h2 class="text-3xl font-bold text-gray-900 mb-3">Opérationnel en 5 minutes</h2>
            <p class="text-gray-500 text-lg">Pas de technicien, pas d'installation. Tout se passe dans votre navigateur.</p>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
            @foreach([
                ['num' => '1', 'title' => 'Créez votre compte',      'desc' => 'Renseignez vos informations personnelles.'],
                ['num' => '2', 'title' => 'Ajoutez votre entreprise', 'desc' => 'Logo, nom, secteur d\'activité.'],
                ['num' => '3', 'title' => 'Choisissez vos modules',   'desc' => 'Sélectionnez ce dont vous avez besoin.'],
                ['num' => '4', 'title' => 'Personnalisez le thème',   'desc' => 'Vos couleurs, votre identité.'],
            ] as $step)
            <div class="text-center">
                <div class="w-12 h-12 bg-orange-500 text-white font-bold text-lg rounded-full flex items-center justify-center mx-auto mb-4">{{ $step['num'] }}</div>
                <h4 class="font-bold text-gray-900 mb-1 text-sm">{{ $step['title'] }}</h4>
                <p class="text-gray-500 text-xs">{{ $step['desc'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Pricing -->
<section class="py-24">
    <div class="max-w-5xl mx-auto px-6">
        <div class="text-center mb-14">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Des tarifs adaptés à votre croissance</h2>
            <p class="text-gray-500 text-lg">Commencez gratuitement. Passez à un plan payant quand vous êtes prêt.</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="bg-white rounded-2xl border-2 border-orange-200 p-8 relative">
                <div class="absolute -top-3 left-6">
                    <span class="bg-orange-500 text-white text-xs font-bold px-3 py-1 rounded-full">COMMENCEZ ICI</span>
                </div>
                <div class="text-orange-600 font-bold text-sm mb-2 mt-2">ESSAI GRATUIT</div>
                <div class="text-5xl font-extrabold text-gray-900 mb-1">0 <span class="text-2xl font-semibold text-gray-400">FCFA</span></div>
                <div class="text-gray-400 text-sm mb-7">pendant 14 jours</div>
                <ul class="space-y-3 text-sm text-gray-600 mb-8">
                    @foreach(['Tous les modules inclus', 'Utilisateurs illimités', 'Logo & thème personnalisé', 'Support inclus', 'Données conservées après essai'] as $f)
                    <li class="flex items-center gap-2"><span class="text-green-500 font-bold">✓</span> {{ $f }}</li>
                    @endforeach
                </ul>
                <a href="{{ route('register') }}" class="btn-primary w-full text-center py-3 rounded-xl block">Démarrer gratuitement</a>
            </div>
            <div class="bg-gray-900 rounded-2xl border border-gray-700 p-8">
                <div class="text-orange-400 font-bold text-sm mb-2">STARTER</div>
                <div class="text-5xl font-extrabold text-white mb-1">15 000 <span class="text-2xl font-semibold text-gray-500">FCFA</span></div>
                <div class="text-gray-500 text-sm mb-7">par mois</div>
                <ul class="space-y-3 text-sm text-gray-300 mb-8">
                    @foreach(['3 modules au choix', 'Jusqu\'à 10 utilisateurs', '5 Go de stockage', 'Support par email', 'Mises à jour incluses'] as $f)
                    <li class="flex items-center gap-2"><span class="text-orange-400 font-bold">✓</span> {{ $f }}</li>
                    @endforeach
                </ul>
                <a href="{{ route('register') }}" class="block text-center bg-orange-500 hover:bg-orange-600 text-white py-3 px-4 rounded-xl font-semibold transition-colors text-sm">Choisir Starter</a>
            </div>
            <div class="bg-white rounded-2xl border border-gray-100 p-8 shadow-sm">
                <div class="text-purple-600 font-bold text-sm mb-2">PROFESSIONNEL</div>
                <div class="text-5xl font-extrabold text-gray-900 mb-1">35 000 <span class="text-2xl font-semibold text-gray-400">FCFA</span></div>
                <div class="text-gray-400 text-sm mb-7">par mois</div>
                <ul class="space-y-3 text-sm text-gray-600 mb-8">
                    @foreach(['Tous les modules', 'Utilisateurs illimités', 'Stockage illimité', 'Support prioritaire', 'Formations incluses'] as $f)
                    <li class="flex items-center gap-2"><span class="text-green-500 font-bold">✓</span> {{ $f }}</li>
                    @endforeach
                </ul>
                <a href="{{ route('register') }}" class="btn-secondary w-full text-center py-3 rounded-xl block">Choisir Pro</a>
            </div>
        </div>
    </div>
</section>

<!-- CTA final -->
<section class="py-20 bg-orange-500">
    <div class="max-w-3xl mx-auto px-6 text-center">
        <h2 class="text-3xl md:text-4xl font-bold text-white mb-4">Prêt à digitaliser votre usine ?</h2>
        <p class="text-orange-100 text-lg mb-8">Rejoignez les PME industrielles africaines qui pilotent leur activité avec AfricaERP.</p>
        <a href="{{ route('register') }}" class="inline-flex items-center gap-2 bg-white text-orange-600 font-bold px-8 py-4 rounded-xl text-base hover:bg-orange-50 transition-colors">
            Créer mon compte gratuitement →
        </a>
    </div>
</section>

<!-- Footer -->
<footer class="bg-gray-950 text-gray-500 py-10">
    <div class="max-w-6xl mx-auto px-6 flex flex-col md:flex-row items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 bg-orange-500 rounded-lg flex items-center justify-center font-bold text-white text-sm">A</div>
            <span class="font-bold text-white">AfricaERP</span>
            <span class="text-gray-600 hidden md:inline">— Conçu pour les PME industrielles africaines</span>
        </div>
        <div class="flex items-center gap-6 text-sm">
            <a href="{{ route('login') }}" class="hover:text-gray-300 transition-colors">Se connecter</a>
            <a href="{{ route('register') }}" class="hover:text-gray-300 transition-colors">S'inscrire</a>
            <span>© {{ date('Y') }} AfricaERP</span>
        </div>
    </div>
</footer>

@endsection
