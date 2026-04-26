@extends('layouts.app')
@section('title', 'Modifier plan MRP')
@section('page-title', 'Modifier plan MRP')

@section('content')
<div class="mt-2 max-w-xl">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <form method="POST" action="{{ route('mrp.update', $mrp) }}" class="space-y-4">
            @csrf @method('PUT')

            <div>
                <label class="form-label">Produit / Matière première *</label>
                <select name="product_id" id="product-select" required class="form-input" onchange="updateStockInfo(this)">
                    <option value="">Sélectionner un produit...</option>
                    @foreach($products as $p)
                    <option value="{{ $p->id }}"
                        data-stock="{{ $p->quantity_in_stock }}"
                        data-unit="{{ $p->unit }}"
                        {{ old('product_id', $mrp->product_id) == $p->id ? 'selected' : '' }}>
                        {{ $p->name }} ({{ $p->code }})
                    </option>
                    @endforeach
                </select>
                <div id="stock-info" class="mt-2 px-3 py-2 bg-gray-50 rounded-lg text-sm">
                    <span class="text-gray-500">Stock actuel :</span>
                    <span id="stock-value" class="font-bold text-gray-800 ml-1">{{ number_format($mrp->quantity_available, 2) }}</span>
                </div>
            </div>

            <div>
                <label class="form-label">Quantité requise *</label>
                <input type="number" name="quantity_needed" id="quantity-needed"
                    value="{{ old('quantity_needed', $mrp->quantity_needed) }}" step="0.01" min="0.01" required
                    class="form-input" oninput="checkShortage()">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Date prévue *</label>
                    <input type="date" name="planned_date" value="{{ old('planned_date', $mrp->planned_date->format('Y-m-d')) }}" required class="form-input">
                </div>
                <div>
                    <label class="form-label">Statut</label>
                    <select name="status" class="form-input">
                        <option value="ouvert"   {{ old('status', $mrp->status) === 'ouvert'   ? 'selected' : '' }}>Ouvert</option>
                        <option value="confirme" {{ old('status', $mrp->status) === 'confirme' ? 'selected' : '' }}>Confirmé</option>
                        <option value="cloture"  {{ old('status', $mrp->status) === 'cloture'  ? 'selected' : '' }}>Clôturé</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="form-label">Notes</label>
                <textarea name="notes" rows="3" class="form-input">{{ old('notes', $mrp->notes) }}</textarea>
            </div>

            @if($errors->any())
            <div class="bg-red-50 border border-red-200 rounded-lg p-3 text-sm text-red-700">
                <ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
            @endif

            <div class="flex gap-3 pt-2">
                <a href="{{ route('mrp.index') }}" class="btn-secondary px-5 py-2.5">Annuler</a>
                <button type="submit" class="btn-primary flex-1 py-2.5">Mettre à jour</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
let currentStock = {{ $mrp->quantity_available }};

function updateStockInfo(select) {
    const opt = select.options[select.selectedIndex];
    const stockVal = document.getElementById('stock-value');
    if (select.value && opt.dataset.stock !== undefined) {
        currentStock = parseFloat(opt.dataset.stock);
        stockVal.textContent = currentStock.toLocaleString('fr-FR', {minimumFractionDigits: 2}) + ' ' + opt.dataset.unit;
        checkShortage();
    }
}

function checkShortage() {
    const needed = parseFloat(document.getElementById('quantity-needed').value) || 0;
    const stockVal = document.getElementById('stock-value');
    if (needed > currentStock) {
        stockVal.classList.add('text-red-600');
        stockVal.classList.remove('text-gray-800');
    } else {
        stockVal.classList.remove('text-red-600');
        stockVal.classList.add('text-gray-800');
    }
}

window.addEventListener('DOMContentLoaded', () => {
    const sel = document.getElementById('product-select');
    if (sel && sel.value) updateStockInfo(sel);
});
</script>
@endpush
@endsection
