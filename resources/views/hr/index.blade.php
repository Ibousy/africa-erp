@extends('layouts.app')
@section('title', 'Ressources Humaines')
@section('page-title', 'Ressources Humaines')
@section('header-actions')
    <a href="{{ route('hr.create') }}" class="btn-primary">+ Nouvel employé</a>
@endsection

@section('content')
<div class="mt-2 space-y-4">
    <!-- Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs text-gray-500 uppercase font-semibold">Total effectif</p>
            <p class="text-2xl font-bold text-gray-800 mt-1">{{ $stats['total'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs text-gray-500 uppercase font-semibold">Actifs</p>
            <p class="text-2xl font-bold text-green-600 mt-1">{{ $stats['actif'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs text-gray-500 uppercase font-semibold">En congé</p>
            <p class="text-2xl font-bold text-yellow-600 mt-1">{{ $stats['conge'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs text-gray-500 uppercase font-semibold">Partis</p>
            <p class="text-2xl font-bold text-gray-400 mt-1">{{ $stats['quitte'] }}</p>
        </div>
    </div>

    <!-- Filtres + Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="px-5 py-4 border-b border-gray-100 flex flex-wrap items-center justify-between gap-3">
            <h3 class="font-semibold text-gray-800 text-sm">Employés</h3>
            <form method="GET" class="flex gap-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Nom, poste..." class="form-input text-xs w-44">
                <select name="status" class="form-input text-xs w-36">
                    <option value="">Tous les statuts</option>
                    <option value="actif" {{ request('status') === 'actif' ? 'selected' : '' }}>Actif</option>
                    <option value="conge" {{ request('status') === 'conge' ? 'selected' : '' }}>En congé</option>
                    <option value="quitte" {{ request('status') === 'quitte' ? 'selected' : '' }}>Quitté</option>
                </select>
                <button type="submit" class="btn-primary text-xs">Filtrer</button>
            </form>
        </div>
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100">
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Employé</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Poste / Service</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Contact</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Embauche</th>
                    <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Salaire (FCFA)</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Statut</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($employees as $emp)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold shrink-0" style="background-color: var(--clr-p)">
                                {{ strtoupper(substr($emp->name, 0, 1)) }}
                            </div>
                            <span class="font-medium text-gray-800">{{ $emp->name }}</span>
                        </div>
                    </td>
                    <td class="px-5 py-3">
                        <p class="text-gray-800">{{ $emp->position }}</p>
                        <p class="text-xs text-gray-400">{{ $emp->department ?: '—' }}</p>
                    </td>
                    <td class="px-5 py-3 text-gray-600 text-xs">
                        <p>{{ $emp->phone ?: '—' }}</p>
                        <p>{{ $emp->email ?: '' }}</p>
                    </td>
                    <td class="px-5 py-3 text-gray-600">{{ $emp->hire_date->format('d/m/Y') }}</td>
                    <td class="px-5 py-3 text-right font-medium text-gray-800">
                        {{ $emp->salary ? money($emp->salary) : '—' }}
                    </td>
                    <td class="px-5 py-3">
                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full
                            {{ $emp->status === 'actif' ? 'bg-green-100 text-green-700' :
                               ($emp->status === 'conge' ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-500') }}">
                            @label($emp->status)
                        </span>
                    </td>
                    <td class="px-5 py-3 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('hr.edit', $emp) }}" class="text-blue-500 hover:text-blue-700 text-xs">Modifier</a>
                            <form method="POST" action="{{ route('hr.destroy', $emp) }}" onsubmit="return confirm('Supprimer cet employé ?')">
                                @csrf @method('DELETE')
                                <button class="text-red-400 hover:text-red-600 text-xs">Suppr.</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-5 py-12 text-center text-gray-400">Aucun employé enregistré</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-5 py-3">{{ $employees->links() }}</div>
    </div>
</div>
@endsection
