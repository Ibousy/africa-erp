@extends('layouts.app')
@section('title', 'Comptabilité & Finance')
@section('page-title', 'Comptabilité & Finance')
@section('header-actions')
    <a href="{{ route('accounting.create') }}" class="btn-primary">+ Nouvelle transaction</a>
@endsection

@section('content')
<div class="mt-2 space-y-4">
    <!-- Stats -->
    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs text-gray-500 uppercase font-semibold">Recettes ce mois</p>
            <p class="text-2xl font-bold text-green-600 mt-1">{{ money($stats['recettes_month'], false, false) }}<span class="text-sm font-normal text-gray-400">FCFA</span></p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs text-gray-500 uppercase font-semibold">Dépenses ce mois</p>
            <p class="text-2xl font-bold text-red-600 mt-1">{{ money($stats['depenses_month'], false, false) }}<span class="text-sm font-normal text-gray-400">FCFA</span></p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs text-gray-500 uppercase font-semibold">Solde ce mois</p>
            <p class="text-2xl font-bold mt-1 {{ $stats['solde_month'] >= 0 ? 'text-green-700' : 'text-red-700' }}">
                {{ ($stats['solde_month'] >= 0 ? '+' : '') . money($stats['solde_month'], false, false) }}<span class="text-sm font-normal text-gray-400">FCFA</span>
            </p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs text-gray-500 uppercase font-semibold">Recettes cette année</p>
            <p class="text-xl font-bold text-green-600 mt-1">{{ money($stats['recettes_year'], false, false) }}<span class="text-sm font-normal text-gray-400">FCFA</span></p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs text-gray-500 uppercase font-semibold">Dépenses cette année</p>
            <p class="text-xl font-bold text-red-600 mt-1">{{ money($stats['depenses_year'], false, false) }}<span class="text-sm font-normal text-gray-400">FCFA</span></p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs text-gray-500 uppercase font-semibold">Solde cette année</p>
            <p class="text-xl font-bold mt-1 {{ $stats['solde_year'] >= 0 ? 'text-green-700' : 'text-red-700' }}">
                {{ ($stats['solde_year'] >= 0 ? '+' : '') . money($stats['solde_year'], false, false) }}<span class="text-sm font-normal text-gray-400">FCFA</span>
            </p>
        </div>
    </div>

    <!-- Transactions -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="px-5 py-4 border-b border-gray-100 flex flex-wrap items-center justify-between gap-3">
            <h3 class="font-semibold text-gray-800 text-sm">Transactions</h3>
            <form method="GET" class="flex gap-2">
                <select name="type" class="form-input text-xs w-32">
                    <option value="">Tous types</option>
                    <option value="recette" {{ request('type') === 'recette' ? 'selected' : '' }}>Recette</option>
                    <option value="depense" {{ request('type') === 'depense' ? 'selected' : '' }}>Dépense</option>
                </select>
                <input type="month" name="month" value="{{ request('month', date('Y-m')) }}" class="form-input text-xs">
                <button type="submit" class="btn-primary text-xs">Filtrer</button>
            </form>
        </div>
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100">
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Date</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Description</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Catégorie</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Mode</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Réf.</th>
                    <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Montant (FCFA)</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($transactions as $tx)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3 text-gray-600">{{ $tx->date->format('d/m/Y') }}</td>
                    <td class="px-5 py-3 font-medium text-gray-800">{{ $tx->description }}</td>
                    <td class="px-5 py-3 text-gray-600">{{ $tx->category }}</td>
                    <td class="px-5 py-3 text-gray-500 text-xs">{{ $tx->payment_method ?: '—' }}</td>
                    <td class="px-5 py-3 text-gray-400 text-xs">{{ $tx->reference ?: '—' }}</td>
                    <td class="px-5 py-3 text-right font-bold {{ $tx->type === 'recette' ? 'text-green-600' : 'text-red-600' }}">
                        {{ $tx->type === 'recette' ? '+' : '−' }} {{ money($tx->amount) }}
                    </td>
                    <td class="px-5 py-3 text-right">
                        <div class="flex items-center justify-end gap-3">
                            <a href="{{ route('accounting.edit', $tx) }}" class="text-xs text-gray-500 hover:text-gray-700">Modifier</a>
                            <form method="POST" action="{{ route('accounting.destroy', $tx) }}" onsubmit="return confirm('Supprimer ?')">
                                @csrf @method('DELETE')
                                <button class="text-red-400 hover:text-red-600 text-xs">Suppr.</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-5 py-12 text-center text-gray-400">Aucune transaction ce mois</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-5 py-3">{{ $transactions->links() }}</div>
    </div>
</div>
@endsection
