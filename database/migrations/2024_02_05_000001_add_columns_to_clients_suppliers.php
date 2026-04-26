<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('segment', 20)->default('nouveau')->after('country'); // nouveau, regulier, vip
            $table->unsignedInteger('loyalty_points')->default(0)->after('segment');
            $table->decimal('credit_limit', 15, 2)->default(0)->after('loyalty_points');
            $table->decimal('balance_due', 15, 2)->default(0)->after('credit_limit');
        });

        Schema::table('suppliers', function (Blueprint $table) {
            $table->unsignedTinyInteger('reliability_score')->default(5)->after('notes'); // 1-10
            $table->string('payment_terms', 50)->nullable()->after('reliability_score'); // net30, net60, etc.
            $table->decimal('balance_due', 15, 2)->default(0)->after('payment_terms');
        });

        Schema::table('shipments', function (Blueprint $table) {
            $table->unsignedBigInteger('carrier_id')->nullable()->after('carrier');
            $table->foreign('carrier_id')->references('id')->on('carriers')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['segment', 'loyalty_points', 'credit_limit', 'balance_due']);
        });
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn(['reliability_score', 'payment_terms', 'balance_due']);
        });
        Schema::table('shipments', function (Blueprint $table) {
            $table->dropForeign(['carrier_id']);
            $table->dropColumn('carrier_id');
        });
    }
};
