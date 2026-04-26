@extends('layouts.app')
@section('title', 'Nouveau prospect')
@section('page-title', 'Nouveau prospect')

@section('content')
<div class="mt-2 max-w-xl">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <form method="POST" action="{{ route('crm.store') }}" class="space-y-4">
            @csrf

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Nom du contact *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required class="form-input">
                </div>
                <div>
                    <label class="form-label">Entreprise</label>
                    <input type="text" name="company" value="{{ old('company') }}" class="form-input">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Téléphone</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" placeholder="+221 77 000 00 00" class="form-input">
                </div>
                <div>
                    <label class="form-label">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="form-input">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Source</label>
                    <select name="source" class="form-input">
                        <option value="">—</option>
                        <option value="Site web" {{ old('source') === 'Site web' ? 'selected' : '' }}>Site web</option>
                        <option value="Recommandation" {{ old('source') === 'Recommandation' ? 'selected' : '' }}>Recommandation</option>
                        <option value="Appel sortant" {{ old('source') === 'Appel sortant' ? 'selected' : '' }}>Appel sortant</option>
                        <option value="Salon / Expo" {{ old('source') === 'Salon / Expo' ? 'selected' : '' }}>Salon / Expo</option>
                        <option value="Autre" {{ old('source') === 'Autre' ? 'selected' : '' }}>Autre</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Statut *</label>
                    <select name="status" required class="form-input">
                        <option value="nouveau" {{ old('status', 'nouveau') === 'nouveau' ? 'selected' : '' }}>Nouveau</option>
                        <option value="contacte" {{ old('status') === 'contacte' ? 'selected' : '' }}>Contacté</option>
                        <option value="qualifie" {{ old('status') === 'qualifie' ? 'selected' : '' }}>Qualifié</option>
                        <option value="proposition" {{ old('status') === 'proposition' ? 'selected' : '' }}>Proposition envoyée</option>
                        <option value="gagne" {{ old('status') === 'gagne' ? 'selected' : '' }}>Gagné</option>
                        <option value="perdu" {{ old('status') === 'perdu' ? 'selected' : '' }}>Perdu</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="form-label">Valeur estimée (FCFA)</label>
                <input type="number" name="estimated_value" value="{{ old('estimated_value') }}" step="1000" min="0" class="form-input">
            </div>

            <div>
                <label class="form-label">Notes</label>
                <textarea name="notes" rows="3" class="form-input" placeholder="Besoins identifiés, prochaine action...">{{ old('notes') }}</textarea>
            </div>

            <div class="flex gap-3 pt-2">
                <a href="{{ route('crm.index') }}" class="btn-secondary px-5 py-2.5">Annuler</a>
                <button type="submit" class="btn-primary flex-1 py-2.5">Ajouter le prospect</button>
            </div>
        </form>
    </div>
</div>
@endsection
