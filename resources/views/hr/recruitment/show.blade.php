@extends('layouts.app')
@section('title', $posting->title)
@section('page-title', $posting->title)
@section('header-actions')
    <a href="{{ route('hr.recruitment.index') }}" class="btn-secondary">← Retour</a>
@endsection

@section('content')
<div class="mt-2 grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Détails poste -->
    <div class="space-y-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h3 class="font-semibold text-gray-800 mb-4">Détails du poste</h3>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <dt class="text-gray-500">Type</dt>
                    <dd>{{ \App\Models\JobPosting::TYPES[$posting->type] ?? $posting->type }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Département</dt>
                    <dd>{{ $posting->department ?: '—' }}</dd>
                </div>
                @if($posting->salary_min)
                <div class="flex justify-between">
                    <dt class="text-gray-500">Salaire</dt>
                    <dd>{{ money($posting->salary_min) }}@if($posting->salary_max) – {{ money($posting->salary_max) }}@endif FCFA</dd>
                </div>
                @endif
                @if($posting->closes_at)
                <div class="flex justify-between">
                    <dt class="text-gray-500">Clôture</dt>
                    <dd>{{ $posting->closes_at->format('d/m/Y') }}</dd>
                </div>
                @endif
            </dl>

            @if($posting->description)
            <div class="mt-4 pt-4 border-t border-gray-100">
                <p class="text-xs text-gray-400 font-semibold uppercase mb-1">Description</p>
                <p class="text-sm text-gray-600 whitespace-pre-line">{{ $posting->description }}</p>
            </div>
            @endif
            @if($posting->requirements)
            <div class="mt-3">
                <p class="text-xs text-gray-400 font-semibold uppercase mb-1">Profil requis</p>
                <p class="text-sm text-gray-600 whitespace-pre-line">{{ $posting->requirements }}</p>
            </div>
            @endif
        </div>

        <!-- Changer statut -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <form method="POST" action="{{ route('hr.recruitment.status', $posting) }}" class="space-y-3">
                @csrf @method('PATCH')
                <label class="form-label">Statut de l'offre</label>
                <select name="status" class="form-input">
                    <option value="ouvert" {{ $posting->status === 'ouvert' ? 'selected' : '' }}>Ouvert</option>
                    <option value="ferme" {{ $posting->status === 'ferme' ? 'selected' : '' }}>Fermé</option>
                    <option value="pourvue" {{ $posting->status === 'pourvue' ? 'selected' : '' }}>Pourvu</option>
                </select>
                <button type="submit" class="btn-primary w-full text-sm">Mettre à jour</button>
            </form>
        </div>
    </div>

    <!-- Candidatures -->
    <div class="lg:col-span-2 space-y-4">
        <!-- Ajouter candidature -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h3 class="font-semibold text-gray-800 mb-3 text-sm">Ajouter une candidature</h3>
            <form method="POST" action="{{ route('hr.recruitment.apply', $posting) }}" class="grid grid-cols-2 gap-3">
                @csrf
                <div>
                    <label class="form-label text-xs">Nom du candidat *</label>
                    <input type="text" name="applicant_name" required class="form-input text-xs">
                </div>
                <div>
                    <label class="form-label text-xs">Téléphone</label>
                    <input type="text" name="phone" class="form-input text-xs">
                </div>
                <div>
                    <label class="form-label text-xs">Email</label>
                    <input type="email" name="email" class="form-input text-xs">
                </div>
                <div>
                    <label class="form-label text-xs">Notes</label>
                    <input type="text" name="notes" class="form-input text-xs">
                </div>
                <div class="col-span-2">
                    <button type="submit" class="btn-primary text-xs">Enregistrer la candidature</button>
                </div>
            </form>
        </div>

        <!-- Liste candidatures -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-semibold text-gray-800 text-sm">Candidatures ({{ $posting->applications->count() }})</h3>
            </div>
            <div class="divide-y divide-gray-50">
                @forelse($posting->applications as $app)
                <div class="px-5 py-4 flex items-center justify-between">
                    <div>
                        <p class="font-medium text-gray-800">{{ $app->applicant_name }}</p>
                        <p class="text-xs text-gray-400">
                            {{ $app->email ?: '' }} {{ $app->phone ? '· ' . $app->phone : '' }}
                            · Reçue le {{ $app->applied_at->format('d/m/Y') }}
                        </p>
                        @if($app->notes)<p class="text-xs text-gray-500 mt-0.5">{{ $app->notes }}</p>@endif
                    </div>
                    <div class="flex items-center gap-2">
                        <form method="POST" action="{{ route('hr.recruitment.application.update', $app) }}">
                            @csrf @method('PATCH')
                            <select name="status" onchange="this.form.submit()" class="text-xs border border-gray-200 rounded px-2 py-1">
                                @foreach(\App\Models\JobApplication::STATUSES as $k => $v)
                                <option value="{{ $k }}" {{ $app->status === $k ? 'selected' : '' }}>{{ $v }}</option>
                                @endforeach
                            </select>
                        </form>
                    </div>
                </div>
                @empty
                <div class="px-5 py-8 text-center text-gray-400 text-sm">Aucune candidature</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
