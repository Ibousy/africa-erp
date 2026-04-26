@extends('layouts.app')
@section('title', 'Recrutement')
@section('page-title', 'Recrutement & Candidatures')
@section('header-actions')
    <a href="{{ route('hr.recruitment.create') }}" class="btn-primary">+ Nouvelle offre</a>
@endsection

@section('content')
<div class="mt-2 space-y-4">
    <div class="grid grid-cols-3 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs text-gray-500 uppercase font-semibold">Postes ouverts</p>
            <p class="text-2xl font-bold mt-1" style="color:var(--clr-p)">{{ $stats['ouvert'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs text-gray-500 uppercase font-semibold">Total candidatures</p>
            <p class="text-2xl font-bold text-gray-800 mt-1">{{ $stats['candidats'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs text-gray-500 uppercase font-semibold">Recrutements effectués</p>
            <p class="text-2xl font-bold text-green-600 mt-1">{{ $stats['embauche'] }}</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="px-5 py-4 border-b border-gray-100">
            <h3 class="font-semibold text-gray-800 text-sm">Offres d'emploi</h3>
        </div>
        <div class="divide-y divide-gray-50">
            @forelse($postings as $post)
            <div class="px-5 py-4 flex items-center justify-between hover:bg-gray-50">
                <div class="flex-1">
                    <div class="flex items-center gap-3">
                        <p class="font-semibold text-gray-800">{{ $post->title }}</p>
                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full
                            {{ $post->status === 'ouvert' ? 'bg-green-100 text-green-700' :
                               ($post->status === 'pourvue' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-500') }}">
                            {{ $post->status === 'ouvert' ? 'Ouvert' : ($post->status === 'pourvue' ? 'Pourvu' : 'Fermé') }}
                        </span>
                        <span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded">{{ \App\Models\JobPosting::TYPES[$post->type] ?? $post->type }}</span>
                    </div>
                    <p class="text-xs text-gray-400 mt-0.5">
                        {{ $post->department ?: 'Tous départements' }}
                        @if($post->salary_min) · {{ money($post->salary_min) }}@if($post->salary_max) – {{ money($post->salary_max) }}@endif FCFA @endif
                        @if($post->closes_at) · Clôture: {{ $post->closes_at->format('d/m/Y') }} @endif
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-sm font-semibold text-gray-700">{{ $post->applications_count }} candidat(s)</span>
                    <a href="{{ route('hr.recruitment.show', $post) }}" class="btn-secondary text-xs">Gérer</a>
                    <form method="POST" action="{{ route('hr.recruitment.destroy', $post) }}" onsubmit="return confirm('Supprimer ?')">
                        @csrf @method('DELETE')
                        <button class="text-red-400 hover:text-red-600 text-xs">Suppr.</button>
                    </form>
                </div>
            </div>
            @empty
            <div class="px-5 py-12 text-center text-gray-400">Aucune offre d'emploi</div>
            @endforelse
        </div>
        <div class="px-5 py-3">{{ $postings->links() }}</div>
    </div>
</div>
@endsection
