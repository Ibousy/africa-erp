@extends('layouts.app')
@section('title', 'Retours')
@section('page-title', 'Gestion des Retours')
@section('header-actions')
    <a href="{{ route('returns.create') }}" class="btn-primary">+ Nouveau retour</a>
@endsection

@section('content')
<div class="mt-2 space-y-4">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs text-gray-500 uppercase font-semibold">Total retours</p>
            <p class="text-2xl font-bold text-gray-800 mt-1">{{ $stats['total'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs text-gray-500 uppercase font-semibold">Clients</p>
            <p class="text-2xl font-bold text-yellow-500 mt-1">{{ $stats['client'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs text-gray-500 uppercase font-semibold">Fournisseurs</p>
            <p class="text-2xl font-bold mt-1" style="color:var(--clr-p)">{{ $stats['fournisseur'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs text-gray-500 uppercase font-semibold">Traités</p>
            <p class="text-2xl font-bold text-green-600 mt-1">{{ $stats['traite'] }}</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="px-5 py-4 border-b border-gray-100 flex flex-wrap items-center justify-between gap-3">
            <h3 class="font-semibold text-gray-800 text-sm">Retours de marchandises</h3>
            <form method="GET" class="flex gap-2">
                <select name="type" class="form-input text-xs w-36">
                    <option value="">Tous types</option>
                    <option value="client" {{ request('type') === 'client' ? 'selected' : '' }}>Client</option>
                    <option value="fournisseur" {{ request('type') === 'fournisseur' ? 'selected' : '' }}>Fournisseur</option>
                </select>
                <select name="status" class="form-input text-xs w-36">
                    <option value="">Tous statuts</option>
                    <option value="en_attente" {{ request('status') === 'en_attente' ? 'selected' : '' }}>En attente</option>
                    <option value="accepte" {{ request('status') === 'accepte' ? 'selected' : '' }}>Accepté</option>
                    <option value="traite" {{ request('status') === 'traite' ? 'selected' : '' }}>Traité</option>
                </select>
                <button type="submit" class="btn-primary text-xs">Filtrer</button>
            </form>
        </div>
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100">
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Référence</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Type</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Partie</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Date</th>
                    <th class="text-center px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Articles</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Statut</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($returns as $r)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3 font-mono text-xs text-gray-700">{{ $r->reference }}</td>
                    <td class="px-5 py-3">
                        <span class="px-2 py-0.5 text-xs rounded-full {{ $r->type === 'client' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700' }}">
                            {{ $r->type === 'client' ? 'Client' : 'Fournisseur' }}
                        </span>
                    </td>
                    <td class="px-5 py-3 font-medium text-gray-800">{{ $r->client?->name ?? $r->supplier?->name ?? '—' }}</td>
                    <td class="px-5 py-3 text-gray-500">{{ $r->return_date->format('d/m/Y') }}</td>
                    <td class="px-5 py-3 text-center text-gray-700">{{ $r->items->count() }}</td>
                    <td class="px-5 py-3">
                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full
                            {{ $r->status === 'traite' ? 'bg-green-100 text-green-700' :
                               ($r->status === 'accepte' ? 'bg-blue-100 text-blue-700' : 'bg-yellow-100 text-yellow-700') }}">
                            {{ $r->status === 'traite' ? 'Traité' : ($r->status === 'accepte' ? 'Accepté' : 'En attente') }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-right">
                        <div class="flex items-center justify-end gap-2">
                            @if($r->status !== 'traite')
                            <form method="POST" action="{{ route('returns.process', $r) }}">
                                @csrf @method('PATCH')
                                <button class="text-xs text-green-600 hover:text-green-800 font-medium">Traiter</button>
                            </form>
                            @endif
                            <form method="POST" action="{{ route('returns.destroy', $r) }}" onsubmit="return confirm('Supprimer ?')">
                                @csrf @method('DELETE')
                                <button class="text-xs text-red-400 hover:text-red-600">Suppr.</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-5 py-12 text-center text-gray-400">Aucun retour</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-5 py-3">{{ $returns->links() }}</div>
    </div>
</div>
@endsection
