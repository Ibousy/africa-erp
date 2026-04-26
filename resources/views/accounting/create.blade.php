@extends('layouts.app')
@section('title', 'Nouvelle transaction')
@section('page-title', 'Nouvelle transaction')

@section('content')
<div class="mt-2 max-w-xl">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <form method="POST" action="{{ route('accounting.store') }}" class="space-y-4">
            @csrf

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Type *</label>
                    <select name="type" required class="form-input">
                        <option value="recette" {{ old('type') === 'recette' ? 'selected' : '' }}>Recette</option>
                        <option value="depense" {{ old('type', 'depense') === 'depense' ? 'selected' : '' }}>Dépense</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Date *</label>
                    <input type="date" name="date" value="{{ old('date', date('Y-m-d')) }}" required class="form-input">
                </div>
            </div>

            <div>
                <label class="form-label">Description *</label>
                <input type="text" name="description" value="{{ old('description') }}" required placeholder="Achat matières premières, vente produits..." class="form-input">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Catégorie *</label>
                    <input type="text" name="category" value="{{ old('category') }}" required placeholder="Matières premières, Salaires, Ventes..." class="form-input">
                </div>
                <div>
                    <label class="form-label">Montant (FCFA) *</label>
                    <input type="number" name="amount" value="{{ old('amount') }}" required step="1" min="1" class="form-input">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Mode de paiement</label>
                    <select name="payment_method" class="form-input">
                        <option value="">—</option>
                        <option value="Espèces" {{ old('payment_method') === 'Espèces' ? 'selected' : '' }}>Espèces</option>
                        <option value="Virement" {{ old('payment_method') === 'Virement' ? 'selected' : '' }}>Virement bancaire</option>
                        <option value="Chèque" {{ old('payment_method') === 'Chèque' ? 'selected' : '' }}>Chèque</option>
                        <option value="Mobile Money" {{ old('payment_method') === 'Mobile Money' ? 'selected' : '' }}>Mobile Money</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Référence</label>
                    <input type="text" name="reference" value="{{ old('reference') }}" placeholder="N° facture, bordereau..." class="form-input">
                </div>
            </div>

            <div>
                <label class="form-label">Notes</label>
                <textarea name="notes" rows="2" class="form-input">{{ old('notes') }}</textarea>
            </div>

            <div class="flex gap-3 pt-2">
                <a href="{{ route('accounting.index') }}" class="btn-secondary px-5 py-2.5">Annuler</a>
                <button type="submit" class="btn-primary flex-1 py-2.5">Enregistrer</button>
            </div>
        </form>
    </div>
</div>
@endsection
