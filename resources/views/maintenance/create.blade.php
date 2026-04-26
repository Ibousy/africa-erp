@extends('layouts.app')
@section('title', 'Planifier maintenance')
@section('page-title', 'Planifier une maintenance')

@section('content')
<div class="max-w-lg mt-2">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <form method="POST" action="{{ route('maintenance.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="form-label">Machine *</label>
                <select name="machine_id" required class="form-input">
                    <option value="">-- Sélectionner --</option>
                    @foreach($machines as $machine)
                        <option value="{{ $machine->id }}" {{ (old('machine_id') == $machine->id || request('machine_id') == $machine->id) ? 'selected' : '' }}>
                            {{ $machine->name }} ({{ $machine->code }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Type *</label>
                    <select name="type" required class="form-input">
                        <option value="preventive" {{ old('type', 'preventive') === 'preventive' ? 'selected' : '' }}>Préventive</option>
                        <option value="corrective" {{ old('type') === 'corrective' ? 'selected' : '' }}>Corrective</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Date planifiée *</label>
                    <input type="date" name="scheduled_date" value="{{ old('scheduled_date', date('Y-m-d')) }}" required class="form-input">
                </div>
            </div>
            <div>
                <label class="form-label">Description *</label>
                <textarea name="description" rows="3" required class="form-input" placeholder="Décrire les travaux à effectuer...">{{ old('description') }}</textarea>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Technicien</label>
                    <select name="technician_id" class="form-input">
                        <option value="">-- Non assigné --</option>
                        @foreach($technicians as $tech)
                            <option value="{{ $tech->id }}" {{ old('technician_id') == $tech->id ? 'selected' : '' }}>{{ $tech->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Coût estimé (FCFA)</label>
                    <input type="number" name="cost" value="{{ old('cost', 0) }}" min="0" class="form-input">
                </div>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary">Planifier</button>
                <a href="{{ route('maintenance.index') }}" class="btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection
