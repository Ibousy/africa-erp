@extends('layouts.app')
@section('title', 'MRP — Planification des besoins')
@section('page-title', 'MRP — Planification des besoins en matières')
@section('header-actions')
    <div class="flex gap-2">
        <form method="POST" action="{{ route('mrp.recalculate') }}">
            @csrf
            <button type="submit" class="btn-secondary text-xs px-4 py-2" title="Met à jour le stock disponible depuis l'inventaire actuel">
                ↻ Recalculer depuis le stock
            </button>
        </form>
        <a href="{{ route('mrp.create') }}" class="btn-primary">+ Nouveau plan</a>
    </div>
@endsection

@section('content')
<div class="mt-2 space-y-4">
    <!-- Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs text-gray-500 uppercase font-semibold">Total plans</p>
            <p class="text-2xl font-bold text-gray-800 mt-1">{{ $stats['total'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs text-gray-500 uppercase font-semibold">Ouverts</p>
            <p class="text-2xl font-bold mt-1" style="color: var(--clr-p)">{{ $stats['ouvert'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs text-gray-500 uppercase font-semibold">Confirmés</p>
            <p class="text-2xl font-bold text-blue-600 mt-1">{{ $stats['confirme'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs text-gray-500 uppercase font-semibold">Manques détectés</p>
            <p class="text-2xl font-bold text-red-600 mt-1">{{ $stats['shortage'] }}</p>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-semibold text-gray-800 text-sm">Plans de besoins en matières</h3>
            <p class="text-xs text-gray-400">Le stock disponible est calculé automatiquement. Cliquez sur "Recalculer" pour mettre à jour.</p>
        </div>
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100">
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Produit</th>
                    <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Besoin</th>
                    <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Stock actuel</th>
                    <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Manque</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Date prévue</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Statut</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($plans as $plan)
                <tr class="hover:bg-gray-50 {{ $plan->shortage > 0 ? 'bg-red-50/30' : '' }}">
                    <td class="px-5 py-3">
                        <p class="font-medium text-gray-800">{{ $plan->product->name }}</p>
                        <p class="text-xs text-gray-400">{{ $plan->product->code }} · {{ $plan->product->unit }}</p>
                    </td>
                    <td class="px-5 py-3 text-right font-medium text-gray-700">{{ number_format($plan->quantity_needed, 2) }}</td>
                    <td class="px-5 py-3 text-right text-gray-600">{{ number_format($plan->quantity_available, 2) }}</td>
                    <td class="px-5 py-3 text-right">
                        @if($plan->shortage > 0)
                            <span class="font-bold text-red-600">−{{ number_format($plan->shortage, 2) }}</span>
                        @else
                            <span class="text-green-600 font-semibold text-xs px-2 py-0.5 bg-green-50 rounded-full">OK</span>
                        @endif
                    </td>
                    <td class="px-5 py-3 text-gray-600">{{ $plan->planned_date->format('d/m/Y') }}</td>
                    <td class="px-5 py-3">
                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full
                            {{ $plan->status === 'ouvert' ? 'bg-yellow-100 text-yellow-700' :
                               ($plan->status === 'confirme' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-500') }}">
                            @label($plan->status)
                        </span>
                    </td>
                    <td class="px-5 py-3 text-right">
                        <div class="flex items-center justify-end gap-3">
                            <a href="{{ route('mrp.edit', $plan) }}" class="text-xs text-gray-500 hover:text-gray-700">Modifier</a>
                            <form method="POST" action="{{ route('mrp.destroy', $plan) }}" onsubmit="return confirm('Supprimer ce plan ?')">
                                @csrf @method('DELETE')
                                <button class="text-red-400 hover:text-red-600 text-xs">Suppr.</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-5 py-12 text-center text-gray-400">
                    <p class="text-lg mb-1">Aucun plan MRP</p>
                    <p class="text-xs">Créez votre premier plan de besoin en matières.</p>
                </td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-5 py-3">{{ $plans->links() }}</div>
    </div>
</div>
@endsection
