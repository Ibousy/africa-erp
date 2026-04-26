@extends('layouts.app')
@section('title', $invoice->reference)
@section('page-title', $invoice->reference)

@section('content')
<div class="mt-2 max-w-3xl">
    <!-- Status actions -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-4 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <span class="px-3 py-1 text-sm font-semibold rounded-full
                {{ $invoice->status === 'paye' ? 'bg-green-100 text-green-700' :
                   ($invoice->status === 'envoye' ? 'bg-blue-100 text-blue-700' :
                   ($invoice->status === 'annule' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-600')) }}">
                @label($invoice->status)
            </span>
            <span class="text-sm text-gray-500">@label($invoice->type)</span>
        </div>
        <div class="flex gap-2">
            @if($invoice->status === 'brouillon')
                <form method="POST" action="{{ route('invoices.status', $invoice) }}">
                    @csrf @method('PATCH')
                    <input type="hidden" name="status" value="envoye">
                    <button class="btn-primary text-xs">Marquer envoyé</button>
                </form>
            @endif
            @if($invoice->status === 'envoye')
                <form method="POST" action="{{ route('invoices.status', $invoice) }}">
                    @csrf @method('PATCH')
                    <input type="hidden" name="status" value="paye">
                    <button class="btn-primary text-xs bg-green-600 hover:bg-green-700">Marquer payé</button>
                </form>
            @endif
            @if(!in_array($invoice->status, ['paye', 'annule']))
                <form method="POST" action="{{ route('invoices.status', $invoice) }}" onsubmit="return confirm('Annuler ce document ?')">
                    @csrf @method('PATCH')
                    <input type="hidden" name="status" value="annule">
                    <button class="btn-secondary text-xs text-red-500 border-red-200">Annuler</button>
                </form>
            @endif
            <a href="{{ route('invoices.pdf', $invoice) }}" target="_blank" class="btn-secondary text-xs flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 10v6m0 0l-3-3m3 3l3-3M3 17v3a1 1 0 001 1h16a1 1 0 001-1v-3M3 7V4a1 1 0 011-1h4l2 3h9a1 1 0 011 1v3"/></svg>
                Télécharger PDF
            </a>
            <a href="{{ route('invoices.index') }}" class="btn-secondary text-xs">Retour</a>
        </div>
    </div>

    <!-- Document -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8">
        <!-- En-tête -->
        <div class="flex justify-between mb-8">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <div class="w-8 h-8 bg-orange-500 rounded-lg flex items-center justify-center font-bold text-white text-sm">A</div>
                    <span class="font-bold text-lg text-gray-800">AfricaERP</span>
                </div>
                <p class="text-sm text-gray-500">Dakar, Sénégal</p>
            </div>
            <div class="text-right">
                <h2 class="text-2xl font-bold text-gray-800 uppercase">{{ $invoice->type }}</h2>
                <p class="font-mono text-sm text-gray-500 mt-1">{{ $invoice->reference }}</p>
                <p class="text-sm text-gray-500">Émis le {{ $invoice->issue_date->format('d/m/Y') }}</p>
                @if($invoice->due_date)
                <p class="text-sm text-gray-500">Échéance: {{ $invoice->due_date->format('d/m/Y') }}</p>
                @endif
            </div>
        </div>

        <!-- Client -->
        <div class="bg-gray-50 rounded-lg p-4 mb-6">
            <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Facturé à</p>
            <p class="font-bold text-gray-800">{{ $invoice->client->name }}</p>
            @if($invoice->client->contact_person)<p class="text-sm text-gray-600">{{ $invoice->client->contact_person }}</p>@endif
            @if($invoice->client->phone)<p class="text-sm text-gray-600">{{ $invoice->client->phone }}</p>@endif
            @if($invoice->client->address)<p class="text-sm text-gray-600">{{ $invoice->client->address }}</p>@endif
            @if($invoice->client->city)<p class="text-sm text-gray-600">{{ $invoice->client->city }}, {{ $invoice->client->country }}</p>@endif
        </div>

        <!-- Lignes -->
        <table class="w-full text-sm mb-6">
            <thead>
                <tr class="border-b-2 border-gray-200">
                    <th class="text-left py-2 text-xs font-semibold text-gray-500 uppercase">Description</th>
                    <th class="text-right py-2 text-xs font-semibold text-gray-500 uppercase w-20">Qté</th>
                    <th class="text-right py-2 text-xs font-semibold text-gray-500 uppercase w-36">Prix unit.</th>
                    <th class="text-right py-2 text-xs font-semibold text-gray-500 uppercase w-36">Total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($invoice->items as $item)
                <tr>
                    <td class="py-3 text-gray-800">{{ $item->description }}</td>
                    <td class="py-3 text-right text-gray-600">{{ number_format($item->quantity, 2) }}</td>
                    <td class="py-3 text-right text-gray-600">{{ money($item->unit_price) }}</td>
                    <td class="py-3 text-right font-medium text-gray-800">{{ money($item->total) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totaux -->
        <div class="flex justify-end">
            <div class="w-72 space-y-2 text-sm border-t border-gray-200 pt-3">
                <div class="flex justify-between">
                    <span class="text-gray-500">Sous-total HT</span>
                    <span>{{ money($invoice->subtotal) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">TVA ({{ $invoice->tax_rate }}%)</span>
                    <span>{{ money($invoice->tax_amount) }}</span>
                </div>
                <div class="flex justify-between font-bold text-base border-t border-gray-200 pt-2">
                    <span>Total TTC</span>
                    <span class="text-orange-600">{{ money($invoice->total) }}</span>
                </div>
            </div>
        </div>

        @if($invoice->notes)
        <div class="mt-6 border-t border-gray-100 pt-4">
            <p class="text-xs text-gray-500 font-semibold uppercase mb-1">Notes</p>
            <p class="text-sm text-gray-600">{{ $invoice->notes }}</p>
        </div>
        @endif
    </div>
</div>
@endsection
