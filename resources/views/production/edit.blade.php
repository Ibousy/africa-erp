@extends('layouts.app')
@section('title', 'Modifier ordre')
@section('page-title', 'Modifier ' . $production->reference)

@section('content')
<div class="max-w-lg mt-2">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <form method="POST" action="{{ route('production.update', $production) }}" class="space-y-4">
            @csrf @method('PUT')
            <div>
                <label class="form-label">Produit *</label>
                <select name="product_id" required class="form-input">
                    @foreach($products as $p)
                        <option value="{{ $p->id }}" {{ old('product_id', $production->product_id) == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Qté planifiée *</label>
                    <input type="number" name="quantity_planned" value="{{ old('quantity_planned', $production->quantity_planned) }}" min="0.01" step="0.01" required class="form-input">
                </div>
                <div>
                    <label class="form-label">Qté produite</label>
                    <input type="number" name="quantity_produced" value="{{ old('quantity_produced', $production->quantity_produced) }}" min="0" step="0.01" class="form-input">
                </div>
                <div>
                    <label class="form-label">Date début</label>
                    <input type="date" name="start_date" value="{{ old('start_date', $production->start_date?->format('Y-m-d')) }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">Date fin</label>
                    <input type="date" name="end_date" value="{{ old('end_date', $production->end_date?->format('Y-m-d')) }}" class="form-input">
                </div>
            </div>
            <div>
                <label class="form-label">Statut</label>
                <select name="status" class="form-input">
                    <option value="brouillon" {{ old('status', $production->status) === 'brouillon' ? 'selected' : '' }}>Brouillon</option>
                    <option value="en_cours" {{ old('status', $production->status) === 'en_cours' ? 'selected' : '' }}>En cours</option>
                    <option value="termine" {{ old('status', $production->status) === 'termine' ? 'selected' : '' }}>Terminé</option>
                    <option value="annule" {{ old('status', $production->status) === 'annule' ? 'selected' : '' }}>Annulé</option>
                </select>
            </div>
            <div>
                <label class="form-label">Notes</label>
                <textarea name="notes" rows="3" class="form-input">{{ old('notes', $production->notes) }}</textarea>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary">Enregistrer</button>
                <a href="{{ route('production.show', $production) }}" class="btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection
