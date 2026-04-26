@extends('layouts.app')
@section('title', $quote->reference)
@section('page-title', 'Devis ' . $quote->reference)
@section('header-actions')
    <a href="{{ route('quotes.pdf', $quote) }}" target="_blank" class="btn-secondary flex items-center gap-1">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 10v6m0 0l-3-3m3 3l3-3M3 17v3a1 1 0 001 1h16a1 1 0 001-1v-3M3 7V4a1 1 0 011-1h4l2 3h9a1 1 0 011 1v3"/></svg>
        PDF
    </a>
    <a href="{{ route('quotes.index') }}" class="btn-secondary">← Retour</a>
@endsection

@section('content')
<div class="mt-2 grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Infos -->
    <div class="space-y-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h3 class="font-semibold text-gray-800 mb-4">Informations</h3>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-gray-500">Référence</dt><dd class="font-mono font-medium">{{ $quote->reference }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Client</dt><dd class="font-medium text-gray-800">{{ $quote->client->name }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Émis le</dt><dd>{{ $quote->issue_date->format('d/m/Y') }}</dd></div>
                @if($quote->valid_until)
                <div class="flex justify-between">
                    <dt class="text-gray-500">Valide jusqu'au</dt>
                    <dd class="{{ $quote->valid_until->isPast() ? 'text-red-500' : 'text-gray-700' }}">{{ $quote->valid_until->format('d/m/Y') }}</dd>
                </div>
                @endif
                <div class="flex justify-between pt-2 border-t border-gray-100">
                    <dt class="text-gray-500">Statut</dt>
                    <dd>
                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full
                            {{ $quote->status === 'accepte' ? 'bg-green-100 text-green-700' :
                               ($quote->status === 'envoye' ? 'bg-yellow-100 text-yellow-700' :
                               ($quote->status === 'refuse' || $quote->status === 'expire' ? 'bg-red-100 text-red-600' : 'bg-gray-100 text-gray-500')) }}">
                            {{ ['brouillon' => 'Brouillon', 'envoye' => 'Envoyé', 'accepte' => 'Accepté', 'refuse' => 'Refusé', 'expire' => 'Expiré'][$quote->status] ?? $quote->status }}
                        </span>
                    </dd>
                </div>
            </dl>
        </div>

        <!-- Actions -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 space-y-3">
            <h3 class="font-semibold text-gray-800 text-sm">Actions</h3>
            <form method="POST" action="{{ route('quotes.status', $quote) }}" class="space-y-2">
                @csrf @method('PATCH')
                <select name="status" class="form-input text-sm">
                    @foreach(['brouillon' => 'Brouillon', 'envoye' => 'Envoyé', 'accepte' => 'Accepté', 'refuse' => 'Refusé', 'expire' => 'Expiré'] as $k => $v)
                    <option value="{{ $k }}" {{ $quote->status === $k ? 'selected' : '' }}>{{ $v }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn-primary w-full text-sm">Mettre à jour statut</button>
            </form>

            @if($quote->status !== 'refuse' && $quote->status !== 'expire')
            <form method="POST" action="{{ route('quotes.convert', $quote) }}">
                @csrf
                <button type="submit" class="btn-secondary w-full text-sm" onclick="return confirm('Convertir en commande client ?')">
                    Convertir en commande →
                </button>
            </form>
            @endif
        </div>

        <!-- Totaux -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-gray-500">Sous-total HT</dt><dd>{{ money($quote->subtotal) }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">TVA ({{ $quote->tax_rate }}%)</dt><dd>{{ money($quote->tax_amount) }}</dd></div>
                <div class="flex justify-between pt-2 border-t border-gray-200 font-bold text-base">
                    <dt>Total TTC</dt><dd>{{ money($quote->total) }}</dd>
                </div>
            </dl>
        </div>
    </div>

    <!-- Lignes -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100">
            <div class="px-5 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-800">Lignes du devis</h3>
                @if($quote->notes)<p class="text-xs text-gray-400 mt-1">{{ $quote->notes }}</p>@endif
            </div>
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50">
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500">Description</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500">Produit</th>
                        <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500">Qté</th>
                        <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500">Prix unit.</th>
                        <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500">Remise</th>
                        <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500">Total HT</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($quote->items as $item)
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-3 font-medium text-gray-800">{{ $item->description }}</td>
                        <td class="px-5 py-3 text-xs text-gray-500">{{ $item->product?->name ?? '—' }}</td>
                        <td class="px-5 py-3 text-right">{{ rtrim(rtrim(number_format($item->quantity, 4), '0'), '.') }}</td>
                        <td class="px-5 py-3 text-right">{{ money($item->unit_price) }}</td>
                        <td class="px-5 py-3 text-right text-gray-500">{{ $item->discount_pct > 0 ? $item->discount_pct . '%' : '—' }}</td>
                        <td class="px-5 py-3 text-right font-semibold text-gray-900">{{ money($item->total) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
