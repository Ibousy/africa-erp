@extends('layouts.app')
@section('title', 'Nouvelle expédition')
@section('page-title', 'Nouvelle expédition / réception')

@section('content')
<div class="mt-2 max-w-2xl">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <form method="POST" action="{{ route('logistics.store') }}" class="space-y-4">
            @csrf

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Référence *</label>
                    <input type="text" name="reference" value="{{ old('reference') }}" required placeholder="EXP-2024-001" class="form-input">
                </div>
                <div>
                    <label class="form-label">Type de mouvement *</label>
                    <select name="type" required class="form-input">
                        <option value="sortant" {{ old('type', 'sortant') === 'sortant' ? 'selected' : '' }}>Sortant — Expédition / Bon de sortie</option>
                        <option value="entrant" {{ old('type') === 'entrant' ? 'selected' : '' }}>Entrant — Réception matières / produits</option>
                        <option value="retour"  {{ old('type') === 'retour'  ? 'selected' : '' }}>Retour — Retour client / fournisseur</option>
                    </select>
                </div>
            </div>

            <!-- Produit concerné -->
            <div class="border border-gray-100 rounded-xl p-4 bg-gray-50 space-y-3">
                <p class="text-xs font-semibold text-gray-600 uppercase">Produit concerné (optionnel — met à jour le stock automatiquement à la livraison)</p>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Matière première / Produit fini</label>
                        <select name="product_id" class="form-input">
                            <option value="">Aucun produit lié</option>
                            @foreach($products as $p)
                            <option value="{{ $p->id }}" {{ old('product_id') == $p->id ? 'selected' : '' }}>
                                {{ $p->name }} ({{ $p->code }}) — Stock : {{ number_format($p->quantity_in_stock, 2) }} {{ $p->unit }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Quantité</label>
                        <input type="number" name="quantity" value="{{ old('quantity') }}" step="0.01" min="0.01" placeholder="0.00" class="form-input">
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Contact (client / fournisseur / service) *</label>
                    <input type="text" name="contact_name" value="{{ old('contact_name') }}" required placeholder="Ex : BTP Sénégal, Production..." class="form-input">
                </div>
                <div>
                    <label class="form-label">Origine / Destination *</label>
                    <input type="text" name="origin_destination" value="{{ old('origin_destination') }}" required placeholder="Dakar, Atelier A..." class="form-input">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Transporteur</label>
                    <input type="text" name="carrier" value="{{ old('carrier') }}" placeholder="DHL, Senpost, Interne..." class="form-input">
                </div>
                <div>
                    <label class="form-label">Poids total (kg)</label>
                    <input type="number" name="weight_kg" value="{{ old('weight_kg') }}" step="0.01" min="0" class="form-input">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Date de départ</label>
                    <input type="date" name="departure_date" value="{{ old('departure_date', date('Y-m-d')) }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">Date d'arrivée prévue</label>
                    <input type="date" name="arrival_date" value="{{ old('arrival_date') }}" class="form-input">
                </div>
            </div>

            <div>
                <label class="form-label">Statut initial</label>
                <select name="status" class="form-input">
                    <option value="en_attente" {{ old('status', 'en_attente') === 'en_attente' ? 'selected' : '' }}>En attente</option>
                    <option value="en_transit" {{ old('status') === 'en_transit' ? 'selected' : '' }}>En transit</option>
                    <option value="livre"      {{ old('status') === 'livre'      ? 'selected' : '' }}>Livré / Réceptionné (met à jour le stock immédiatement)</option>
                    <option value="annule"     {{ old('status') === 'annule'     ? 'selected' : '' }}>Annulé</option>
                </select>
            </div>

            <div>
                <label class="form-label">Notes</label>
                <textarea name="notes" rows="3" class="form-input" placeholder="Instructions, numéro de tracking...">{{ old('notes') }}</textarea>
            </div>

            <div class="flex gap-3 pt-2">
                <a href="{{ route('logistics.index') }}" class="btn-secondary px-5 py-2.5">Annuler</a>
                <button type="submit" class="btn-primary flex-1 py-2.5">Enregistrer</button>
            </div>
        </form>
    </div>
</div>
@endsection
