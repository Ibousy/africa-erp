@extends('layouts.app')
@section('title', 'Évaluations des performances')
@section('page-title', 'Évaluations des performances')
@section('header-actions')
    <a href="{{ route('hr.performance.create') }}" class="btn-primary">+ Nouvelle évaluation</a>
@endsection

@section('content')
<div class="mt-2 space-y-4">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-semibold text-gray-800 text-sm">Évaluations</h3>
            <form method="GET" class="flex gap-2">
                <input type="text" name="period" value="{{ request('period') }}" placeholder="ex: 2024-Q1" class="form-input text-xs w-32">
                <button type="submit" class="btn-primary text-xs">Filtrer</button>
            </form>
        </div>
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100">
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Employé</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Période</th>
                    <th class="text-center px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Ponctualité</th>
                    <th class="text-center px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Productivité</th>
                    <th class="text-center px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Qualité</th>
                    <th class="text-center px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Équipe</th>
                    <th class="text-center px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Initiative</th>
                    <th class="text-center px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Moy.</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($reviews as $r)
                @php $avg = $r->averageScore(); @endphp
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3">
                        <p class="font-medium text-gray-800">{{ $r->employee->name }}</p>
                        <p class="text-xs text-gray-400">{{ $r->employee->position }}</p>
                    </td>
                    <td class="px-5 py-3 font-mono text-sm text-gray-700">{{ $r->period }}</td>
                    @foreach(['punctuality_score', 'productivity_score', 'quality_score', 'teamwork_score', 'initiative_score'] as $sc)
                    <td class="px-5 py-3 text-center">
                        <span class="text-sm font-semibold {{ $r->$sc >= 4 ? 'text-green-600' : ($r->$sc >= 3 ? 'text-yellow-600' : 'text-red-500') }}">{{ $r->$sc }}/5</span>
                    </td>
                    @endforeach
                    <td class="px-5 py-3 text-center">
                        <span class="px-2 py-1 rounded-full text-xs font-bold {{ $avg >= 4 ? 'bg-green-100 text-green-700' : ($avg >= 3 ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-600') }}">
                            {{ $avg }}/5
                        </span>
                    </td>
                    <td class="px-5 py-3 text-right">
                        <form method="POST" action="{{ route('hr.performance.destroy', $r) }}" onsubmit="return confirm('Supprimer ?')">
                            @csrf @method('DELETE')
                            <button class="text-red-400 hover:text-red-600 text-xs">Suppr.</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" class="px-5 py-12 text-center text-gray-400">Aucune évaluation</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-5 py-3">{{ $reviews->links() }}</div>
    </div>
</div>
@endsection
