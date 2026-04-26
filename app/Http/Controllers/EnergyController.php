<?php

namespace App\Http\Controllers;

use App\Models\EnergyConsumption;
use App\Models\Machine;
use Illuminate\Http\Request;

class EnergyController extends Controller
{
    private function tid(): int { return auth()->user()->tenant_id; }

    public function index(Request $request)
    {
        $query = EnergyConsumption::with('machine')->where('tenant_id', $this->tid());
        if ($request->filled('machine_id')) $query->where('machine_id', $request->machine_id);
        if ($request->filled('month')) {
            [$year, $month] = explode('-', $request->month);
            $query->whereYear('date', $year)->whereMonth('date', $month);
        }

        $consumptions = $query->orderByDesc('date')->paginate(20)->withQueryString();
        $machines     = Machine::where('tenant_id', $this->tid())->orderBy('name')->get();

        $base = EnergyConsumption::where('tenant_id', $this->tid());
        $stats = [
            'total_kwh_month'  => (clone $base)->whereMonth('date', now()->month)->whereYear('date', now()->year)->sum('kwh_consumed'),
            'total_cost_month' => (clone $base)->whereMonth('date', now()->month)->whereYear('date', now()->year)->sum('total_cost'),
            'total_kwh_year'   => (clone $base)->whereYear('date', now()->year)->sum('kwh_consumed'),
            'total_cost_year'  => (clone $base)->whereYear('date', now()->year)->sum('total_cost'),
        ];

        $by_machine = Machine::where('tenant_id', $this->tid())
            ->withSum(['energyConsumptions as kwh_month' => fn($q) => $q->whereMonth('date', now()->month)->whereYear('date', now()->year)], 'kwh_consumed')
            ->withSum(['energyConsumptions as cost_month' => fn($q) => $q->whereMonth('date', now()->month)->whereYear('date', now()->year)], 'total_cost')
            ->orderByDesc('kwh_month')->get();

        return view('energy.index', compact('consumptions', 'machines', 'stats', 'by_machine'));
    }

    public function create()
    {
        $machines = Machine::where('tenant_id', $this->tid())->orderBy('name')->get();
        return view('energy.create', compact('machines'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'machine_id'   => 'required|exists:machines,id',
            'date'         => 'required|date',
            'kwh_consumed' => 'required|numeric|min:0',
            'cost_per_kwh' => 'required|numeric|min:0',
            'hours_used'   => 'nullable|integer|min:0',
            'notes'        => 'nullable|string',
        ]);

        $machine = Machine::findOrFail($data['machine_id']);
        abort_if($machine->tenant_id !== $this->tid(), 403);

        $data['tenant_id']  = $this->tid();
        $data['total_cost'] = round($data['kwh_consumed'] * $data['cost_per_kwh'], 2);
        EnergyConsumption::create($data);

        return redirect()->route('energy.index')->with('success', 'Consommation enregistrée.');
    }

    public function edit(EnergyConsumption $energy)
    {
        abort_if($energy->tenant_id !== $this->tid(), 403);
        $machines = Machine::where('tenant_id', $this->tid())->orderBy('name')->get();
        return view('energy.edit', compact('energy', 'machines'));
    }

    public function update(Request $request, EnergyConsumption $energy)
    {
        abort_if($energy->tenant_id !== $this->tid(), 403);

        $data = $request->validate([
            'machine_id'   => 'required|exists:machines,id',
            'date'         => 'required|date',
            'kwh_consumed' => 'required|numeric|min:0',
            'cost_per_kwh' => 'required|numeric|min:0',
            'hours_used'   => 'nullable|integer|min:0',
            'notes'        => 'nullable|string',
        ]);

        $machine = Machine::findOrFail($data['machine_id']);
        abort_if($machine->tenant_id !== $this->tid(), 403);

        $data['total_cost'] = round($data['kwh_consumed'] * $data['cost_per_kwh'], 2);
        $energy->update($data);

        return redirect()->route('energy.index')->with('success', 'Consommation mise à jour.');
    }

    public function destroy(EnergyConsumption $energy)
    {
        abort_if($energy->tenant_id !== $this->tid(), 403);
        $energy->delete();
        return redirect()->route('energy.index')->with('success', 'Enregistrement supprimé.');
    }
}
