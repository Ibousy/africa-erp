@extends('layouts.app')
@section('title', 'Gestion des Congés')
@section('page-title', 'Congés & Absences')
@section('header-actions')
    <a href="{{ route('hr.leaves.create') }}" class="btn-primary">+ Nouvelle demande</a>
@endsection

@section('content')
<div class="mt-2 space-y-4">
    <!-- Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs text-gray-500 uppercase font-semibold">Total demandes</p>
            <p class="text-2xl font-bold text-gray-800 mt-1">{{ $stats['total'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs text-gray-500 uppercase font-semibold">En attente</p>
            <p class="text-2xl font-bold text-yellow-500 mt-1">{{ $stats['en_attente'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs text-gray-500 uppercase font-semibold">Approuvés</p>
            <p class="text-2xl font-bold text-green-600 mt-1">{{ $stats['approuve'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs text-gray-500 uppercase font-semibold">Refusés</p>
            <p class="text-2xl font-bold text-red-500 mt-1">{{ $stats['refuse'] }}</p>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="px-5 py-4 border-b border-gray-100 flex flex-wrap items-center justify-between gap-3">
            <h3 class="font-semibold text-gray-800 text-sm">Demandes de congé</h3>
            <form method="GET" class="flex gap-2 flex-wrap">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Nom employé..." class="form-input text-xs w-40">
                <select name="type" class="form-input text-xs w-40">
                    <option value="">Tous les types</option>
                    @foreach(\App\Models\Leave::TYPES as $k => $v)
                    <option value="{{ $k }}" {{ request('type') === $k ? 'selected' : '' }}>{{ $v }}</option>
                    @endforeach
                </select>
                <select name="status" class="form-input text-xs w-36">
                    <option value="">Tous les statuts</option>
                    @foreach(\App\Models\Leave::STATUSES as $k => $v)
                    <option value="{{ $k }}" {{ request('status') === $k ? 'selected' : '' }}>{{ $v }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn-primary text-xs">Filtrer</button>
            </form>
        </div>
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100">
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Employé</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Type</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Période</th>
                    <th class="text-center px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Jours</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Motif</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Statut</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($leaves as $leave)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-full flex items-center justify-center text-white text-xs font-bold shrink-0" style="background-color: var(--clr-p)">
                                {{ strtoupper(substr($leave->employee->name, 0, 1)) }}
                            </div>
                            <span class="font-medium text-gray-800">{{ $leave->employee->name }}</span>
                        </div>
                    </td>
                    <td class="px-5 py-3 text-gray-700">{{ \App\Models\Leave::TYPES[$leave->type] ?? $leave->type }}</td>
                    <td class="px-5 py-3 text-gray-600 text-xs">
                        <p>{{ $leave->start_date->format('d/m/Y') }}</p>
                        <p class="text-gray-400">→ {{ $leave->end_date->format('d/m/Y') }}</p>
                    </td>
                    <td class="px-5 py-3 text-center font-semibold text-gray-800">{{ $leave->days }}j</td>
                    <td class="px-5 py-3 text-gray-500 text-xs max-w-xs truncate">{{ $leave->reason ?: '—' }}</td>
                    <td class="px-5 py-3">
                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full
                            {{ $leave->status === 'approuve' ? 'bg-green-100 text-green-700' :
                               ($leave->status === 'refuse'  ? 'bg-red-100 text-red-600'  : 'bg-yellow-100 text-yellow-700') }}">
                            {{ \App\Models\Leave::STATUSES[$leave->status] ?? $leave->status }}
                        </span>
                    </td>
                    <td class="px-5 py-3">
                        <div class="flex items-center justify-end gap-2">
                            @if($leave->status === 'en_attente')
                            <form method="POST" action="{{ route('hr.leaves.status', $leave) }}">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="approuve">
                                <button class="text-xs text-green-600 hover:text-green-800 font-medium">Approuver</button>
                            </form>
                            <form method="POST" action="{{ route('hr.leaves.status', $leave) }}">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="refuse">
                                <button class="text-xs text-red-500 hover:text-red-700 font-medium">Refuser</button>
                            </form>
                            @endif
                            <form method="POST" action="{{ route('hr.leaves.destroy', $leave) }}" onsubmit="return confirm('Supprimer cette demande ?')">
                                @csrf @method('DELETE')
                                <button class="text-gray-400 hover:text-red-500 text-xs">Suppr.</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-5 py-12 text-center text-gray-400">Aucune demande de congé</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-5 py-3">{{ $leaves->links() }}</div>
    </div>
</div>
@endsection
