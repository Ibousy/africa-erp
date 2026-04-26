@extends('layouts.app')
@section('title', $materialRequest->reference)
@section('page-title', 'Demande ' . $materialRequest->reference)
@section('header-actions')
    <a href="{{ route('material-requests.index') }}" class="btn-secondary">← Retour</a>
@endsection

@section('content')
<div class="mt-2 max-w-2xl space-y-4">
    <!-- Infos -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-gray-800">Informations</h3>
            @php
                $badge = match($materialRequest->status) {
                    'approved' => 'bg-green-100 text-green-700',
                    'rejected' => 'bg-red-100 text-red-700',
                    'partial'  => 'bg-orange-100 text-orange-700',
                    default    => 'bg-yellow-100 text-yellow-700',
                };
                $label = match($materialRequest->status) {
                    'approved' => '✅ Approuvé', 'rejected' => '❌ Refusé',
                    'partial'  => '⚠️ Partiel',  default    => '⏳ En attente',
                };
            @endphp
            <span class="px-3 py-1 text-xs font-bold rounded-full {{ $badge }}">{{ $label }}</span>
        </div>
        <dl class="grid grid-cols-2 gap-3 text-sm">
            <div><dt class="text-gray-500">Référence</dt><dd class="font-mono font-bold text-gray-800">{{ $materialRequest->reference }}</dd></div>
            <div><dt class="text-gray-500">Demandé par</dt><dd class="font-medium text-gray-800">{{ $materialRequest->requester->name }}</dd></div>
            @if($materialRequest->productionOrder)
            <div><dt class="text-gray-500">Ordre de prod.</dt><dd class="font-mono text-gray-700">{{ $materialRequest->productionOrder->reference }}</dd></div>
            @endif
            <div><dt class="text-gray-500">Date demande</dt><dd class="text-gray-700">{{ $materialRequest->created_at->format('d/m/Y H:i') }}</dd></div>
            @if($materialRequest->responded_at)
            <div><dt class="text-gray-500">Répondu le</dt><dd class="text-gray-700">{{ $materialRequest->responded_at->format('d/m/Y H:i') }}</dd></div>
            <div><dt class="text-gray-500">Traité par</dt><dd class="text-gray-700">{{ $materialRequest->handler?->name ?? '—' }}</dd></div>
            @endif
        </dl>
        @if($materialRequest->notes)
        <div class="mt-3 pt-3 border-t border-gray-100">
            <p class="text-xs text-gray-500 font-semibold uppercase mb-1">Notes production</p>
            <p class="text-sm text-gray-700">{{ $materialRequest->notes }}</p>
        </div>
        @endif
        @if($materialRequest->logistics_notes)
        <div class="mt-3 pt-3 border-t border-gray-100">
            <p class="text-xs text-gray-500 font-semibold uppercase mb-1">Réponse logistique</p>
            <p class="text-sm text-gray-700">{{ $materialRequest->logistics_notes }}</p>
        </div>
        @endif
    </div>

    <!-- Articles -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="px-5 py-4 border-b border-gray-100">
            <h3 class="font-semibold text-gray-800">Articles demandés</h3>
        </div>
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50">
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Produit</th>
                    <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Demandé</th>
                    <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Disponible</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Statut</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Note logistique</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($materialRequest->items as $item)
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
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3 font-medium text-gray-800">{{ $item->product->name }}</td>
                    <td class="px-5 py-3 text-right text-gray-700">{{ number_format($item->quantity_needed, 2) }} {{ $item->product->unit }}</td>
                    <td class="px-5 py-3 text-right {{ $item->quantity_available !== null ? ($item->quantity_available >= $item->quantity_needed ? 'text-green-600' : 'text-orange-600') : 'text-gray-400' }} font-semibold">
                        {{ $item->quantity_available !== null ? number_format($item->quantity_available, 2) . ' ' . $item->product->unit : '—' }}
                    </td>
                    <td class="px-5 py-3"><span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $ib }}">{{ $il }}</span></td>
                    <td class="px-5 py-3 text-gray-500 text-xs">{{ $item->logistics_note ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
