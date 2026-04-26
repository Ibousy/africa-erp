@extends('layouts.app')
@section('title', 'Paie & Salaires')
@section('page-title', 'Gestion de la Paie')
@section('header-actions')
    <a href="{{ route('hr.salaries.create') }}" class="btn-primary">+ Nouveau bulletin</a>
@endsection

@section('content')
<div class="mt-2 space-y-4">
    <!-- Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs text-gray-500 uppercase font-semibold">Masse salariale brute</p>
            <p class="text-xl font-bold text-gray-800 mt-1">{{ money($stats['total_brut'], false) }}<span class="text-xs">FCFA</span></p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs text-gray-500 uppercase font-semibold">Masse salariale nette</p>
            <p class="text-xl font-bold mt-1" style="color:var(--clr-p)">{{ money($stats['total_net'], false) }}<span class="text-xs">FCFA</span></p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs text-gray-500 uppercase font-semibold">Bulletins payés</p>
            <p class="text-2xl font-bold text-green-600 mt-1">{{ $stats['paye'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs text-gray-500 uppercase font-semibold">En attente</p>
            <p class="text-2xl font-bold text-yellow-500 mt-1">{{ $stats['en_attente'] }}</p>
        </div>
    </div>

    <!-- Génération en masse + filtres -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex flex-wrap gap-4 items-end">
        <form method="POST" action="{{ route('hr.salaries.generate') }}" class="flex gap-2 items-end">
            @csrf
            <div>
                <label class="form-label text-xs">Générer les bulletins pour la période</label>
                <input type="month" name="period" value="{{ $currentPeriod }}" class="form-input">
            </div>
            <button type="submit" class="btn-secondary text-sm">Générer automatiquement</button>
        </form>
        <form method="GET" class="flex gap-2 items-end ml-auto">
            <input type="month" name="period" value="{{ request('period') }}" class="form-input text-sm">
            <select name="status" class="form-input text-sm w-32">
                <option value="">Tous</option>
                <option value="brouillon" {{ request('status') === 'brouillon' ? 'selected' : '' }}>Brouillon</option>
                <option value="paye" {{ request('status') === 'paye' ? 'selected' : '' }}>Payé</option>
            </select>
            <button type="submit" class="btn-primary text-sm">Filtrer</button>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100">
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Employé</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Période</th>
                    <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Brut (FCFA)</th>
                    <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Primes</th>
                    <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Retenues</th>
                    <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Net (FCFA)</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Statut</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($salaries as $s)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3">
                        <p class="font-medium text-gray-800">{{ $s->employee->name }}</p>
                        <p class="text-xs text-gray-400">{{ $s->employee->position }}</p>
                    </td>
                    <td class="px-5 py-3 font-mono text-sm text-gray-700">{{ $s->period }}</td>
                    <td class="px-5 py-3 text-right text-gray-700">{{ money($s->base_salary) }}</td>
                    <td class="px-5 py-3 text-right text-green-600">+{{ money($s->bonuses) }}</td>
                    <td class="px-5 py-3 text-right text-red-500">-{{ money($s->deductions) }}</td>
                    <td class="px-5 py-3 text-right font-bold text-gray-900">{{ money($s->net_salary) }}</td>
                    <td class="px-5 py-3">
                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $s->status === 'paye' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                            {{ $s->status === 'paye' ? 'Payé' : 'Brouillon' }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-right">
                        <div class="flex items-center justify-end gap-3">
                            @if($s->status === 'brouillon')
                            <form method="POST" action="{{ route('hr.salaries.pay', $s) }}">
                                @csrf @method('PATCH')
                                <button class="text-xs text-green-600 hover:text-green-800 font-medium">Marquer payé</button>
                            </form>
                            @endif
                            <a href="{{ route('hr.salaries.pdf', $s) }}" target="_blank" class="text-xs text-blue-500 hover:text-blue-700 font-medium">PDF</a>
                            <form method="POST" action="{{ route('hr.salaries.destroy', $s) }}" onsubmit="return confirm('Supprimer ?')">
                                @csrf @method('DELETE')
                                <button class="text-xs text-red-400 hover:text-red-600">Suppr.</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="px-5 py-12 text-center text-gray-400">Aucun bulletin de paie</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-5 py-3">{{ $salaries->links() }}</div>
    </div>
</div>
@endsection
