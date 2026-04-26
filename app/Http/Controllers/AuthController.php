<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    // Map module key → named route for the default landing page
    private const MODULE_ROUTES = [
        'stock'       => 'products.index',
        'production'  => 'production.index',
        'mrp'         => 'mrp.index',
        'quality'     => 'quality.index',
        'maintenance' => 'machines.index',
        'logistics'   => 'logistics.index',
        'purchases'   => 'purchases.index',
        'sales'       => 'clients.index',
        'crm'         => 'crm.index',
        'accounting'  => 'accounting.index',
        'hr'          => 'hr.index',
        'energy'      => 'energy.index',
        'users'       => 'users.index',
    ];

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            /** @var \App\Models\User $user */
            $user = Auth::user();

            // Bloquer les comptes désactivés
            if (!$user->is_active) {
                Auth::logout();
                $request->session()->invalidate();
                return back()
                    ->withErrors(['email' => 'Ce compte est désactivé. Contactez votre administrateur.'])
                    ->onlyInput('email');
            }

            $request->session()->regenerate();

            if ($user->isSuperAdmin()) {
                return redirect()->route('superadmin.index');
            }

            return redirect()->intended($user->deptHomeRoute());
        }

        return back()->withErrors(['email' => 'Email ou mot de passe incorrect.'])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
