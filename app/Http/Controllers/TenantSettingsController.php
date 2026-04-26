<?php

namespace App\Http\Controllers;

use App\Models\TenantModule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TenantSettingsController extends Controller
{
    private const ALL_MODULES = [
        'stock', 'production', 'mrp', 'quality', 'maintenance',
        'logistics', 'purchases', 'sales', 'crm', 'accounting', 'hr', 'energy', 'users',
    ];

    public function show()
    {
        $tenant = auth()->user()->load('tenant.modules')->tenant;
        return view('settings.company', compact('tenant'));
    }

    public function update(Request $request)
    {
        $tenant = auth()->user()->tenant;

        $data = $request->validate([
            'company_name' => 'required|string|max:191',
            'industry'     => 'nullable|string|max:191',
            'country'      => 'nullable|string|max:100',
            'city'         => 'nullable|string|max:100',
            'address'      => 'nullable|string|max:255',
            'phone'        => 'nullable|string|max:30',
            'email'        => 'nullable|email|max:191',
            'website'      => 'nullable|string|max:191',
            'logo'         => 'nullable|image|max:2048',
            'theme'        => 'required|in:orange,blue,green,purple,red',
            'modules'      => 'array',
        ]);

        $logoPath = $tenant->logo_path;
        if ($request->hasFile('logo')) {
            if ($logoPath) Storage::disk('public')->delete($logoPath);
            $logoPath = $request->file('logo')->store('logos', 'public');
        }

        $tenant->update([
            'company_name' => $data['company_name'],
            'industry'     => $data['industry'] ?? null,
            'country'      => $data['country'] ?? 'Sénégal',
            'city'         => $data['city'] ?? null,
            'address'      => $data['address'] ?? null,
            'phone'        => $data['phone'] ?? null,
            'email'        => $data['email'] ?? null,
            'website'      => $data['website'] ?? null,
            'logo_path'    => $logoPath,
            'theme'        => $data['theme'],
        ]);

        $selected = $request->input('modules', []);
        foreach (self::ALL_MODULES as $module) {
            TenantModule::updateOrCreate(
                ['tenant_id' => $tenant->id, 'module' => $module],
                ['enabled' => in_array($module, $selected)]
            );
        }

        return back()->with('success', 'Paramètres de l\'entreprise mis à jour.');
    }
}
