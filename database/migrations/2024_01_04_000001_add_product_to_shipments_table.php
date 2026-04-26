<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete()->after('reference');
            $table->decimal('quantity', 12, 2)->nullable()->after('product_id');
            $table->boolean('stock_processed')->default(false)->after('notes');
        });

        // Ajouter le type 'retour' à l'enum
        DB::statement("ALTER TABLE shipments MODIFY COLUMN type ENUM('entrant','sortant','retour') NOT NULL");
    }

    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('product_id');
            $table->dropColumn(['quantity', 'stock_processed']);
        });
        DB::statement("ALTER TABLE shipments MODIFY COLUMN type ENUM('entrant','sortant') NOT NULL");
    }
};
