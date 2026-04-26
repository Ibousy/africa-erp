@extends('layouts.app')
@section('title', 'Modifier machine')
@section('page-title', 'Modifier ' . $machine->name)

@section('content')
<div class="max-w-xl mt-2">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <form method="POST" action="{{ route('machines.update', $machine) }}" class="space-y-4">
            @csrf @method('PUT')
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Code *</label>
                    <input type="text" name="code" value="{{ old('code', $machine->code) }}" required class="form-input">
                </div>
                <div>
                    <label class="form-label">Nom *</label>
                    <input type="text" name="name" value="{{ old('name', $machine->name) }}" required class="form-input">
                </div>
                <div>
                    <label class="form-label">Type</label>
                    <input type="text" name="type" value="{{ old('type', $machine->type) }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">Emplacement</label>
                    <input type="text" name="location" value="{{ old('location', $machine->location) }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">Puissance (kW)</label>
                    <input type="number" name="power_kw" value="{{ old('power_kw', $machine->power_kw) }}" min="0" step="0.1" class="form-input">
                </div>
                <div>
                    <label class="form-label">Date d'achat</label>
                    <input type="date" name="purchase_date" value="{{ old('purchase_date', $machine->purchase_date?->format('Y-m-d')) }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">Statut *</label>
                    <select name="status" required class="form-input">
                        <option value="actif" {{ old('status', $machine->status) === 'actif' ? 'selected' : '' }}>Actif</option>
                        <option value="en_panne" {{ old('status', $machine->status) === 'en_panne' ? 'selected' : '' }}>En panne</option>
                        <option value="maintenance" {{ old('status', $machine->status) === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="form-label">Description</label>
                <textarea name="description" rows="3" class="form-input">{{ old('description', $machine->description) }}</textarea>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary">Enregistrer</button>
                <a href="{{ route('machines.index') }}" class="btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection
