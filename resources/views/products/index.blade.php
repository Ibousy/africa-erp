@extends('layouts.app')
@section('title', 'Produits')
@section('page-title', 'Produits & Stock')
@section('header-actions')
    <a href="{{ route('products.create') }}" class="btn-primary">+ Nouveau produit</a>
@endsection

@section('content')
<div class="mt-2 space-y-4">
    <!-- Filters -->
    <form method="GET" class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex flex-wrap gap-3">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher..." class="form-input w-48">
        <select name="category" class="form-input w-40">
            <option value="">Toutes catégories</option>
            @foreach($categories as $cat)
                <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
            @endforeach
        </select>
        <label class="flex items-center gap-2 text-sm text-gray-600">
            <input type="checkbox" name="low_stock" value="1" {{ request('low_stock') ? 'checked' : '' }} class="rounded">
            Stock bas seulement
        </label>
        <button type="submit" class="btn-primary">Filtrer</button>
        <a href="{{ route('products.index') }}" class="btn-secondary">Reset</a>
    </form>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100">
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Code</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Produit</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Catégorie</th>
                    <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Stock actuel</th>
                    <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Prix unitaire</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Statut</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($products as $product)
                <tr class="hover:bg-gray-50 {{ $product->isLowStock() ? 'bg-red-50' : '' }}">
                    <td class="px-5 py-3 font-mono text-xs text-gray-500">{{ $product->code }}</td>
                    <td class="px-5 py-3 font-medium text-gray-800">
                        {{ $product->name }}
                        @if($product->isLowStock())
                        <span class="ml-1 text-xs text-red-500 font-semibold">⚠ Stock bas</span>
                        @endif
                    </td>
                    <td class="px-5 py-3 text-gray-600">{{ $product->category ?? '—' }}</td>
                    <td class="px-5 py-3 text-right font-semibold {{ $product->isLowStock() ? 'text-red-600' : 'text-gray-800' }}">
                        {{ number_format($product->quantity_in_stock, 2) }} {{ $product->unit }}
                    </td>
                    <td class="px-5 py-3 text-right text-gray-600">{{ money($product->unit_price) }}</td>
                    <td class="px-5 py-3">
                        <span class="px-2 py-1 text-xs font-medium rounded-full {{ $product->status === 'actif' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                            @label($product->status)
                        </span>
                    </td>
                    <td class="px-5 py-3 text-right whitespace-nowrap">
                        <a href="{{ route('stock.create') }}?product_id={{ $product->id }}" class="text-green-500 hover:text-green-700 text-xs font-medium mr-3">Mouvement</a>
                        <a href="{{ route('products.bom.show', $product) }}" class="text-purple-500 hover:text-purple-700 text-xs font-medium mr-3">BOM</a>
                        <a href="{{ route('products.edit', $product) }}" class="text-blue-500 hover:text-blue-700 text-xs font-medium mr-3">Modifier</a>
                        <form method="POST" action="{{ route('products.destroy', $product) }}" class="inline" onsubmit="return confirm('Supprimer ce produit ?')">
                            @csrf @method('DELETE')
                            <button class="text-red-500 hover:text-red-700 text-xs font-medium">Supprimer</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-5 py-10 text-center text-gray-400">Aucun produit trouvé</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-5 py-3">{{ $products->links() }}</div>
    </div>
</div>
@endsection
