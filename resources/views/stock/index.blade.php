@extends('layouts.app')
@section('title', 'Mouvements de stock')
@section('page-title', 'Mouvements de stock')
@section('header-actions')
    <a href="{{ route('stock.create') }}" class="btn-primary">+ Nouveau mouvement</a>
@endsection

@section('content')
<div class="mt-2 space-y-4">
    <form method="GET" class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex flex-wrap gap-3">
        <select name="type" class="form-input w-36">
            <option value="">Tous types</option>
            <option value="entree" {{ request('type') === 'entree' ? 'selected' : '' }}>Entrées</option>
            <option value="sortie" {{ request('type') === 'sortie' ? 'selected' : '' }}>Sorties</option>
        </select>
        <select name="product_id" class="form-input w-52">
            <option value="">Tous produits</option>
            @foreach($products as $p)
                <option value="{{ $p->id }}" {{ request('product_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn-primary">Filtrer</button>
        <a href="{{ route('stock.index') }}" class="btn-secondary">Reset</a>
    </form>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100">
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Date</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Produit</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Type</th>
                    <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Quantité</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Référence</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Motif</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Utilisateur</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($movements as $m)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3 text-gray-500">{{ $m->created_at->format('d/m/Y H:i') }}</td>
                    <td class="px-5 py-3 font-medium text-gray-800">{{ $m->product->name }}</td>
                    <td class="px-5 py-3">
                        <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $m->type === 'entree' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                            {{ $m->type === 'entree' ? '+ Entrée' : '- Sortie' }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-right font-semibold {{ $m->type === 'entree' ? 'text-green-600' : 'text-red-600' }}">
                        {{ $m->type === 'entree' ? '+' : '-' }}{{ number_format($m->quantity, 2) }} {{ $m->product->unit }}
                    </td>
                    <td class="px-5 py-3 text-gray-500 font-mono text-xs">{{ $m->reference ?? '—' }}</td>
                    <td class="px-5 py-3 text-gray-600">{{ $m->reason ?? '—' }}</td>
                    <td class="px-5 py-3 text-gray-500">{{ $m->user->name ?? '—' }}</td>
                    <td class="px-5 py-3 text-right">
                        <div class="flex items-center justify-end gap-3">
                            <a href="{{ route('stock.edit', $m) }}" class="text-xs text-gray-500 hover:text-gray-700">Modifier</a>
                            <form method="POST" action="{{ route('stock.destroy', $m) }}" onsubmit="return confirm('Supprimer ce mouvement ? Le stock sera corrigé automatiquement.')">
                                @csrf @method('DELETE')
                                <button class="text-xs text-red-400 hover:text-red-600">Suppr.</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="px-5 py-10 text-center text-gray-400">Aucun mouvement</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-5 py-3">{{ $movements->links() }}</div>
    </div>
</div>
@endsection
