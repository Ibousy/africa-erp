@extends('layouts.app')
@section('title', 'Modifier maintenance')
@section('page-title', 'Modifier maintenance')

@section('content')
<div class="max-w-lg mt-2">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <form method="POST" action="{{ route('maintenance.update', $maintenance) }}" class="space-y-4">
            @csrf @method('PUT')
            <div>
                <label class="form-label">Machine *</label>
                <select name="machine_id" required class="form-input">
                    @foreach($machines as $machine)
                        <option value="{{ $machine->id }}" {{ old('machine_id', $maintenance->machine_id) == $machine->id ? 'selected' : '' }}>
                            {{ $machine->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Type *</label>
                    <select name="type" required class="form-input">
                        <option value="preventive" {{ old('type', $maintenance->type) === 'preventive' ? 'selected' : '' }}>Préventive</option>
                        <option value="corrective" {{ old('type', $maintenance->type) === 'corrective' ? 'selected' : '' }}>Corrective</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Statut *</label>
                    <select name="status" required class="form-input">
                        <option value="planifie" {{ old('status', $maintenance->status) === 'planifie' ? 'selected' : '' }}>Planifié</option>
                        <option value="en_cours" {{ old('status', $maintenance->status) === 'en_cours' ? 'selected' : '' }}>En cours</option>
                        <option value="termine" {{ old('status', $maintenance->status) === 'termine' ? 'selected' : '' }}>Terminé</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Date planifiée *</label>
                    <input type="date" name="scheduled_date" value="{{ old('scheduled_date', $maintenance->scheduled_date->format('Y-m-d')) }}" required class="form-input">
                </div>
                <div>
                    <label class="form-label">Date réalisée</label>
                    <input type="date" name="completed_date" value="{{ old('completed_date', $maintenance->completed_date?->format('Y-m-d')) }}" class="form-input">
                </div>
            </div>
            <div>
                <label class="form-label">Description *</label>
                <textarea name="description" rows="3" required class="form-input">{{ old('description', $maintenance->description) }}</textarea>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Technicien</label>
                    <select name="technician_id" class="form-input">
                        <option value="">-- Non assigné --</option>
                        @foreach($technicians as $tech)
                            <option value="{{ $tech->id }}" {{ old('technician_id', $maintenance->technician_id) == $tech->id ? 'selected' : '' }}>{{ $tech->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Coût réel (FCFA)</label>
                    <input type="number" name="cost" value="{{ old('cost', $maintenance->cost) }}" min="0" class="form-input">
                </div>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary">Enregistrer</button>
                <a href="{{ route('maintenance.index') }}" class="btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection
