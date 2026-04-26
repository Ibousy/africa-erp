@extends('layouts.app')
@section('title', 'Modifier contrôle qualité')
@section('page-title', 'Modifier contrôle qualité')

@section('content')
<div class="max-w-2xl mt-2">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <form method="POST" action="{{ route('quality.update', $quality) }}" class="space-y-4">
            @csrf @method('PUT')
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Produit *</label>
                    <select name="product_id" required class="form-input">
                        <option value="">-- Sélectionner --</option>
                        @foreach($products as $p)
                            <option value="{{ $p->id }}" {{ old('product_id', $quality->product_id) == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Ordre de fabrication</label>
                    <select name="production_order_id" class="form-input">
                        <option value="">-- Aucun --</option>
                        @foreach($productions as $po)
                            <option value="{{ $po->id }}" {{ old('production_order_id', $quality->production_order_id) == $po->id ? 'selected' : '' }}>
                                {{ $po->reference }} — {{ $po->product->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Date de contrôle *</label>
                    <input type="date" name="check_date" value="{{ old('check_date', $quality->check_date->format('Y-m-d')) }}" required class="form-input">
                </div>
                <div>
                    <label class="form-label">Quantité vérifiée *</label>
                    <input type="number" name="quantity_checked" value="{{ old('quantity_checked', $quality->quantity_checked) }}" min="0.01" step="0.01" required class="form-input">
                </div>
                <div>
                    <label class="form-label">Quantité défectueuse *</label>
                    <input type="number" name="quantity_defective" value="{{ old('quantity_defective', $quality->quantity_defective) }}" min="0" step="0.01" required class="form-input">
                </div>
            </div>
            <div>
                <label class="form-label">Notes</label>
                <textarea name="notes" rows="2" class="form-input">{{ old('notes', $quality->notes) }}</textarea>
            </div>

            @if($errors->any())
            <div class="bg-red-50 border border-red-200 rounded-lg p-3 text-sm text-red-700">
                <ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
            @endif

            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary">Mettre à jour</button>
                <a href="{{ route('quality.show', $quality) }}" class="btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection
