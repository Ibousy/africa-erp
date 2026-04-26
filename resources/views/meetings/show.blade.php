@extends('layouts.app')
@section('title', $meeting->title)
@section('page-title', $meeting->title)
@section('header-actions')
    <a href="{{ route('meetings.index') }}" class="btn-secondary">← Retour</a>
    @if($meeting->organized_by === auth()->id() || auth()->user()->isAdmin())
    <form method="POST" action="{{ route('meetings.destroy', $meeting) }}"
          onsubmit="return confirm('Supprimer cette réunion ?')">
        @csrf @method('DELETE')
        <button class="btn-secondary text-red-500 border-red-200 hover:bg-red-50">Supprimer</button>
    </form>
    @endif
@endsection

@section('content')
<div class="mt-2 max-w-2xl space-y-4">

    {{-- Info card --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        @php
            $statusBadge = match($meeting->status) {
                'done'      => 'bg-green-100 text-green-700',
                'cancelled' => 'bg-red-100 text-red-700',
                default     => 'bg-blue-100 text-blue-700',
            };
            $statusLabel = match($meeting->status) {
                'done'      => '✅ Terminée',
                'cancelled' => '❌ Annulée',
                default     => '📅 Programmée',
            };
            $deptLabels = array_map(fn($d) => $departments[$d] ?? $d, $meeting->departments ?? []);
        @endphp
        <div class="flex items-start justify-between mb-4">
            <h3 class="font-semibold text-gray-800">Informations</h3>
            <span class="px-3 py-1 text-xs font-bold rounded-full {{ $statusBadge }}">{{ $statusLabel }}</span>
        </div>
        <dl class="grid grid-cols-2 gap-3 text-sm">
            <div>
                <dt class="text-gray-500">Date</dt>
                <dd class="font-medium text-gray-800">{{ $meeting->scheduled_at->format('d/m/Y H:i') }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Durée</dt>
                <dd class="font-medium text-gray-800">
                    @php
                        $dm = $meeting->duration_minutes;
                        if ($dm < 60) { $dlabel = $dm.' min'; }
                        elseif ($dm === 60) { $dlabel = '1 h'; }
                        else { $h = intdiv($dm,60); $m = $dm%60; $dlabel = $h.'h'.($m ? $m.'min' : ''); }
                    @endphp
                    {{ $dlabel }}
                </dd>
            </div>
            <div>
                <dt class="text-gray-500">Organisateur</dt>
                <dd class="font-medium text-gray-800">{{ $meeting->organizer->name }}</dd>
            </div>
            @if($meeting->location)
            <div>
                <dt class="text-gray-500">Lieu</dt>
                <dd class="font-medium text-gray-800">📍 {{ $meeting->location }}</dd>
            </div>
            @endif
        </dl>
        <div class="mt-3 pt-3 border-t border-gray-100">
            <p class="text-xs text-gray-500 font-semibold uppercase mb-2">Participants</p>
            <div class="flex flex-wrap gap-1.5">
                @foreach($deptLabels as $dl)
                <span class="px-2.5 py-1 text-xs bg-blue-50 text-blue-700 rounded-full border border-blue-100 font-medium">{{ $dl }}</span>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Meet link with countdown --}}
    @if(($meeting->meet_url || $meeting->zoom_join_url) && $meeting->status !== 'cancelled')
    @php
        $startTs = $meeting->scheduled_at->timestamp;
        $endTs   = $meeting->scheduled_at->copy()->addMinutes($meeting->duration_minutes)->timestamp;
        $openTs  = $startTs - 15 * 60; // open 15 min before
        $meetUrl = $meeting->meet_url ?? '';
        $zoomUrl = $meeting->zoom_join_url ?? '';
        $zoomStartUrl = $meeting->zoom_start_url ?? '';
        $isOrganizer  = $meeting->organized_by === auth()->id() || auth()->user()->isAdmin();
    @endphp

    <div id="meet-card" class="rounded-xl shadow-sm border p-5 transition-all duration-500"
         style="background: linear-gradient(135deg,#f0fdf4 0%,#eff6ff 100%); border-color:#bbf7d0;">

        {{-- Header --}}
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.069A1 1 0 0121 8.868v6.264a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                </svg>
                <h3 class="font-semibold text-gray-800">Réunion en ligne</h3>
            </div>
            {{-- Live indicator (shown when meeting is active) --}}
            <span id="live-badge" class="hidden items-center gap-1.5 px-2.5 py-1 bg-red-100 text-red-700 text-xs font-bold rounded-full">
                <span class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></span> EN DIRECT
            </span>
        </div>

        {{-- State: countdown --}}
        <div id="state-countdown" class="hidden text-center py-4">
            <p class="text-xs text-gray-500 mb-1">La réunion ouvre dans</p>
            <p id="countdown-display" class="text-3xl font-bold font-mono text-gray-800"></p>
            <p class="text-xs text-gray-400 mt-1">Le bouton s'activera 15 min avant l'heure prévue</p>
            @if($meetUrl)
            <p class="text-xs font-mono text-gray-400 mt-3 break-all">{{ $meetUrl }}</p>
            @endif
        </div>

        {{-- State: active --}}
        <div id="state-active" class="hidden">
            <div class="flex flex-wrap gap-3 items-center">
                @if($meetUrl)
                <a href="{{ $meetUrl }}" target="_blank" id="btn-meet"
                   class="inline-flex items-center gap-2 px-6 py-3 rounded-xl text-sm font-bold text-white shadow-lg transition hover:scale-105 active:scale-100"
                   style="background: linear-gradient(135deg,#16a34a,#15803d);">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.069A1 1 0 0121 8.868v6.264a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                    🎥 Rejoindre le Meet
                </a>
                <span class="text-xs font-mono text-gray-500 bg-white/80 px-3 py-2 rounded-lg border border-gray-200 break-all">{{ $meetUrl }}</span>
                @endif
                @if($zoomUrl)
                <a href="{{ $zoomUrl }}" target="_blank"
                   class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg text-sm font-semibold text-white hover:opacity-90 transition"
                   style="background:#2D8CFF;">
                    Rejoindre Zoom
                </a>
                @if($isOrganizer && $zoomStartUrl)
                <a href="{{ $zoomStartUrl }}" target="_blank"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold text-white bg-blue-700 hover:opacity-90 transition">
                    Démarrer (hôte)
                </a>
                @endif
                @endif
            </div>
        </div>

        {{-- State: ended --}}
        <div id="state-ended" class="hidden text-center py-3">
            <p class="text-sm text-gray-400">⏹ La réunion est terminée.</p>
        </div>

    </div>
    @endif

    {{-- Agenda --}}
    @if($meeting->agenda)
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <h3 class="font-semibold text-gray-800 mb-3">Ordre du jour</h3>
        <div class="text-sm text-gray-700 whitespace-pre-line leading-relaxed">{{ $meeting->agenda }}</div>
    </div>
    @endif

    {{-- Minutes / compte rendu --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <h3 class="font-semibold text-gray-800 mb-3">Compte rendu</h3>

        @if($meeting->minutes && $meeting->status === 'done')
        <div class="text-sm text-gray-700 whitespace-pre-line leading-relaxed bg-gray-50 rounded-lg p-3 mb-4">{{ $meeting->minutes }}</div>
        @endif

        @if($meeting->organized_by === auth()->id() || auth()->user()->isAdmin())
        <form method="POST" action="{{ route('meetings.minutes', $meeting) }}" class="space-y-3">
            @csrf @method('PATCH')
            <textarea name="minutes" rows="5" class="form-input text-sm"
                placeholder="Résumé des décisions, actions à prendre…">{{ old('minutes', $meeting->minutes) }}</textarea>
            <div class="flex gap-3 items-center">
                <select name="status" class="form-input w-44">
                    <option value="scheduled" {{ $meeting->status === 'scheduled' ? 'selected':'' }}>📅 Programmée</option>
                    <option value="done"      {{ $meeting->status === 'done'      ? 'selected':'' }}>✅ Terminée</option>
                    <option value="cancelled" {{ $meeting->status === 'cancelled' ? 'selected':'' }}>❌ Annulée</option>
                </select>
                <button type="submit" class="btn-primary text-sm px-5">Enregistrer</button>
            </div>
        </form>
        @elseif(!$meeting->minutes)
        <p class="text-sm text-gray-400 italic">Aucun compte rendu pour l'instant.</p>
        @endif
    </div>

</div>
@endsection

@if(($meeting->meet_url || $meeting->zoom_join_url) && $meeting->status !== 'cancelled')
@push('scripts')
<script>
const OPEN_TS  = {{ $openTs }};
const START_TS = {{ $startTs }};
const END_TS   = {{ $endTs }};

function pad(n) { return String(n).padStart(2, '0'); }
function fmtCountdown(secs) {
    if (secs <= 0) return '00:00';
    const h = Math.floor(secs / 3600);
    const m = Math.floor((secs % 3600) / 60);
    const s = secs % 60;
    return (h > 0 ? pad(h) + ':' : '') + pad(m) + ':' + pad(s);
}

function tick() {
    const now  = Math.floor(Date.now() / 1000);
    const card = document.getElementById('meet-card');
    if (!card) return;

    const els = {
        countdown : document.getElementById('state-countdown'),
        active    : document.getElementById('state-active'),
        ended     : document.getElementById('state-ended'),
        live      : document.getElementById('live-badge'),
        display   : document.getElementById('countdown-display'),
    };

    if (now < OPEN_TS) {
        // Locked — too early
        els.countdown.classList.remove('hidden');
        els.active.classList.add('hidden');
        els.ended.classList.add('hidden');
        els.live.classList.add('hidden');
        els.display.textContent = fmtCountdown(OPEN_TS - now);
        card.style.borderColor  = '#d1d5db';
        card.style.background   = 'linear-gradient(135deg,#f9fafb,#f3f4f6)';

    } else if (now <= END_TS) {
        // Active — meeting window open
        els.countdown.classList.add('hidden');
        els.active.classList.remove('hidden');
        els.ended.classList.add('hidden');
        card.style.borderColor = '#86efac';
        card.style.background  = 'linear-gradient(135deg,#f0fdf4,#dcfce7)';
        if (now >= START_TS) {
            els.live.classList.remove('hidden');
            els.live.classList.add('flex');
        }

    } else {
        // Ended
        els.countdown.classList.add('hidden');
        els.active.classList.add('hidden');
        els.ended.classList.remove('hidden');
        els.live.classList.add('hidden');
        card.style.borderColor = '#e5e7eb';
        card.style.background  = '#f9fafb';
    }
}

tick();
setInterval(tick, 1000);
</script>
@endpush
@endif
