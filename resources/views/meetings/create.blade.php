@extends('layouts.app')
@section('title', 'Planifier une réunion')
@section('page-title', 'Planifier une réunion')
@section('header-actions')
    <a href="{{ route('meetings.index') }}" class="btn-secondary">← Retour</a>
@endsection

@section('content')
<div class="mt-2 max-w-xl">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <form method="POST" action="{{ route('meetings.store') }}" class="space-y-5">
            @csrf

            <div>
                <label class="form-label">Titre de la réunion *</label>
                <input type="text" name="title" value="{{ old('title') }}" required
                    class="form-input" placeholder="Ex: Revue hebdomadaire production">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Date et heure *</label>
                    <input type="datetime-local" name="scheduled_at"
                        value="{{ old('scheduled_at') }}" required class="form-input">
                </div>
                <div>
                    <label class="form-label">Durée (minutes) *</label>
                    <select name="duration_minutes" class="form-input">
                        @foreach([15,30,45,60,90,120,180,240] as $d)
                        @php
                            if ($d < 60) { $dlabel = $d . ' min'; }
                            elseif ($d === 60) { $dlabel = '1 h'; }
                            else { $h = intdiv($d,60); $m = $d%60; $dlabel = $h.'h'.($m ? $m.'min' : ''); }
                        @endphp
                        <option value="{{ $d }}" {{ old('duration_minutes', 60) == $d ? 'selected' : '' }}>{{ $dlabel }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="form-label">Lieu (optionnel)</label>
                <input type="text" name="location" value="{{ old('location') }}"
                    class="form-input" placeholder="Salle A, Teams, Zoom…">
            </div>

            <div>
                <label class="form-label">Départements invités *</label>
                <div class="grid grid-cols-2 gap-2 mt-1">
                    @foreach($departments as $key => $label)
                    <label class="flex items-center gap-2 p-2.5 rounded-lg border border-gray-100 hover:bg-gray-50 cursor-pointer text-sm">
                        <input type="checkbox" name="departments[]" value="{{ $key }}"
                            {{ in_array($key, old('departments', [])) ? 'checked' : '' }}
                            class="rounded border-gray-300 text-blue-600">
                        <span class="text-gray-700">{{ $label }}</span>
                    </label>
                    @endforeach
                </div>
                @error('departments') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="form-label">Ordre du jour</label>
                <textarea name="agenda" rows="4" class="form-input"
                    placeholder="1. Point sur la production&#10;2. Problèmes en cours&#10;3. Décisions à prendre…">{{ old('agenda') }}</textarea>
            </div>

            <div class="flex items-center gap-2 p-3 rounded-lg bg-green-50 border border-green-100 text-sm text-green-700">
                <svg class="w-4 h-4 shrink-0 text-green-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.069A1 1 0 0121 8.868v6.264a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                Un lien de réunion vidéo sera généré automatiquement.
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary flex-1 py-2.5">Planifier & Notifier les équipes</button>
                <a href="{{ route('meetings.index') }}" class="btn-secondary px-6">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection
