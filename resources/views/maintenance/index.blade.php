@extends('layouts.app')
@section('title', 'Maintenance')
@section('page-title', 'Planning Maintenance')
@section('header-actions')
    <a href="{{ route('maintenance.create') }}" class="btn-primary">+ Planifier maintenance</a>
@endsection

@section('content')
<div class="mt-2 space-y-4">
    <form method="GET" class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex flex-wrap gap-3">
        <select name="status" class="form-input w-40">
            <option value="">Tous statuts</option>
            <option value="planifie" {{ request('status') === 'planifie' ? 'selected' : '' }}>Planifié</option>
            <option value="en_cours" {{ request('status') === 'en_cours' ? 'selected' : '' }}>En cours</option>
            <option value="termine" {{ request('status') === 'termine' ? 'selected' : '' }}>Terminé</option>
        </select>
        <select name="type" class="form-input w-40">
            <option value="">Tous types</option>
            <option value="preventive" {{ request('type') === 'preventive' ? 'selected' : '' }}>Préventive</option>
            <option value="corrective" {{ request('type') === 'corrective' ? 'selected' : '' }}>Corrective</option>
        </select>
        <button type="submit" class="btn-primary">Filtrer</button>
        <a href="{{ route('maintenance.index') }}" class="btn-secondary">Reset</a>
    </form>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100">
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Machine</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Type</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Description</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Date planifiée</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Technicien</th>
                    <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Coût</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Statut</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($maintenances as $m)
                <tr class="hover:bg-gray-50 {{ $m->status === 'planifie' && $m->scheduled_date->isPast() ? 'bg-red-50' : '' }}">
                    <td class="px-5 py-3 font-medium text-gray-800">{{ $m->machine->name }}</td>
                    <td class="px-5 py-3">
                        <span class="px-2 py-1 text-xs font-medium rounded-full {{ $m->type === 'preventive' ? 'bg-blue-100 text-blue-700' : 'bg-orange-100 text-orange-700' }}">
                            @label($m->type)
                        </span>
                    </td>
                    <td class="px-5 py-3 text-gray-600 max-w-xs truncate">{{ $m->description }}</td>
                    <td class="px-5 py-3 text-gray-600">
                        {{ $m->scheduled_date->format('d/m/Y') }}
                        @if($m->status === 'planifie' && $m->scheduled_date->isPast())
                            <span class="text-red-500 text-xs ml-1">En retard</span>
                        @endif
                    </td>
                    <td class="px-5 py-3 text-gray-500">{{ $m->technician->name ?? '—' }}</td>
                    <td class="px-5 py-3 text-right text-gray-600">{{ $m->cost > 0 ? money($m->cost)  : '—' }}</td>
                    <td class="px-5 py-3">
                        <span class="px-2 py-1 text-xs font-medium rounded-full
                            {{ $m->status === 'termine' ? 'bg-green-100 text-green-700' :
                               ($m->status === 'en_cours' ? 'bg-blue-100 text-blue-700' : 'bg-yellow-100 text-yellow-700') }}">
                            @label($m->status)
                        </span>
                    </td>
                    <td class="px-5 py-3 text-right whitespace-nowrap">
                        <a href="{{ route('maintenance.edit', $m) }}" class="text-blue-500 hover:text-blue-700 text-xs font-medium mr-3">Modifier</a>
                        <form method="POST" action="{{ route('maintenance.destroy', $m) }}" class="inline" onsubmit="return confirm('Supprimer ?')">
                            @csrf @method('DELETE')
                            <button class="text-red-500 hover:text-red-700 text-xs font-medium">Supprimer</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="px-5 py-10 text-center text-gray-400">Aucune maintenance planifiée</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-5 py-3">{{ $maintenances->links() }}</div>
    </div>
</div>
@endsection
