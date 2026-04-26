@extends('layouts.app')
@section('title', 'Transporteurs & Véhicules')
@section('page-title', 'Transporteurs & Véhicules')

@section('content')
<div class="mt-2 grid grid-cols-1 lg:grid-cols-3 gap-4">
    <!-- Formulaire ajout -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <h3 class="font-semibold text-gray-800 mb-4 text-sm">Ajouter un transporteur</h3>
        <form method="POST" action="{{ route('carriers.store') }}" class="space-y-3">
            @csrf
            <div>
                <label class="form-label text-xs">Nom / Société *</label>
                <input type="text" name="name" required class="form-input text-xs">
            </div>
            <div>
                <label class="form-label text-xs">Téléphone</label>
                <input type="text" name="phone" class="form-input text-xs">
            </div>
            <div>
                <label class="form-label text-xs">Email</label>
                <input type="email" name="email" class="form-input text-xs">
            </div>
            <div>
                <label class="form-label text-xs">Type de véhicule</label>
                <select name="vehicle_type" class="form-input text-xs">
                    <option value="">—</option>
                    @foreach(\App\Models\Carrier::VEHICLE_TYPES as $k => $v)
                    <option value="{{ $k }}">{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label text-xs">Plaque d'immatriculation</label>
                <input type="text" name="plate_number" class="form-input text-xs" placeholder="ex: DK-1234-AB">
            </div>
            <div>
                <label class="form-label text-xs">Notes</label>
                <textarea name="notes" rows="2" class="form-input text-xs"></textarea>
            </div>
            <button type="submit" class="btn-primary w-full text-sm">Ajouter</button>
        </form>
    </div>

    <!-- Liste -->
    <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="px-5 py-4 border-b border-gray-100">
            <h3 class="font-semibold text-gray-800 text-sm">Transporteurs ({{ $carriers->total() }})</h3>
        </div>
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100">
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Nom</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Contact</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Véhicule</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Statut</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($carriers as $c)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3 font-medium text-gray-800">{{ $c->name }}</td>
                    <td class="px-5 py-3 text-xs text-gray-500">
                        <p>{{ $c->phone ?: '—' }}</p>
                        <p>{{ $c->email ?: '' }}</p>
                    </td>
                    <td class="px-5 py-3 text-xs text-gray-600">
                        <p>{{ \App\Models\Carrier::VEHICLE_TYPES[$c->vehicle_type] ?? '—' }}</p>
                        @if($c->plate_number)<p class="font-mono text-gray-400">{{ $c->plate_number }}</p>@endif
                    </td>
                    <td class="px-5 py-3">
                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $c->status === 'actif' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                            {{ $c->status === 'actif' ? 'Actif' : 'Inactif' }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <form method="POST" action="{{ route('carriers.update', $c) }}">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="{{ $c->status === 'actif' ? 'inactif' : 'actif' }}">
                                <button class="text-xs text-blue-500 hover:text-blue-700">{{ $c->status === 'actif' ? 'Désactiver' : 'Activer' }}</button>
                            </form>
                            <form method="POST" action="{{ route('carriers.destroy', $c) }}" onsubmit="return confirm('Supprimer ?')">
                                @csrf @method('DELETE')
                                <button class="text-xs text-red-400 hover:text-red-600">Suppr.</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-5 py-12 text-center text-gray-400">Aucun transporteur</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-5 py-3">{{ $carriers->links() }}</div>
    </div>
</div>
@endsection
