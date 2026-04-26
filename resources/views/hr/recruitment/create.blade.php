@extends('layouts.app')
@section('title', "Nouvelle offre d'emploi")
@section('page-title', "Nouvelle offre d'emploi")

@section('content')
<div class="mt-2 max-w-2xl">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <form method="POST" action="{{ route('hr.recruitment.store') }}" class="space-y-4">
            @csrf
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="form-label">Intitulé du poste *</label>
                    <input type="text" name="title" value="{{ old('title') }}" required placeholder="ex: Technicien de production" class="form-input">
                </div>
                <div>
                    <label class="form-label">Département</label>
                    <select name="department" class="form-input">
                        <option value="">— Tous —</option>
                        @foreach(\App\Models\User::DEPARTMENTS as $k => $v)
                        <option value="{{ $k }}" {{ old('department') === $k ? 'selected' : '' }}>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Type de contrat *</label>
                    <select name="type" required class="form-input">
                        @foreach(\App\Models\JobPosting::TYPES as $k => $v)
                        <option value="{{ $k }}" {{ old('type', 'cdi') === $k ? 'selected' : '' }}>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Salaire min (FCFA)</label>
                    <input type="number" name="salary_min" value="{{ old('salary_min') }}" min="0" step="1000" class="form-input">
                </div>
                <div>
                    <label class="form-label">Salaire max (FCFA)</label>
                    <input type="number" name="salary_max" value="{{ old('salary_max') }}" min="0" step="1000" class="form-input">
                </div>
                <div>
                    <label class="form-label">Date de clôture</label>
                    <input type="date" name="closes_at" value="{{ old('closes_at') }}" class="form-input">
                </div>
                <div class="col-span-2">
                    <label class="form-label">Description du poste</label>
                    <textarea name="description" rows="4" class="form-input" placeholder="Responsabilités, missions...">{{ old('description') }}</textarea>
                </div>
                <div class="col-span-2">
                    <label class="form-label">Profil recherché / Exigences</label>
                    <textarea name="requirements" rows="3" class="form-input" placeholder="Diplôme, expérience, compétences...">{{ old('requirements') }}</textarea>
                </div>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary flex-1 py-2.5">Publier l'offre →</button>
                <a href="{{ route('hr.recruitment.index') }}" class="btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection
