@extends('layouts.app')
@section('title', 'Ordres de fabrication')
@section('page-title', 'Ordres de fabrication')
@section('header-actions')
    <a href="{{ route('production.create') }}" class="btn-primary">+ Nouvel ordre</a>
@endsection

@section('content')
<div class="mt-2 space-y-4">
    <form method="GET" class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex gap-3">
        <select name="status" class="form-input w-44">
            <option value="">Tous statuts</option>
            <option value="brouillon" {{ request('status') === 'brouillon' ? 'selected' : '' }}>Brouillon</option>
            <option value="en_cours" {{ request('status') === 'en_cours' ? 'selected' : '' }}>En cours</option>
            <option value="termine" {{ request('status') === 'termine' ? 'selected' : '' }}>Terminé</option>
            <option value="annule" {{ request('status') === 'annule' ? 'selected' : '' }}>Annulé</option>
        </select>
        <button type="submit" class="btn-primary">Filtrer</button>
        <a href="{{ route('production.index') }}" class="btn-secondary">Reset</a>
    </form>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100">
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Référence</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Produit</th>
                    <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Planifié</th>
                    <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Produit</th>
                    <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Rendement</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Dates</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Statut</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($orders as $order)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3 font-mono text-xs text-gray-500">{{ $order->reference }}</td>
                    <td class="px-5 py-3 font-medium text-gray-800">{{ $order->product->name }}</td>
                    <td class="px-5 py-3 text-right text-gray-600">{{ number_format($order->quantity_planned, 2) }}</td>
                    <td class="px-5 py-3 text-right text-gray-600">{{ number_format($order->quantity_produced, 2) }}</td>
                    <td class="px-5 py-3 text-right">
                        <span class="font-semibold {{ $order->rendement >= 90 ? 'text-green-600' : ($order->rendement >= 70 ? 'text-yellow-600' : 'text-red-600') }}">
                            {{ $order->rendement }}%
                        </span>
                    </td>
                    <td class="px-5 py-3 text-xs text-gray-500">
                        {{ $order->start_date ? $order->start_date->format('d/m/Y') : '—' }}
                        @if($order->end_date) → {{ $order->end_date->format('d/m/Y') }} @endif
                    </td>
                    <td class="px-5 py-3">
                        <span class="px-2 py-1 text-xs font-medium rounded-full
                            {{ $order->status === 'termine' ? 'bg-green-100 text-green-700' :
                               ($order->status === 'en_cours' ? 'bg-blue-100 text-blue-700' :
                               ($order->status === 'annule' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-600')) }}">
                            @label($order->status)
                        </span>
                    </td>
                    <td class="px-5 py-3 text-right whitespace-nowrap">
                        <a href="{{ route('production.show', $order) }}" class="text-blue-500 hover:text-blue-700 text-xs font-medium mr-3">Détails</a>
                        <form method="POST" action="{{ route('production.destroy', $order) }}" class="inline" onsubmit="return confirm('Supprimer cet ordre ?')">
                            @csrf @method('DELETE')
                            <button class="text-red-500 hover:text-red-700 text-xs font-medium">Supprimer</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="px-5 py-10 text-center text-gray-400">Aucun ordre de fabrication</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-5 py-3">{{ $orders->links() }}</div>
    </div>
</div>
@endsection
