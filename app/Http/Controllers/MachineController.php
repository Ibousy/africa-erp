<?php

namespace App\Http\Controllers;

use App\Models\Machine;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MachineController extends Controller
{
    private function tid(): int { return auth()->user()->tenant_id; }

    public function index(Request $request)
    {
        $query = Machine::where('tenant_id', $this->tid());
        if ($request->filled('status')) $query->where('status', $request->status);
        $machines = $query->latest()->paginate(15)->withQueryString();
        return view('machines.index', compact('machines'));
    }

    public function create()
    {
        return view('machines.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code'          => 'required|string|unique:machines',
            'name'          => 'required|string|max:255',
            'type'          => 'nullable|string|max:100',
            'description'   => 'nullable|string',
            'status'        => 'required|in:actif,en_panne,maintenance',
            'location'      => 'nullable|string|max:100',
            'purchase_date' => 'nullable|date',
            'power_kw'      => 'nullable|numeric|min:0',
        ]);
        $data['tenant_id'] = $this->tid();
        Machine::create($data);
        return redirect()->route('machines.index')->with('success', 'Machine créée.');
    }

    public function edit(Machine $machine)
    {
        abort_if($machine->tenant_id !== $this->tid(), 403);
        return view('machines.edit', compact('machine'));
    }

    public function update(Request $request, Machine $machine)
    {
        abort_if($machine->tenant_id !== $this->tid(), 403);
        $data = $request->validate([
            'code'          => ['required', 'string', Rule::unique('machines')->ignore($machine->id)],
            'name'          => 'required|string|max:255',
            'type'          => 'nullable|string|max:100',
            'description'   => 'nullable|string',
            'status'        => 'required|in:actif,en_panne,maintenance',
            'location'      => 'nullable|string|max:100',
            'purchase_date' => 'nullable|date',
            'power_kw'      => 'nullable|numeric|min:0',
        ]);
        $machine->update($data);
        return redirect()->route('machines.index')->with('success', 'Machine mise à jour.');
    }

    public function destroy(Machine $machine)
    {
        abort_if($machine->tenant_id !== $this->tid(), 403);
        $machine->delete();
        return redirect()->route('machines.index')->with('success', 'Machine supprimée.');
    }
}
