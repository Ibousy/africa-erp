@extends('layouts.app')
@section('title', 'Nomenclature — ' . $product->name)
@section('page-title', 'Nomenclature (BOM) — ' . $product->name)

@section('header-actions')
    <a href="{{ route('products.index') }}" class="btn-secondary">← Retour produits</a>
@endsection

@section('content')
<div class="mt-2 max-w-2xl space-y-4">

    <!-- Info produit -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex items-center gap-4">
        <div class="w-10 h-10 rounded-lg flex items-center justify-center text-white font-bold text-sm shrink-0" style="background-color: var(--clr-p)">
            {{ strtoupper(substr($product->name, 0, 1)) }}
        </div>
        <div>
            <p class="font-semibold text-gray-800">{{ $product->name }}</p>
            <p class="text-xs text-gray-400">{{ $product->code }} · {{ $product->category ?: 'Sans catégorie' }}</p>
        </div>
    </div>

    <!-- Composants existants -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-semibold text-gray-800 text-sm">Composants nécessaires (pour 1 unité produite)</h3>
            <span class="text-xs text-gray-400">{{ $product->bomItems->count() }} composant(s)</span>
        </div>

        @if($product->bomItems->isEmpty())
        <div class="px-5 py-10 text-center text-gray-400 text-sm">
            Aucun composant défini. Ajoutez-en ci-dessous.
        </div>
        @else
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100">
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Composant</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Référence</th>
                    <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Qté / unité</th>
                    <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Stock actuel</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($product->bomItems as $bom)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3 font-medium text-gray-800">{{ $bom->component->name }}</td>
                    <td class="px-5 py-3 text-gray-500 text-xs">{{ $bom->component->code }}</td>
                    <td class="px-5 py-3 text-right font-semibold text-gray-800">
                        {{ rtrim(rtrim(number_format($bom->quantity, 4), '0'), '.') }} {{ $bom->component->unit }}
                    </td>
                    <td class="px-5 py-3 text-right text-xs {{ $bom->component->isLowStock() ? 'text-red-600 font-semibold' : 'text-gray-500' }}">
                        {{ number_format($bom->component->quantity_in_stock, 2) }} {{ $bom->component->unit }}
                        @if($bom->component->isLowStock()) ⚠️ @endif
                    </td>
                    <td class="px-5 py-3 text-right">
                        <form method="POST" action="{{ route('products.bom.destroy', [$product, $bom]) }}" onsubmit="return confirm('Retirer ce composant ?')">
                            @csrf @method('DELETE')
                            <button class="text-red-400 hover:text-red-600 text-xs">Suppr.</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>

    <!-- Ajouter un composant -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <h3 class="font-semibold text-gray-800 mb-4 text-sm">Ajouter un composant</h3>
        <form method="POST" action="{{ route('products.bom.store', $product) }}" class="grid grid-cols-2 gap-4">
            @csrf
            <div class="col-span-2 md:col-span-1">
                <label class="form-label">Composant / Matière première *</label>
                <select name="component_id" required class="form-input">
                    <option value="">— Sélectionner un produit —</option>
                    @foreach($components as $comp)
                    @if(!$product->bomItems->pluck('component_id')->contains($comp->id))
                    <option value="{{ $comp->id }}" {{ old('component_id') == $comp->id ? 'selected' : '' }}>
                        {{ $comp->name }} ({{ $comp->code }})
                    </option>
                    @endif
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Quantité nécessaire (par unité produite) *</label>
                <input type="number" name="quantity" value="{{ old('quantity') }}" required min="0.0001" step="0.0001" placeholder="ex: 2.5" class="form-input">
            </div>
            <div class="col-span-2 flex gap-3">
                <button type="submit" class="btn-primary">Ajouter le composant</button>
            </div>
        </form>
    </div>

    <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 text-sm text-amber-700">
        <strong>Comment ça fonctionne :</strong> Lorsqu'un ordre de fabrication est marqué
        <em>Terminé</em>, le système consomme automatiquement les composants de cette nomenclature
        (selon la quantité produite) et ajoute le produit fini au stock.
    </div>
</div>
@endsection
