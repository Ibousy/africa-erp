<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    private static array $moduleLabels = [
        'stock'       => 'Stock & Inventaire',
        'production'  => 'Production',
        'mrp'         => 'MRP — Planification',
        'quality'     => 'Contrôle Qualité',
        'maintenance' => 'Maintenance',
        'logistics'   => 'Logistique',
        'purchases'   => 'Achats',
        'sales'       => 'Commercial',
        'crm'         => 'CRM',
        'accounting'  => 'Comptabilité',
        'hr'          => 'RH',
        'energy'      => 'Énergie',
        'users'       => 'Utilisateurs',
    ];

    private function tenantId(): int
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        return $user->tenant_id;
    }

    private function enabledModules(): array
    {
        /** @var \App\Models\User $user */
        $user   = Auth::user();
        $tenant = $user->load('tenant.modules')->tenant;
        $keys   = $tenant->modules->where('enabled', true)->pluck('module')->toArray();
        return array_filter(self::$moduleLabels, fn($k) => in_array($k, $keys), ARRAY_FILTER_USE_KEY);
    }

    public function index()
    {
        $users = User::where('tenant_id', $this->tenantId())->latest()->paginate(20);
        return view('users.index', compact('users'));
    }

    public function create()
    {
        $modules = $this->enabledModules();
        return view('users.create', compact('modules'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'           => 'required|string|max:191',
            'email'          => 'required|email|unique:users,email|max:191',
            'password'       => 'required|min:6|confirmed',
            'role'           => 'required|in:admin,manager,operateur',
            'phone'          => 'nullable|string|max:30',
            'default_module' => 'nullable|string|max:50',
            'department'     => 'nullable|string|max:50',
        ]);

        User::create([
            'tenant_id'      => $this->tenantId(),
            'name'           => $data['name'],
            'email'          => $data['email'],
            'password'       => Hash::make($data['password']),
            'role'           => $data['role'],
            'phone'          => $data['phone'] ?? null,
            'default_module' => $data['default_module'] ?: null,
            'department'     => $data['department'] ?: null,
            'is_active'      => true,
        ]);

        return redirect()->route('users.index')->with('success', 'Utilisateur créé. Il peut maintenant se connecter.');
    }

    public function edit(User $user)
    {
        abort_if($user->tenant_id !== $this->tenantId(), 403);
        $modules = $this->enabledModules();
        return view('users.edit', compact('user', 'modules'));
    }

    public function update(Request $request, User $user)
    {
        abort_if($user->tenant_id !== $this->tenantId(), 403);

        $data = $request->validate([
            'name'           => 'required|string|max:191',
            'email'          => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'role'           => 'required|in:admin,manager,operateur',
            'phone'          => 'nullable|string|max:30',
            'default_module' => 'nullable|string|max:50',
            'department'     => 'nullable|string|max:50',
        ]);

        if ($request->filled('password')) {
            $request->validate(['password' => 'min:6|confirmed']);
            $data['password'] = Hash::make($request->password);
        }

        $data['is_active']      = $request->boolean('is_active', true);
        $data['default_module'] = $data['default_module'] ?: null;
        $data['department']     = $data['department'] ?: null;
        $user->update($data);

        return redirect()->route('users.index')->with('success', 'Utilisateur mis à jour.');
    }

    public function destroy(User $user)
    {
        abort_if($user->tenant_id !== $this->tenantId(), 403);

        /** @var \App\Models\User|null $me */
        $me = Auth::user();
        if ($user->id === $me?->id) {
            return back()->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        $user->delete();
        return redirect()->route('users.index')->with('success', 'Utilisateur supprimé.');
    }
}
