@extends('layouts.app')
@section('title', 'Contrôle Qualité')
@section('page-title', 'Contrôle Qualité')
@section('header-actions')
    <a href="{{ route('quality.create') }}" class="btn-primary">+ Nouveau contrôle</a>
@endsection

@section('content')
<div class="mt-2 space-y-4">
    <!-- Stats -->
    <div class="grid grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 text-center">
            <p class="text-2xl font-bold text-gray-800">{{ $stats['total'] }}</p>
            <p class="text-xs text-gray-500 mt-1">Total contrôles</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 text-center">
            <p class="text-2xl font-bold text-green-600">{{ $stats['passed'] }}</p>
            <p class="text-xs text-gray-500 mt-1">Réussis</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 text-center">
            <p class="text-2xl font-bold text-red-600">{{ $stats['failed'] }}</p>
            <p class="text-xs text-gray-500 mt-1">Échoués</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 text-center">
            <p class="text-2xl font-bold text-orange-600">{{ $stats['defects_total'] }}</p>
            <p class="text-xs text-gray-500 mt-1">Défauts enregistrés</p>
        </div>
    </div>

    <form method="GET" class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex gap-3">
        <select name="status" class="form-input w-36">
            <option value="">Tous statuts</option>
            <option value="passe" {{ request('status') === 'passe' ? 'selected' : '' }}>Réussi</option>
            <option value="echoue" {{ request('status') === 'echoue' ? 'selected' : '' }}>Échoué</option>
        </select>
        <button type="submit" class="btn-primary">Filtrer</button>
        <a href="{{ route('quality.index') }}" class="btn-secondary">Reset</a>
    </form>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100">
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Date</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Produit</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Ordre fabrication</th>
                    <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Vérifiés</th>
                    <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Défectueux</th>
                    <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Taux défaut</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Contrôleur</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Résultat</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($controls as $qc)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3 text-gray-500">{{ $qc->check_date->format('d/m/Y') }}</td>
                    <td class="px-5 py-3 font-medium text-gray-800">{{ $qc->product->name }}</td>
                    <td class="px-5 py-3 text-gray-500 font-mono text-xs">{{ $qc->productionOrder?->reference ?? '—' }}</td>
                    <td class="px-5 py-3 text-right text-gray-600">{{ number_format($qc->quantity_checked, 2) }}</td>
                    <td class="px-5 py-3 text-right {{ $qc->quantity_defective > 0 ? 'text-red-600 font-semibold' : 'text-gray-600' }}">{{ number_format($qc->quantity_defective, 2) }}</td>
                    <td class="px-5 py-3 text-right font-semibold {{ $qc->taux_defaut > 5 ? 'text-red-600' : 'text-green-600' }}">{{ $qc->taux_defaut }}%</td>
                    <td class="px-5 py-3 text-gray-500">{{ $qc->checker?->name ?? '—' }}</td>
                    <td class="px-5 py-3">
                        <span class="px-2 py-1 text-xs font-medium rounded-full {{ $qc->status === 'passe' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                            {{ $qc->status === 'passe' ? 'Passé' : 'Échoué' }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-right">
                        <div class="flex items-center justify-end gap-3">
                            <a href="{{ route('quality.show', $qc) }}" class="text-xs text-blue-500 hover:text-blue-700">Détail</a>
                            <a href="{{ route('quality.edit', $qc) }}" class="text-xs text-gray-500 hover:text-gray-700">Modifier</a>
                            <form method="POST" action="{{ route('quality.destroy', $qc) }}" onsubmit="return confirm('Supprimer ce contrôle ?')">
                                @csrf @method('DELETE')
                                <button class="text-xs text-red-400 hover:text-red-600">Suppr.</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" class="px-5 py-10 text-center text-gray-400">Aucun contrôle</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-5 py-3">{{ $controls->links() }}</div>
    </div>
</div>
@endsection
