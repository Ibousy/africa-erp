@extends('layouts.app')
@section('title', 'Modifier transaction')
@section('page-title', 'Modifier transaction')

@section('content')
<div class="mt-2 max-w-xl">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <form method="POST" action="{{ route('accounting.update', $accounting) }}" class="space-y-4">
            @csrf @method('PUT')

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Type *</label>
                    <select name="type" required class="form-input">
                        <option value="recette" {{ old('type', $accounting->type) === 'recette' ? 'selected' : '' }}>Recette</option>
                        <option value="depense" {{ old('type', $accounting->type) === 'depense' ? 'selected' : '' }}>Dépense</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Date *</label>
                    <input type="date" name="date" value="{{ old('date', $accounting->date->format('Y-m-d')) }}" required class="form-input">
                </div>
            </div>

            <div>
                <label class="form-label">Description *</label>
                <input type="text" name="description" value="{{ old('description', $accounting->description) }}" required class="form-input">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Catégorie *</label>
                    <input type="text" name="category" value="{{ old('category', $accounting->category) }}" required class="form-input">
                </div>
                <div>
                    <label class="form-label">Montant (FCFA) *</label>
                    <input type="number" name="amount" value="{{ old('amount', $accounting->amount) }}" required step="1" min="1" class="form-input">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Mode de paiement</label>
                    <select name="payment_method" class="form-input">
                        <option value="">—</option>
                        @foreach(['Espèces', 'Virement', 'Chèque', 'Mobile Money'] as $method)
                        <option value="{{ $method }}" {{ old('payment_method', $accounting->payment_method) === $method ? 'selected' : '' }}>{{ $method }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Référence</label>
                    <input type="text" name="reference" value="{{ old('reference', $accounting->reference) }}" class="form-input">
                </div>
            </div>

            <div>
                <label class="form-label">Notes</label>
                <textarea name="notes" rows="2" class="form-input">{{ old('notes', $accounting->notes) }}</textarea>
            </div>

            @if($errors->any())
            <div class="bg-red-50 border border-red-200 rounded-lg p-3 text-sm text-red-700">
                <ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
            @endif

            <div class="flex gap-3 pt-2">
                <a href="{{ route('accounting.index') }}" class="btn-secondary px-5 py-2.5">Annuler</a>
                <button type="submit" class="btn-primary flex-1 py-2.5">Mettre à jour</button>
            </div>
        </form>
    </div>
</div>
@endsection
