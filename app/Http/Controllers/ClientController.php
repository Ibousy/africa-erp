<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ClientController extends Controller
{
    private function tid(): int { return auth()->user()->tenant_id; }

    public function index(Request $request)
    {
        $query = Client::where('tenant_id', $this->tid());
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('phone', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }
        $clients = $query->latest()->paginate(15)->withQueryString();
        return view('clients.index', compact('clients'));
    }

    public function create()
    {
        return view('clients.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code'           => 'required|string|unique:clients',
            'name'           => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone'          => 'nullable|string|max:20',
            'email'          => 'nullable|email|max:255',
            'address'        => 'nullable|string',
            'city'           => 'nullable|string|max:100',
            'country'        => 'required|string|max:100',
        ]);
        $data['tenant_id'] = $this->tid();
        Client::create($data);
        return redirect()->route('clients.index')->with('success', 'Client créé.');
    }

    public function show(Client $client)
    {
        abort_if($client->tenant_id !== $this->tid(), 403);
        $client->load(['quotes' => fn($q) => $q->latest()->limit(10),
                       'customerOrders' => fn($q) => $q->latest()->limit(10),
                       'payments' => fn($q) => $q->latest()->limit(10)]);
        return view('clients.show', compact('client'));
    }

    public function edit(Client $client)
    {
        abort_if($client->tenant_id !== $this->tid(), 403);
        return view('clients.edit', compact('client'));
    }

    public function update(Request $request, Client $client)
    {
        abort_if($client->tenant_id !== $this->tid(), 403);
        $data = $request->validate([
            'code'           => ['required', 'string', Rule::unique('clients')->ignore($client->id)],
            'name'           => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone'          => 'nullable|string|max:20',
            'email'          => 'nullable|email|max:255',
            'address'        => 'nullable|string',
            'city'           => 'nullable|string|max:100',
            'country'        => 'required|string|max:100',
        ]);
        $client->update($data);
        return redirect()->route('clients.index')->with('success', 'Client mis à jour.');
    }

    public function destroy(Client $client)
    {
        abort_if($client->tenant_id !== $this->tid(), 403);
        $client->delete();
        return redirect()->route('clients.index')->with('success', 'Client supprimé.');
    }
}
