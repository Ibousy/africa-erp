@extends('layouts.app')
@section('title', 'Stock — Matières & Produits')
@section('page-title', 'Stock Matières premières & Produits finis')
@section('header-actions')
    <a href="{{ route('stock.create') }}" class="btn-secondary text-sm">+ Mouvement de stock</a>
    <a href="{{ route('logistics.index') }}" class="btn-secondary text-sm">← Expéditions</a>
@endsection

@section('content')
<div class="mt-2 space-y-4">
    <!-- Stats -->
    <div class="grid grid-cols-3 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs text-gray-500 uppercase font-semibold">Produits actifs</p>
            <p class="text-2xl font-bold text-gray-800 mt-1">{{ $stats['total_products'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs text-gray-500 uppercase font-semibold">Alertes stock bas</p>
            <p class="text-2xl font-bold {{ $stats['low_stock'] > 0 ? 'text-red-600' : 'text-green-600' }} mt-1">{{ $stats['low_stock'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs text-gray-500 uppercase font-semibold">Valeur totale stock</p>
            <p class="text-2xl font-bold mt-1" style="color:var(--clr-p)">{{ money($stats['total_value'], false) }}<span class="text-sm font-normal">FCFA</span></p>
        </div>
    </div>

    <!-- Filtres -->
    <form method="GET" class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex flex-wrap gap-3 items-end">
        <div>
            <label class="form-label text-xs">Catégorie</label>
            <select name="category" class="form-input text-sm w-48">
                <option value="">Toutes catégories</option>
                @foreach($categories as $cat)
                <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-end gap-2">
            <label class="flex items-center gap-2 text-sm text-gray-600 pb-2.5">
                <input type="checkbox" name="alert" value="1" {{ request('alert') === '1' ? 'checked' : '' }} class="rounded">
                Alerte stock bas seulement
            </label>
        </div>
        <button type="submit" class="btn-primary text-sm">Filtrer</button>
        <a href="{{ route('logistics.stock') }}" class="btn-secondary text-sm">Reset</a>
    </form>

    <!-- Tableau stock -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50">
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Produit</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Catégorie</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Code</th>
                    <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Stock actuel</th>
                    <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Seuil alerte</th>
                    <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Prix unit.</th>
                    <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Valeur stock</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">État</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($products as $p)
                @php $isLow = $p->min_stock_alert > 0 && $p->quantity_in_stock <= $p->min_stock_alert; @endphp
                <tr class="hover:bg-gray-50 {{ $isLow ? 'bg-red-50/30' : '' }}">
                    <td class="px-5 py-3">
                        <p class="font-medium text-gray-800">{{ $p->name }}</p>
                        @if($p->description)
                        <p class="text-xs text-gray-400 truncate max-w-xs">{{ $p->description }}</p>
                        @endif
                    </td>
                    <td class="px-5 py-3">
                        <span class="px-2 py-0.5 text-xs font-medium bg-gray-100 text-gray-600 rounded-full">{{ $p->category ?: '—' }}</span>
                    </td>
                    <td class="px-5 py-3 font-mono text-xs text-gray-500">{{ $p->code }}</td>
                    <td class="px-5 py-3 text-right">
                        <span class="font-bold text-lg {{ $isLow ? 'text-red-600' : 'text-gray-800' }}">{{ number_format($p->quantity_in_stock, 2) }}</span>
                        <span class="text-xs text-gray-400 ml-1">{{ $p->unit }}</span>
                    </td>
                    <td class="px-5 py-3 text-right text-gray-500 text-sm">
                        {{ $p->min_stock_alert > 0 ? number_format($p->min_stock_alert, 2) . ' ' . $p->unit : '—' }}
                    </td>
                    <td class="px-5 py-3 text-right text-gray-600">{{ money($p->unit_price) }}</td>
                    <td class="px-5 py-3 text-right font-semibold text-gray-700">{{ money($p->quantity_in_stock * $p->unit_price) }}</td>
                    <td class="px-5 py-3">
                        @if($isLow)
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-700">⚠ Stock bas</span>
                        @elseif($p->quantity_in_stock <= 0)
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-500">Rupture</span>
                        @else
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-700">OK</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="px-5 py-12 text-center text-gray-400">Aucun produit en stock</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Lien vers mouvements de stock -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex items-center justify-between">
        <div>
            <p class="text-sm font-semibold text-gray-800">Mouvements de stock & historique</p>
            <p class="text-xs text-gray-500 mt-0.5">Consultez l'historique complet des entrées et sorties de stock</p>
        </div>
        <a href="{{ route('stock.index') }}" class="btn-secondary text-sm">Voir les mouvements →</a>
    </div>
</div>
@endsection
