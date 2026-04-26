<?php

namespace App\Http\Controllers;

use App\Models\Machine;
use App\Models\Maintenance;
use App\Models\User;
use Illuminate\Http\Request;

class MaintenanceController extends Controller
{
    private function tid(): int { return auth()->user()->tenant_id; }

    public function index(Request $request)
    {
        $query = Maintenance::with(['machine', 'technician'])->where('tenant_id', $this->tid());
        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('type'))   $query->where('type', $request->type);
        $maintenances = $query->orderBy('scheduled_date')->paginate(15)->withQueryString();
        return view('maintenance.index', compact('maintenances'));
    }

    public function create()
    {
        $machines    = Machine::where('tenant_id', $this->tid())->orderBy('name')->get();
        $technicians = User::where('tenant_id', $this->tid())->where('is_active', true)->orderBy('name')->get();
        return view('maintenance.create', compact('machines', 'technicians'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'machine_id'     => 'required|exists:machines,id',
            'type'           => 'required|in:preventive,corrective',
            'description'    => 'required|string',
            'scheduled_date' => 'required|date',
            'cost'           => 'nullable|numeric|min:0',
            'technician_id'  => 'nullable|exists:users,id',
        ]);
        $data['tenant_id'] = $this->tid();
        $data['status']    = 'planifie';
        Maintenance::create($data);
        return redirect()->route('maintenance.index')->with('success', 'Maintenance planifiée.');
    }

    public function edit(Maintenance $maintenance)
    {
        abort_if($maintenance->tenant_id !== $this->tid(), 403);
        $machines    = Machine::where('tenant_id', $this->tid())->orderBy('name')->get();
        $technicians = User::where('tenant_id', $this->tid())->where('is_active', true)->orderBy('name')->get();
        return view('maintenance.edit', compact('maintenance', 'machines', 'technicians'));
    }

    public function update(Request $request, Maintenance $maintenance)
    {
        abort_if($maintenance->tenant_id !== $this->tid(), 403);
        $data = $request->validate([
            'machine_id'     => 'required|exists:machines,id',
            'type'           => 'required|in:preventive,corrective',
            'description'    => 'required|string',
            'scheduled_date' => 'required|date',
            'completed_date' => 'nullable|date',
            'cost'           => 'nullable|numeric|min:0',
            'status'         => 'required|in:planifie,en_cours,termine',
            'technician_id'  => 'nullable|exists:users,id',
        ]);

        if ($data['status'] === 'termine' && !$maintenance->completed_date && empty($data['completed_date'])) {
            $data['completed_date'] = now()->toDateString();
        }
        if ($data['status'] === 'termine') {
            $maintenance->machine->update(['status' => 'actif']);
        }

        $maintenance->update($data);
        return redirect()->route('maintenance.index')->with('success', 'Maintenance mise à jour.');
    }

    public function destroy(Maintenance $maintenance)
    {
        abort_if($maintenance->tenant_id !== $this->tid(), 403);
        $maintenance->delete();
        return redirect()->route('maintenance.index')->with('success', 'Maintenance supprimée.');
    }
}
