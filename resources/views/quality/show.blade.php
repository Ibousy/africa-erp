@extends('layouts.app')
@section('title', 'Contrôle qualité')
@section('page-title', 'Détail contrôle qualité')

@section('header-actions')
    <a href="{{ route('quality.edit', $quality) }}" class="btn-secondary">Modifier</a>
    <form method="POST" action="{{ route('quality.destroy', $quality) }}" onsubmit="return confirm('Supprimer ce contrôle ?')">
        @csrf @method('DELETE')
        <button class="btn-secondary text-red-500 border-red-200">Supprimer</button>
    </form>
    <a href="{{ route('quality.index') }}" class="btn-secondary">Retour</a>
@endsection

@section('content')
<div class="mt-2 max-w-2xl space-y-4">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between mb-5">
            <h3 class="font-semibold text-gray-800">Résultats du contrôle</h3>
            <span class="px-3 py-1 text-sm font-semibold rounded-full {{ $quality->status === 'passe' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                {{ $quality->status === 'passe' ? 'Passé' : 'Échoué' }}
            </span>
        </div>

        <dl class="grid grid-cols-2 gap-4 text-sm">
            <div>
                <dt class="text-gray-500 mb-1">Produit</dt>
                <dd class="font-medium text-gray-800">{{ $quality->product->name }}</dd>
            </div>
            <div>
                <dt class="text-gray-500 mb-1">Date de contrôle</dt>
                <dd class="font-medium text-gray-800">{{ $quality->check_date->format('d/m/Y') }}</dd>
            </div>
            <div>
                <dt class="text-gray-500 mb-1">Ordre de fabrication</dt>
                <dd class="font-medium text-gray-800">{{ $quality->productionOrder?->reference ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-gray-500 mb-1">Contrôleur</dt>
                <dd class="font-medium text-gray-800">{{ $quality->checker?->name ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-gray-500 mb-1">Quantité vérifiée</dt>
                <dd class="font-bold text-gray-800">{{ number_format($quality->quantity_checked, 2) }}</dd>
            </div>
            <div>
                <dt class="text-gray-500 mb-1">Quantité défectueuse</dt>
                <dd class="font-bold {{ $quality->quantity_defective > 0 ? 'text-red-600' : 'text-green-600' }}">{{ number_format($quality->quantity_defective, 2) }}</dd>
            </div>
            <div>
                <dt class="text-gray-500 mb-1">Taux de défaut</dt>
                <dd class="font-bold text-xl {{ $quality->taux_defaut > 5 ? 'text-red-600' : 'text-green-600' }}">{{ $quality->taux_defaut }}%</dd>
            </div>
        </dl>

        @if($quality->notes)
        <div class="mt-4 pt-4 border-t border-gray-100">
            <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Notes</p>
            <p class="text-sm text-gray-700">{{ $quality->notes }}</p>
        </div>
        @endif
    </div>

    @if($quality->defects->count() > 0)
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="px-5 py-4 border-b border-gray-100">
            <h3 class="font-semibold text-gray-800">Défauts détectés ({{ $quality->defects->count() }})</h3>
        </div>
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100">
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Type</th>
                    <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Quantité</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Gravité</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Description</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($quality->defects as $defect)
                <tr>
                    <td class="px-5 py-3 font-medium text-gray-800">{{ $defect->type }}</td>
                    <td class="px-5 py-3 text-right text-gray-600">{{ $defect->quantity }}</td>
                    <td class="px-5 py-3">
                        <span class="px-2 py-1 text-xs font-medium rounded-full
                            {{ $defect->severity === 'grave' ? 'bg-red-100 text-red-700' :
                               ($defect->severity === 'moyen' ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700') }}">
                            @label($defect->severity)
                        </span>
                    </td>
                    <td class="px-5 py-3 text-gray-600">{{ $defect->description ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>
@endsection
