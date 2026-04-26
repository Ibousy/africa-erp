<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use Illuminate\Http\Request;

class CrmController extends Controller
{
    private function tid(): int { return auth()->user()->tenant_id; }

    public function index(Request $request)
    {
        $query = Lead::where('tenant_id', $this->tid());
        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('search')) $query->where(function ($q) use ($request) {
            $q->where('name', 'like', '%' . $request->search . '%')
              ->orWhere('company', 'like', '%' . $request->search . '%');
        });

        $leads = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        $base  = Lead::where('tenant_id', $this->tid());
        $stats = [
            'nouveau'     => (clone $base)->where('status', 'nouveau')->count(),
            'contacte'    => (clone $base)->where('status', 'contacte')->count(),
            'qualifie'    => (clone $base)->where('status', 'qualifie')->count(),
            'proposition' => (clone $base)->where('status', 'proposition')->count(),
            'gagne'       => (clone $base)->where('status', 'gagne')->count(),
            'perdu'       => (clone $base)->where('status', 'perdu')->count(),
        ];

        $pipeline_value = (clone $base)->whereIn('status', ['qualifie', 'proposition'])->sum('estimated_value');
        $won_value      = (clone $base)->where('status', 'gagne')->sum('estimated_value');

        return view('crm.index', compact('leads', 'stats', 'pipeline_value', 'won_value'));
    }

    public function create()
    {
        return view('crm.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'            => 'required|string|max:191',
            'company'         => 'nullable|string|max:191',
            'email'           => 'nullable|email|max:191',
            'phone'           => 'nullable|string|max:30',
            'source'          => 'nullable|string|max:50',
            'status'          => 'required|in:nouveau,contacte,qualifie,proposition,gagne,perdu',
            'estimated_value' => 'nullable|numeric|min:0',
            'notes'           => 'nullable|string',
        ]);

        $data['tenant_id'] = $this->tid();
        Lead::create($data);
        return redirect()->route('crm.index')->with('success', 'Prospect ajouté.');
    }

    public function edit(Lead $crm)
    {
        abort_if($crm->tenant_id !== $this->tid(), 403);
        return view('crm.edit', compact('crm'));
    }

    public function update(Request $request, Lead $crm)
    {
        abort_if($crm->tenant_id !== $this->tid(), 403);
        $data = $request->validate([
            'name'            => 'required|string|max:191',
            'company'         => 'nullable|string|max:191',
            'email'           => 'nullable|email|max:191',
            'phone'           => 'nullable|string|max:30',
            'source'          => 'nullable|string|max:50',
            'status'          => 'required|in:nouveau,contacte,qualifie,proposition,gagne,perdu',
            'estimated_value' => 'nullable|numeric|min:0',
            'notes'           => 'nullable|string',
        ]);

        $crm->update($data);
        return redirect()->route('crm.index')->with('success', 'Prospect mis à jour.');
    }

    public function destroy(Lead $crm)
    {
        abort_if($crm->tenant_id !== $this->tid(), 403);
        $crm->delete();
        return redirect()->route('crm.index')->with('success', 'Prospect supprimé.');
    }
}
