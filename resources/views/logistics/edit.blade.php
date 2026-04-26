@extends('layouts.app')
@section('title', 'Modifier expédition')
@section('page-title', 'Modifier expédition')

@section('content')
<div class="mt-2 max-w-2xl">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <form method="POST" action="{{ route('logistics.update', $logistic) }}" class="space-y-4">
            @csrf @method('PUT')

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Référence *</label>
                    <input type="text" name="reference" value="{{ old('reference', $logistic->reference) }}" required class="form-input">
                </div>
                <div>
                    <label class="form-label">Type de mouvement *</label>
                    <select name="type" required class="form-input">
                        <option value="sortant" {{ old('type', $logistic->type) === 'sortant' ? 'selected' : '' }}>Sortant — Expédition / Bon de sortie</option>
                        <option value="entrant" {{ old('type', $logistic->type) === 'entrant' ? 'selected' : '' }}>Entrant — Réception matières / produits</option>
                        <option value="retour"  {{ old('type', $logistic->type) === 'retour'  ? 'selected' : '' }}>Retour — Retour client / fournisseur</option>
                    </select>
                </div>
            </div>

            <div class="border border-gray-100 rounded-xl p-4 bg-gray-50 space-y-3">
                <p class="text-xs font-semibold text-gray-600 uppercase">Produit concerné (optionnel)</p>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Matière première / Produit fini</label>
                        <select name="product_id" class="form-input">
                            <option value="">Aucun produit lié</option>
                            @foreach($products as $p)
                            <option value="{{ $p->id }}" {{ old('product_id', $logistic->product_id) == $p->id ? 'selected' : '' }}>
                                {{ $p->name }} ({{ $p->code }}) — Stock : {{ number_format($p->quantity_in_stock, 2) }} {{ $p->unit }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Quantité</label>
                        <input type="number" name="quantity" value="{{ old('quantity', $logistic->quantity) }}" step="0.01" min="0.01" class="form-input">
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Contact *</label>
                    <input type="text" name="contact_name" value="{{ old('contact_name', $logistic->contact_name) }}" required class="form-input">
                </div>
                <div>
                    <label class="form-label">Origine / Destination *</label>
                    <input type="text" name="origin_destination" value="{{ old('origin_destination', $logistic->origin_destination) }}" required class="form-input">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Transporteur</label>
                    <input type="text" name="carrier" value="{{ old('carrier', $logistic->carrier) }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">Poids (kg)</label>
                    <input type="number" name="weight_kg" value="{{ old('weight_kg', $logistic->weight_kg) }}" step="0.01" min="0" class="form-input">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Date de départ</label>
                    <input type="date" name="departure_date" value="{{ old('departure_date', $logistic->departure_date?->format('Y-m-d')) }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">Date d'arrivée prévue</label>
                    <input type="date" name="arrival_date" value="{{ old('arrival_date', $logistic->arrival_date?->format('Y-m-d')) }}" class="form-input">
                </div>
            </div>

            <div>
                <label class="form-label">Statut</label>
                <select name="status" class="form-input">
                    @foreach(['en_attente' => 'En attente', 'en_transit' => 'En transit', 'livre' => 'Livré / Réceptionné', 'annule' => 'Annulé'] as $val => $label)
                    <option value="{{ $val }}" {{ old('status', $logistic->status) === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="form-label">Notes</label>
                <textarea name="notes" rows="3" class="form-input">{{ old('notes', $logistic->notes) }}</textarea>
            </div>

            @if($errors->any())
            <div class="bg-red-50 border border-red-200 rounded-lg p-3 text-sm text-red-700">
                <ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
            @endif

            <div class="flex gap-3 pt-2">
                <a href="{{ route('logistics.index') }}" class="btn-secondary px-5 py-2.5">Annuler</a>
                <button type="submit" class="btn-primary flex-1 py-2.5">Mettre à jour</button>
            </div>
        </form>
    </div>
</div>
@endsection
