@extends('layouts.app')

@section('title', 'Tableau de bord')
@section('page-title', 'Tableau de bord')

@section('content')
<div class="space-y-6 mt-2">

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase">Produits actifs</p>
                    <p class="text-2xl font-bold text-gray-800 mt-1">{{ $stats['products_total'] }}</p>
                </div>
                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
            </div>
            @if($stats['low_stock'] > 0)
            <p class="text-xs text-red-500 mt-2 font-medium">⚠ {{ $stats['low_stock'] }} en stock bas</p>
            @endif
        </div>

        <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase">En fabrication</p>
                    <p class="text-2xl font-bold text-gray-800 mt-1">{{ $stats['productions_en_cours'] }}</p>
                </div>
                <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                </div>
            </div>
            <p class="text-xs text-gray-400 mt-2">ordres en cours</p>
        </div>

        <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase">Machines en panne</p>
                    <p class="text-2xl font-bold {{ $stats['machines_en_panne'] > 0 ? 'text-red-600' : 'text-gray-800' }} mt-1">{{ $stats['machines_en_panne'] }}</p>
                </div>
                <div class="w-10 h-10 {{ $stats['machines_en_panne'] > 0 ? 'bg-red-100' : 'bg-green-100' }} rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 {{ $stats['machines_en_panne'] > 0 ? 'text-red-600' : 'text-green-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/></svg>
                </div>
            </div>
            <p class="text-xs text-gray-400 mt-2">{{ $stats['maintenances_planifiees'] }} maintenances planifiées</p>
        </div>

        <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase">CA ce mois</p>
                    <p class="text-2xl font-bold text-gray-800 mt-1">{{ money($stats['ca_mois']) }}</p>
                </div>
                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <p class="text-xs text-gray-400 mt-2">{{ $stats['factures_impayees'] }} factures impayées</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Stock bas -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-semibold text-gray-800 text-sm">Alertes Stock Bas</h3>
                <a href="{{ route('products.index', ['low_stock' => 1]) }}" class="text-xs text-orange-500 hover:text-orange-600 font-medium">Voir tout</a>
            </div>
            <div class="divide-y divide-gray-50">
                @forelse($low_stock_products as $product)
                <div class="px-5 py-3 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-800">{{ $product->name }}</p>
                        <p class="text-xs text-gray-400">{{ $product->code }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-bold text-red-600">{{ $product->quantity_in_stock }} {{ $product->unit }}</p>
                        <p class="text-xs text-gray-400">min: {{ $product->min_stock_alert }}</p>
                    </div>
                </div>
                @empty
                <div class="px-5 py-8 text-center text-gray-400 text-sm">
                    Aucune alerte stock
                </div>
                @endforelse
            </div>
        </div>

        <!-- Productions récentes -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-semibold text-gray-800 text-sm">Fabrication récente</h3>
                <a href="{{ route('production.index') }}" class="text-xs text-orange-500 hover:text-orange-600 font-medium">Voir tout</a>
            </div>
            <div class="divide-y divide-gray-50">
                @forelse($recent_productions as $order)
                <div class="px-5 py-3 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-800">{{ $order->reference }}</p>
                        <p class="text-xs text-gray-400">{{ $order->product->name }}</p>
                    </div>
                    <span class="px-2 py-1 text-xs font-medium rounded-full
                        {{ $order->status === 'termine' ? 'bg-green-100 text-green-700' :
                           ($order->status === 'en_cours' ? 'bg-blue-100 text-blue-700' :
                           ($order->status === 'annule' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-600')) }}">
                        {{ $order->status }}
                    </span>
                </div>
                @empty
                <div class="px-5 py-8 text-center text-gray-400 text-sm">Aucun ordre récent</div>
                @endforelse
            </div>
        </div>

        <!-- Maintenances à venir -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-semibold text-gray-800 text-sm">Maintenances planifiées</h3>
                <a href="{{ route('maintenance.index') }}" class="text-xs text-orange-500 hover:text-orange-600 font-medium">Voir tout</a>
            </div>
            <div class="divide-y divide-gray-50">
                @forelse($upcoming_maintenances as $m)
                <div class="px-5 py-3 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-800">{{ $m->machine->name }}</p>
                        <p class="text-xs text-gray-400 capitalize">{{ $m->type }}</p>
                    </div>
                    <p class="text-xs text-gray-500">{{ $m->scheduled_date->format('d/m/Y') }}</p>
                </div>
                @empty
                <div class="px-5 py-8 text-center text-gray-400 text-sm">Aucune maintenance planifiée</div>
                @endforelse
            </div>
        </div>

        <!-- Énergie + Qualité -->
        <div class="space-y-4">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="font-semibold text-gray-800 text-sm">Énergie ce mois</h3>
                    <a href="{{ route('energy.index') }}" class="text-xs text-orange-500 hover:text-orange-600 font-medium">Détails</a>
                </div>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <div>
                        <p class="text-xl font-bold text-gray-800">{{ money($energy_this_month) }}</p>
                        <p class="text-xs text-gray-400">Coût énergie total</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="font-semibold text-gray-800 text-sm">Qualité ce mois</h3>
                    <a href="{{ route('quality.index') }}" class="text-xs text-orange-500 hover:text-orange-600 font-medium">Détails</a>
                </div>
                <div class="flex gap-4">
                    <div class="text-center">
                        <p class="text-2xl font-bold text-gray-800">{{ $quality_stats['total'] }}</p>
                        <p class="text-xs text-gray-400">Contrôles</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-bold text-green-600">{{ $quality_stats['passed'] }}</p>
                        <p class="text-xs text-gray-400">Réussis</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-bold text-red-600">{{ $quality_stats['total'] - $quality_stats['passed'] }}</p>
                        <p class="text-xs text-gray-400">Échecs</p>
                    </div>
                    @if($quality_stats['total'] > 0)
                    <div class="text-center">
                        <p class="text-2xl font-bold text-blue-600">{{ round($quality_stats['passed'] / $quality_stats['total'] * 100) }}%</p>
                        <p class="text-xs text-gray-400">Taux réussite</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
