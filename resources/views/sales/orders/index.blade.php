@extends('layouts.app')
@section('title', 'Commandes clients')
@section('page-title', 'Commandes clients')
@section('header-actions')
    <a href="{{ route('orders.create') }}" class="btn-primary">+ Nouvelle commande</a>
@endsection

@section('content')
<div class="mt-2 space-y-4">
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs text-gray-500 uppercase font-semibold">Total</p>
            <p class="text-2xl font-bold text-gray-800 mt-1">{{ $stats['total'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs text-gray-500 uppercase font-semibold">Nouvelles</p>
            <p class="text-2xl font-bold text-yellow-500 mt-1">{{ $stats['nouveau'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs text-gray-500 uppercase font-semibold">En cours</p>
            <p class="text-2xl font-bold mt-1" style="color:var(--clr-p)">{{ $stats['en_cours'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs text-gray-500 uppercase font-semibold">Livrées</p>
            <p class="text-2xl font-bold text-green-600 mt-1">{{ $stats['livre'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 col-span-2 md:col-span-1">
            <p class="text-xs text-gray-500 uppercase font-semibold">CA total</p>
            <p class="text-base font-bold text-gray-900 mt-1">{{ money($stats['ca']) }}</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="px-5 py-4 border-b border-gray-100 flex flex-wrap items-center justify-between gap-3">
            <h3 class="font-semibold text-gray-800 text-sm">Commandes</h3>
            <form method="GET" class="flex gap-2">
                <select name="client_id" class="form-input text-xs w-44">
                    <option value="">Tous clients</option>
                    @foreach($clients as $c)
                    <option value="{{ $c->id }}" {{ request('client_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
                <select name="status" class="form-input text-xs w-36">
                    <option value="">Tous statuts</option>
                    @foreach(['nouveau' => 'Nouveau', 'confirme' => 'Confirmé', 'en_preparation' => 'En préparation', 'livre' => 'Livré', 'annule' => 'Annulé'] as $k => $v)
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
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Livraison</th>
                    <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Montant</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Statut</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($orders as $o)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3 font-mono text-xs text-gray-700">{{ $o->reference }}</td>
                    <td class="px-5 py-3 font-medium text-gray-800">{{ $o->client->name }}</td>
                    <td class="px-5 py-3 text-gray-500">{{ $o->order_date->format('d/m/Y') }}</td>
                    <td class="px-5 py-3 text-gray-500 {{ $o->delivery_date && $o->delivery_date->isPast() && $o->status !== 'livre' ? 'text-red-500' : '' }}">
                        {{ $o->delivery_date ? $o->delivery_date->format('d/m/Y') : '—' }}
                    </td>
                    <td class="px-5 py-3 text-right font-semibold text-gray-800">{{ money($o->total_amount) }}</td>
                    <td class="px-5 py-3">
                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full
                            {{ $o->status === 'livre' ? 'bg-green-100 text-green-700' :
                               ($o->status === 'en_preparation' ? 'bg-blue-100 text-blue-700' :
                               ($o->status === 'confirme' ? 'bg-yellow-100 text-yellow-700' :
                               ($o->status === 'annule' ? 'bg-red-100 text-red-600' : 'bg-gray-100 text-gray-500'))) }}">
                            @label($o->status)
                        </span>
                    </td>
                    <td class="px-5 py-3 text-right whitespace-nowrap">
                        <a href="{{ route('orders.show', $o) }}" class="text-blue-500 hover:text-blue-700 text-xs mr-2">Voir</a>
                        <form method="POST" action="{{ route('orders.destroy', $o) }}" class="inline" onsubmit="return confirm('Supprimer ?')">
                            @csrf @method('DELETE')
                            <button class="text-red-400 hover:text-red-600 text-xs">Suppr.</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-5 py-12 text-center text-gray-400">Aucune commande</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-5 py-3">{{ $orders->links() }}</div>
    </div>
</div>
@endsection
