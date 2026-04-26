@extends('layouts.app')
@section('title', 'Demandes matières — Logistique')
@section('page-title', 'Demandes de matières')
@section('header-actions')
    @if($pending > 0)
    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-yellow-100 text-yellow-800 text-xs font-bold rounded-full">
        ⏳ {{ $pending }} en attente
    </span>
    @endif
@endsection

@section('content')
<div class="mt-2 space-y-4">

    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-semibold text-gray-800 text-sm">Toutes les demandes de production</h3>
            <form method="GET" class="flex gap-2">
                <select name="status" class="form-input text-xs w-36">
                    <option value="">Tous statuts</option>
                    <option value="pending"  {{ request('status')==='pending'  ? 'selected':'' }}>En attente</option>
                    <option value="approved" {{ request('status')==='approved' ? 'selected':'' }}>Approuvé</option>
                    <option value="partial"  {{ request('status')==='partial'  ? 'selected':'' }}>Partiel</option>
                    <option value="rejected" {{ request('status')==='rejected' ? 'selected':'' }}>Refusé</option>
                </select>
                <button class="btn-primary text-xs">Filtrer</button>
            </form>
        </div>

        <div class="divide-y divide-gray-50">
            @forelse($requests as $r)
            @php
                $badge = match($r->status) {
                    'approved' => 'bg-green-100 text-green-700',
                    'rejected' => 'bg-red-100 text-red-700',
                    'partial'  => 'bg-orange-100 text-orange-700',
                    default    => 'bg-yellow-100 text-yellow-700',
                };
                $label = match($r->status) {
                    'approved' => '✅ Approuvé', 'rejected' => '❌ Refusé',
                    'partial'  => '⚠️ Partiel',  default    => '⏳ En attente',
                };
            @endphp

            <details class="group" {{ $r->status === 'pending' ? 'open' : '' }}>
                <summary class="p-5 flex items-start justify-between gap-4 cursor-pointer list-none hover:bg-gray-50 transition-colors">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-3 flex-wrap">
                            <span class="font-mono font-bold text-sm text-gray-800">{{ $r->reference }}</span>
                            <span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $badge }}">{{ $label }}</span>
                            @if($r->productionOrder)
                            <span class="text-xs text-gray-500">OF: <span class="font-mono text-gray-700">{{ $r->productionOrder->reference }}</span></span>
                            @endif
                        </div>
                        <p class="text-xs text-gray-500 mt-1">
                            Par <strong class="text-gray-700">{{ $r->requester->name }}</strong>
                            · {{ $r->created_at->format('d/m/Y H:i') }}
                            · {{ $r->items->count() }} article(s)
                        </p>
                        @if($r->notes)
                        <p class="text-xs text-gray-600 mt-1 italic">"{{ $r->notes }}"</p>
                        @endif
                    </div>
                    <span class="shrink-0 text-xs text-gray-400 mt-1 select-none">
                        <span class="group-open:hidden">▼ Ouvrir</span>
                        <span class="hidden group-open:inline">▲ Fermer</span>
                    </span>
                </summary>

                <div class="px-5 pb-5 space-y-4">
                    @if($r->status === 'pending')
                    <form method="POST" action="{{ route('material-requests.respond', $r) }}" class="space-y-4">
                        @csrf

                        <div class="overflow-x-auto rounded-lg border border-gray-100">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="bg-gray-50 border-b border-gray-100">
                                        <th class="text-left px-4 py-2 text-xs font-semibold text-gray-500 uppercase">Produit</th>
                                        <th class="text-right px-4 py-2 text-xs font-semibold text-gray-500 uppercase">Demandé</th>
                                        <th class="text-right px-4 py-2 text-xs font-semibold text-gray-500 uppercase">Stock actuel</th>
                                        <th class="px-4 py-2 text-xs font-semibold text-gray-500 uppercase">Statut</th>
                                        <th class="text-right px-4 py-2 text-xs font-semibold text-gray-500 uppercase">Qté disponible</th>
                                        <th class="px-4 py-2 text-xs font-semibold text-gray-500 uppercase">Note</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50">
                                    @foreach($r->items as $item)
                                    @php
                                        $autoStatus = $item->product->quantity_in_stock >= $item->quantity_needed
                                            ? 'available'
                                            : ($item->product->quantity_in_stock > 0 ? 'insufficient' : 'unavailable');
                                    @endphp
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 font-medium text-gray-800">
                                            {{ $item->product->name }}
                                            <span class="text-xs text-gray-400 ml-1">{{ $item->product->unit }}</span>
                                        </td>
                                        <td class="px-4 py-3 text-right font-semibold text-gray-700">
                                            {{ number_format($item->quantity_needed, 2) }}
                                        </td>
                                        <td class="px-4 py-3 text-right font-semibold {{ $item->product->quantity_in_stock >= $item->quantity_needed ? 'text-green-600' : 'text-orange-600' }}">
                                            {{ number_format($item->product->quantity_in_stock, 2) }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <select name="items[{{ $item->id }}][item_status]" required class="form-input text-xs py-1">
                                                <option value="available"    {{ $autoStatus === 'available'    ? 'selected' : '' }}>✅ Disponible</option>
                                                <option value="insufficient" {{ $autoStatus === 'insufficient' ? 'selected' : '' }}>⚠️ Insuffisant</option>
                                                <option value="unavailable"  {{ $autoStatus === 'unavailable'  ? 'selected' : '' }}>❌ Indisponible</option>
                                            </select>
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <input type="number"
                                                name="items[{{ $item->id }}][quantity_available]"
                                                value="{{ number_format($item->product->quantity_in_stock, 2, '.', '') }}"
                                                min="0" step="0.01"
                                                class="form-input text-xs py-1 w-24 text-right">
                                        </td>
                                        <td class="px-4 py-3">
                                            <input type="text"
                                                name="items[{{ $item->id }}][logistics_note]"
                                                placeholder="Note optionnelle…"
                                                class="form-input text-xs py-1 w-full">
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div>
                            <label class="form-label text-xs">Commentaire global (optionnel)</label>
                            <textarea name="logistics_notes" rows="2" class="form-input text-sm" placeholder="Message général pour la production…"></textarea>
                        </div>

                        <div class="flex gap-2">
                            <button type="submit" class="btn-primary text-sm px-5">Envoyer la réponse</button>
                        </div>
                    </form>

                    @else
                    <div class="overflow-x-auto rounded-lg border border-gray-100">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-100">
                                    <th class="text-left px-4 py-2 text-xs font-semibold text-gray-500 uppercase">Produit</th>
                                    <th class="text-right px-4 py-2 text-xs font-semibold text-gray-500 uppercase">Demandé</th>
                                    <th class="text-right px-4 py-2 text-xs font-semibold text-gray-500 uppercase">Disponible</th>
                                    <th class="px-4 py-2 text-xs font-semibold text-gray-500 uppercase">Statut</th>
                                    <th class="px-4 py-2 text-xs font-semibold text-gray-500 uppercase">Note</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @foreach($r->items as $item)
                                @php
                                    $ib = match($item->item_status) {
                                        'available'   => 'bg-green-100 text-green-700',
                                        'insufficient'=> 'bg-orange-100 text-orange-700',
                                        'unavailable' => 'bg-red-100 text-red-700',
                                        default       => 'bg-gray-100 text-gray-500',
                                    };
                                    $il = match($item->item_status) {
                                        'available'   => '✅ Disponible',
                                        'insufficient'=> '⚠️ Insuffisant',
                                        'unavailable' => '❌ Indisponible',
                                        default       => '⏳ En attente',
                                    };
                                @endphp
                                <tr>
                                    <td class="px-4 py-2.5 font-medium text-gray-800">{{ $item->product->name }}</td>
                                    <td class="px-4 py-2.5 text-right text-gray-700">{{ number_format($item->quantity_needed, 2) }} {{ $item->product->unit }}</td>
                                    <td class="px-4 py-2.5 text-right text-gray-700">
                                        {{ $item->quantity_available !== null ? number_format($item->quantity_available, 2) . ' ' . $item->product->unit : '—' }}
                                    </td>
                                    <td class="px-4 py-2.5"><span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $ib }}">{{ $il }}</span></td>
                                    <td class="px-4 py-2.5 text-xs text-gray-500">{{ $item->logistics_note ?? '—' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($r->logistics_notes)
                    <p class="text-sm text-gray-600 italic">"{{ $r->logistics_notes }}"</p>
                    @endif
                    @if($r->responded_at)
                    <p class="text-xs text-gray-400">Répondu le {{ $r->responded_at->format('d/m/Y H:i') }} par {{ $r->handler?->name ?? '—' }}</p>
                    @endif
                    @endif
                </div>
            </details>
            @empty
            <div class="py-16 text-center text-gray-400">
                <div class="text-4xl mb-3">📋</div>
                <p class="font-medium">Aucune demande</p>
            </div>
            @endforelse
        </div>

        <div class="px-5 py-3">{{ $requests->links() }}</div>
    </div>
</div>
@endsection
