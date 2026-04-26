@extends('layouts.app')
@section('title', 'CRM — Prospects')
@section('page-title', 'CRM & Gestion des prospects')
@section('header-actions')
    <a href="{{ route('crm.create') }}" class="btn-primary">+ Nouveau prospect</a>
@endsection

@section('content')
<div class="mt-2 space-y-4">
    <!-- Pipeline stats -->
    <div class="grid grid-cols-3 md:grid-cols-6 gap-3">
        @foreach([
            ['key' => 'nouveau',     'label' => 'Nouveau',     'color' => 'bg-gray-100 text-gray-700'],
            ['key' => 'contacte',    'label' => 'Contacté',    'color' => 'bg-blue-100 text-blue-700'],
            ['key' => 'qualifie',    'label' => 'Qualifié',    'color' => 'bg-yellow-100 text-yellow-700'],
            ['key' => 'proposition', 'label' => 'Proposition', 'color' => 'bg-purple-100 text-purple-700'],
            ['key' => 'gagne',       'label' => 'Gagné',       'color' => 'bg-green-100 text-green-700'],
            ['key' => 'perdu',       'label' => 'Perdu',       'color' => 'bg-red-100 text-red-600'],
        ] as $stage)
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 text-center">
            <span class="inline-block px-2 py-0.5 text-xs font-semibold rounded-full {{ $stage['color'] }} mb-2">{{ $stage['label'] }}</span>
            <p class="text-2xl font-bold text-gray-800">{{ $stats[$stage['key']] }}</p>
        </div>
        @endforeach
    </div>

    <!-- Valeurs pipeline -->
    <div class="grid grid-cols-2 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex items-center gap-4">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white shrink-0" style="background-color: var(--clr-p)">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
            </div>
            <div>
                <p class="text-xs text-gray-500">Valeur pipeline (qualifiés + propositions)</p>
                <p class="text-lg font-bold text-gray-800">{{ money($pipeline_value) }}</p>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex items-center gap-4">
            <div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <p class="text-xs text-gray-500">Valeur affaires gagnées</p>
                <p class="text-lg font-bold text-green-700">{{ money($won_value) }}</p>
            </div>
        </div>
    </div>

    <!-- Liste prospects -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="px-5 py-4 border-b border-gray-100 flex flex-wrap items-center justify-between gap-3">
            <h3 class="font-semibold text-gray-800 text-sm">Prospects</h3>
            <form method="GET" class="flex gap-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Nom, entreprise..." class="form-input text-xs w-44">
                <select name="status" class="form-input text-xs w-36">
                    <option value="">Tous statuts</option>
                    <option value="nouveau" {{ request('status') === 'nouveau' ? 'selected' : '' }}>Nouveau</option>
                    <option value="contacte" {{ request('status') === 'contacte' ? 'selected' : '' }}>Contacté</option>
                    <option value="qualifie" {{ request('status') === 'qualifie' ? 'selected' : '' }}>Qualifié</option>
                    <option value="proposition" {{ request('status') === 'proposition' ? 'selected' : '' }}>Proposition</option>
                    <option value="gagne" {{ request('status') === 'gagne' ? 'selected' : '' }}>Gagné</option>
                    <option value="perdu" {{ request('status') === 'perdu' ? 'selected' : '' }}>Perdu</option>
                </select>
                <button type="submit" class="btn-primary text-xs">Filtrer</button>
            </form>
        </div>
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100">
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Contact</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Source</th>
                    <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Valeur estimée</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Statut</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Créé le</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($leads as $lead)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3">
                        <p class="font-medium text-gray-800">{{ $lead->name }}</p>
                        <p class="text-xs text-gray-400">{{ $lead->company ?: '' }} {{ $lead->phone ? '· ' . $lead->phone : '' }}</p>
                    </td>
                    <td class="px-5 py-3 text-gray-500 text-xs">{{ $lead->source ?: '—' }}</td>
                    <td class="px-5 py-3 text-right font-medium text-gray-700">
                        {{ $lead->estimated_value ? money($lead->estimated_value)  : '—' }}
                    </td>
                    <td class="px-5 py-3">
                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full
                            {{ $lead->status === 'nouveau' ? 'bg-gray-100 text-gray-700' :
                               ($lead->status === 'contacte' ? 'bg-blue-100 text-blue-700' :
                               ($lead->status === 'qualifie' ? 'bg-yellow-100 text-yellow-700' :
                               ($lead->status === 'proposition' ? 'bg-purple-100 text-purple-700' :
                               ($lead->status === 'gagne' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600')))) }}">
                            @label($lead->status)
                        </span>
                    </td>
                    <td class="px-5 py-3 text-gray-500 text-xs">{{ $lead->created_at->format('d/m/Y') }}</td>
                    <td class="px-5 py-3 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('crm.edit', $lead) }}" class="text-blue-500 hover:text-blue-700 text-xs">Modifier</a>
                            <form method="POST" action="{{ route('crm.destroy', $lead) }}" onsubmit="return confirm('Supprimer ?')">
                                @csrf @method('DELETE')
                                <button class="text-red-400 hover:text-red-600 text-xs">Suppr.</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-5 py-12 text-center text-gray-400">Aucun prospect enregistré</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-5 py-3">{{ $leads->links() }}</div>
    </div>
</div>
@endsection
