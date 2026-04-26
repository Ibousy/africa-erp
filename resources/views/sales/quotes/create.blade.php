@extends('layouts.app')
@section('title', 'Nouveau devis')
@section('page-title', 'Nouveau devis')

@section('content')
<div class="mt-2 max-w-4xl">
    <form method="POST" action="{{ route('quotes.store') }}" id="quote-form" class="space-y-4">
        @csrf
        <!-- En-tête -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="font-semibold text-gray-800 mb-4 text-sm">Informations devis</h3>
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="form-label">Client *</label>
                    <select name="client_id" required class="form-input">
                        <option value="">Sélectionner...</option>
                        @foreach($clients as $c)
                        <option value="{{ $c->id }}" {{ old('client_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Date d'émission *</label>
                    <input type="date" name="issue_date" value="{{ old('issue_date', date('Y-m-d')) }}" required class="form-input">
                </div>
                <div>
                    <label class="form-label">Valide jusqu'au</label>
                    <input type="date" name="valid_until" value="{{ old('valid_until', now()->addDays(30)->format('Y-m-d')) }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">TVA (%)</label>
                    <input type="number" name="tax_rate" value="{{ old('tax_rate', 18) }}" min="0" max="100" step="0.5" class="form-input" id="tax-rate" oninput="calcGrand()">
                </div>
                <div class="col-span-2">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" rows="2" class="form-input">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>

        <!-- Lignes devis (same pattern as purchase orders) -->
        @include('sales._line_items_form', ['formId' => 'quote', 'products' => $products])

        <div class="flex gap-3">
            <a href="{{ route('quotes.index') }}" class="btn-secondary px-5 py-2.5">Annuler</a>
            <button type="submit" class="btn-primary flex-1 py-2.5">Créer le devis →</button>
        </div>
    </form>
</div>
@endsection
