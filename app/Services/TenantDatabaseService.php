<?php

namespace App\Services;

use App\Models\Tenant;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class TenantDatabaseService
{
    public function provision(Tenant $tenant): string
    {
        $dbName = 'erp_t' . $tenant->id;

        // Create the database
        DB::statement("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

        // Point tenant connection to the new database
        config(['database.connections.tenant.database' => $dbName]);
        DB::purge('tenant');

        // Run all migrations on the tenant's database
        Artisan::call('migrate', [
            '--database' => 'tenant',
            '--force'    => true,
            '--path'     => 'database/migrations',
        ]);

        // Save db_name on the tenant record
        $tenant->update(['db_name' => $dbName]);

        return $dbName;
    }

    public function drop(Tenant $tenant): void
    {
        if (!$tenant->db_name) return;

        DB::statement("DROP DATABASE IF EXISTS `{$tenant->db_name}`");
        $tenant->update(['db_name' => null]);
    }

    public function isProvisioned(Tenant $tenant): bool
    {
        return !empty($tenant->db_name);
    }
}
