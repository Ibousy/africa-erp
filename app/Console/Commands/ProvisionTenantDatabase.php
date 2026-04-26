<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Services\TenantDatabaseService;
use Illuminate\Console\Command;

class ProvisionTenantDatabase extends Command
{
    protected $signature   = 'tenant:provision {id? : Tenant ID (leave empty to provision all)}';
    protected $description = 'Create an isolated database for a tenant';

    public function handle(TenantDatabaseService $service): void
    {
        $id = $this->argument('id');

        $tenants = $id
            ? Tenant::where('id', $id)->get()
            : Tenant::whereNull('db_name')->get();

        if ($tenants->isEmpty()) {
            $this->info('No tenants to provision.');
            return;
        }

        foreach ($tenants as $tenant) {
            $this->line("Provisioning <info>{$tenant->company_name}</info> (id={$tenant->id})...");
            try {
                $db = $service->provision($tenant);
                $this->info("  ✓ Database created: {$db}");
            } catch (\Throwable $e) {
                $this->error("  ✗ Failed: " . $e->getMessage());
            }
        }
    }
}
