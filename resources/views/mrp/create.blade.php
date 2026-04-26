@extends('layouts.app')
@section('title', 'Nouveau plan MRP')
@section('page-title', 'Nouveau plan MRP')

@section('content')
<div class="mt-2 max-w-xl">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">

        <div class="bg-blue-50 border border-blue-100 rounded-lg px-4 py-3 text-sm text-blue-700 mb-5">
            Le stock disponible est calculé automatiquement depuis l'inventaire actuel dès que vous sélectionnez un produit.
        </div>

        <form method="POST" action="{{ route('mrp.store') }}" class="space-y-4">
            @csrf

            <div>
                <label class="form-label">Produit / Matière première *</label>
                <select name="product_id" id="product-select" required class="form-input" onchange="updateStockInfo(this)">
                    <option value="">Sélectionner un produit...</option>
                    @foreach($products as $p)
                    <option value="{{ $p->id }}"
                        data-stock="{{ $p->quantity_in_stock }}"
                        data-unit="{{ $p->unit }}"
                        {{ old('product_id') == $p->id ? 'selected' : '' }}>
                        {{ $p->name }} ({{ $p->code }})
                    </option>
                    @endforeach
                </select>

                <!-- Affiche le stock actuel après sélection -->
                <div id="stock-info" class="mt-2 px-3 py-2 bg-gray-50 rounded-lg text-sm hidden">
                    <span class="text-gray-500">Stock actuel :</span>
                    <span id="stock-value" class="font-bold text-gray-800 ml-1"></span>
                    <span id="stock-alert" class="ml-2 text-xs font-semibold text-red-600 hidden">— Stock insuffisant probable</span>
                </div>
            </div>

            <div>
                <label class="form-label">Quantité requise *</label>
                <input type="number" name="quantity_needed" id="quantity-needed"
                    value="{{ old('quantity_needed') }}" step="0.01" min="0.01" required
                    class="form-input" oninput="checkShortage()">
                <p class="text-xs text-gray-400 mt-1">La quantité disponible sera automatiquement tirée du stock actuel.</p>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Date prévue *</label>
                    <input type="date" name="planned_date" value="{{ old('planned_date', date('Y-m-d')) }}" required class="form-input">
                </div>
                <div>
                    <label class="form-label">Statut</label>
                    <select name="status" class="form-input">
                        <option value="ouvert" {{ old('status', 'ouvert') === 'ouvert' ? 'selected' : '' }}>Ouvert</option>
                        <option value="confirme" {{ old('status') === 'confirme' ? 'selected' : '' }}>Confirmé</option>
                        <option value="cloture" {{ old('status') === 'cloture' ? 'selected' : '' }}>Clôturé</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="form-label">Notes</label>
                <textarea name="notes" rows="3" class="form-input" placeholder="Lien ordre de production, observations...">{{ old('notes') }}</textarea>
            </div>

            <div class="flex gap-3 pt-2">
                <a href="{{ route('mrp.index') }}" class="btn-secondary px-5 py-2.5">Annuler</a>
                <button type="submit" class="btn-primary flex-1 py-2.5">Créer le plan</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
let currentStock = 0;

function updateStockInfo(select) {
    const opt = select.options[select.selectedIndex];
    const stockInfo = document.getElementById('stock-info');
    const stockVal  = document.getElementById('stock-value');

    if (select.value && opt.dataset.stock !== undefined) {
        currentStock = parseFloat(opt.dataset.stock);
        stockVal.textContent = parseFloat(opt.dataset.stock).toLocaleString('fr-FR', {minimumFractionDigits: 2}) + ' ' + opt.dataset.unit;
        stockInfo.classList.remove('hidden');
        checkShortage();
    } else {
        currentStock = 0;
        stockInfo.classList.add('hidden');
    }
}

function checkShortage() {
    const needed = parseFloat(document.getElementById('quantity-needed').value) || 0;
    const alert  = document.getElementById('stock-alert');
    if (alert) {
        if (needed > currentStock && currentStock >= 0) {
            alert.classList.remove('hidden');
        } else {
            alert.classList.add('hidden');
        }
    }
}

// Init si old value selected
window.addEventListener('DOMContentLoaded', () => {
    const sel = document.getElementById('product-select');
    if (sel && sel.value) updateStockInfo(sel);
});
</script>
@endpush
@endsection
