@extends('layouts.app')
@section('title', 'Nouvelle commande client')
@section('page-title', 'Nouvelle commande client')

@section('content')
<div class="mt-2 max-w-4xl">
    <form method="POST" action="{{ route('orders.store') }}" id="order-form" class="space-y-4">
        @csrf
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="font-semibold text-gray-800 mb-4 text-sm">Informations commande</h3>
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
                    <label class="form-label">Date commande *</label>
                    <input type="date" name="order_date" value="{{ old('order_date', date('Y-m-d')) }}" required class="form-input">
                </div>
                <div>
                    <label class="form-label">Livraison prévue</label>
                    <input type="date" name="delivery_date" value="{{ old('delivery_date') }}" class="form-input">
                </div>
                <div class="col-span-2">
                    <label class="form-label">Adresse de livraison</label>
                    <input type="text" name="delivery_address" value="{{ old('delivery_address') }}" class="form-input">
                </div>
                <div class="col-span-2">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" rows="2" class="form-input">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>

        @include('sales._line_items_form', ['products' => $products])

        <div class="flex gap-3">
            <a href="{{ route('orders.index') }}" class="btn-secondary px-5 py-2.5">Annuler</a>
            <button type="submit" class="btn-primary flex-1 py-2.5">Créer la commande →</button>
        </div>
    </form>
</div>
@endsection
