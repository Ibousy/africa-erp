@extends('layouts.app')
@section('title', 'Modifier prospect')
@section('page-title', 'Modifier prospect')

@section('content')
<div class="mt-2 max-w-xl">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <form method="POST" action="{{ route('crm.update', $crm) }}" class="space-y-4">
            @csrf @method('PUT')

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Nom du contact *</label>
                    <input type="text" name="name" value="{{ old('name', $crm->name) }}" required class="form-input">
                </div>
                <div>
                    <label class="form-label">Entreprise</label>
                    <input type="text" name="company" value="{{ old('company', $crm->company) }}" class="form-input">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Téléphone</label>
                    <input type="text" name="phone" value="{{ old('phone', $crm->phone) }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">Email</label>
                    <input type="email" name="email" value="{{ old('email', $crm->email) }}" class="form-input">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Source</label>
                    <select name="source" class="form-input">
                        <option value="">—</option>
                        @foreach(['Site web', 'Recommandation', 'Appel sortant', 'Salon / Expo', 'Autre'] as $src)
                        <option value="{{ $src }}" {{ old('source', $crm->source) === $src ? 'selected' : '' }}>{{ $src }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Statut *</label>
                    <select name="status" required class="form-input">
                        @foreach(['nouveau' => 'Nouveau', 'contacte' => 'Contacté', 'qualifie' => 'Qualifié', 'proposition' => 'Proposition', 'gagne' => 'Gagné', 'perdu' => 'Perdu'] as $val => $lbl)
                        <option value="{{ $val }}" {{ old('status', $crm->status) === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="form-label">Valeur estimée (FCFA)</label>
                <input type="number" name="estimated_value" value="{{ old('estimated_value', $crm->estimated_value) }}" step="1000" min="0" class="form-input">
            </div>

            <div>
                <label class="form-label">Notes</label>
                <textarea name="notes" rows="3" class="form-input">{{ old('notes', $crm->notes) }}</textarea>
            </div>

            <div class="flex gap-3 pt-2">
                <a href="{{ route('crm.index') }}" class="btn-secondary px-5 py-2.5">Annuler</a>
                <button type="submit" class="btn-primary flex-1 py-2.5">Enregistrer</button>
            </div>
        </form>
    </div>
</div>
@endsection
