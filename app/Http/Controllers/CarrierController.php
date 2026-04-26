<?php

namespace App\Http\Controllers;

use App\Models\Carrier;
use Illuminate\Http\Request;

class CarrierController extends Controller
{
    private function tid(): int { return auth()->user()->tenant_id; }

    public function index()
    {
        $carriers = Carrier::where('tenant_id', $this->tid())->orderBy('name')->paginate(20);
        return view('logistics.carriers.index', compact('carriers'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'         => 'required|string|max:191',
            'phone'        => 'nullable|string|max:30',
            'email'        => 'nullable|email|max:191',
            'vehicle_type' => 'nullable|in:camion,fourgon,moto,bateau,avion,autre',
            'plate_number' => 'nullable|string|max:30',
            'notes'        => 'nullable|string',
        ]);

        $data['tenant_id'] = $this->tid();
        $data['status']    = 'actif';
        Carrier::create($data);

        return back()->with('success', 'Transporteur ajouté.');
    }

    public function update(Request $request, Carrier $carrier)
    {
        abort_if($carrier->tenant_id !== $this->tid(), 403);
        $data = $request->validate([
            'status' => 'required|in:actif,inactif',
        ]);
        $carrier->update($data);
        return back()->with('success', 'Statut mis à jour.');
    }

    public function destroy(Carrier $carrier)
    {
        abort_if($carrier->tenant_id !== $this->tid(), 403);
        $carrier->delete();
        return back()->with('success', 'Transporteur supprimé.');
    }
}
