@extends('layouts.app')
@section('title', 'Rapports financiers')
@section('page-title', 'Rapports Financiers ' . $year)
@section('header-actions')
    <form method="GET" class="flex gap-2 items-center">
        <select name="year" class="form-input text-xs w-28" onchange="this.form.submit()">
            @foreach($years as $y)
            <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
            @endforeach
        </select>
        <a href="{{ route('accounting.index') }}" class="btn-secondary text-xs">Transactions</a>
    </form>
@endsection

@section('content')
<div class="mt-2 space-y-4">

    <!-- Résumé annuel -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs text-gray-500 uppercase font-semibold">Recettes {{ $year }}</p>
            <p class="text-xl font-bold text-green-600 mt-1">{{ money($totalRecettes) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs text-gray-500 uppercase font-semibold">Dépenses {{ $year }}</p>
            <p class="text-xl font-bold text-red-500 mt-1">{{ money($totalDepenses) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs text-gray-500 uppercase font-semibold">Résultat net</p>
            <p class="text-xl font-bold mt-1 {{ $resultatNet >= 0 ? 'text-green-600' : 'text-red-600' }}">
                {{ ($resultatNet >= 0 ? '+' : '') . money($resultatNet) }}
            </p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs text-gray-500 uppercase font-semibold">Trésorerie fin {{ $year }}</p>
            @php $dernierSolde = end($tresorerie); @endphp
            <p class="text-xl font-bold mt-1 {{ $dernierSolde >= 0 ? 'text-blue-600' : 'text-red-600' }}" style="{{ $dernierSolde >= 0 ? 'color:var(--clr-p)' : '' }}">
                {{ money($dernierSolde) }}
            </p>
        </div>
    </div>

    <!-- Mensuel tableau -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="px-5 py-4 border-b border-gray-100">
            <h3 class="font-semibold text-gray-800 text-sm">Compte de résultat mensuel {{ $year }}</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50">
                        <th class="text-left px-4 py-2 text-xs font-semibold text-gray-500">Mois</th>
                        <th class="text-right px-4 py-2 text-xs font-semibold text-green-600">Recettes</th>
                        <th class="text-right px-4 py-2 text-xs font-semibold text-red-500">Dépenses</th>
                        <th class="text-right px-4 py-2 text-xs font-semibold text-gray-700">Résultat</th>
                        <th class="text-right px-4 py-2 text-xs font-semibold text-blue-600">Trésorerie cum.</th>
                        <th class="px-4 py-2 w-32"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @php
                    $moisLabels = ['Jan','Fév','Mar','Avr','Mai','Jun','Jul','Aoû','Sep','Oct','Nov','Déc'];
                    $maxVal = max(array_map(fn($m) => max($m['recettes'], $m['depenses']), $monthly)) ?: 1;
                    @endphp
                    @foreach($monthly as $m => $data)
                    @php $solde = $data['recettes'] - $data['depenses']; @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-700 w-16">{{ $moisLabels[$m-1] }}</td>
                        <td class="px-4 py-3 text-right text-green-600 font-medium">{{ money($data['recettes']) }}</td>
                        <td class="px-4 py-3 text-right text-red-500 font-medium">{{ money($data['depenses']) }}</td>
                        <td class="px-4 py-3 text-right font-bold {{ $solde >= 0 ? 'text-green-700' : 'text-red-600' }}">
                            {{ ($solde >= 0 ? '+' : '') . money($solde) }}
                        </td>
                        <td class="px-4 py-3 text-right font-medium {{ $tresorerie[$m] >= 0 ? 'text-blue-600' : 'text-red-600' }}">
                            {{ money($tresorerie[$m]) }}
                        </td>
                        <td class="px-4 py-3">
                            @if($data['recettes'] > 0 || $data['depenses'] > 0)
                            <div class="flex gap-1 items-center">
                                <div class="bg-green-400 rounded-sm h-2" style="width: {{ round($data['recettes'] / $maxVal * 80) }}px; min-width: 2px"></div>
                                <div class="bg-red-400 rounded-sm h-2" style="width: {{ round($data['depenses'] / $maxVal * 80) }}px; min-width: 2px"></div>
                            </div>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-gray-50 font-bold border-t-2 border-gray-200">
                        <td class="px-4 py-3 text-gray-700">Total</td>
                        <td class="px-4 py-3 text-right text-green-600">{{ money($totalRecettes) }}</td>
                        <td class="px-4 py-3 text-right text-red-500">{{ money($totalDepenses) }}</td>
                        <td class="px-4 py-3 text-right {{ $resultatNet >= 0 ? 'text-green-700' : 'text-red-600' }}">
                            {{ ($resultatNet >= 0 ? '+' : '') . money($resultatNet) }}
                        </td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Catégories -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Recettes par catégorie -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100">
            <div class="px-5 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-800 text-sm">Recettes par catégorie</h3>
            </div>
            <div class="divide-y divide-gray-50">
                @forelse($recettesCategories as $cat)
                @php $pct = $totalRecettes > 0 ? round($cat->total / $totalRecettes * 100) : 0; @endphp
                <div class="px-5 py-3">
                    <div class="flex justify-between mb-1">
                        <span class="text-sm text-gray-700">{{ $cat->category }}</span>
                        <span class="text-sm font-semibold text-green-600">{{ money($cat->total) }} ({{ $pct }}%)</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-1.5">
                        <div class="bg-green-400 h-1.5 rounded-full" style="width: {{ $pct }}%"></div>
                    </div>
                </div>
                @empty
                <p class="px-5 py-6 text-center text-gray-400 text-sm">Aucune recette</p>
                @endforelse
            </div>
        </div>

        <!-- Dépenses par catégorie -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100">
            <div class="px-5 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-800 text-sm">Dépenses par catégorie</h3>
            </div>
            <div class="divide-y divide-gray-50">
                @forelse($depensesCategories as $cat)
                @php $pct = $totalDepenses > 0 ? round($cat->total / $totalDepenses * 100) : 0; @endphp
                <div class="px-5 py-3">
                    <div class="flex justify-between mb-1">
                        <span class="text-sm text-gray-700">{{ $cat->category }}</span>
                        <span class="text-sm font-semibold text-red-500">{{ money($cat->total) }} ({{ $pct }}%)</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-1.5">
                        <div class="bg-red-400 h-1.5 rounded-full" style="width: {{ $pct }}%"></div>
                    </div>
                </div>
                @empty
                <p class="px-5 py-6 text-center text-gray-400 text-sm">Aucune dépense</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Flux de trésorerie -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <h3 class="font-semibold text-gray-800 text-sm mb-4">Flux de trésorerie — encaissements vs décaissements</h3>
        <div class="grid grid-cols-2 gap-6">
            <div>
                <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Encaissements clients</p>
                <p class="text-2xl font-bold text-green-600">{{ money($encaissementsClients) }}</p>
                <p class="text-xs text-gray-400 mt-1">Paiements reçus des clients</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Décaissements fournisseurs</p>
                <p class="text-2xl font-bold text-red-500">{{ money($paiementsFournisseurs) }}</p>
                <p class="text-xs text-gray-400 mt-1">Paiements effectués aux fournisseurs</p>
            </div>
        </div>
    </div>

</div>
@endsection
