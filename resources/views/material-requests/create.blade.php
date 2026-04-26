@extends('layouts.app')
@section('title', 'Nouvelle demande matières')
@section('page-title', 'Nouvelle demande de matières')
@section('header-actions')
    <a href="{{ route('material-requests.index') }}" class="btn-secondary">← Retour</a>
@endsection

@section('content')
<div class="mt-2 max-w-2xl">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-5">

        <div class="bg-blue-50 border border-blue-100 rounded-lg px-4 py-3 text-sm text-blue-700">
            Créez une demande de matières. La logistique recevra une notification et vous répondra sur la disponibilité.
        </div>

        <form method="POST" action="{{ route('material-requests.store') }}" class="space-y-5" id="mr-form">
            @csrf

            <div>
                <label class="form-label">Ordre de production associé (optionnel)</label>
                <select name="production_order_id" class="form-input">
                    <option value="">— Aucun ordre de production —</option>
                    @foreach($productions as $po)
                    <option value="{{ $po->id }}" {{ (old('production_order_id') ?? request('production_order_id')) == $po->id ? 'selected' : '' }}>
                        {{ $po->reference }} — {{ $po->product->name }}
                        ({{ ['brouillon' => 'Brouillon', 'en_cours' => 'En cours'][$po->status] ?? $po->status }})
                    </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="form-label">Notes / Contexte</label>
                <textarea name="notes" rows="2" class="form-input" placeholder="Décrivez le besoin, la priorité...">{{ old('notes') }}</textarea>
            </div>

            <!-- Lignes de matières -->
            <div>
                <div class="flex items-center justify-between mb-3">
                    <label class="form-label mb-0">Matières / Produits demandés *</label>
                    <button type="button" onclick="addLine()" class="btn-secondary text-xs">+ Ajouter ligne</button>
                </div>
                <div id="lines" class="space-y-2">
                    <div class="grid grid-cols-12 gap-2 items-end bg-gray-50 p-3 rounded-lg" data-line>
                        <div class="col-span-7">
                            <label class="text-xs text-gray-500 mb-1 block">Produit / Matière *</label>
                            <select name="items[0][product_id]" required class="form-input text-sm">
                                <option value="">— Sélectionner —</option>
                                @foreach($products as $p)
                                <option value="{{ $p->id }}" data-stock="{{ $p->quantity_in_stock }}" data-unit="{{ $p->unit }}">
                                    {{ $p->name }} — Stock: {{ $p->quantity_in_stock }} {{ $p->unit }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-span-3">
                            <label class="text-xs text-gray-500 mb-1 block">Quantité *</label>
                            <input type="number" name="items[0][quantity_needed]" required min="0.01" step="0.01" class="form-input text-sm" placeholder="0">
                        </div>
                        <div class="col-span-2 flex justify-end">
                            <button type="button" onclick="this.closest('[data-line]').remove()" class="text-red-400 hover:text-red-600 text-xl font-bold leading-none mt-5">&times;</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary flex-1 py-2.5">Envoyer à la logistique</button>
                <a href="{{ route('material-requests.index') }}" class="btn-secondary px-6">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
let lineIdx = 1;
const productOptions = `@foreach($products as $p)<option value="{{ $p->id }}" data-stock="{{ $p->quantity_in_stock }}" data-unit="{{ $p->unit }}">{{ addslashes($p->name) }} — Stock: {{ $p->quantity_in_stock }} {{ $p->unit }}</option>@endforeach`;

function addLine() {
    const i = lineIdx++;
    const div = document.createElement('div');
    div.setAttribute('data-line', '');
    div.className = 'grid grid-cols-12 gap-2 items-end bg-gray-50 p-3 rounded-lg';
    div.innerHTML = `
        <div class="col-span-7">
            <label class="text-xs text-gray-500 mb-1 block">Produit / Matière *</label>
            <select name="items[${i}][product_id]" required class="form-input text-sm">
                <option value="">— Sélectionner —</option>
                ${productOptions}
            </select>
        </div>
        <div class="col-span-3">
            <label class="text-xs text-gray-500 mb-1 block">Quantité *</label>
            <input type="number" name="items[${i}][quantity_needed]" required min="0.01" step="0.01" class="form-input text-sm" placeholder="0">
        </div>
        <div class="col-span-2 flex justify-end">
            <button type="button" onclick="this.closest('[data-line]').remove()" class="text-red-400 hover:text-red-600 text-xl font-bold leading-none mt-5">&times;</button>
        </div>`;
    document.getElementById('lines').appendChild(div);
}
</script>
@endpush
