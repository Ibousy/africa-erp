@extends('layouts.app')
@section('title', 'Modifier mouvement de stock')
@section('page-title', 'Modifier mouvement de stock')

@section('content')
<div class="max-w-lg mt-2">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">

        <div class="bg-amber-50 border border-amber-200 rounded-lg px-4 py-3 text-sm text-amber-700 mb-5">
            La modification ajuste automatiquement le stock : l'ancien mouvement sera annulé et le nouveau appliqué.
        </div>

        <form method="POST" action="{{ route('stock.update', $stock) }}" class="space-y-4">
            @csrf @method('PUT')
            <div>
                <label class="form-label">Produit *</label>
                <select name="product_id" required class="form-input">
                    <option value="">-- Sélectionner --</option>
                    @foreach($products as $p)
                        <option value="{{ $p->id }}" {{ old('product_id', $stock->product_id) == $p->id ? 'selected' : '' }}>
                            {{ $p->name }} (stock actuel: {{ $p->quantity_in_stock }} {{ $p->unit }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Type de mouvement *</label>
                <div class="flex gap-3">
                    @php $currentType = old('type', $stock->type); @endphp
                    <label class="flex items-center gap-2 cursor-pointer flex-1 border rounded-lg px-4 py-3 {{ $currentType === 'entree' ? 'border-green-500 bg-green-50' : 'border-gray-200' }}">
                        <input type="radio" name="type" value="entree" {{ $currentType === 'entree' ? 'checked' : '' }} class="text-green-500">
                        <span class="text-sm font-medium text-green-700">+ Entrée stock</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer flex-1 border rounded-lg px-4 py-3 {{ $currentType === 'sortie' ? 'border-red-500 bg-red-50' : 'border-gray-200' }}">
                        <input type="radio" name="type" value="sortie" {{ $currentType === 'sortie' ? 'checked' : '' }} class="text-red-500">
                        <span class="text-sm font-medium text-red-700">- Sortie stock</span>
                    </label>
                </div>
            </div>
            <div>
                <label class="form-label">Quantité *</label>
                <input type="number" name="quantity" value="{{ old('quantity', $stock->quantity) }}" min="0.01" step="0.01" required class="form-input">
            </div>
            <div>
                <label class="form-label">Référence</label>
                <input type="text" name="reference" value="{{ old('reference', $stock->reference) }}" class="form-input" placeholder="N° bon de livraison, commande...">
            </div>
            <div>
                <label class="form-label">Motif</label>
                <input type="text" name="reason" value="{{ old('reason', $stock->reason) }}" class="form-input" placeholder="Achat, production, retour...">
            </div>

            @if($errors->any())
            <div class="bg-red-50 border border-red-200 rounded-lg p-3 text-sm text-red-700">
                <ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
            @endif

            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary">Mettre à jour</button>
                <a href="{{ route('stock.index') }}" class="btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection
