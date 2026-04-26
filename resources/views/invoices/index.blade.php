@extends('layouts.app')
@section('title', 'Devis & Factures')
@section('page-title', 'Devis & Factures')
@section('header-actions')
    <a href="{{ route('invoices.create') }}" class="btn-primary">+ Nouveau document</a>
@endsection

@section('content')
<div class="mt-2 space-y-4">
    <form method="GET" class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex flex-wrap gap-3">
        <select name="type" class="form-input w-36">
            <option value="">Tous types</option>
            <option value="devis" {{ request('type') === 'devis' ? 'selected' : '' }}>Devis</option>
            <option value="facture" {{ request('type') === 'facture' ? 'selected' : '' }}>Factures</option>
        </select>
        <select name="status" class="form-input w-40">
            <option value="">Tous statuts</option>
            <option value="brouillon" {{ request('status') === 'brouillon' ? 'selected' : '' }}>Brouillon</option>
            <option value="envoye" {{ request('status') === 'envoye' ? 'selected' : '' }}>Envoyé</option>
            <option value="paye" {{ request('status') === 'paye' ? 'selected' : '' }}>Payé</option>
            <option value="annule" {{ request('status') === 'annule' ? 'selected' : '' }}>Annulé</option>
        </select>
        <button type="submit" class="btn-primary">Filtrer</button>
        <a href="{{ route('invoices.index') }}" class="btn-secondary">Reset</a>
    </form>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100">
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Référence</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Type</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Client</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Date</th>
                    <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Total</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Statut</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($invoices as $invoice)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3 font-mono text-xs text-gray-500">{{ $invoice->reference }}</td>
                    <td class="px-5 py-3">
                        <span class="px-2 py-1 text-xs font-medium rounded-full {{ $invoice->type === 'facture' ? 'bg-purple-100 text-purple-700' : 'bg-indigo-100 text-indigo-700' }}">
                            @label($invoice->type)
                        </span>
                    </td>
                    <td class="px-5 py-3 font-medium text-gray-800">{{ $invoice->client->name }}</td>
                    <td class="px-5 py-3 text-gray-500">{{ $invoice->issue_date->format('d/m/Y') }}</td>
                    <td class="px-5 py-3 text-right font-semibold text-gray-800">{{ money($invoice->total) }}</td>
                    <td class="px-5 py-3">
                        <span class="px-2 py-1 text-xs font-medium rounded-full
                            {{ $invoice->status === 'paye' ? 'bg-green-100 text-green-700' :
                               ($invoice->status === 'envoye' ? 'bg-blue-100 text-blue-700' :
                               ($invoice->status === 'annule' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-600')) }}">
                            @label($invoice->status)
                        </span>
                    </td>
                    <td class="px-5 py-3 text-right whitespace-nowrap">
                        <a href="{{ route('invoices.show', $invoice) }}" class="text-blue-500 hover:text-blue-700 text-xs font-medium mr-3">Voir</a>
                        <form method="POST" action="{{ route('invoices.destroy', $invoice) }}" class="inline" onsubmit="return confirm('Supprimer ?')">
                            @csrf @method('DELETE')
                            <button class="text-red-500 hover:text-red-700 text-xs font-medium">Supprimer</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-5 py-10 text-center text-gray-400">Aucun document</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-5 py-3">{{ $invoices->links() }}</div>
    </div>
</div>
@endsection
