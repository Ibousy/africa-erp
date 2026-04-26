@extends('layouts.app')
@section('title', 'Nouvelle demande de congé')
@section('page-title', 'Nouvelle demande de congé')

@section('content')
<div class="mt-2 max-w-xl">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <form method="POST" action="{{ route('hr.leaves.store') }}" class="space-y-4">
            @csrf

            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="form-label">Employé *</label>
                    <select name="employee_id" required class="form-input">
                        <option value="">— Sélectionner un employé —</option>
                        @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" {{ old('employee_id') == $emp->id ? 'selected' : '' }}>
                            {{ $emp->name }} — {{ $emp->position }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-span-2">
                    <label class="form-label">Type de congé *</label>
                    <select name="type" required class="form-input">
                        <option value="">— Sélectionner —</option>
                        @foreach(\App\Models\Leave::TYPES as $key => $label)
                        <option value="{{ $key }}" {{ old('type') === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="form-label">Date de début *</label>
                    <input type="date" name="start_date" value="{{ old('start_date') }}" required class="form-input" id="start_date">
                </div>
                <div>
                    <label class="form-label">Date de fin *</label>
                    <input type="date" name="end_date" value="{{ old('end_date') }}" required class="form-input" id="end_date">
                </div>

                <div class="col-span-2 bg-blue-50 border border-blue-100 rounded-lg px-4 py-2 text-sm text-blue-700 hidden" id="days-preview">
                    Durée estimée : <strong id="days-count">—</strong> jour(s) ouvrables
                </div>

                <div class="col-span-2">
                    <label class="form-label">Motif</label>
                    <textarea name="reason" rows="3" class="form-input" placeholder="Raison de la demande...">{{ old('reason') }}</textarea>
                </div>
                <div class="col-span-2">
                    <label class="form-label">Notes internes</label>
                    <textarea name="notes" rows="2" class="form-input" placeholder="Notes pour le RH...">{{ old('notes') }}</textarea>
                </div>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary flex-1 py-2.5">Enregistrer la demande →</button>
                <a href="{{ route('hr.leaves.index') }}" class="btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function calcDays() {
    const s = document.getElementById('start_date').value;
    const e = document.getElementById('end_date').value;
    if (!s || !e) return;
    const start = new Date(s), end = new Date(e);
    if (end < start) return;
    let days = 0;
    const cur = new Date(start);
    while (cur <= end) {
        const dow = cur.getDay();
        if (dow !== 0 && dow !== 6) days++;
        cur.setDate(cur.getDate() + 1);
    }
    document.getElementById('days-count').textContent = days;
    document.getElementById('days-preview').classList.remove('hidden');
}
document.getElementById('start_date').addEventListener('change', calcDays);
document.getElementById('end_date').addEventListener('change', calcDays);
</script>
@endpush
@endsection
