<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;

class HrController extends Controller
{
    private function tid(): int { return auth()->user()->tenant_id; }

    public function index(Request $request)
    {
        $query = Employee::where('tenant_id', $this->tid());
        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('search')) $query->where(function ($q) use ($request) {
            $q->where('name', 'like', '%' . $request->search . '%')
              ->orWhere('position', 'like', '%' . $request->search . '%')
              ->orWhere('department', 'like', '%' . $request->search . '%');
        });

        $employees = $query->orderBy('name')->paginate(20)->withQueryString();

        $base  = Employee::where('tenant_id', $this->tid());
        $stats = [
            'total'  => (clone $base)->count(),
            'actif'  => (clone $base)->where('status', 'actif')->count(),
            'conge'  => (clone $base)->where('status', 'conge')->count(),
            'quitte' => (clone $base)->where('status', 'quitte')->count(),
        ];

        return view('hr.index', compact('employees', 'stats'));
    }

    public function create()
    {
        return view('hr.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:191',
            'position'   => 'required|string|max:191',
            'department' => 'nullable|string|max:100',
            'email'      => 'nullable|email|max:191',
            'phone'      => 'nullable|string|max:30',
            'hire_date'  => 'required|date',
            'salary'     => 'nullable|numeric|min:0',
            'status'     => 'required|in:actif,conge,quitte',
            'notes'      => 'nullable|string',
        ]);

        $data['tenant_id'] = $this->tid();
        Employee::create($data);
        return redirect()->route('hr.index')->with('success', 'Employé ajouté.');
    }

    public function edit(Employee $hr)
    {
        abort_if($hr->tenant_id !== $this->tid(), 403);
        return view('hr.edit', compact('hr'));
    }

    public function update(Request $request, Employee $hr)
    {
        abort_if($hr->tenant_id !== $this->tid(), 403);
        $data = $request->validate([
            'name'       => 'required|string|max:191',
            'position'   => 'required|string|max:191',
            'department' => 'nullable|string|max:100',
            'email'      => 'nullable|email|max:191',
            'phone'      => 'nullable|string|max:30',
            'hire_date'  => 'required|date',
            'salary'     => 'nullable|numeric|min:0',
            'status'     => 'required|in:actif,conge,quitte',
            'notes'      => 'nullable|string',
        ]);

        $hr->update($data);
        return redirect()->route('hr.index')->with('success', 'Employé mis à jour.');
    }

    public function destroy(Employee $hr)
    {
        abort_if($hr->tenant_id !== $this->tid(), 403);
        $hr->delete();
        return redirect()->route('hr.index')->with('success', 'Employé supprimé.');
    }
}
