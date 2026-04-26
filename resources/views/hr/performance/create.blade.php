@extends('layouts.app')
@section('title', 'Nouvelle évaluation')
@section('page-title', 'Nouvelle évaluation de performance')

@section('content')
<div class="mt-2 max-w-2xl">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <form method="POST" action="{{ route('hr.performance.store') }}" class="space-y-5">
            @csrf
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="form-label">Employé *</label>
                    <select name="employee_id" required class="form-input">
                        <option value="">Sélectionner...</option>
                        @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" {{ old('employee_id') == $emp->id ? 'selected' : '' }}>{{ $emp->name }} — {{ $emp->position }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Période *</label>
                    <input type="text" name="period" value="{{ old('period', now()->format('Y') . '-Q' . ceil(now()->month / 3)) }}" required placeholder="ex: 2024-Q1 ou 2024" class="form-input">
                </div>
                <div>
                    <label class="form-label">Date d'évaluation *</label>
                    <input type="date" name="reviewed_at" value="{{ old('reviewed_at', now()->format('Y-m-d')) }}" required class="form-input">
                </div>
            </div>

            <!-- Grille de notation -->
            <div class="bg-gray-50 rounded-xl p-5 space-y-4">
                <h3 class="font-semibold text-gray-700 text-sm">Grille de notation (0 = insuffisant, 5 = excellent)</h3>
                @foreach([
                    'punctuality_score'  => 'Ponctualité & Assiduité',
                    'productivity_score' => 'Productivité',
                    'quality_score'      => 'Qualité du travail',
                    'teamwork_score'     => 'Travail en équipe',
                    'initiative_score'   => 'Initiative & Autonomie',
                ] as $field => $label)
                <div class="flex items-center justify-between">
                    <label class="text-sm font-medium text-gray-700 w-56">{{ $label }}</label>
                    <div class="flex gap-2">
                        @for($i = 0; $i <= 5; $i++)
                        <label class="flex flex-col items-center gap-1 cursor-pointer">
                            <input type="radio" name="{{ $field }}" value="{{ $i }}" {{ old($field, 3) == $i ? 'checked' : '' }} class="accent-orange-500">
                            <span class="text-xs text-gray-500">{{ $i }}</span>
                        </label>
                        @endfor
                    </div>
                </div>
                @endforeach
            </div>

            <div>
                <label class="form-label">Points forts</label>
                <textarea name="strengths" rows="2" class="form-input" placeholder="Ce que l'employé fait bien...">{{ old('strengths') }}</textarea>
            </div>
            <div>
                <label class="form-label">Axes d'amélioration</label>
                <textarea name="improvements" rows="2" class="form-input" placeholder="Ce qu'il peut améliorer...">{{ old('improvements') }}</textarea>
            </div>
            <div>
                <label class="form-label">Notes</label>
                <textarea name="notes" rows="2" class="form-input">{{ old('notes') }}</textarea>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary flex-1 py-2.5">Enregistrer l'évaluation</button>
                <a href="{{ route('hr.performance.index') }}" class="btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection
