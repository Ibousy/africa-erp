@extends('layouts.app')
@section('title', 'Achats & Fournisseurs')
@section('page-title', 'Achats & Approvisionnement')
@section('header-actions')
    <a href="{{ route('purchases.create') }}" class="btn-primary">+ Nouvelle commande</a>
@endsection

@section('content')
<div class="mt-2 space-y-4">
    <!-- Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs text-gray-500 uppercase font-semibold">Total commandes</p>
            <p class="text-2xl font-bold text-gray-800 mt-1">{{ $stats['total'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs text-gray-500 uppercase font-semibold">Brouillons</p>
            <p class="text-2xl font-bold text-gray-400 mt-1">{{ $stats['brouillon'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs text-gray-500 uppercase font-semibold">En cours</p>
            <p class="text-2xl font-bold mt-1" style="color: var(--clr-p)">{{ $stats['en_cours'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs text-gray-500 uppercase font-semibold">Reçues</p>
            <p class="text-2xl font-bold text-green-600 mt-1">{{ $stats['recu'] }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <!-- Fournisseurs -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-semibold text-gray-800 text-sm">Fournisseurs ({{ $suppliers->count() }})</h3>
            </div>
            <div class="divide-y divide-gray-50">
                @forelse($suppliers as $sup)
                <div class="px-5 py-3">
                    <p class="font-medium text-gray-800 text-sm">{{ $sup->name }}</p>
                    <p class="text-xs text-gray-400">{{ $sup->contact_name ?: '' }} {{ $sup->phone ? '· ' . $sup->phone : '' }}</p>
                </div>
                @empty
                <div class="px-5 py-6 text-center text-gray-400 text-sm">Aucun fournisseur</div>
                @endforelse
            </div>
            <!-- Ajout rapide fournisseur -->
            <div class="px-5 py-4 border-t border-gray-100 bg-gray-50 rounded-b-xl">
                <p class="text-xs font-semibold text-gray-600 mb-3">Ajouter un fournisseur</p>
                <form method="POST" action="{{ route('purchases.suppliers.store') }}" class="space-y-2">
                    @csrf
                    <input type="text" name="name" placeholder="Nom fournisseur *" required class="form-input text-xs">
                    <div class="grid grid-cols-2 gap-2">
                        <input type="text" name="phone" placeholder="Téléphone" class="form-input text-xs">
                        <input type="text" name="country" placeholder="Pays" class="form-input text-xs">
                    </div>
                    <button type="submit" class="btn-primary w-full text-xs py-2">Ajouter</button>
                </form>
            </div>
        </div>

        <!-- Commandes -->
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100">
            <div class="px-5 py-4 border-b border-gray-100 flex flex-wrap items-center justify-between gap-3">
                <h3 class="font-semibold text-gray-800 text-sm">Commandes fournisseurs</h3>
                <form method="GET" class="flex gap-2">
                    <select name="status" class="form-input text-xs w-36">
                        <option value="">Tous statuts</option>
                        <option value="brouillon" {{ request('status') === 'brouillon' ? 'selected' : '' }}>Brouillon</option>
                        <option value="envoye" {{ request('status') === 'envoye' ? 'selected' : '' }}>Envoyé</option>
                        <option value="confirme" {{ request('status') === 'confirme' ? 'selected' : '' }}>Confirmé</option>
                        <option value="recu" {{ request('status') === 'recu' ? 'selected' : '' }}>Reçu</option>
                        <option value="annule" {{ request('status') === 'annule' ? 'selected' : '' }}>Annulé</option>
                    </select>
                    <button type="submit" class="btn-primary text-xs">Filtrer</button>
                </form>
            </div>
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100">
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Référence</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Fournisseur</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Date</th>
                        <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Montant</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Statut</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($orders as $order)
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-3 font-medium text-gray-800">{{ $order->reference }}</td>
                        <td class="px-5 py-3 text-gray-600">{{ $order->supplier->name }}</td>
                        <td class="px-5 py-3 text-gray-500">{{ $order->order_date->format('d/m/Y') }}</td>
                        <td class="px-5 py-3 text-right font-medium text-gray-800">{{ money($order->total_amount) }}</td>
                        <td class="px-5 py-3">
                            <span class="px-2 py-0.5 text-xs font-semibold rounded-full
                                {{ $order->status === 'brouillon' ? 'bg-gray-100 text-gray-500' :
                                   ($order->status === 'envoye' ? 'bg-yellow-100 text-yellow-700' :
                                   ($order->status === 'confirme' ? 'bg-blue-100 text-blue-700' :
                                   ($order->status === 'recu' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600'))) }}">
                                @label($order->status)
                            </span>
                        </td>
                        <td class="px-5 py-3 text-right whitespace-nowrap">
                            <a href="{{ route('purchases.show', $order) }}" class="text-blue-500 hover:text-blue-700 text-xs mr-3">Voir</a>
                            <form method="POST" action="{{ route('purchases.destroy', $order) }}" class="inline" onsubmit="return confirm('Supprimer ?')">
                                @csrf @method('DELETE')
                                <button class="text-red-400 hover:text-red-600 text-xs">Suppr.</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-5 py-10 text-center text-gray-400">Aucune commande</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="px-5 py-3">{{ $orders->links() }}</div>
        </div>
    </div>
</div>
@endsection
