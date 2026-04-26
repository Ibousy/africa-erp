@extends('layouts.app')
@section('title', 'Chat inter-départements')
@section('page-title', 'Messagerie')

@section('content')
<div class="mt-2 flex gap-4" style="height: calc(100vh - 170px); min-height: 480px;">

    <!-- Sidebar: department list -->
    <div class="w-56 shrink-0 bg-white rounded-xl shadow-sm border border-gray-100 flex flex-col overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100">
            <p class="text-xs font-semibold text-gray-500 uppercase">Conversations</p>
            <p class="text-xs text-gray-400 mt-0.5">Moi: <strong class="text-gray-600">{{ $departments[$myDept] ?? $myDept }}</strong></p>
        </div>
        <div class="flex-1 overflow-y-auto py-1">
            @foreach($departments as $key => $label)
            @if($key !== $myDept)
            <a href="{{ route('chat.index', ['with' => $key]) }}"
               class="flex items-center justify-between px-4 py-2.5 text-sm transition-colors {{ $withDept === $key ? 'font-semibold' : 'text-gray-600 hover:bg-gray-50' }}"
               style="{{ $withDept === $key ? 'background: var(--clr-p-light, #eff6ff); color: var(--clr-p);' : '' }}">
                <span>{{ $label }}</span>
                @if(($unreadCounts[$key] ?? 0) > 0)
                <span class="text-xs font-bold px-1.5 py-0.5 rounded-full text-white" style="background: var(--clr-p);">
                    {{ $unreadCounts[$key] }}
                </span>
                @endif
            </a>
            @endif
            @endforeach
        </div>
    </div>

    <!-- Chat panel -->
    <div class="flex-1 bg-white rounded-xl shadow-sm border border-gray-100 flex flex-col overflow-hidden">

        <!-- Chat header -->
        <div class="px-5 py-3 border-b border-gray-100 flex items-center gap-3 shrink-0">
            <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold text-white" style="background: var(--clr-p);">
                {{ strtoupper(substr($departments[$withDept] ?? $withDept, 0, 1)) }}
            </div>
            <div>
                <p class="font-semibold text-gray-800 text-sm">{{ $departments[$withDept] ?? $withDept }}</p>
                <p class="text-xs text-gray-400" id="chat-status">En ligne</p>
            </div>
        </div>

        <!-- Messages area -->
        <div class="flex-1 overflow-y-auto px-5 py-4 space-y-3" id="messages-container">
            @forelse($messages as $msg)
            @php $mine = $msg->from_department === $myDept; @endphp
            <div class="flex {{ $mine ? 'justify-end' : 'justify-start' }}" data-msg-id="{{ $msg->id }}">
                <div class="max-w-xs lg:max-w-md">
                    @if(!$mine)
                    <p class="text-xs text-gray-400 mb-1 ml-1">{{ $msg->sender?->name ?? ($departments[$msg->from_department] ?? $msg->from_department) }}</p>
                    @endif
                    @if($msg->type === 'voice')
                    <div class="px-3 py-2.5 rounded-2xl {{ $mine ? 'rounded-tr-sm' : 'rounded-tl-sm' }} border {{ $mine ? 'border-blue-200' : 'border-gray-200' }}" style="{{ $mine ? 'background: var(--clr-p); color: white;' : 'background: #f3f4f6;' }}">
                        <div class="flex items-center gap-2">
                            <span class="text-base">🎤</span>
                            <audio controls class="h-8 w-40" style="filter: {{ $mine ? 'invert(1)' : 'none' }};" preload="none">
                                <source src="{{ Storage::url($msg->voice_path) }}" type="audio/webm">
                            </audio>
                            @if($msg->voice_duration)
                            <span class="text-xs opacity-75">{{ gmdate('i:s', $msg->voice_duration) }}</span>
                            @endif
                        </div>
                    </div>
                    @else
                    <div class="px-3.5 py-2.5 rounded-2xl {{ $mine ? 'rounded-tr-sm' : 'rounded-tl-sm' }} text-sm" style="{{ $mine ? 'background: var(--clr-p); color: white;' : 'background: #f3f4f6; color: #1f2937;' }}">
                        {{ $msg->body }}
                    </div>
                    @endif
                    <p class="text-xs text-gray-400 mt-1 {{ $mine ? 'text-right' : 'text-left' }} px-1">
                        {{ $msg->created_at->format('H:i') }}
                    </p>
                </div>
            </div>
            @empty
            <div class="flex flex-col items-center justify-center h-full text-center text-gray-400 py-16">
                <div class="text-5xl mb-3">💬</div>
                <p class="font-medium text-sm">Aucun message pour l'instant</p>
                <p class="text-xs mt-1">Envoyez le premier message à {{ $departments[$withDept] ?? $withDept }}</p>
            </div>
            @endforelse
        </div>

        <!-- Voice preview bar (hidden by default) -->
        <div id="voice-preview" class="hidden px-5 py-3 border-t border-gray-100 bg-yellow-50 flex items-center gap-3">
            <span class="text-sm text-yellow-700 font-medium">🎤 Message vocal prêt</span>
            <span id="voice-duration" class="text-xs text-yellow-600"></span>
            <audio id="voice-playback" controls class="h-8 flex-1"></audio>
            <button id="voice-send-btn" class="btn-primary text-xs px-3 py-1.5">Envoyer</button>
            <button id="voice-cancel-btn" class="btn-secondary text-xs px-3 py-1.5">Annuler</button>
        </div>

        <!-- Recording indicator -->
        <div id="recording-indicator" class="hidden px-5 py-2 border-t border-gray-100 bg-red-50 flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></span>
            <span class="text-xs text-red-600 font-medium">Enregistrement en cours…</span>
            <span id="rec-timer" class="text-xs text-red-500 font-mono ml-auto">0:00</span>
        </div>

        <!-- Input bar -->
        <div class="px-4 py-3 border-t border-gray-100 flex items-end gap-2 shrink-0">
            <form id="chat-form" class="flex-1 flex items-end gap-2">
                @csrf
                <input type="hidden" name="to_department" value="{{ $withDept }}">
                <textarea id="msg-input"
                    name="body"
                    rows="1"
                    placeholder="Écrire un message à {{ $departments[$withDept] ?? $withDept }}…"
                    class="form-input flex-1 resize-none text-sm py-2 leading-5"
                    style="max-height: 120px; overflow-y: auto;"
                    onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();sendText();}"></textarea>
                <button type="button" onclick="sendText()"
                    class="shrink-0 w-9 h-9 rounded-full flex items-center justify-center text-white transition"
                    style="background: var(--clr-p);" title="Envoyer">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z"/></svg>
                </button>
            </form>
            <!-- Mic button -->
            <button id="mic-btn"
                onclick="toggleRecording()"
                class="shrink-0 w-9 h-9 rounded-full flex items-center justify-center border-2 transition"
                style="border-color: var(--clr-p); color: var(--clr-p);"
                title="Message vocal">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7 4a3 3 0 016 0v4a3 3 0 11-6 0V4zm4 10.93A7.001 7.001 0 0017 8a1 1 0 10-2 0A5 5 0 015 8a1 1 0 00-2 0 7.001 7.001 0 006 6.93V17H6a1 1 0 100 2h8a1 1 0 100-2h-3v-2.07z" clip-rule="evenodd"/></svg>
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const WITH_DEPT   = '{{ $withDept }}';
const CSRF_TOKEN  = '{{ csrf_token() }}';
let lastMsgId     = {{ $messages->isNotEmpty() ? $messages->last()->id : 0 }};
let mediaRecorder = null;
let audioChunks   = [];
let recInterval   = null;
let recSeconds    = 0;
let blobToSend    = null;

// ── Scroll to bottom ─────────────────────────────────────────
function scrollToBottom(smooth = false) {
    const c = document.getElementById('messages-container');
    c.scrollTo({ top: c.scrollHeight, behavior: smooth ? 'smooth' : 'instant' });
}
scrollToBottom();

// ── Send text message ─────────────────────────────────────────
async function sendText() {
    const input = document.getElementById('msg-input');
    const body  = input.value.trim();
    if (!body) return;
    input.value = '';
    input.style.height = 'auto';

    try {
        await fetch('{{ route("chat.store") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
            body: JSON.stringify({ to_department: WITH_DEPT, body }),
        });
        await loadMessages();
    } catch(e) { console.error(e); }
}

// ── Textarea auto-resize ──────────────────────────────────────
document.getElementById('msg-input').addEventListener('input', function() {
    this.style.height = 'auto';
    this.style.height = Math.min(this.scrollHeight, 120) + 'px';
});

// ── Poll for new messages every 5s ───────────────────────────
async function loadMessages() {
    try {
        const res  = await fetch(`{{ route("chat.messages") }}?with=${WITH_DEPT}`, { headers: { 'Accept': 'application/json' } });
        const data = await res.json();
        if (!data.length) return;

        const last = data[data.length - 1];
        if (last.id === lastMsgId) return;

        const container = document.getElementById('messages-container');
        const atBottom  = container.scrollHeight - container.scrollTop - container.clientHeight < 80;

        // Find new messages only
        const newMsgs = data.filter(m => m.id > lastMsgId);
        newMsgs.forEach(m => {
            lastMsgId = Math.max(lastMsgId, m.id);
            container.appendChild(buildBubble(m));
        });

        if (atBottom) scrollToBottom(true);
    } catch(e) {}
}

function buildBubble(m) {
    const wrap = document.createElement('div');
    wrap.className = `flex ${m.mine ? 'justify-end' : 'justify-start'}`;
    wrap.dataset.msgId = m.id;

    let inner = '';
    if (!m.mine) inner += `<p class="text-xs text-gray-400 mb-1 ml-1">${escHtml(m.sender)}</p>`;

    const bubbleStyle = m.mine ? 'background: var(--clr-p); color: white;' : 'background: #f3f4f6; color: #1f2937;';
    const radiusCls   = m.mine ? 'rounded-2xl rounded-tr-sm' : 'rounded-2xl rounded-tl-sm';

    if (m.type === 'voice') {
        const filter = m.mine ? 'filter: invert(1);' : '';
        inner += `
            <div class="px-3 py-2.5 ${radiusCls}" style="${bubbleStyle}">
                <div class="flex items-center gap-2">
                    <span class="text-base">🎤</span>
                    <audio controls class="h-8 w-40" preload="none" style="${filter}">
                        <source src="${escHtml(m.voice)}" type="audio/webm">
                    </audio>
                    ${m.duration ? `<span class="text-xs opacity-75">${fmtDur(m.duration)}</span>` : ''}
                </div>
            </div>`;
    } else {
        inner += `<div class="px-3.5 py-2.5 ${radiusCls} text-sm" style="${bubbleStyle}">${escHtml(m.body)}</div>`;
    }
    inner += `<p class="text-xs text-gray-400 mt-1 ${m.mine ? 'text-right' : 'text-left'} px-1">${escHtml(m.time)}</p>`;

    const inner2 = document.createElement('div');
    inner2.className = 'max-w-xs lg:max-w-md';
    inner2.innerHTML = inner;
    wrap.appendChild(inner2);
    return wrap;
}

function escHtml(s) {
    if (!s) return '';
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function fmtDur(s) {
    const m = Math.floor(s/60), sec = s%60;
    return `${m}:${String(sec).padStart(2,'0')}`;
}

setInterval(loadMessages, 5000);

// ── Voice recording ───────────────────────────────────────────
async function toggleRecording() {
    if (mediaRecorder && mediaRecorder.state === 'recording') {
        stopRecording();
    } else {
        await startRecording();
    }
}

async function startRecording() {
    try {
        const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
        audioChunks  = [];
        recSeconds   = 0;
        mediaRecorder = new MediaRecorder(stream, { mimeType: MediaRecorder.isTypeSupported('audio/webm') ? 'audio/webm' : 'audio/ogg' });

        mediaRecorder.ondataavailable = e => { if (e.data.size > 0) audioChunks.push(e.data); };
        mediaRecorder.onstop = () => {
            blobToSend = new Blob(audioChunks, { type: mediaRecorder.mimeType });
            const url  = URL.createObjectURL(blobToSend);
            document.getElementById('voice-playback').src = url;
            document.getElementById('voice-duration').textContent = fmtDur(recSeconds);
            document.getElementById('recording-indicator').classList.add('hidden');
            document.getElementById('voice-preview').classList.remove('hidden');
            // Stop all tracks
            stream.getTracks().forEach(t => t.stop());
        };

        mediaRecorder.start(250);

        // UI
        const btn = document.getElementById('mic-btn');
        btn.style.background = '#ef4444';
        btn.style.borderColor = '#ef4444';
        btn.style.color = 'white';
        document.getElementById('recording-indicator').classList.remove('hidden');

        recInterval = setInterval(() => {
            recSeconds++;
            document.getElementById('rec-timer').textContent = fmtDur(recSeconds);
            if (recSeconds >= 120) stopRecording(); // max 2 min
        }, 1000);

    } catch(e) {
        alert('Impossible d\'accéder au microphone. Vérifiez les permissions.');
    }
}

function stopRecording() {
    if (!mediaRecorder) return;
    clearInterval(recInterval);
    mediaRecorder.stop();
    const btn = document.getElementById('mic-btn');
    btn.style.background = '';
    btn.style.borderColor = 'var(--clr-p)';
    btn.style.color = 'var(--clr-p)';
}

function cancelVoice() {
    blobToSend = null;
    document.getElementById('voice-preview').classList.add('hidden');
    document.getElementById('voice-playback').src = '';
    mediaRecorder = null;
}

async function sendVoice() {
    if (!blobToSend) return;
    const btn = document.getElementById('voice-send-btn');
    btn.disabled = true;
    btn.textContent = 'Envoi…';

    const ext  = blobToSend.type.includes('ogg') ? 'ogg' : 'webm';
    const fd   = new FormData();
    fd.append('audio', blobToSend, `voice.${ext}`);
    fd.append('to_department', WITH_DEPT);
    fd.append('duration', recSeconds);
    fd.append('_token', CSRF_TOKEN);

    try {
        await fetch('{{ route("chat.voice") }}', { method: 'POST', body: fd });
        cancelVoice();
        await loadMessages();
    } catch(e) {
        btn.disabled = false;
        btn.textContent = 'Envoyer';
        alert('Erreur lors de l\'envoi.');
    }
}

document.getElementById('voice-send-btn').addEventListener('click', sendVoice);
document.getElementById('voice-cancel-btn').addEventListener('click', cancelVoice);
</script>
@endpush
