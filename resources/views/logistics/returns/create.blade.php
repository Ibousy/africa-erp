@extends('layouts.app')
@section('title', 'Nouveau retour')
@section('page-title', 'Enregistrer un retour')

@section('content')
<div class="mt-2 max-w-2xl">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <form method="POST" action="{{ route('returns.store') }}" class="space-y-4" id="return-form">
            @csrf
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Type de retour *</label>
                    <select name="type" required id="return-type" class="form-input" onchange="toggleParty()">
                        <option value="client">Retour client</option>
                        <option value="fournisseur">Retour fournisseur</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Date de retour *</label>
                    <input type="date" name="return_date" value="{{ old('return_date', date('Y-m-d')) }}" required class="form-input">
                </div>
                <div id="client-field">
                    <label class="form-label">Client</label>
                    <select name="client_id" class="form-input">
                        <option value="">—</option>
                        @foreach($clients as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div id="supplier-field" class="hidden">
                    <label class="form-label">Fournisseur</label>
                    <select name="supplier_id" class="form-input">
                        <option value="">—</option>
                        @foreach($suppliers as $s)
                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-span-2">
                    <label class="form-label">Motif</label>
                    <textarea name="reason" rows="2" class="form-input" placeholder="Raison du retour...">{{ old('reason') }}</textarea>
                </div>
            </div>

            <!-- Articles -->
            <div class="border border-gray-200 rounded-xl overflow-hidden">
                <div class="px-4 py-3 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-700">Articles retournés</h3>
                    <button type="button" onclick="addReturnItem()" class="btn-primary text-xs">+ Article</button>
                </div>
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100">
                            <th class="text-left px-4 py-2 text-xs font-semibold text-gray-500">Produit *</th>
                            <th class="text-right px-4 py-2 text-xs font-semibold text-gray-500 w-24">Qté *</th>
                            <th class="text-left px-4 py-2 text-xs font-semibold text-gray-500 w-32">État</th>
                            <th class="w-10 px-4 py-2"></th>
                        </tr>
                    </thead>
                    <tbody id="return-items-body"></tbody>
                </table>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary flex-1 py-2.5">Enregistrer le retour →</button>
                <a href="{{ route('returns.index') }}" class="btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
const retProducts = @json($products->map(fn($p) => ['id' => $p->id, 'name' => $p->name]));
let retIdx = 0;

function addReturnItem() {
    const i = retIdx++;
    const tr = document.createElement('tr');
    tr.className = 'border-b border-gray-50';
    const opts = retProducts.map(p => `<option value="${p.id}">${p.name}</option>`).join('');
    tr.innerHTML = `
        <td class="px-4 py-2">
            <select name="items[${i}][product_id]" required class="form-input text-xs w-full">
                <option value="">Sélectionner...</option>${opts}
            </select>
        </td>
        <td class="px-4 py-2">
            <input type="number" name="items[${i}][quantity]" required min="0.001" step="0.001" value="1" class="form-input text-xs text-right w-full">
        </td>
        <td class="px-4 py-2">
            <select name="items[${i}][condition]" required class="form-input text-xs w-full">
                <option value="bon">Bon état</option>
                <option value="defectueux">Défectueux</option>
                <option value="a_reparer">À réparer</option>
            </select>
        </td>
        <td class="px-4 py-2 text-center">
            <button type="button" onclick="this.closest('tr').remove()" class="text-red-400 hover:text-red-600 text-lg">×</button>
        </td>`;
    document.getElementById('return-items-body').appendChild(tr);
}

function toggleParty() {
    const t = document.getElementById('return-type').value;
    document.getElementById('client-field').style.display   = t === 'client' ? '' : 'none';
    document.getElementById('supplier-field').style.display = t === 'fournisseur' ? '' : 'none';
}

addReturnItem();
</script>
@endpush
@endsection
