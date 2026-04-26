@extends('layouts.app')
@section('title', 'Clients')
@section('page-title', 'Clients')
@section('header-actions')
    <a href="{{ route('clients.create') }}" class="btn-primary">+ Nouveau client</a>
@endsection

@section('content')
<div class="mt-2 space-y-4">
    <form method="GET" class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex gap-3">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher..." class="form-input w-64">
        <button type="submit" class="btn-primary">Rechercher</button>
        <a href="{{ route('clients.index') }}" class="btn-secondary">Reset</a>
    </form>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100">
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Code</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Nom</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Contact</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Téléphone</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Ville</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Pays</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($clients as $client)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3 font-mono text-xs text-gray-500">{{ $client->code }}</td>
                    <td class="px-5 py-3 font-medium text-gray-800">{{ $client->name }}</td>
                    <td class="px-5 py-3 text-gray-600">{{ $client->contact_person ?? '—' }}</td>
                    <td class="px-5 py-3 text-gray-600">{{ $client->phone ?? '—' }}</td>
                    <td class="px-5 py-3 text-gray-600">{{ $client->city ?? '—' }}</td>
                    <td class="px-5 py-3 text-gray-600">{{ $client->country }}</td>
                    <td class="px-5 py-3 text-right whitespace-nowrap">
                        <a href="{{ route('clients.show', $client) }}" class="text-gray-500 hover:text-gray-700 text-xs font-medium mr-3">Fiche</a>
                        <a href="{{ route('invoices.create') }}?client_id={{ $client->id }}" class="text-green-500 hover:text-green-700 text-xs font-medium mr-3">Facturer</a>
                        <a href="{{ route('clients.edit', $client) }}" class="text-blue-500 hover:text-blue-700 text-xs font-medium mr-3">Modifier</a>
                        <form method="POST" action="{{ route('clients.destroy', $client) }}" class="inline" onsubmit="return confirm('Supprimer ?')">
                            @csrf @method('DELETE')
                            <button class="text-red-500 hover:text-red-700 text-xs font-medium">Supprimer</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-5 py-10 text-center text-gray-400">Aucun client</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-5 py-3">{{ $clients->links() }}</div>
    </div>
</div>
@endsection
