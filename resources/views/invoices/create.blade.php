@extends('layouts.app')
@section('title', 'Nouveau document')
@section('page-title', 'Nouveau devis / facture')

@section('content')
<div class="max-w-3xl mt-2">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <form method="POST" action="{{ route('invoices.store') }}" class="space-y-5" id="invoice-form">
            @csrf
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Type *</label>
                    <select name="type" required class="form-input">
                        <option value="facture">Facture</option>
                        <option value="devis">Devis</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Client *</label>
                    <select name="client_id" required class="form-input">
                        <option value="">-- Sélectionner --</option>
                        @foreach($clients as $c)
                            <option value="{{ $c->id }}" {{ (old('client_id') == $c->id || request('client_id') == $c->id) ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Date d'émission *</label>
                    <input type="date" name="issue_date" value="{{ old('issue_date', date('Y-m-d')) }}" required class="form-input">
                </div>
                <div>
                    <label class="form-label">Date d'échéance</label>
                    <input type="date" name="due_date" value="{{ old('due_date') }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">TVA (%)</label>
                    <input type="number" name="tax_rate" value="{{ old('tax_rate', 18) }}" min="0" max="100" step="0.5" class="form-input" id="tax_rate">
                </div>
            </div>

            <!-- Lignes -->
            <div>
                <div class="flex items-center justify-between mb-3">
                    <label class="form-label mb-0">Lignes *</label>
                    <button type="button" onclick="addLine()" class="btn-secondary text-xs">+ Ajouter ligne</button>
                </div>
                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="text-left px-3 py-2 text-xs text-gray-500">Description</th>
                                <th class="text-left px-3 py-2 text-xs text-gray-500 w-28">Produit (opt.)</th>
                                <th class="text-right px-3 py-2 text-xs text-gray-500 w-24">Qté</th>
                                <th class="text-right px-3 py-2 text-xs text-gray-500 w-32">Prix unit. (FCFA)</th>
                                <th class="text-right px-3 py-2 text-xs text-gray-500 w-32">Total</th>
                                <th class="w-10"></th>
                            </tr>
                        </thead>
                        <tbody id="lines-body">
                            <tr class="line-row border-t border-gray-100">
                                <td class="px-3 py-2"><input type="text" name="items[0][description]" required placeholder="Description..." class="w-full border border-gray-200 rounded px-2 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-orange-400"></td>
                                <td class="px-3 py-2">
                                    <select name="items[0][product_id]" class="w-full border border-gray-200 rounded px-2 py-1 text-xs focus:outline-none" onchange="fillProduct(this, 0)">
                                        <option value="">—</option>
                                        @foreach($products as $p)
                                            <option value="{{ $p->id }}" data-price="{{ $p->unit_price }}">{{ $p->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="px-3 py-2"><input type="number" name="items[0][quantity]" value="1" min="0.01" step="0.01" required class="w-full border border-gray-200 rounded px-2 py-1 text-sm text-right focus:outline-none focus:ring-1 focus:ring-orange-400 line-qty" oninput="calcLine(this)"></td>
                                <td class="px-3 py-2"><input type="number" name="items[0][unit_price]" value="0" min="0" step="1" required class="w-full border border-gray-200 rounded px-2 py-1 text-sm text-right focus:outline-none focus:ring-1 focus:ring-orange-400 line-price" oninput="calcLine(this)"></td>
                                <td class="px-3 py-2 text-right font-medium line-total text-gray-700">0</td>
                                <td class="px-3 py-2 text-center"><button type="button" onclick="removeLine(this)" class="text-red-400 hover:text-red-600 text-lg leading-none">&times;</button></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Totaux -->
            <div class="flex justify-end">
                <div class="w-64 space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Sous-total</span>
                        <span id="display-subtotal" class="font-medium">0 FCFA</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">TVA (<span id="display-tax-rate">18</span>%)</span>
                        <span id="display-tax" class="font-medium">0 FCFA</span>
                    </div>
                    <div class="flex justify-between border-t border-gray-200 pt-2">
                        <span class="font-bold text-gray-800">Total</span>
                        <span id="display-total" class="font-bold text-lg text-orange-600">0 FCFA</span>
                    </div>
                </div>
            </div>

            <div>
                <label class="form-label">Notes</label>
                <textarea name="notes" rows="2" class="form-input">{{ old('notes') }}</textarea>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary">Créer le document</button>
                <a href="{{ route('invoices.index') }}" class="btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
let lineCount = 1;
const products = @json($products->pluck('unit_price', 'id'));

function addLine() {
    const i = lineCount++;
    const tbody = document.getElementById('lines-body');
    const productOptions = @json($products->map(fn($p) => ['id' => $p->id, 'name' => $p->name, 'price' => $p->unit_price]));
    let opts = '<option value="">—</option>' + productOptions.map(p => `<option value="${p.id}" data-price="${p.price}">${p.name}</option>`).join('');
    const row = `<tr class="line-row border-t border-gray-100">
        <td class="px-3 py-2"><input type="text" name="items[${i}][description]" required placeholder="Description..." class="w-full border border-gray-200 rounded px-2 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-orange-400"></td>
        <td class="px-3 py-2"><select name="items[${i}][product_id]" class="w-full border border-gray-200 rounded px-2 py-1 text-xs focus:outline-none" onchange="fillProduct(this, ${i})">${opts}</select></td>
        <td class="px-3 py-2"><input type="number" name="items[${i}][quantity]" value="1" min="0.01" step="0.01" required class="w-full border border-gray-200 rounded px-2 py-1 text-sm text-right focus:outline-none focus:ring-1 focus:ring-orange-400 line-qty" oninput="calcLine(this)"></td>
        <td class="px-3 py-2"><input type="number" name="items[${i}][unit_price]" value="0" min="0" step="1" required class="w-full border border-gray-200 rounded px-2 py-1 text-sm text-right focus:outline-none focus:ring-1 focus:ring-orange-400 line-price" oninput="calcLine(this)"></td>
        <td class="px-3 py-2 text-right font-medium line-total text-gray-700">0</td>
        <td class="px-3 py-2 text-center"><button type="button" onclick="removeLine(this)" class="text-red-400 hover:text-red-600 text-lg leading-none">&times;</button></td>
    </tr>`;
    tbody.insertAdjacentHTML('beforeend', row);
}

function fillProduct(sel, i) {
    const opt = sel.options[sel.selectedIndex];
    const price = opt.dataset.price || 0;
    const row = sel.closest('tr');
    row.querySelector('.line-price').value = price;
    calcLine(row.querySelector('.line-qty'));
}

function calcLine(input) {
    const row = input.closest('tr');
    const qty = parseFloat(row.querySelector('.line-qty').value) || 0;
    const price = parseFloat(row.querySelector('.line-price').value) || 0;
    const total = qty * price;
    row.querySelector('.line-total').textContent = new Intl.NumberFormat('fr-FR').format(total) + ' FCFA';
    calcTotals();
}

function removeLine(btn) {
    const rows = document.querySelectorAll('.line-row');
    if (rows.length > 1) { btn.closest('tr').remove(); calcTotals(); }
}

function calcTotals() {
    let sub = 0;
    document.querySelectorAll('.line-row').forEach(row => {
        const qty = parseFloat(row.querySelector('.line-qty').value) || 0;
        const price = parseFloat(row.querySelector('.line-price').value) || 0;
        sub += qty * price;
    });
    const rate = parseFloat(document.getElementById('tax_rate').value) || 0;
    const tax = sub * rate / 100;
    const total = sub + tax;
    const fmt = v => new Intl.NumberFormat('fr-FR').format(Math.round(v)) + ' FCFA';
    document.getElementById('display-subtotal').textContent = fmt(sub);
    document.getElementById('display-tax').textContent = fmt(tax);
    document.getElementById('display-total').textContent = fmt(total);
    document.getElementById('display-tax-rate').textContent = rate;
}

document.getElementById('tax_rate').addEventListener('input', calcTotals);
calcTotals();
</script>
@endpush
