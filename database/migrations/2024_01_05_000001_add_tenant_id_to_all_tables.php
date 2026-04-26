<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    private array $tables = [
        'products', 'stock_movements', 'production_orders', 'machines',
        'maintenances', 'clients', 'invoices', 'quality_controls',
        'energy_consumptions', 'mrp_plans', 'shipments', 'employees',
        'accounting_transactions', 'suppliers', 'leads', 'purchase_orders',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            if (Schema::hasTable($table) && !Schema::hasColumn($table, 'tenant_id')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->unsignedBigInteger('tenant_id')->nullable()->after('id');
                    $t->index('tenant_id');
                });
            }
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'tenant_id')) {
                Schema::table($table, function (Blueprint $t) use ($table) {
                    $t->dropIndex("{$table}_tenant_id_index");
                    $t->dropColumn('tenant_id');
                });
            }
        }
    }
};
