@extends('layouts.app')
@section('title', 'Salle de réunion')
@section('page-title', 'Salle de réunion')
@section('header-actions')
    <a href="{{ route('meetings.create') }}" class="btn-primary">+ Planifier une réunion</a>
@endsection

@section('content')
<div class="mt-2 space-y-6">

    {{-- Upcoming --}}
    <div>
        <h3 class="text-sm font-semibold text-gray-500 uppercase mb-3">À venir ({{ $upcoming->count() }})</h3>
        @forelse($upcoming as $m)
        @php
            $deptLabels = array_map(fn($d) => $departments[$d] ?? $d, $m->departments ?? []);
        @endphp
        <div class="bg-white rounded-xl shadow-sm border border-blue-100 p-5 flex gap-5 mb-3">
            {{-- Date block --}}
            <div class="shrink-0 w-16 text-center">
                <p class="text-2xl font-bold text-blue-600 leading-none">{{ $m->scheduled_at->format('d') }}</p>
                <p class="text-xs text-gray-500 uppercase">{{ $m->scheduled_at->format('M') }}</p>
                <p class="text-xs font-semibold text-gray-700 mt-1">{{ $m->scheduled_at->format('H:i') }}</p>
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="font-semibold text-gray-800">{{ $m->title }}</p>
                        <p class="text-xs text-gray-500 mt-0.5">
                            Organisé par <strong>{{ $m->organizer->name }}</strong>
                            · {{ $m->duration_minutes }} min
                            @if($m->location) · 📍 {{ $m->location }} @endif
                        </p>
                        <div class="flex flex-wrap gap-1 mt-2">
                            @foreach($deptLabels as $dl)
                            <span class="px-2 py-0.5 text-xs bg-blue-50 text-blue-700 rounded-full border border-blue-100">{{ $dl }}</span>
                            @endforeach
                        </div>
                        @if($m->agenda)
                        <p class="text-xs text-gray-500 mt-2 line-clamp-2">{{ $m->agenda }}</p>
                        @endif
                    </div>
                    <a href="{{ route('meetings.show', $m) }}" class="shrink-0 btn-secondary text-xs px-3 py-1.5">Détail</a>
                </div>
            </div>
        </div>
        @empty
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm py-10 text-center text-gray-400">
            <div class="text-4xl mb-2">📅</div>
            <p class="text-sm font-medium">Aucune réunion à venir</p>
            <p class="text-xs mt-1"><a href="{{ route('meetings.create') }}" class="hover:underline" style="color:var(--clr-p)">Planifier une réunion →</a></p>
        </div>
        @endforelse
    </div>

    {{-- Past --}}
    @if($past->count())
    <div>
        <h3 class="text-sm font-semibold text-gray-500 uppercase mb-3">Historique</h3>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 divide-y divide-gray-50">
            @foreach($past as $m)
            @php
                $deptLabels = array_map(fn($d) => $departments[$d] ?? $d, $m->departments ?? []);
                $statusBadge = match($m->status) {
                    'done'      => 'bg-green-100 text-green-700',
                    'cancelled' => 'bg-red-100 text-red-700',
                    default     => 'bg-gray-100 text-gray-500',
                };
                $statusLabel = match($m->status) {
                    'done'      => '✅ Terminée',
                    'cancelled' => '❌ Annulée',
                    default     => '⏳ Passée',
                };
            @endphp
            <div class="px-5 py-3 flex items-center justify-between gap-4">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="font-medium text-sm text-gray-700">{{ $m->title }}</span>
                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $statusBadge }}">{{ $statusLabel }}</span>
                    </div>
                    <p class="text-xs text-gray-400 mt-0.5">
                        {{ $m->scheduled_at->format('d/m/Y H:i') }}
                        · {{ implode(', ', $deptLabels) }}
                    </p>
                </div>
                <a href="{{ route('meetings.show', $m) }}" class="text-xs font-medium hover:underline shrink-0" style="color:var(--clr-p)">Voir →</a>
            </div>
            @endforeach
        </div>
    </div>
    @endif

</div>
@endsection
