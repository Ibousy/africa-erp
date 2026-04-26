@extends('layouts.app')
@section('title', $purchase->reference)
@section('page-title', 'Commande ' . $purchase->reference)

@section('header-actions')
    <a href="{{ route('purchases.index') }}" class="btn-secondary">← Retour</a>
@endsection

@section('content')
<div class="mt-2 grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Infos principales -->
    <div class="lg:col-span-1 space-y-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h3 class="font-semibold text-gray-800 mb-4">Informations</h3>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <dt class="text-gray-500">Référence</dt>
                    <dd class="font-mono font-medium text-gray-800">{{ $purchase->reference }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Fournisseur</dt>
                    <dd class="font-medium text-gray-800">{{ $purchase->supplier->name }}</dd>
                </div>
                @if($purchase->supplier->contact_name)
                <div class="flex justify-between">
                    <dt class="text-gray-500">Contact</dt>
                    <dd class="text-gray-600">{{ $purchase->supplier->contact_name }}</dd>
                </div>
                @endif
                @if($purchase->supplier->phone)
                <div class="flex justify-between">
                    <dt class="text-gray-500">Téléphone</dt>
                    <dd class="text-gray-600">{{ $purchase->supplier->phone }}</dd>
                </div>
                @endif
                <div class="flex justify-between">
                    <dt class="text-gray-500">Date commande</dt>
                    <dd>{{ $purchase->order_date->format('d/m/Y') }}</dd>
                </div>
                @if($purchase->expected_date)
                <div class="flex justify-between">
                    <dt class="text-gray-500">Livraison prévue</dt>
                    <dd>{{ $purchase->expected_date->format('d/m/Y') }}</dd>
                </div>
                @endif
                <div class="flex justify-between items-center pt-2 border-t border-gray-100">
                    <dt class="text-gray-500">Statut</dt>
                    <dd>
                        <span class="px-2 py-1 text-xs font-semibold rounded-full
                            {{ $purchase->status === 'recu'    ? 'bg-green-100 text-green-700'  :
                               ($purchase->status === 'confirme' ? 'bg-blue-100 text-blue-700'   :
                               ($purchase->status === 'envoye'   ? 'bg-yellow-100 text-yellow-700':
                               ($purchase->status === 'annule'   ? 'bg-red-100 text-red-600'     : 'bg-gray-100 text-gray-500'))) }}">
                            @label($purchase->status)
                        </span>
                    </dd>
                </div>
                <div class="flex justify-between pt-2 border-t border-gray-100">
                    <dt class="text-gray-500 font-semibold">Total</dt>
                    <dd class="font-bold text-lg text-gray-900">{{ money($purchase->total_amount) }}</dd>
                </div>
            </dl>
            @if($purchase->notes)
            <div class="mt-4 pt-4 border-t border-gray-100 text-sm text-gray-600">
                <p class="text-xs text-gray-400 font-semibold uppercase mb-1">Notes</p>
                {{ $purchase->notes }}
            </div>
            @endif
        </div>

        <!-- Changer le statut -->
        @if($purchase->status !== 'recu' && $purchase->status !== 'annule')
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h3 class="font-semibold text-gray-800 mb-3 text-sm">Mettre à jour le statut</h3>
            <form method="POST" action="{{ route('purchases.status', $purchase) }}" class="space-y-3">
                @csrf @method('PATCH')
                <select name="status" class="form-input">
                    @foreach(['brouillon' => 'Brouillon', 'envoye' => 'Envoyé', 'confirme' => 'Confirmé', 'recu' => 'Reçu ✓', 'annule' => 'Annulé'] as $k => $v)
                    <option value="{{ $k }}" {{ $purchase->status === $k ? 'selected' : '' }}>{{ $v }}</option>
                    @endforeach
                </select>
                <p class="text-xs text-amber-600">Passer à <strong>Reçu</strong> mettra automatiquement les produits en stock.</p>
                <button type="submit" class="btn-primary w-full text-sm">Mettre à jour</button>
            </form>
        </div>
        @endif

        @if($purchase->status === 'recu')
        <div class="bg-green-50 border border-green-200 rounded-xl p-4 text-sm text-green-700 flex gap-2">
            <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            Commande reçue. Les produits liés ont été ajoutés au stock.
        </div>
        @endif
    </div>

    <!-- Lignes de commande -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100">
            <div class="px-5 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-800">Lignes de commande ({{ $purchase->items->count() }})</h3>
            </div>
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50">
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500">Description</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500">Produit lié</th>
                        <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500">Qté</th>
                        <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500">Prix unit.</th>
                        <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($purchase->items as $item)
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-3 font-medium text-gray-800">{{ $item->description }}</td>
                        <td class="px-5 py-3 text-gray-500 text-xs">
                            @if($item->product)
                            <span class="bg-purple-100 text-purple-700 px-1.5 py-0.5 rounded text-xs">{{ $item->product->name }}</span>
                            @else
                            <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-right text-gray-700">{{ rtrim(rtrim(number_format($item->quantity, 4), '0'), '.') }}</td>
                        <td class="px-5 py-3 text-right text-gray-700">{{ money($item->unit_price) }}</td>
                        <td class="px-5 py-3 text-right font-semibold text-gray-900">{{ money($item->total) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-5 py-8 text-center text-gray-400">Aucune ligne</td></tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="border-t-2 border-gray-200 bg-gray-50">
                        <td colspan="4" class="px-5 py-3 text-right font-bold text-gray-700">Total :</td>
                        <td class="px-5 py-3 text-right font-bold text-lg text-gray-900">{{ money($purchase->total_amount) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
