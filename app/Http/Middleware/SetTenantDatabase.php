<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SetTenantDatabase
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check() && !auth()->user()->isSuperAdmin()) {
            $tenant = auth()->user()->tenant;

            if ($tenant && $tenant->db_name) {
                // Switch default connection to this tenant's isolated database
                config(['database.connections.tenant.database' => $tenant->db_name]);
                DB::purge('tenant');
                DB::setDefaultConnection('tenant');
            }
        }

        return $next($request);
    }
}
