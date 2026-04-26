@extends('layouts.app')
@section('title', 'Enregistrer consommation')
@section('page-title', 'Enregistrer consommation énergie')

@section('content')
<div class="max-w-lg mt-2">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <form method="POST" action="{{ route('energy.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="form-label">Machine *</label>
                <select name="machine_id" required class="form-input">
                    <option value="">-- Sélectionner --</option>
                    @foreach($machines as $m)
                        <option value="{{ $m->id }}" {{ old('machine_id') == $m->id ? 'selected' : '' }}>
                            {{ $m->name }} {{ $m->power_kw ? '(' . $m->power_kw . ' kW)' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Date *</label>
                    <input type="date" name="date" value="{{ old('date', date('Y-m-d')) }}" required class="form-input">
                </div>
                <div>
                    <label class="form-label">Heures d'utilisation</label>
                    <input type="number" name="hours_used" value="{{ old('hours_used', 8) }}" min="0" max="24" class="form-input">
                </div>
                <div>
                    <label class="form-label">Consommation (kWh) *</label>
                    <input type="number" name="kwh_consumed" value="{{ old('kwh_consumed') }}" min="0" step="0.001" required class="form-input" id="kwh" oninput="calcCost()">
                </div>
                <div>
                    <label class="form-label">Coût / kWh (FCFA) *</label>
                    <input type="number" name="cost_per_kwh" value="{{ old('cost_per_kwh', 100) }}" min="0" step="0.01" required class="form-input" id="cpkwh" oninput="calcCost()">
                </div>
            </div>
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg px-4 py-3 flex items-center justify-between">
                <span class="text-sm text-yellow-800">Coût total estimé:</span>
                <span id="total-cost" class="font-bold text-yellow-900 text-lg">0 FCFA</span>
            </div>
            <div>
                <label class="form-label">Notes</label>
                <textarea name="notes" rows="2" class="form-input">{{ old('notes') }}</textarea>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary">Enregistrer</button>
                <a href="{{ route('energy.index') }}" class="btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function calcCost() {
    const kwh = parseFloat(document.getElementById('kwh').value) || 0;
    const cpkwh = parseFloat(document.getElementById('cpkwh').value) || 0;
    const total = kwh * cpkwh;
    document.getElementById('total-cost').textContent = new Intl.NumberFormat('fr-FR').format(Math.round(total)) + ' FCFA';
}
calcCost();
</script>
@endpush
