<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureTenantActive
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();
        if ($user?->isSuperAdmin()) return $next($request);

        $tenant = $user?->tenant;
        if ($tenant && !$tenant->isActive()) {
            return redirect()->route('subscription.expired');
        }

        return $next($request);
    }
}
