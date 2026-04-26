@extends('layouts.app')
@section('title', $order->reference)
@section('page-title', 'Commande ' . $order->reference)
@section('header-actions')
    <a href="{{ route('orders.index') }}" class="btn-secondary">← Retour</a>
@endsection

@section('content')
<div class="mt-2 grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="space-y-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h3 class="font-semibold text-gray-800 mb-4">Informations</h3>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-gray-500">Référence</dt><dd class="font-mono font-medium">{{ $order->reference }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Client</dt><dd class="font-medium text-gray-800">{{ $order->client->name }}</dd></div>
                @if($order->client->phone)<div class="flex justify-between"><dt class="text-gray-500">Tél.</dt><dd class="text-gray-600">{{ $order->client->phone }}</dd></div>@endif
                <div class="flex justify-between"><dt class="text-gray-500">Commande</dt><dd>{{ $order->order_date->format('d/m/Y') }}</dd></div>
                @if($order->delivery_date)<div class="flex justify-between"><dt class="text-gray-500">Livraison</dt><dd>{{ $order->delivery_date->format('d/m/Y') }}</dd></div>@endif
                @if($order->delivery_address)<div class="flex justify-between"><dt class="text-gray-500">Adresse</dt><dd class="text-xs text-gray-600">{{ $order->delivery_address }}</dd></div>@endif
                @if($order->quote)<div class="flex justify-between"><dt class="text-gray-500">Devis lié</dt><dd><a href="{{ route('quotes.show', $order->quote) }}" class="text-blue-500 text-xs">{{ $order->quote->reference }}</a></dd></div>@endif
                <div class="flex justify-between pt-2 border-t border-gray-100 font-bold text-base">
                    <dt>Total</dt><dd>{{ money($order->total_amount) }}</dd>
                </div>
            </dl>
        </div>

        <!-- Statut -->
        @if($order->status !== 'annule')
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <form method="POST" action="{{ route('orders.status', $order) }}" class="space-y-3">
                @csrf @method('PATCH')
                <label class="form-label">Statut commande</label>
                <select name="status" class="form-input">
                    @foreach(['nouveau' => 'Nouveau', 'confirme' => 'Confirmé', 'en_preparation' => 'En préparation', 'livre' => 'Livré ✓', 'annule' => 'Annulé'] as $k => $v)
                    <option value="{{ $k }}" {{ $order->status === $k ? 'selected' : '' }}>{{ $v }}</option>
                    @endforeach
                </select>
                <p class="text-xs text-amber-600">Passer à <strong>Livré</strong> déduira les articles du stock.</p>
                <button type="submit" class="btn-primary w-full text-sm">Mettre à jour</button>
            </form>
        </div>
        @endif

        @if($order->status === 'livre')
        <div class="bg-green-50 border border-green-200 rounded-xl p-4 text-sm text-green-700 flex gap-2">
            <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            Commande livrée. Stock mis à jour automatiquement.
        </div>
        @endif
    </div>

    <!-- Lignes -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100">
            <div class="px-5 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-800">Articles commandés ({{ $order->items->count() }})</h3>
                @if($order->notes)<p class="text-xs text-gray-400 mt-0.5">{{ $order->notes }}</p>@endif
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
                    @foreach($order->items as $item)
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-3 font-medium text-gray-800">{{ $item->description }}</td>
                        <td class="px-5 py-3 text-xs text-gray-500">
                            @if($item->product)<span class="bg-purple-100 text-purple-700 px-1.5 py-0.5 rounded text-xs">{{ $item->product->name }}</span>@else <span class="text-gray-400">—</span>@endif
                        </td>
                        <td class="px-5 py-3 text-right">{{ rtrim(rtrim(number_format($item->quantity, 4), '0'), '.') }}</td>
                        <td class="px-5 py-3 text-right text-gray-700">{{ money($item->unit_price) }}</td>
                        <td class="px-5 py-3 text-right font-semibold text-gray-900">{{ money($item->total) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="border-t-2 border-gray-200 bg-gray-50">
                        <td colspan="4" class="px-5 py-3 text-right font-bold text-gray-700">Total :</td>
                        <td class="px-5 py-3 text-right font-bold text-lg text-gray-900">{{ money($order->total_amount) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
