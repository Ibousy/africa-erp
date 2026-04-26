@extends('layouts.app')
@section('title', 'Nouveau contrôle qualité')
@section('page-title', 'Nouveau contrôle qualité')

@section('content')
<div class="max-w-2xl mt-2">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <form method="POST" action="{{ route('quality.store') }}" class="space-y-4" id="qc-form">
            @csrf
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Produit *</label>
                    <select name="product_id" required class="form-input">
                        <option value="">-- Sélectionner --</option>
                        @foreach($products as $p)
                            <option value="{{ $p->id }}" {{ old('product_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Ordre de fabrication</label>
                    <select name="production_order_id" class="form-input">
                        <option value="">-- Optionnel --</option>
                        @foreach($productions as $po)
                            <option value="{{ $po->id }}" {{ old('production_order_id') == $po->id ? 'selected' : '' }}>
                                {{ $po->reference }} — {{ $po->product->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Date de contrôle *</label>
                    <input type="date" name="check_date" value="{{ old('check_date', date('Y-m-d')) }}" required class="form-input">
                </div>
                <div>
                    <label class="form-label">Quantité vérifiée *</label>
                    <input type="number" name="quantity_checked" value="{{ old('quantity_checked') }}" min="0.01" step="0.01" required class="form-input">
                </div>
                <div>
                    <label class="form-label">Quantité défectueuse *</label>
                    <input type="number" name="quantity_defective" value="{{ old('quantity_defective', 0) }}" min="0" step="0.01" required class="form-input" id="qty-defective">
                </div>
            </div>
            <div>
                <label class="form-label">Notes</label>
                <textarea name="notes" rows="2" class="form-input">{{ old('notes') }}</textarea>
            </div>

            <!-- Défauts -->
            <div>
                <div class="flex items-center justify-between mb-2">
                    <label class="form-label mb-0">Détail des défauts</label>
                    <button type="button" onclick="addDefect()" class="btn-secondary text-xs">+ Ajouter défaut</button>
                </div>
                <div id="defects-list" class="space-y-2"></div>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary">Enregistrer le contrôle</button>
                <a href="{{ route('quality.index') }}" class="btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
let defectCount = 0;
function addDefect() {
    const i = defectCount++;
    const html = `<div class="grid grid-cols-4 gap-2 items-end bg-gray-50 p-3 rounded-lg">
        <div>
            <label class="text-xs text-gray-500">Type *</label>
            <input type="text" name="defects[${i}][type]" required placeholder="Ex: Dimension, Surface..." class="form-input text-xs">
        </div>
        <div>
            <label class="text-xs text-gray-500">Quantité</label>
            <input type="number" name="defects[${i}][quantity]" value="1" min="0" step="0.01" class="form-input text-xs">
        </div>
        <div>
            <label class="text-xs text-gray-500">Gravité</label>
            <select name="defects[${i}][severity]" class="form-input text-xs">
                <option value="faible">Faible</option>
                <option value="moyen" selected>Moyen</option>
                <option value="grave">Grave</option>
            </select>
        </div>
        <div class="flex gap-2">
            <div class="flex-1">
                <label class="text-xs text-gray-500">Description</label>
                <input type="text" name="defects[${i}][description]" placeholder="Optionnel" class="form-input text-xs">
            </div>
            <button type="button" onclick="this.closest('div.grid').remove()" class="text-red-400 hover:text-red-600 text-xl mb-1">&times;</button>
        </div>
    </div>`;
    document.getElementById('defects-list').insertAdjacentHTML('beforeend', html);
}
</script>
@endpush
