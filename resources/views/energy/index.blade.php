@extends('layouts.app')
@section('title', 'Consommation Énergie')
@section('page-title', 'Consommation Énergétique')
@section('header-actions')
    <a href="{{ route('energy.create') }}" class="btn-primary">+ Enregistrer consommation</a>
@endsection

@section('content')
<div class="mt-2 space-y-4">
    <!-- Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs text-gray-500 uppercase font-semibold">kWh ce mois</p>
            <p class="text-2xl font-bold text-gray-800 mt-1">{{ number_format($stats['total_kwh_month'], 1) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs text-gray-500 uppercase font-semibold">Coût ce mois</p>
            <p class="text-2xl font-bold text-orange-600 mt-1">{{ money($stats['total_cost_month'], false) }}<span class="text-sm">FCFA</span></p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs text-gray-500 uppercase font-semibold">kWh cette année</p>
            <p class="text-2xl font-bold text-gray-800 mt-1">{{ number_format($stats['total_kwh_year'], 1) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs text-gray-500 uppercase font-semibold">Coût cette année</p>
            <p class="text-2xl font-bold text-orange-600 mt-1">{{ money($stats['total_cost_year'], false) }}<span class="text-sm">FCFA</span></p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <!-- Par machine -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100">
            <div class="px-5 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-800 text-sm">Consommation par machine (ce mois)</h3>
            </div>
            <div class="divide-y divide-gray-50">
                @foreach($by_machine as $m)
                @if($m->kwh_month > 0)
                <div class="px-5 py-3">
                    <div class="flex justify-between text-sm mb-1">
                        <span class="font-medium text-gray-800">{{ $m->name }}</span>
                        <span class="text-gray-600">{{ number_format($m->kwh_month, 1) }} kWh</span>
                    </div>
                    <div class="flex justify-between text-xs text-gray-400">
                        <span>{{ $m->code }}</span>
                        <span>{{ money($m->cost_month) }}</span>
                    </div>
                    @php $max = $by_machine->max('kwh_month'); $pct = $max > 0 ? ($m->kwh_month / $max * 100) : 0; @endphp
                    <div class="w-full bg-gray-100 rounded-full h-1.5 mt-1.5">
                        <div class="bg-yellow-400 h-1.5 rounded-full" style="width: {{ $pct }}%"></div>
                    </div>
                </div>
                @endif
                @endforeach
                @if($by_machine->where('kwh_month', '>', 0)->isEmpty())
                <div class="px-5 py-8 text-center text-gray-400 text-sm">Aucune donnée ce mois</div>
                @endif
            </div>
        </div>

        <!-- Historique -->
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100">
            <div class="px-5 py-4 border-b border-gray-100 flex flex-wrap items-center justify-between gap-3">
                <h3 class="font-semibold text-gray-800 text-sm">Historique</h3>
                <form method="GET" class="flex gap-2">
                    <select name="machine_id" class="form-input text-xs w-40">
                        <option value="">Toutes machines</option>
                        @foreach($machines as $m)
                            <option value="{{ $m->id }}" {{ request('machine_id') == $m->id ? 'selected' : '' }}>{{ $m->name }}</option>
                        @endforeach
                    </select>
                    <input type="month" name="month" value="{{ request('month', date('Y-m')) }}" class="form-input text-xs">
                    <button type="submit" class="btn-primary text-xs">Filtrer</button>
                </form>
            </div>
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100">
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Date</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Machine</th>
                        <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase">kWh</th>
                        <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Heures</th>
                        <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Coût (FCFA)</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($consumptions as $c)
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-3 text-gray-500">{{ $c->date->format('d/m/Y') }}</td>
                        <td class="px-5 py-3 font-medium text-gray-800">{{ $c->machine->name }}</td>
                        <td class="px-5 py-3 text-right text-gray-600">{{ number_format($c->kwh_consumed, 2) }}</td>
                        <td class="px-5 py-3 text-right text-gray-600">{{ $c->hours_used ?: '—' }}</td>
                        <td class="px-5 py-3 text-right font-semibold text-gray-800">{{ money($c->total_cost) }}</td>
                        <td class="px-5 py-3 text-right">
                            <div class="flex items-center justify-end gap-3">
                                <a href="{{ route('energy.edit', $c) }}" class="text-xs text-gray-500 hover:text-gray-700">Modifier</a>
                                <form method="POST" action="{{ route('energy.destroy', $c) }}" onsubmit="return confirm('Supprimer ?')">
                                    @csrf @method('DELETE')
                                    <button class="text-red-400 hover:text-red-600 text-xs">Suppr.</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-5 py-10 text-center text-gray-400">Aucune donnée</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="px-5 py-3">{{ $consumptions->links() }}</div>
        </div>
    </div>
</div>
@endsection
