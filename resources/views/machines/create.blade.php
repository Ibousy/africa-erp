@extends('layouts.app')
@section('title', 'Nouvelle machine')
@section('page-title', 'Nouvelle machine')

@section('content')
<div class="max-w-xl mt-2">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <form method="POST" action="{{ route('machines.store') }}" class="space-y-4">
            @csrf
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Code *</label>
                    <input type="text" name="code" value="{{ old('code') }}" required class="form-input" placeholder="MCH-001">
                </div>
                <div>
                    <label class="form-label">Nom *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required class="form-input">
                </div>
                <div>
                    <label class="form-label">Type</label>
                    <input type="text" name="type" value="{{ old('type') }}" class="form-input" placeholder="Presse, Tour, Fraiseuse...">
                </div>
                <div>
                    <label class="form-label">Emplacement</label>
                    <input type="text" name="location" value="{{ old('location') }}" class="form-input" placeholder="Atelier A, Hall 2...">
                </div>
                <div>
                    <label class="form-label">Puissance (kW)</label>
                    <input type="number" name="power_kw" value="{{ old('power_kw') }}" min="0" step="0.1" class="form-input">
                </div>
                <div>
                    <label class="form-label">Date d'achat</label>
                    <input type="date" name="purchase_date" value="{{ old('purchase_date') }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">Statut *</label>
                    <select name="status" required class="form-input">
                        <option value="actif" {{ old('status', 'actif') === 'actif' ? 'selected' : '' }}>Actif</option>
                        <option value="en_panne" {{ old('status') === 'en_panne' ? 'selected' : '' }}>En panne</option>
                        <option value="maintenance" {{ old('status') === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="form-label">Description</label>
                <textarea name="description" rows="3" class="form-input">{{ old('description') }}</textarea>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary">Créer la machine</button>
                <a href="{{ route('machines.index') }}" class="btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection
