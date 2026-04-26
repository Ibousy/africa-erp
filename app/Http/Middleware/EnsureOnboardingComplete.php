<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureOnboardingComplete
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        if ($user->isSuperAdmin()) return $next($request);

        if (!$user->tenant_id) {
            return redirect()->route('onboarding.step2');
        }

        $tenant = $user->tenant;

        if (!$tenant->onboarding_complete) {
            if (!$tenant->modules()->exists()) {
                return redirect()->route('onboarding.step3');
            }
            return redirect()->route('onboarding.step4');
        }

        return $next($request);
    }
}
