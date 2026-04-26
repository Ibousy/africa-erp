@extends('layouts.app')
@section('title', 'Nouveau bulletin de paie')
@section('page-title', 'Nouveau bulletin de paie')

@section('content')
<div class="mt-2 max-w-lg">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <form method="POST" action="{{ route('hr.salaries.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="form-label">Employé *</label>
                <select name="employee_id" required class="form-input">
                    <option value="">Sélectionner...</option>
                    @foreach($employees as $emp)
                    <option value="{{ $emp->id }}" {{ old('employee_id') == $emp->id ? 'selected' : '' }}
                        data-salary="{{ $emp->salary }}">
                        {{ $emp->name }} — {{ $emp->position }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Période (mois) *</label>
                <input type="month" name="period" value="{{ old('period', now()->format('Y-m')) }}" required class="form-input">
            </div>
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="form-label">Salaire de base *</label>
                    <input type="number" name="base_salary" id="base_salary" value="{{ old('base_salary') }}" required min="0" step="1" class="form-input">
                </div>
                <div>
                    <label class="form-label">Primes / Bonus</label>
                    <input type="number" name="bonuses" id="bonuses" value="{{ old('bonuses', 0) }}" min="0" step="1" class="form-input" oninput="calcNet()">
                </div>
                <div>
                    <label class="form-label">Retenues</label>
                    <input type="number" name="deductions" id="deductions" value="{{ old('deductions', 0) }}" min="0" step="1" class="form-input" oninput="calcNet()">
                </div>
            </div>
            <div class="bg-gray-50 rounded-lg px-4 py-3 flex justify-between items-center">
                <span class="text-sm font-semibold text-gray-600">Salaire net calculé :</span>
                <span class="text-lg font-bold text-gray-900" id="net-display">0 FCFA</span>
            </div>
            <div>
                <label class="form-label">Notes</label>
                <textarea name="notes" rows="2" class="form-input">{{ old('notes') }}</textarea>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary flex-1 py-2.5">Créer le bulletin</button>
                <a href="{{ route('hr.salaries.index') }}" class="btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
const empSelect = document.querySelector('select[name="employee_id"]');
empSelect.addEventListener('change', function() {
    const opt = this.options[this.selectedIndex];
    const salary = parseFloat(opt.dataset.salary) || 0;
    document.getElementById('base_salary').value = salary;
    calcNet();
});

document.getElementById('base_salary').addEventListener('input', calcNet);

function calcNet() {
    const base = parseFloat(document.getElementById('base_salary').value) || 0;
    const bon  = parseFloat(document.getElementById('bonuses').value) || 0;
    const ded  = parseFloat(document.getElementById('deductions').value) || 0;
    const net  = base + bon - ded;
    document.getElementById('net-display').textContent = net.toLocaleString('fr-FR') + ' FCFA';
}
calcNet();
</script>
@endpush
@endsection
