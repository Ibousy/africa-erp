@extends('layouts.app')
@section('title', 'Modifier produit')
@section('page-title', 'Modifier produit')

@section('content')
<div class="max-w-2xl mt-2">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <form method="POST" action="{{ route('products.update', $product) }}" class="space-y-4">
            @csrf @method('PUT')
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Code produit *</label>
                    <input type="text" name="code" value="{{ old('code', $product->code) }}" required class="form-input">
                </div>
                <div>
                    <label class="form-label">Nom *</label>
                    <input type="text" name="name" value="{{ old('name', $product->name) }}" required class="form-input">
                </div>
                <div>
                    <label class="form-label">Catégorie</label>
                    <input type="text" name="category" value="{{ old('category', $product->category) }}" class="form-input" list="categories">
                    <datalist id="categories">
                        @foreach($categories as $cat)<option value="{{ $cat }}">@endforeach
                    </datalist>
                </div>
                <div>
                    <label class="form-label">Unité *</label>
                    <input type="text" name="unit" value="{{ old('unit', $product->unit) }}" required class="form-input">
                </div>
                <div>
                    <label class="form-label">Stock actuel *</label>
                    <input type="number" name="quantity_in_stock" value="{{ old('quantity_in_stock', $product->quantity_in_stock) }}" min="0" step="0.01" required class="form-input">
                </div>
                <div>
                    <label class="form-label">Alerte stock minimum *</label>
                    <input type="number" name="min_stock_alert" value="{{ old('min_stock_alert', $product->min_stock_alert) }}" min="0" step="0.01" required class="form-input">
                </div>
                <div>
                    <label class="form-label">Prix unitaire (FCFA) *</label>
                    <input type="number" name="unit_price" value="{{ old('unit_price', $product->unit_price) }}" min="0" step="1" required class="form-input">
                </div>
                <div>
                    <label class="form-label">Statut *</label>
                    <select name="status" required class="form-input">
                        <option value="actif" {{ old('status', $product->status) === 'actif' ? 'selected' : '' }}>Actif</option>
                        <option value="inactif" {{ old('status', $product->status) === 'inactif' ? 'selected' : '' }}>Inactif</option>
                    </select>
                </div>
                <div class="col-span-2">
                    <label class="form-label">Description</label>
                    <textarea name="description" rows="3" class="form-input">{{ old('description', $product->description) }}</textarea>
                </div>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary">Enregistrer</button>
                <a href="{{ route('products.index') }}" class="btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection
