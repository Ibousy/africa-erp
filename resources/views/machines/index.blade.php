@extends('layouts.app')
@section('title', 'Machines')
@section('page-title', 'Parc machines')
@section('header-actions')
    <a href="{{ route('machines.create') }}" class="btn-primary">+ Nouvelle machine</a>
@endsection

@section('content')
<div class="mt-2 space-y-4">
    <form method="GET" class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex gap-3">
        <select name="status" class="form-input w-44">
            <option value="">Tous statuts</option>
            <option value="actif" {{ request('status') === 'actif' ? 'selected' : '' }}>Actif</option>
            <option value="en_panne" {{ request('status') === 'en_panne' ? 'selected' : '' }}>En panne</option>
            <option value="maintenance" {{ request('status') === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
        </select>
        <button type="submit" class="btn-primary">Filtrer</button>
        <a href="{{ route('machines.index') }}" class="btn-secondary">Reset</a>
    </form>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        @forelse($machines as $machine)
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-start justify-between mb-3">
                <div>
                    <p class="font-semibold text-gray-800">{{ $machine->name }}</p>
                    <p class="text-xs text-gray-400 font-mono">{{ $machine->code }}</p>
                </div>
                <span class="px-2 py-1 text-xs font-medium rounded-full
                    {{ $machine->status === 'actif' ? 'bg-green-100 text-green-700' :
                       ($machine->status === 'en_panne' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700') }}">
                    @label($machine->status)
                </span>
            </div>
            <div class="space-y-1 text-xs text-gray-500 mb-4">
                @if($machine->type)<div>Type: <span class="text-gray-700">{{ $machine->type }}</span></div>@endif
                @if($machine->location)<div>Emplacement: <span class="text-gray-700">{{ $machine->location }}</span></div>@endif
                @if($machine->power_kw)<div>Puissance: <span class="text-gray-700">{{ $machine->power_kw }} kW</span></div>@endif
                @if($machine->purchase_date)<div>Achetée: <span class="text-gray-700">{{ $machine->purchase_date->format('d/m/Y') }}</span></div>@endif
            </div>
            <div class="flex gap-2">
                <a href="{{ route('machines.edit', $machine) }}" class="btn-secondary text-xs flex-1 text-center">Modifier</a>
                <a href="{{ route('maintenance.create') }}?machine_id={{ $machine->id }}" class="btn-primary text-xs flex-1 text-center">Maintenance</a>
            </div>
        </div>
        @empty
        <div class="col-span-3 bg-white rounded-xl shadow-sm border border-gray-100 py-16 text-center text-gray-400">
            Aucune machine enregistrée
        </div>
        @endforelse
    </div>
    <div>{{ $machines->links() }}</div>
</div>
@endsection
