@extends('layouts.app')
@section('title', 'Demandes matières')
@section('page-title', 'Mes demandes de matières')
@section('header-actions')
    <a href="{{ route('material-requests.create') }}" class="btn-primary">+ Nouvelle demande</a>
@endsection

@section('content')
<div class="mt-2 space-y-4">
    <!-- Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 text-center">
            <p class="text-2xl font-bold text-yellow-500">{{ $stats['pending'] }}</p>
            <p class="text-xs text-gray-500 mt-1">En attente</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 text-center">
            <p class="text-2xl font-bold text-green-600">{{ $stats['approved'] }}</p>
            <p class="text-xs text-gray-500 mt-1">Approuvées</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 text-center">
            <p class="text-2xl font-bold text-orange-500">{{ $stats['partial'] }}</p>
            <p class="text-xs text-gray-500 mt-1">Partielles</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 text-center">
            <p class="text-2xl font-bold text-red-500">{{ $stats['rejected'] }}</p>
            <p class="text-xs text-gray-500 mt-1">Refusées</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-semibold text-gray-800 text-sm">Historique des demandes</h3>
            <form method="GET" class="flex gap-2">
                <select name="status" class="form-input text-xs w-36">
                    <option value="">Tous statuts</option>
                    <option value="pending"  {{ request('status')==='pending'  ? 'selected':'' }}>En attente</option>
                    <option value="approved" {{ request('status')==='approved' ? 'selected':'' }}>Approuvé</option>
                    <option value="partial"  {{ request('status')==='partial'  ? 'selected':'' }}>Partiel</option>
                    <option value="rejected" {{ request('status')==='rejected' ? 'selected':'' }}>Refusé</option>
                </select>
                <button class="btn-primary text-xs">Filtrer</button>
            </form>
        </div>
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50">
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Réf.</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Ordre prod.</th>
                    <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Articles</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Statut</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Date</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Réponse</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($requests as $r)
                @php
                    $badge = match($r->status) {
                        'approved' => 'bg-green-100 text-green-700',
                        'rejected' => 'bg-red-100 text-red-700',
                        'partial'  => 'bg-orange-100 text-orange-700',
                        default    => 'bg-yellow-100 text-yellow-700',
                    };
                    $label = match($r->status) {
                        'approved' => '✅ Approuvé', 'rejected' => '❌ Refusé',
                        'partial'  => '⚠️ Partiel',  default    => '⏳ En attente',
                    };
                @endphp
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3 font-mono text-xs font-semibold text-gray-700">{{ $r->reference }}</td>
                    <td class="px-5 py-3 text-gray-600 text-xs">{{ $r->productionOrder?->reference ?? '—' }}</td>
                    <td class="px-5 py-3 text-right text-gray-600">{{ $r->items->count() }}</td>
                    <td class="px-5 py-3">
                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $badge }}">{{ $label }}</span>
                    </td>
                    <td class="px-5 py-3 text-gray-500 text-xs">{{ $r->created_at->format('d/m/Y H:i') }}</td>
                    <td class="px-5 py-3 text-gray-500 text-xs">{{ $r->responded_at ? $r->responded_at->format('d/m/Y') : '—' }}</td>
                    <td class="px-5 py-3 text-right">
                        <div class="flex items-center justify-end gap-3">
                            <a href="{{ route('material-requests.show', $r) }}" class="text-xs font-medium hover:underline" style="color:var(--clr-p)">Détail</a>
                            @if($r->status === 'pending')
                            <form method="POST" action="{{ route('material-requests.destroy', $r) }}" onsubmit="return confirm('Annuler cette demande ?')">
                                @csrf @method('DELETE')
                                <button class="text-xs text-red-400 hover:text-red-600">Annuler</button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-5 py-12 text-center text-gray-400">Aucune demande</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-5 py-3">{{ $requests->links() }}</div>
    </div>
</div>
@endsection
