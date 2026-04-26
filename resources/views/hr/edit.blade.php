@extends('layouts.app')
@section('title', 'Modifier employé')
@section('page-title', 'Modifier employé')

@section('content')
<div class="mt-2 max-w-2xl">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <form method="POST" action="{{ route('hr.update', $hr) }}" class="space-y-4">
            @csrf @method('PUT')

            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="form-label">Nom complet *</label>
                    <input type="text" name="name" value="{{ old('name', $hr->name) }}" required class="form-input">
                </div>
                <div>
                    <label class="form-label">Poste *</label>
                    <input type="text" name="position" value="{{ old('position', $hr->position) }}" required class="form-input">
                </div>
                <div>
                    <label class="form-label">Service / Département</label>
                    <input type="text" name="department" value="{{ old('department', $hr->department) }}" class="form-input">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Téléphone</label>
                    <input type="text" name="phone" value="{{ old('phone', $hr->phone) }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">Email</label>
                    <input type="email" name="email" value="{{ old('email', $hr->email) }}" class="form-input">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Date d'embauche *</label>
                    <input type="date" name="hire_date" value="{{ old('hire_date', $hr->hire_date->format('Y-m-d')) }}" required class="form-input">
                </div>
                <div>
                    <label class="form-label">Salaire mensuel (FCFA)</label>
                    <input type="number" name="salary" value="{{ old('salary', $hr->salary) }}" step="1000" min="0" class="form-input">
                </div>
            </div>

            <div>
                <label class="form-label">Statut</label>
                <select name="status" class="form-input">
                    <option value="actif" {{ old('status', $hr->status) === 'actif' ? 'selected' : '' }}>Actif</option>
                    <option value="conge" {{ old('status', $hr->status) === 'conge' ? 'selected' : '' }}>En congé</option>
                    <option value="quitte" {{ old('status', $hr->status) === 'quitte' ? 'selected' : '' }}>Quitté</option>
                </select>
            </div>

            <div>
                <label class="form-label">Notes</label>
                <textarea name="notes" rows="3" class="form-input">{{ old('notes', $hr->notes) }}</textarea>
            </div>

            <div class="flex gap-3 pt-2">
                <a href="{{ route('hr.index') }}" class="btn-secondary px-5 py-2.5">Annuler</a>
                <button type="submit" class="btn-primary flex-1 py-2.5">Enregistrer</button>
            </div>
        </form>
    </div>
</div>
@endsection
