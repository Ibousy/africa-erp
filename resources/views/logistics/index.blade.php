@extends('layouts.app')
@section('title', 'Logistique & Transport')
@section('page-title', 'Logistique & Transport')
@section('header-actions')
    <a href="{{ route('logistics.create') }}" class="btn-primary">+ Nouvelle expédition</a>
@endsection

@section('content')
<div class="mt-2 space-y-4">
    <!-- Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs text-gray-500 uppercase font-semibold">Total</p>
            <p class="text-2xl font-bold text-gray-800 mt-1">{{ $stats['total'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs text-gray-500 uppercase font-semibold">En attente</p>
            <p class="text-2xl font-bold text-yellow-600 mt-1">{{ $stats['en_attente'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs text-gray-500 uppercase font-semibold">En transit</p>
            <p class="text-2xl font-bold mt-1" style="color: var(--clr-p)">{{ $stats['en_transit'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs text-gray-500 uppercase font-semibold">Livrées</p>
            <p class="text-2xl font-bold text-green-600 mt-1">{{ $stats['livre'] }}</p>
        </div>
    </div>

    <!-- Filtres + Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="px-5 py-4 border-b border-gray-100 flex flex-wrap items-center justify-between gap-3">
            <h3 class="font-semibold text-gray-800 text-sm">Mouvements logistiques</h3>
            <form method="GET" class="flex gap-2">
                <select name="type" class="form-input text-xs w-36">
                    <option value="">Tous types</option>
                    <option value="entrant" {{ request('type') === 'entrant' ? 'selected' : '' }}>Entrant</option>
                    <option value="sortant" {{ request('type') === 'sortant' ? 'selected' : '' }}>Sortant</option>
                    <option value="retour"  {{ request('type') === 'retour'  ? 'selected' : '' }}>Retour</option>
                </select>
                <select name="status" class="form-input text-xs w-36">
                    <option value="">Tous statuts</option>
                    <option value="en_attente" {{ request('status') === 'en_attente' ? 'selected' : '' }}>En attente</option>
                    <option value="en_transit" {{ request('status') === 'en_transit' ? 'selected' : '' }}>En transit</option>
                    <option value="livre"      {{ request('status') === 'livre'      ? 'selected' : '' }}>Livré</option>
                    <option value="annule"     {{ request('status') === 'annule'     ? 'selected' : '' }}>Annulé</option>
                </select>
                <button type="submit" class="btn-primary text-xs">Filtrer</button>
            </form>
        </div>
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100">
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Réf / Type</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Produit</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Contact / Destination</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Départ</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Statut</th>
                    <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($shipments as $s)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3">
                        <p class="font-medium text-gray-800">{{ $s->reference }}</p>
                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full
                            {{ $s->type === 'entrant' ? 'bg-green-100 text-green-700' :
                               ($s->type === 'retour'  ? 'bg-yellow-100 text-yellow-700' : 'bg-blue-100 text-blue-700') }}">
                            @label($s->type)
                        </span>
                    </td>
                    <td class="px-5 py-3">
                        @if($s->product)
                            <p class="text-gray-800 font-medium text-xs">{{ $s->product->name }}</p>
                            <p class="text-gray-400 text-xs">{{ number_format($s->quantity, 2) }} {{ $s->product->unit }}</p>
                            @if($s->stock_processed)
                                <span class="text-xs text-green-600 font-semibold">✓ Stock mis à jour</span>
                            @endif
                        @else
                            <span class="text-gray-400 text-xs">—</span>
                        @endif
                    </td>
                    <td class="px-5 py-3">
                        <p class="text-gray-800">{{ $s->contact_name }}</p>
                        <p class="text-xs text-gray-400">{{ $s->origin_destination }}</p>
                    </td>
                    <td class="px-5 py-3 text-gray-500 text-xs">{{ $s->departure_date?->format('d/m/Y') ?: '—' }}</td>
                    <td class="px-5 py-3">
                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full
                            {{ $s->status === 'en_attente' ? 'bg-yellow-100 text-yellow-700' :
                               ($s->status === 'en_transit' ? 'bg-blue-100 text-blue-700' :
                               ($s->status === 'livre'      ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500')) }}">
                            @label($s->status)
                        </span>
                    </td>
                    <td class="px-5 py-3">
                        <div class="flex items-center justify-end gap-2">

                            {{-- Changer le statut --}}
                            @if($s->status !== 'livre' && $s->status !== 'annule')
                            <form method="POST" action="{{ route('logistics.status', $s) }}">
                                @csrf @method('PATCH')
                                <select name="status" onchange="this.form.submit()" class="text-xs border border-gray-200 rounded px-2 py-1 text-gray-600 cursor-pointer">
                                    <option value="{{ $s->status }}" selected disabled>Changer...</option>
                                    @if($s->status === 'en_attente')
                                    <option value="en_transit">→ En transit</option>
                                    @endif
                                    <option value="livre">→ Livré / Réceptionné</option>
                                    <option value="annule">→ Annuler</option>
                                </select>
                            </form>
                            @endif

                            {{-- Bon de document --}}
                            <a href="{{ route('logistics.document', $s) }}" target="_blank"
                                class="text-xs text-blue-500 hover:text-blue-700 whitespace-nowrap">📄 Bon</a>

                            {{-- Modifier --}}
                            <a href="{{ route('logistics.edit', $s) }}" class="text-xs text-gray-500 hover:text-gray-700">Modifier</a>

                            {{-- Supprimer --}}
                            <form method="POST" action="{{ route('logistics.destroy', $s) }}" onsubmit="return confirm('Supprimer ?')">
                                @csrf @method('DELETE')
                                <button class="text-red-400 hover:text-red-600 text-xs">Suppr.</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-5 py-12 text-center text-gray-400">Aucune expédition enregistrée</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-5 py-3">{{ $shipments->links() }}</div>
    </div>
</div>
@endsection
