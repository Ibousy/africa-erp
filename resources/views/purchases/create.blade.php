@extends('layouts.app')
@section('title', 'Nouvelle commande fournisseur')
@section('page-title', 'Nouvelle commande fournisseur')

@section('content')
<div class="mt-2 max-w-4xl">
    @if($suppliers->isEmpty())
    <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded-lg mb-4 text-sm">
        Vous n'avez aucun fournisseur enregistré. <a href="{{ route('purchases.index') }}" class="underline font-medium">Ajoutez-en un d'abord.</a>
    </div>
    @endif

    <form method="POST" action="{{ route('purchases.store') }}" class="space-y-4" id="po-form">
        @csrf

        <!-- En-tête commande -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="font-semibold text-gray-800 mb-4 text-sm">Informations commande</h3>
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="form-label">Fournisseur *</label>
                    <select name="supplier_id" required class="form-input">
                        <option value="">Sélectionner...</option>
                        @foreach($suppliers as $sup)
                        <option value="{{ $sup->id }}" {{ old('supplier_id') == $sup->id ? 'selected' : '' }}>
                            {{ $sup->name }} @if($sup->contact_name) — {{ $sup->contact_name }} @endif
                        </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Référence *</label>
                    <input type="text" name="reference" value="{{ old('reference', 'BC-' . date('Ymd') . '-001') }}" required class="form-input">
                </div>
                <div>
                    <label class="form-label">Statut</label>
                    <select name="status" class="form-input">
                        <option value="brouillon" {{ old('status', 'brouillon') === 'brouillon' ? 'selected' : '' }}>Brouillon</option>
                        <option value="envoye" {{ old('status') === 'envoye' ? 'selected' : '' }}>Envoyé</option>
                        <option value="confirme" {{ old('status') === 'confirme' ? 'selected' : '' }}>Confirmé</option>
                        <option value="recu" {{ old('status') === 'recu' ? 'selected' : '' }}>Reçu</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Date commande *</label>
                    <input type="date" name="order_date" value="{{ old('order_date', date('Y-m-d')) }}" required class="form-input">
                </div>
                <div>
                    <label class="form-label">Livraison prévue</label>
                    <input type="date" name="expected_date" value="{{ old('expected_date') }}" class="form-input">
                </div>
                <div class="col-span-2">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" rows="2" class="form-input">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>

        <!-- Lignes de commande -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-semibold text-gray-800 text-sm">Lignes de commande</h3>
                <button type="button" onclick="addRow()" class="btn-primary text-xs">+ Ajouter une ligne</button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm" id="items-table">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50">
                            <th class="text-left px-4 py-2 text-xs font-semibold text-gray-500 w-48">Produit (optionnel)</th>
                            <th class="text-left px-4 py-2 text-xs font-semibold text-gray-500">Description *</th>
                            <th class="text-right px-4 py-2 text-xs font-semibold text-gray-500 w-28">Qté *</th>
                            <th class="text-right px-4 py-2 text-xs font-semibold text-gray-500 w-36">Prix unit. (FCFA) *</th>
                            <th class="text-right px-4 py-2 text-xs font-semibold text-gray-500 w-36">Total (FCFA)</th>
                            <th class="w-10 px-4 py-2"></th>
                        </tr>
                    </thead>
                    <tbody id="items-body">
                        <!-- Rows injected by JS -->
                    </tbody>
                    <tfoot>
                        <tr class="border-t border-gray-200 bg-gray-50">
                            <td colspan="4" class="px-4 py-3 text-right font-semibold text-gray-700 text-sm">Total commande :</td>
                            <td class="px-4 py-3 text-right font-bold text-gray-900" id="grand-total">0 FCFA</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <p id="items-error" class="hidden text-red-600 text-xs px-5 pb-3">Ajoutez au moins une ligne.</p>
        </div>

        <div class="flex gap-3 pt-2">
            <a href="{{ route('purchases.index') }}" class="btn-secondary px-5 py-2.5">Annuler</a>
            <button type="submit" class="btn-primary flex-1 py-2.5">Créer la commande →</button>
        </div>
    </form>
</div>

@push('scripts')
<script>
const products = @json($products->map(fn($p) => ['id' => $p->id, 'name' => $p->name, 'unit_price' => $p->unit_price]));

let rowIndex = 0;

function addRow(data = {}) {
    const i = rowIndex++;
    const tr = document.createElement('tr');
    tr.className = 'border-b border-gray-50 hover:bg-gray-50';
    tr.dataset.index = i;

    const productOptions = products.map(p =>
        `<option value="${p.id}" ${data.product_id == p.id ? 'selected' : ''}>${p.name}</option>`
    ).join('');

    tr.innerHTML = `
        <td class="px-4 py-2">
            <select name="items[${i}][product_id]" class="form-input text-xs w-full" onchange="fillFromProduct(this, ${i})">
                <option value="">— Libre —</option>
                ${productOptions}
            </select>
        </td>
        <td class="px-4 py-2">
            <input type="text" name="items[${i}][description]" required value="${data.description || ''}"
                placeholder="Description de l'article" class="form-input text-xs w-full">
        </td>
        <td class="px-4 py-2">
            <input type="number" name="items[${i}][quantity]" required min="0.0001" step="0.0001"
                value="${data.quantity || 1}" class="form-input text-xs text-right w-full" oninput="calcRow(${i})">
        </td>
        <td class="px-4 py-2">
            <input type="number" name="items[${i}][unit_price]" required min="0" step="1"
                value="${data.unit_price || 0}" class="form-input text-xs text-right w-full" oninput="calcRow(${i})">
        </td>
        <td class="px-4 py-2 text-right font-medium text-gray-800 text-xs" id="row-total-${i}">0</td>
        <td class="px-4 py-2 text-center">
            <button type="button" onclick="removeRow(this)" class="text-red-400 hover:text-red-600 text-lg leading-none">×</button>
        </td>`;

    document.getElementById('items-body').appendChild(tr);
    calcRow(i);
}

function fillFromProduct(sel, i) {
    const p = products.find(p => p.id == sel.value);
    if (!p) return;
    const row = document.querySelector(`[data-index="${i}"]`);
    row.querySelector('[name$="[description]"]').value = p.name;
    row.querySelector('[name$="[unit_price]"]').value = p.unit_price;
    calcRow(i);
}

function calcRow(i) {
    const row = document.querySelector(`[data-index="${i}"]`);
    if (!row) return;
    const qty   = parseFloat(row.querySelector('[name$="[quantity]"]').value) || 0;
    const price = parseFloat(row.querySelector('[name$="[unit_price]"]').value) || 0;
    const total = qty * price;
    document.getElementById(`row-total-${i}`).textContent = total.toLocaleString('fr-FR') + ' FCFA';
    calcGrand();
}

function calcGrand() {
    let grand = 0;
    document.querySelectorAll('[id^="row-total-"]').forEach(el => {
        grand += parseFloat(el.textContent.replace(/[^\d.]/g, '')) || 0;
    });
    document.getElementById('grand-total').textContent = grand.toLocaleString('fr-FR') + ' FCFA';
}

function removeRow(btn) {
    btn.closest('tr').remove();
    calcGrand();
}

document.getElementById('po-form').addEventListener('submit', function(e) {
    if (document.getElementById('items-body').children.length === 0) {
        e.preventDefault();
        document.getElementById('items-error').classList.remove('hidden');
    }
});

// Start with one empty row
addRow();
</script>
@endpush
@endsection
