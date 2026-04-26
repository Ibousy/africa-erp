@extends('layouts.app')
@section('title', 'Devis')
@section('page-title', 'Devis & Propositions commerciales')
@section('header-actions')
    <a href="{{ route('quotes.create') }}" class="btn-primary">+ Nouveau devis</a>
@endsection

@section('content')
<div class="mt-2 space-y-4">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs text-gray-500 uppercase font-semibold">Total devis</p>
            <p class="text-2xl font-bold text-gray-800 mt-1">{{ $stats['total'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs text-gray-500 uppercase font-semibold">Envoyés</p>
            <p class="text-2xl font-bold text-yellow-500 mt-1">{{ $stats['envoye'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs text-gray-500 uppercase font-semibold">Acceptés</p>
            <p class="text-2xl font-bold text-green-600 mt-1">{{ $stats['accepte'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs text-gray-500 uppercase font-semibold">CA devis acceptés</p>
            <p class="text-lg font-bold mt-1" style="color:var(--clr-p)">{{ money($stats['ca']) }}</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="px-5 py-4 border-b border-gray-100 flex flex-wrap items-center justify-between gap-3">
            <h3 class="font-semibold text-gray-800 text-sm">Devis</h3>
            <form method="GET" class="flex gap-2">
                <select name="client_id" class="form-input text-xs w-44">
                    <option value="">Tous les clients</option>
                    @foreach($clients as $c)
                    <option value="{{ $c->id }}" {{ request('client_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
                <select name="status" class="form-input text-xs w-32">
                    <option value="">Tous statuts</option>
                    @foreach(['brouillon' => 'Brouillon', 'envoye' => 'Envoyé', 'accepte' => 'Accepté', 'refuse' => 'Refusé', 'expire' => 'Expiré'] as $k => $v)
                    <option value="{{ $k }}" {{ request('status') === $k ? 'selected' : '' }}>{{ $v }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn-primary text-xs">Filtrer</button>
            </form>
        </div>
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100">
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Référence</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Client</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Date</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Validité</th>
                    <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Total (FCFA)</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Statut</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($quotes as $q)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3 font-mono text-xs text-gray-700">{{ $q->reference }}</td>
                    <td class="px-5 py-3 font-medium text-gray-800">{{ $q->client->name }}</td>
                    <td class="px-5 py-3 text-gray-500">{{ $q->issue_date->format('d/m/Y') }}</td>
                    <td class="px-5 py-3 text-gray-500 {{ $q->valid_until && $q->valid_until->isPast() && $q->status === 'envoye' ? 'text-red-500' : '' }}">
                        {{ $q->valid_until ? $q->valid_until->format('d/m/Y') : '—' }}
                    </td>
                    <td class="px-5 py-3 text-right font-semibold text-gray-800">{{ money($q->total) }}</td>
                    <td class="px-5 py-3">
                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full
                            {{ $q->status === 'accepte' ? 'bg-green-100 text-green-700' :
                               ($q->status === 'envoye' ? 'bg-yellow-100 text-yellow-700' :
                               ($q->status === 'refuse' || $q->status === 'expire' ? 'bg-red-100 text-red-600' : 'bg-gray-100 text-gray-500')) }}">
                            {{ ['brouillon' => 'Brouillon', 'envoye' => 'Envoyé', 'accepte' => 'Accepté', 'refuse' => 'Refusé', 'expire' => 'Expiré'][$q->status] ?? $q->status }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-right whitespace-nowrap">
                        <a href="{{ route('quotes.show', $q) }}" class="text-blue-500 hover:text-blue-700 text-xs mr-2">Voir</a>
                        <form method="POST" action="{{ route('quotes.destroy', $q) }}" class="inline" onsubmit="return confirm('Supprimer ?')">
                            @csrf @method('DELETE')
                            <button class="text-red-400 hover:text-red-600 text-xs">Suppr.</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-5 py-12 text-center text-gray-400">Aucun devis</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-5 py-3">{{ $quotes->links() }}</div>
    </div>
</div>
@endsection
