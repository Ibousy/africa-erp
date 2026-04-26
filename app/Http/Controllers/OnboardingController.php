<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\TenantModule;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class OnboardingController extends Controller
{
    private const ALL_MODULES = ['stock', 'production', 'quality', 'maintenance', 'sales', 'energy', 'users'];

    public function showStep1()
    {
        return view('onboarding.step1');
    }

    public function step1(Request $request)
    {
        $data = $request->validate([
            'name'                  => 'required|string|max:191',
            'email'                 => 'required|email|unique:users,email|max:191',
            'password'              => 'required|confirmed|min:8',
            'phone'                 => 'nullable|string|max:30',
        ]);

        $user = User::create([
            'name'      => $data['name'],
            'email'     => $data['email'],
            'password'  => Hash::make($data['password']),
            'phone'     => $data['phone'] ?? null,
            'role'      => 'admin',
            'is_active' => true,
        ]);

        Auth::login($user);

        return redirect()->route('onboarding.step2');
    }

    public function showStep2()
    {
        if (auth()->user()->tenant_id) {
            return redirect()->route('onboarding.step3');
        }
        return view('onboarding.step2');
    }

    public function step2(Request $request)
    {
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
        ]);

        $logoPath = null;
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('logos', 'public');
        }

        $tenant = Tenant::create([
            'company_name'       => $data['company_name'],
            'slug'               => Str::slug($data['company_name']) . '-' . Str::random(6),
            'logo_path'          => $logoPath,
            'industry'           => $data['industry'] ?? null,
            'country'            => $data['country'] ?? 'Sénégal',
            'city'               => $data['city'] ?? null,
            'address'            => $data['address'] ?? null,
            'phone'              => $data['phone'] ?? null,
            'email'              => $data['email'] ?? null,
            'website'            => $data['website'] ?? null,
            'theme'              => 'orange',
            'plan'               => 'trial',
            'status'             => 'trial',
            'trial_ends_at'      => now()->addDays(14),
            'onboarding_complete' => false,
        ]);

        auth()->user()->update(['tenant_id' => $tenant->id]);

        return redirect()->route('onboarding.step3');
    }

    public function showStep3()
    {
        $tenant = auth()->user()->tenant;
        if (!$tenant) return redirect()->route('onboarding.step2');
        return view('onboarding.step3', compact('tenant'));
    }

    public function step3(Request $request)
    {
        $tenant = auth()->user()->tenant;
        if (!$tenant) return redirect()->route('onboarding.step2');

        $selected = $request->input('modules', []);

        $tenant->modules()->delete();
        foreach (self::ALL_MODULES as $module) {
            TenantModule::create([
                'tenant_id' => $tenant->id,
                'module'    => $module,
                'enabled'   => in_array($module, $selected),
            ]);
        }

        return redirect()->route('onboarding.step4');
    }

    public function showStep4()
    {
        $tenant = auth()->user()->tenant;
        if (!$tenant) return redirect()->route('onboarding.step2');
        return view('onboarding.step4', compact('tenant'));
    }

    public function step4(Request $request)
    {
        $tenant = auth()->user()->tenant;
        if (!$tenant) return redirect()->route('onboarding.step2');

        $request->validate(['theme' => 'required|in:orange,blue,green,purple,red']);

        $tenant->update([
            'theme'               => $request->theme,
            'onboarding_complete' => true,
        ]);

        return redirect()->route('onboarding.success');
    }

    public function success()
    {
        $tenant = auth()->user()->load('tenant.modules')->tenant;
        return view('onboarding.success', compact('tenant'));
    }
}
