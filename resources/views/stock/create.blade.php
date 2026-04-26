@extends('layouts.app')
@section('title', 'Nouveau mouvement')
@section('page-title', 'Nouveau mouvement de stock')

@section('content')
<div class="max-w-lg mt-2">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <form method="POST" action="{{ route('stock.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="form-label">Produit *</label>
                <select name="product_id" required class="form-input">
                    <option value="">-- Sélectionner --</option>
                    @foreach($products as $p)
                        <option value="{{ $p->id }}" {{ (old('product_id') == $p->id || request('product_id') == $p->id) ? 'selected' : '' }}>
                            {{ $p->name }} (stock: {{ $p->quantity_in_stock }} {{ $p->unit }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Type de mouvement *</label>
                <div class="flex gap-3">
                    <label class="flex items-center gap-2 cursor-pointer flex-1 border rounded-lg px-4 py-3 {{ old('type', 'entree') === 'entree' ? 'border-green-500 bg-green-50' : 'border-gray-200' }}">
                        <input type="radio" name="type" value="entree" {{ old('type', 'entree') === 'entree' ? 'checked' : '' }} class="text-green-500">
                        <span class="text-sm font-medium text-green-700">+ Entrée stock</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer flex-1 border rounded-lg px-4 py-3 {{ old('type') === 'sortie' ? 'border-red-500 bg-red-50' : 'border-gray-200' }}">
                        <input type="radio" name="type" value="sortie" {{ old('type') === 'sortie' ? 'checked' : '' }} class="text-red-500">
                        <span class="text-sm font-medium text-red-700">- Sortie stock</span>
                    </label>
                </div>
            </div>
            <div>
                <label class="form-label">Quantité *</label>
                <input type="number" name="quantity" value="{{ old('quantity') }}" min="0.01" step="0.01" required class="form-input">
            </div>
            <div>
                <label class="form-label">Référence</label>
                <input type="text" name="reference" value="{{ old('reference') }}" class="form-input" placeholder="N° bon de livraison, commande...">
            </div>
            <div>
                <label class="form-label">Motif</label>
                <input type="text" name="reason" value="{{ old('reason') }}" class="form-input" placeholder="Achat, production, retour...">
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary">Enregistrer</button>
                <a href="{{ route('stock.index') }}" class="btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection
