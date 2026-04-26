<div class="bg-white rounded-xl shadow-sm border border-gray-100">
    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
        <h3 class="font-semibold text-gray-800 text-sm">Lignes</h3>
        <button type="button" onclick="addLineRow()" class="btn-primary text-xs">+ Ajouter une ligne</button>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm" id="line-items-table">
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50">
                    <th class="text-left px-4 py-2 text-xs font-semibold text-gray-500 w-44">Produit</th>
                    <th class="text-left px-4 py-2 text-xs font-semibold text-gray-500">Description *</th>
                    <th class="text-right px-4 py-2 text-xs font-semibold text-gray-500 w-24">Qté *</th>
                    <th class="text-right px-4 py-2 text-xs font-semibold text-gray-500 w-32">Prix unit. *</th>
                    <th class="text-right px-4 py-2 text-xs font-semibold text-gray-500 w-20">Remise %</th>
                    <th class="text-right px-4 py-2 text-xs font-semibold text-gray-500 w-36">Total HT</th>
                    <th class="w-10 px-4 py-2"></th>
                </tr>
            </thead>
            <tbody id="line-body"></tbody>
            <tfoot>
                <tr class="border-t border-gray-200 bg-gray-50">
                    <td colspan="5" class="px-4 py-2 text-right text-sm text-gray-600">Sous-total HT :</td>
                    <td class="px-4 py-2 text-right font-semibold text-gray-800" id="line-subtotal">0 FCFA</td>
                    <td></td>
                </tr>
                <tr class="bg-gray-50">
                    <td colspan="5" class="px-4 py-2 text-right text-sm text-gray-600">TVA (<span id="line-tax-pct">0</span>%) :</td>
                    <td class="px-4 py-2 text-right text-gray-600" id="line-tax">0 FCFA</td>
                    <td></td>
                </tr>
                <tr class="bg-gray-50">
                    <td colspan="5" class="px-4 py-2 text-right font-bold text-gray-700">Total TTC :</td>
                    <td class="px-4 py-2 text-right font-bold text-gray-900 text-base" id="line-total">0 FCFA</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

@push('scripts')
<script>
const lineProducts = @json($products->map(fn($p) => ['id' => $p->id, 'name' => $p->name, 'unit_price' => $p->unit_price]));
let lineIdx = 0;

function addLineRow(data = {}) {
    const i = lineIdx++;
    const tr = document.createElement('tr');
    tr.className = 'border-b border-gray-50 hover:bg-gray-50';
    tr.dataset.idx = i;

    const opts = lineProducts.map(p =>
        `<option value="${p.id}" ${data.product_id == p.id ? 'selected' : ''}>${p.name}</option>`
    ).join('');

    tr.innerHTML = `
        <td class="px-4 py-2">
            <select name="items[${i}][product_id]" class="form-input text-xs w-full" onchange="fillLine(this,${i})">
                <option value="">—</option>${opts}
            </select>
        </td>
        <td class="px-4 py-2">
            <input type="text" name="items[${i}][description]" required value="${data.description||''}"
                placeholder="Description" class="form-input text-xs w-full">
        </td>
        <td class="px-4 py-2">
            <input type="number" name="items[${i}][quantity]" required min="0.0001" step="0.0001"
                value="${data.quantity||1}" class="form-input text-xs text-right w-full" oninput="calcLine(${i})">
        </td>
        <td class="px-4 py-2">
            <input type="number" name="items[${i}][unit_price]" required min="0" step="1"
                value="${data.unit_price||0}" class="form-input text-xs text-right w-full" oninput="calcLine(${i})">
        </td>
        <td class="px-4 py-2">
            <input type="number" name="items[${i}][discount_pct]" min="0" max="100" step="0.5"
                value="${data.discount_pct||0}" class="form-input text-xs text-right w-full" oninput="calcLine(${i})">
        </td>
        <td class="px-4 py-2 text-right font-medium text-gray-800 text-xs" id="lt-${i}">0</td>
        <td class="px-4 py-2 text-center">
            <button type="button" onclick="this.closest('tr').remove();calcGrand()" class="text-red-400 hover:text-red-600 text-lg leading-none">×</button>
        </td>`;
    document.getElementById('line-body').appendChild(tr);
    calcLine(i);
}

function fillLine(sel, i) {
    const p = lineProducts.find(x => x.id == sel.value);
    if (!p) return;
    const row = document.querySelector(`[data-idx="${i}"]`);
    row.querySelector('[name$="[description]"]').value = p.name;
    row.querySelector('[name$="[unit_price]"]').value = p.unit_price;
    calcLine(i);
}

function calcLine(i) {
    const row = document.querySelector(`[data-idx="${i}"]`);
    if (!row) return;
    const qty  = parseFloat(row.querySelector('[name$="[quantity]"]').value) || 0;
    const price= parseFloat(row.querySelector('[name$="[unit_price]"]').value) || 0;
    const disc = parseFloat(row.querySelector('[name$="[discount_pct]"]')?.value) || 0;
    const tot  = qty * price * (1 - disc / 100);
    document.getElementById(`lt-${i}`).textContent = tot.toLocaleString('fr-FR') + ' FCFA';
    calcGrand();
}

function calcGrand() {
    let sub = 0;
    document.querySelectorAll('[id^="lt-"]').forEach(el => {
        sub += parseFloat(el.textContent.replace(/[^\d.]/g, '')) || 0;
    });
    const taxRateEl = document.getElementById('tax-rate');
    const taxPct    = taxRateEl ? parseFloat(taxRateEl.value) || 0 : 0;
    const tax       = sub * taxPct / 100;
    const total     = sub + tax;
    document.getElementById('line-subtotal').textContent = sub.toLocaleString('fr-FR') + ' FCFA';
    const taxPctEl = document.getElementById('line-tax-pct');
    if (taxPctEl) taxPctEl.textContent = taxPct;
    document.getElementById('line-tax').textContent   = tax.toLocaleString('fr-FR') + ' FCFA';
    document.getElementById('line-total').textContent = total.toLocaleString('fr-FR') + ' FCFA';
}

addLineRow();
</script>
@endpush
