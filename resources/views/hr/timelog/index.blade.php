@extends('layouts.app')
@section('title', 'Pointage')
@section('page-title', 'Pointage & Présences')

@section('content')
<div class="mt-2 space-y-4">
    <!-- Stats du jour -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs text-gray-500 uppercase font-semibold">Effectif total</p>
            <p class="text-2xl font-bold text-gray-800 mt-1">{{ $stats['total'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs text-gray-500 uppercase font-semibold">Présents</p>
            <p class="text-2xl font-bold text-green-600 mt-1">{{ $stats['present'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs text-gray-500 uppercase font-semibold">Absents</p>
            <p class="text-2xl font-bold text-red-500 mt-1">{{ $stats['absent'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs text-gray-500 uppercase font-semibold">Retards</p>
            <p class="text-2xl font-bold text-yellow-500 mt-1">{{ $stats['retard'] }}</p>
        </div>
    </div>

    <!-- Pointage rapide collectif -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-semibold text-gray-800 text-sm">Pointage collectif rapide</h3>
            <input type="date" id="bulk-date" value="{{ $date }}" class="form-input text-xs w-40">
        </div>
        <form method="POST" action="{{ route('hr.timelogs.bulk') }}" id="bulk-form">
            @csrf
            <input type="hidden" name="date" id="bulk-date-hidden" value="{{ $date }}">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50">
                            <th class="text-left px-5 py-2 text-xs font-semibold text-gray-500">Employé</th>
                            <th class="text-left px-5 py-2 text-xs font-semibold text-gray-500">Statut</th>
                            <th class="text-left px-5 py-2 text-xs font-semibold text-gray-500">Entrée</th>
                            <th class="text-left px-5 py-2 text-xs font-semibold text-gray-500">Sortie</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($employees as $i => $emp)
                        <input type="hidden" name="logs[{{ $i }}][eid]" value="{{ $emp->id }}">
                        <tr class="border-b border-gray-50 hover:bg-gray-50">
                            <td class="px-5 py-2 font-medium text-gray-800">{{ $emp->name }}</td>
                            <td class="px-5 py-2">
                                <select name="logs[{{ $i }}][status]" class="form-input text-xs w-28">
                                    @foreach(\App\Models\TimeLog::STATUSES as $k => $v)
                                    <option value="{{ $k }}">{{ $v }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="px-5 py-2">
                                <input type="time" name="logs[{{ $i }}][check_in]" class="form-input text-xs w-28">
                            </td>
                            <td class="px-5 py-2">
                                <input type="time" name="logs[{{ $i }}][check_out]" class="form-input text-xs w-28">
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-5 py-3 flex justify-end">
                <button type="submit" class="btn-primary text-sm">Enregistrer le pointage</button>
            </div>
        </form>
    </div>

    <!-- Historique -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="px-5 py-4 border-b border-gray-100 flex flex-wrap items-center justify-between gap-3">
            <h3 class="font-semibold text-gray-800 text-sm">Historique des pointages</h3>
            <form method="GET" class="flex gap-2 flex-wrap">
                <input type="hidden" name="view" value="{{ request('view', 'day') }}">
                <input type="date" name="date" value="{{ $date }}" class="form-input text-xs">
                <select name="employee_id" class="form-input text-xs w-44">
                    <option value="">Tous les employés</option>
                    @foreach($employees as $emp)
                    <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>{{ $emp->name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn-primary text-xs">Filtrer</button>
            </form>
        </div>
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100">
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Employé</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Date</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Entrée</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Sortie</th>
                    <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Heures</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Statut</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($logs as $log)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3 font-medium text-gray-800">{{ $log->employee->name }}</td>
                    <td class="px-5 py-3 text-gray-600">{{ $log->date->format('d/m/Y') }}</td>
                    <td class="px-5 py-3 text-gray-600">{{ $log->check_in ?: '—' }}</td>
                    <td class="px-5 py-3 text-gray-600">{{ $log->check_out ?: '—' }}</td>
                    <td class="px-5 py-3 text-right font-medium text-gray-800">{{ $log->hours_worked ? $log->hours_worked . 'h' : '—' }}</td>
                    <td class="px-5 py-3">
                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full
                            {{ $log->status === 'present' ? 'bg-green-100 text-green-700' :
                               ($log->status === 'absent'  ? 'bg-red-100 text-red-600'   :
                               ($log->status === 'retard'  ? 'bg-yellow-100 text-yellow-700' : 'bg-blue-100 text-blue-700')) }}">
                            {{ \App\Models\TimeLog::STATUSES[$log->status] ?? $log->status }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-5 py-10 text-center text-gray-400">Aucun pointage enregistré</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-5 py-3">{{ $logs->links() }}</div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('bulk-date').addEventListener('change', function() {
    document.getElementById('bulk-date-hidden').value = this.value;
});
</script>
@endpush
@endsection
