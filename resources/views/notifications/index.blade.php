@extends('layouts.app')
@section('title', 'Notifications')
@section('page-title', 'Notifications')
@section('header-actions')
    <form method="POST" action="{{ route('notifications.read-all') }}">
        @csrf
        <button class="btn-secondary text-sm">Tout supprimer</button>
    </form>
@endsection

@section('content')
<div class="mt-2 max-w-2xl space-y-2">
    @forelse($notifications as $n)
    <div class="bg-white rounded-xl border {{ $n->read_at ? 'border-gray-100' : 'border-blue-100 bg-blue-50/30' }} shadow-sm p-4 flex items-start gap-4">
        <div class="text-2xl shrink-0 mt-0.5">
            {{ ['bell' => '🔔', 'warning' => '⚠️', 'message' => '💬', 'truck' => '🚛', 'check' => '✅', 'x' => '❌', 'low_stock' => '📦', 'meeting' => '📅'][$n->icon] ?? '🔔' }}
        </div>
        <div class="flex-1 min-w-0">
            <p class="font-semibold text-sm text-gray-800 {{ $n->read_at ? '' : 'font-bold' }}">{{ $n->title }}</p>
            @if($n->body)
            <p class="text-sm text-gray-600 mt-0.5">{{ $n->body }}</p>
            @endif
            <p class="text-xs text-gray-400 mt-1">{{ $n->created_at->diffForHumans() }}</p>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            @if($n->link)
            <a href="{{ $n->link }}" class="text-xs font-medium hover:underline" style="color:var(--clr-p)">Voir →</a>
            @endif
            @if(!$n->read_at)
            <span class="w-2.5 h-2.5 rounded-full" style="background:var(--clr-p)"></span>
            @endif
        </div>
    </div>
    @empty
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm py-16 text-center text-gray-400">
        <div class="text-4xl mb-3">🔔</div>
        <p class="font-medium">Aucune notification</p>
    </div>
    @endforelse

</div>
@endsection
