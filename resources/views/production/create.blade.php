@extends('layouts.app')
@section('title', 'Nouvel ordre de fabrication')
@section('page-title', 'Nouvel ordre de fabrication')

@section('content')
<div class="max-w-lg mt-2">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <form method="POST" action="{{ route('production.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="form-label">Produit à fabriquer *</label>
                <select name="product_id" required class="form-input">
                    <option value="">-- Sélectionner --</option>
                    @foreach($products as $p)
                        <option value="{{ $p->id }}" {{ old('product_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Quantité planifiée *</label>
                <input type="number" name="quantity_planned" value="{{ old('quantity_planned') }}" min="0.01" step="0.01" required class="form-input">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Date de début</label>
                    <input type="date" name="start_date" value="{{ old('start_date', date('Y-m-d')) }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">Date de fin prévue</label>
                    <input type="date" name="end_date" value="{{ old('end_date') }}" class="form-input">
                </div>
            </div>
            <div>
                <label class="form-label">Notes</label>
                <textarea name="notes" rows="3" class="form-input">{{ old('notes') }}</textarea>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary">Créer l'ordre</button>
                <a href="{{ route('production.index') }}" class="btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection
